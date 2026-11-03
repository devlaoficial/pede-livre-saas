<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Controllers;

use App\Domain\Bot\Services\EvolutionApiService;
use App\Domain\Clientes\Models\Cliente;
use App\Domain\Entregas\Services\CalculadoraFreteService;
use App\Domain\Mesas\Models\Mesa;
use App\Domain\Pagamentos\Models\Pagamento;
use App\Domain\Pagamentos\Services\MercadoPagoPixService;
use App\Domain\Pedidos\Events\PedidoCriado;
use App\Domain\Pedidos\Models\Pedido;
use App\Domain\Pedidos\Models\PedidoEvento;
use App\Domain\Pedidos\Models\PedidoItem;
use App\Domain\Pedidos\Requests\CalcularFreteRequest;
use App\Domain\Pedidos\Requests\CriarPedidoRequest;
use App\Domain\Pedidos\Requests\ValidarCupomRequest;
use App\Domain\Pedidos\Resources\PedidoPublicoTrackingResource;
use App\Domain\Pedidos\Services\CalculoPedidoService;
use App\Support\Security\BlindIndexService;
use App\Support\Tenancy\LojaContext;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class CheckoutController
{
    public function __construct(
        private readonly CalculadoraFreteService $calculadoraFrete,
        private readonly CalculoPedidoService $calculoPedido,
        private readonly MercadoPagoPixService $pixService,
        private readonly BlindIndexService $blindIndex,
        private readonly LojaContext $context,
        private readonly EvolutionApiService $evolutionApi
    ) {}

    public function calcularFrete(CalcularFreteRequest $request): JsonResponse
    {
        $loja = $this->context->getLoja();
        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        try {
            $resultado = $this->calculadoraFrete->calcular(
                $loja,
                $request->only(['cep', 'bairro', 'lat', 'lng']),
                $request->integer('subtotal_centavos')
            );

            return response()->json($resultado);
        } catch (DomainException $e) {
            return response()->json([
                'error' => 'FORA_DO_RAIO',
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function validarCupom(ValidarCupomRequest $request): JsonResponse
    {
        $loja = $this->context->getLoja();
        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        $throttleKey = 'cupom_validar:' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'error' => 'TOO_MANY_REQUESTS',
                'message' => "Muitas tentativas de validação de cupom. Tente em {$seconds} segundos.",
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }
        RateLimiter::hit($throttleKey, 60);

        $telefoneBidx = null;
        if ($request->filled('telefone')) {
            $telefoneBidx = $this->blindIndex->generateForPhone($request->string('telefone')->toString());
        }

        try {
            $cupom = $this->calculoPedido->validarCupom(
                $loja,
                $request->string('codigo')->toString(),
                $request->integer('subtotal_centavos'),
                $request->string('canal')->toString(),
                $telefoneBidx
            );

            $descontoCalculado = 0;
            if ($cupom->tipo === 'percentual') {
                $descontoCalculado = (int) round(($request->integer('subtotal_centavos') * $cupom->valor) / 100);
            } elseif ($cupom->tipo === 'valor') {
                $descontoCalculado = min($request->integer('subtotal_centavos'), $cupom->valor);
            }

            return response()->json([
                'valido' => true,
                'codigo' => $cupom->codigo,
                'tipo' => $cupom->tipo,
                'desconto_centavos' => $descontoCalculado,
                'message' => 'Cupom aplicado com sucesso!',
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'valido' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function criarPedido(CriarPedidoRequest $request): JsonResponse
    {
        // 1. Bot Honeypot Protection
        if (!empty($request->input('website'))) {
            return response()->json(['error' => 'BOT_DETECTED'], Response::HTTP_FORBIDDEN);
        }

        $loja = $this->context->getLoja();
        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        // 2. Anti-flood Rate Limiting (5 orders/min per IP)
        $throttleKey = 'checkout_orders:' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'error' => 'TOO_MANY_ORDERS',
                'message' => "Muitos pedidos enviados. Aguarde {$seconds} segundos antes de tentar novamente.",
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }
        RateLimiter::hit($throttleKey, 60);

        // 3. Distributed Redis Atomic Lock by Idempotency Key
        $idempotencyKey = $request->string('idempotency_key')->toString();
        $lock = Cache::lock("pedido:lock:{$idempotencyKey}", 10);

        if (!$lock->get()) {
            return response()->json([
                'error' => 'CONCURRENT_REQUEST',
                'message' => 'Requisição em processamento. Aguarde alguns instantes.',
            ], Response::HTTP_CONFLICT);
        }

        try {
            $dados = $request->validated();

            // Resolve Customer with normalized E.164 phone + 9th-digit candidate matching
            $telefoneRaw = (string) $dados['cliente']['telefone'];
            $telefoneNormalizado = $this->blindIndex->normalizePhone($telefoneRaw);
            $telefoneBidx = $this->blindIndex->generateForPhone($telefoneNormalizado);
            $candidatosBidx = $this->blindIndex->generatePhoneCandidates($telefoneRaw);
            $dados['cliente_telefone_bidx'] = $telefoneBidx;

            /** @var Cliente|null $cliente */
            $cliente = Cliente::where('loja_id', $loja->id)
                ->whereIn('telefone_bidx', $candidatosBidx)
                ->first();

            if ($cliente === null) {
                $cliente = Cliente::create([
                    'loja_id' => $loja->id,
                    'telefone_bidx' => $telefoneBidx,
                    'nome' => trim((string) $dados['cliente']['nome']),
                    'telefone' => $telefoneNormalizado,
                    'email' => $dados['cliente']['email'] ?? null,
                    'email_bidx' => isset($dados['cliente']['email']) ? $this->blindIndex->generateForEmail($dados['cliente']['email']) : null,
                    'aceita_marketing' => true,
                    'consentimento_em' => now(),
                ]);
            } else {
                // Keep customer name and normalized phone updated
                $cliente->update([
                    'nome' => trim((string) ($dados['cliente']['nome'] ?: $cliente->nome)),
                    'telefone' => $telefoneNormalizado,
                ]);
            }

            $dados['cliente_saldo_cashback_centavos'] = $cliente->saldo_cashback_centavos;

            // Resolve Table if canal === 'mesa'
            $mesaId = null;
            $comandaId = null;
            if ($dados['canal'] === 'mesa') {
                $mesa = Mesa::where('loja_id', $loja->id)
                    ->where('qr_token', $dados['mesa_token'])
                    ->with('comandaAtiva')
                    ->first();

                if ($mesa === null) {
                    throw new DomainException('Mesa selecionada não encontrada ou token inválido.');
                }
                $mesaId = $mesa->id;
                $comandaId = $mesa->comandaAtiva?->id;
            }

            // Execute 100% Server-Side Calculations
            $calculo = $this->calculoPedido->calcular($loja, $dados);

            // Validate change (troco_para) for cash orders
            if ($dados['forma_pagamento'] === 'dinheiro') {
                if (isset($dados['troco_para']) && $dados['troco_para'] !== null) {
                    if ($dados['troco_para'] < $calculo['total_centavos']) {
                        $minimoTroco = number_format($calculo['total_centavos'] / 100, 2, ',', '.');
                        throw new DomainException("O valor para troco deve ser maior ou igual ao total do pedido (R$ {$minimoTroco}).");
                    }
                }
            } else {
                $dados['troco_para'] = null;
            }

            $observacaoGeral = $dados['observacao'] ?? ($request->input('observacoes') ? (string) $request->input('observacoes') : null);

            // Execute atomic persistence in DB Transaction
            $pedido = DB::transaction(function () use ($loja, $cliente, $dados, $calculo, $mesaId, $comandaId, $idempotencyKey, $observacaoGeral): Pedido {
                $codigoPublico = strtoupper(Str::random(4));
                $tokenHash = hash_hmac('sha256', "{$loja->id}:{$codigoPublico}:" . now()->timestamp, (string) config('app.key'));

                $pedido = Pedido::create([
                    'loja_id' => $loja->id,
                    'codigo_publico' => $codigoPublico,
                    'tracking_token_hash' => $tokenHash,
                    'canal' => $dados['canal'],
                    'status' => 'novo',
                    'cliente_id' => $cliente->id,
                    'mesa_id' => $mesaId,
                    'comanda_id' => $comandaId,
                    'subtotal' => $calculo['subtotal_centavos'],
                    'taxa_entrega' => $calculo['taxa_entrega_centavos'],
                    'desconto' => $calculo['desconto_centavos'],
                    'desconto_cashback' => $calculo['desconto_cashback_centavos'],
                    'taxa_servico' => $calculo['taxa_servico_centavos'],
                    'total' => $calculo['total_centavos'],
                    'cupom_id' => $calculo['cupom']?->id,
                    'forma_pagamento' => $dados['forma_pagamento'],
                    'pagamento_status' => 'pendente',
                    'troco_para' => $dados['troco_para'] ?? null,
                    'observacao' => $observacaoGeral,
                    'endereco_json' => $dados['endereco'] ?? null,
                    'origem' => 'cardapio',
                ]);

                if ($calculo['cupom'] !== null) {
                    $calculo['cupom']->increment('usos');
                }

                foreach ($calculo['itens_processados'] as $item) {
                    PedidoItem::create([
                        'pedido_id' => $pedido->id,
                        'produto_id' => $item['produto_id'],
                        'nome_snapshot' => $item['nome_snapshot'],
                        'variacao_snapshot' => $item['variacao_snapshot'],
                        'qtd' => $item['qtd'],
                        'preco_unit' => $item['preco_unit'],
                        'adicionais_json' => $item['adicionais_json'],
                        'observacao' => $item['observacao'],
                        'total' => $item['total'],
                        'status_cozinha' => 'fila',
                    ]);
                }

                PedidoEvento::create([
                    'pedido_id' => $pedido->id,
                    'status_de' => null,
                    'status_para' => 'novo',
                    'observacao' => 'Pedido criado via cardápio online.',
                ]);

                Pagamento::create([
                    'loja_id' => $loja->id,
                    'pedido_id' => $pedido->id,
                    'metodo' => $dados['forma_pagamento'],
                    'valor' => $calculo['total_centavos'],
                    'status' => 'pendente',
                    'idempotency_key' => $idempotencyKey,
                ]);

                $cliente->increment('total_pedidos');
                $cliente->update(['ultimo_pedido_em' => now()]);

                if ($dados['forma_pagamento'] === 'pix') {
                    $pagamento = $pedido->pagamento;
                    if ($pagamento !== null) {
                        $this->pixService->gerarPix($loja, $pedido, $pagamento);
                    }
                }

                return $pedido;
            });

            // Send immediate real-time WhatsApp confirmation + live tracking link (and PIX code if PIX)
            try {
                $this->evolutionApi->notificarMudancaStatus($pedido->load(['cliente', 'itens', 'pagamento', 'loja']), 'novo');
            } catch (\Throwable) {}

            // Dispatch real-time WebSocket event for Merchant Kanban & Staff KDS (isolated so missing Reverb never blocks)
            try {
                if (config('broadcasting.default') && !in_array(config('broadcasting.default'), ['null', 'log'], true)) {
                    event(new PedidoCriado($pedido->load('cliente')));
                }
            } catch (\Throwable) {}

            $trackingSignature = hash_hmac('sha256', $pedido->tracking_token_hash, (string) config('app.key'));
            $trackingUrl = url("/p/{$loja->slug}/pedidos/{$pedido->codigo_publico}?t={$trackingSignature}");

            $pagamentoAtual = $pedido->pagamento;

            return response()->json([
                'message' => 'Pedido realizado com sucesso!',
                'pedido_id' => $pedido->id,
                'codigo_publico' => $pedido->codigo_publico,
                'total_centavos' => $pedido->total,
                'tracking_url' => $trackingUrl,
                'pix' => $pagamentoAtual && $pedido->forma_pagamento === 'pix' ? [
                    'copia_e_cola' => $pagamentoAtual->pix_copia_cola,
                    'qr_code_base64' => $pagamentoAtual->pix_qr_base64,
                    'expira_em' => $pagamentoAtual->expira_em?->toIso8601String(),
                ] : null,
            ], Response::HTTP_CREATED);
        } catch (DomainException $e) {
            return response()->json([
                'error' => 'REQUISICAO_INVALIDA',
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } finally {
            $lock->release();
        }
    }

    public function acompanharPedido(Request $request, string $slug, string $codigo): JsonResponse
    {
        $loja = $this->context->getLoja();
        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        $pedido = Pedido::where('loja_id', $loja->id)
            ->where('codigo_publico', $codigo)
            ->with(['cliente', 'itens', 'eventos'])
            ->first();

        if ($pedido === null) {
            return response()->json(['error' => 'PEDIDO_NAO_ENCONTRADO'], Response::HTTP_NOT_FOUND);
        }

        $signature = $request->query('t');
        $expectedSignature = hash_hmac('sha256', $pedido->tracking_token_hash, (string) config('app.key'));

        if (!is_string($signature) || !hash_equals($expectedSignature, $signature)) {
            return response()->json([
                'error' => 'ASSINATURA_INVALIDA',
                'message' => 'Link de rastreamento inválido ou expirado.',
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'pedido' => new PedidoPublicoTrackingResource($pedido),
        ]);
    }
}
