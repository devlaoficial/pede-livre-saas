<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Controllers;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Bot\Services\EvolutionApiService;
use App\Domain\Clientes\Models\Cliente;
use App\Domain\Entregas\Models\Entregador;
use App\Domain\Pagamentos\Models\Pagamento;
use App\Domain\Pedidos\Events\PagamentoConfirmado;
use App\Domain\Pedidos\Events\PedidoAtualizado;
use App\Domain\Pedidos\Events\PedidoCriado;
use App\Domain\Pedidos\Models\Pedido;
use App\Domain\Pedidos\Models\PedidoEvento;
use App\Domain\Pedidos\Models\PedidoItem;
use App\Domain\Pedidos\Requests\AtualizarStatusPedidoRequest;
use App\Domain\Pedidos\Resources\PedidoPainelResource;
use App\Domain\Pedidos\Services\CalculoPedidoService;
use App\Domain\Pedidos\Services\FluxoStatusPedidoService;
use App\Support\Security\BlindIndexService;
use App\Support\Tenancy\LojaContext;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class PedidoPainelController
{
    public function __construct(
        private readonly FluxoStatusPedidoService $fluxoService,
        private readonly LojaContext $context,
        private readonly EvolutionApiService $evolutionApi,
        private readonly CalculoPedidoService $calculoPedido,
        private readonly BlindIndexService $blindIndex
    ) {}

    /**
     * Active orders query for Kanban board.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $statusFiltro = $request->query('status');

        $query = Pedido::with(['cliente', 'itens.produto', 'mesa', 'entregador', 'eventos.usuario'])
            ->orderBy('created_at', 'desc');

        if (is_string($statusFiltro) && $statusFiltro !== '') {
            $query->where('status', $statusFiltro);
        } else {
            // Exclude delivered/cancelled older than today unless requested
            $query->where(function ($q): void {
                $q->whereNotIn('status', ['entregue', 'retirado', 'servido', 'cancelado'])
                    ->orWhere('updated_at', '>=', now()->startOfDay());
            });
        }

        return PedidoPainelResource::collection($query->get());
    }

    public function show(int $id): JsonResponse
    {
        $pedido = Pedido::with(['cliente', 'itens.produto', 'mesa', 'entregador', 'eventos.usuario'])->findOrFail($id);

        return response()->json(new PedidoPainelResource($pedido));
    }

    public function atualizarStatus(AtualizarStatusPedidoRequest $request, int $id): JsonResponse
    {
        $pedido = Pedido::with(['cliente', 'itens.produto', 'loja', 'pagamento', 'entregador'])->findOrFail($id);

        /** @var Usuario $usuario */
        $usuario = $request->user();

        try {
            $pedidoAtualizado = $this->fluxoService->transicionar(
                $pedido,
                $request->string('status')->toString(),
                $usuario,
                $request->input('cancelado_motivo'),
                $request->filled('entregador_id') ? $request->integer('entregador_id') : null,
                $request->input('observacao')
            );

            return response()->json([
                'message' => "Status do pedido atualizado para [{$pedidoAtualizado->status}].",
                'pedido' => new PedidoPainelResource($pedidoAtualizado->load(['cliente', 'itens.produto', 'mesa', 'entregador', 'eventos.usuario'])),
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'error' => 'TRANSICAO_INVALIDA',
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * POST /api/painel/pedidos/{id}/confirmar-pagamento
     * Confirma manualmente o recebimento do pagamento de um pedido no painel.
     */
    public function confirmarPagamento(Request $request, int $id): JsonResponse
    {
        $pedido = Pedido::with(['pagamento', 'cliente', 'itens.produto', 'mesa', 'entregador', 'eventos.usuario'])->findOrFail($id);
        /** @var Usuario $usuario */
        $usuario = $request->user();

        DB::transaction(function () use ($pedido, $usuario): void {
            $pedido->update(['pagamento_status' => 'aprovado']);

            if ($pedido->pagamento !== null) {
                $pedido->pagamento->update(['status' => 'aprovado']);
            } else {
                Pagamento::create([
                    'loja_id' => $pedido->loja_id,
                    'pedido_id' => $pedido->id,
                    'metodo' => $pedido->forma_pagamento,
                    'valor' => $pedido->total,
                    'status' => 'aprovado',
                    'idempotency_key' => 'manual_pay_' . $pedido->id . '_' . time(),
                ]);
            }

            PedidoEvento::create([
                'pedido_id' => $pedido->id,
                'status_de' => $pedido->status,
                'status_para' => $pedido->status,
                'usuario_id' => $usuario->id,
                'observacao' => 'Pagamento confirmado manualmente pelo operador no painel.',
            ]);
        });

        try {
            if (config('broadcasting.default') && !in_array(config('broadcasting.default'), ['null', 'log'], true)) {
                event(new PagamentoConfirmado($pedido));
                event(new PedidoAtualizado($pedido, $pedido->status));
            }
        } catch (Throwable) {}

        return response()->json([
            'message' => "Pagamento do pedido #{$pedido->codigo_publico} confirmado com sucesso!",
            'pedido' => new PedidoPainelResource($pedido->fresh(['cliente', 'itens.produto', 'mesa', 'entregador', 'eventos.usuario'])),
        ]);
    }

    /**
     * POST /api/painel/pedidos/manual
     * Criação de pedido manual pelo lojista (PDV Balcão / Telefone / WhatsApp).
     */
    public function criarPedidoManual(Request $request): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        /** @var Loja|null $loja */
        $loja = $usuario->loja ?? $this->context->getLoja();

        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NAO_ENCONTRADA'], Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'canal' => ['required', 'string', 'in:balcao,delivery,retirada,mesa'],
            'cliente' => ['required', 'array'],
            'cliente.nome' => ['required', 'string', 'max:120'],
            'cliente.telefone' => ['nullable', 'string', 'max:30'],
            'endereco' => ['nullable', 'array'],
            'mesa_id' => ['nullable', 'integer'],
            'taxa_entrega_centavos' => ['nullable', 'integer', 'min:0'],
            'desconto_manual_centavos' => ['nullable', 'integer', 'min:0'],
            'itens' => ['required', 'array', 'min:1'],
            'itens.*.produto_id' => ['required', 'integer'],
            'itens.*.variacao_id' => ['nullable', 'integer'],
            'itens.*.qtd' => ['required', 'integer', 'min:1'],
            'itens.*.observacao' => ['nullable', 'string', 'max:255'],
            'itens.*.adicionais' => ['nullable', 'array'],
            'forma_pagamento' => ['required', 'string', 'in:pix,cartao_maquina,dinheiro,voucher,cartao_online'],
            'troco_para' => ['nullable', 'integer', 'min:0'],
            'status_inicial' => ['nullable', 'string', 'in:novo,aceito,em_preparo'],
            'observacao' => ['nullable', 'string', 'max:500'],
        ]);

        $dados = $request->all();
        $idempotencyKey = 'pdv_' . now()->timestamp . '_' . Str::random(6);
        $dados['idempotency_key'] = $idempotencyKey;

        // Identifica ou cadastra o cliente com Blind Index e suporte a variações 8/9 dígitos
        $telRaw = trim((string) ($dados['cliente']['telefone'] ?? ''));
        $telNormalizado = $telRaw !== '' ? $this->blindIndex->normalizePhone($telRaw) : '5500000000000';
        $telBidx = $this->blindIndex->generate($telNormalizado);
        $candidatosBidx = $telRaw !== '' ? $this->blindIndex->generatePhoneCandidates($telRaw) : [$telBidx];

        /** @var Cliente|null $cliente */
        $cliente = Cliente::where('loja_id', $loja->id)
            ->whereIn('telefone_bidx', $candidatosBidx)
            ->first();

        if ($cliente === null) {
            $cliente = Cliente::create([
                'loja_id' => $loja->id,
                'telefone_bidx' => $telBidx,
                'nome' => trim((string) ($dados['cliente']['nome'] ?: 'Cliente Balcão')),
                'telefone' => $telNormalizado,
                'aceita_marketing' => true,
            ]);
        } else {
            $cliente->update([
                'nome' => trim((string) ($dados['cliente']['nome'] ?: $cliente->nome)),
                'telefone' => $telNormalizado !== '5500000000000' ? $telNormalizado : $cliente->telefone,
            ]);
        }

        // Calcula itens, adicionais e subtotal com precisão
        $dadosCalculo = $dados;
        $canalOriginal = (string) $dados['canal'];
        if ($canalOriginal === 'retirada' || ($canalOriginal === 'delivery' && $request->has('taxa_entrega_centavos'))) {
            $dadosCalculo['canal'] = 'balcao';
        }

        try {
            $calculo = $this->calculoPedido->calcular($loja, $dadosCalculo);
        } catch (DomainException $e) {
            return response()->json([
                'error' => 'ERRO_VALIDACAO_ITEM',
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($canalOriginal === 'delivery' && $request->has('taxa_entrega_centavos')) {
            $taxaManual = (int) $request->input('taxa_entrega_centavos', 0);
            $difTaxa = $taxaManual - $calculo['taxa_entrega_centavos'];
            $calculo['taxa_entrega_centavos'] = $taxaManual;
            $calculo['total_centavos'] += $difTaxa;
        }

        if ($request->has('desconto_manual_centavos')) {
            $descontoManual = (int) $request->input('desconto_manual_centavos', 0);
            $calculo['desconto_centavos'] += $descontoManual;
            $calculo['total_centavos'] = max(0, $calculo['total_centavos'] - $descontoManual);
        }

        $statusInicial = (string) $request->input('status_inicial', 'em_preparo');
        if ($statusInicial === 'aceito') {
            $statusInicial = 'em_preparo';
        }
        $formaPagto = (string) $dados['forma_pagamento'];
        $pagamentoStatus = in_array($formaPagto, ['pix', 'cartao_maquina', 'cartao_online'], true) && $statusInicial !== 'novo'
            ? 'aprovado'
            : 'pendente';

        $pedido = DB::transaction(function () use ($loja, $cliente, $dados, $calculo, $statusInicial, $formaPagto, $pagamentoStatus, $idempotencyKey): Pedido {
            $codigoPublico = strtoupper(Str::random(4));
            $tokenHash = hash_hmac('sha256', "{$loja->id}:{$codigoPublico}:" . now()->timestamp, (string) config('app.key'));

            $pedido = Pedido::create([
                'loja_id' => $loja->id,
                'codigo_publico' => $codigoPublico,
                'tracking_token_hash' => $tokenHash,
                'canal' => $dados['canal'],
                'status' => $statusInicial,
                'cliente_id' => $cliente->id,
                'mesa_id' => $dados['mesa_id'] ?? null,
                'subtotal' => $calculo['subtotal_centavos'],
                'taxa_entrega' => $calculo['taxa_entrega_centavos'],
                'desconto' => $calculo['desconto_centavos'],
                'desconto_cashback' => 0,
                'taxa_servico' => $calculo['taxa_servico_centavos'],
                'total' => $calculo['total_centavos'],
                'forma_pagamento' => $formaPagto,
                'pagamento_status' => $pagamentoStatus,
                'troco_para' => $dados['troco_para'] ?? null,
                'observacao' => $dados['observacao'] ?? null,
                'endereco_json' => $dados['endereco'] ?? null,
                'aceito_em' => in_array($statusInicial, ['aceito', 'em_preparo'], true) ? now() : null,
                'origem' => 'painel',
            ]);

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

            Pagamento::create([
                'loja_id' => $loja->id,
                'pedido_id' => $pedido->id,
                'metodo' => $formaPagto,
                'valor' => $calculo['total_centavos'],
                'status' => $pagamentoStatus,
                'idempotency_key' => $idempotencyKey,
            ]);

            PedidoEvento::create([
                'pedido_id' => $pedido->id,
                'status_de' => null,
                'status_para' => $statusInicial,
                'usuario_id' => auth()->id(),
                'observacao' => 'Pedido criado manualmente via PDV no Painel',
            ]);

            return $pedido;
        });

        $cliente->increment('total_pedidos');
        $cliente->increment('total_gasto', $pedido->total);
        $cliente->update(['ultimo_pedido_em' => now()]);

        // Dispara acompanhamento em tempo real no WhatsApp do cliente (se telefone foi informado)
        if ($telNormalizado !== '5500000000000') {
            try {
                $this->evolutionApi->notificarMudancaStatus($pedido->load(['cliente', 'itens', 'pagamento', 'loja']), $statusInicial);
            } catch (Throwable) {}
        }

        try {
            if (config('broadcasting.default') && !in_array(config('broadcasting.default'), ['null', 'log'], true)) {
                event(new PedidoCriado($pedido->load(['cliente', 'itens.produto', 'mesa'])));
            }
        } catch (Throwable) {}

        return response()->json([
            'message' => "Pedido #{$pedido->codigo_publico} lançado com sucesso!",
            'pedido' => new PedidoPainelResource($pedido->load(['cliente', 'itens.produto', 'mesa', 'entregador', 'eventos.usuario'])),
        ], Response::HTTP_CREATED);
    }

    /**
     * POST /api/painel/pedidos/{id}/cancelar
     */
    public function cancelarComMotivo(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'motivo' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        $pedido = Pedido::with(['cliente', 'itens.produto', 'loja', 'pagamento', 'entregador'])->findOrFail($id);
        /** @var Usuario $usuario */
        $usuario = $request->user();

        try {
            $pedidoAtualizado = $this->fluxoService->transicionar(
                $pedido,
                'cancelado',
                $usuario,
                $request->input('motivo')
            );

            return response()->json([
                'message' => "Pedido #{$pedidoAtualizado->codigo_publico} cancelado com sucesso.",
                'pedido' => new PedidoPainelResource($pedidoAtualizado->load(['cliente', 'itens.produto', 'mesa', 'entregador', 'eventos.usuario'])),
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'error' => 'TRANSICAO_INVALIDA',
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * POST /api/painel/pedidos/{id}/atribuir-entregador
     */
    public function atribuirEntregador(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'entregador_id' => ['required', 'integer'],
        ]);

        $entregador = Entregador::findOrFail($request->integer('entregador_id'));
        $pedido = Pedido::with(['cliente', 'itens.produto', 'loja', 'pagamento'])->findOrFail($id);
        $statusAnterior = $pedido->status;

        $novoStatus = in_array($pedido->status, ['pronto', 'em_preparo', 'aceito'], true)
            ? 'saiu_entrega'
            : $pedido->status;

        $pedido->update([
            'entregador_id' => $entregador->id,
            'status' => $novoStatus,
        ]);

        PedidoEvento::create([
            'pedido_id' => $pedido->id,
            'status_de' => $statusAnterior,
            'status_para' => $novoStatus,
            'usuario_id' => auth()->id(),
            'observacao' => "Despachado com motoboy {$entregador->nome}",
        ]);

        if ($pedido->canal === 'delivery') {
            $loja = $pedido->loja ?? Loja::withoutGlobalScopes()->find($pedido->loja_id);
            if ($loja !== null) {
                try {
                    $instancia = $this->evolutionApi->obterNomeInstanciaLoja($loja);
                    $this->evolutionApi->notificarMotoboyPedidoPronto($pedido->load('entregador', 'cliente'), $instancia, $loja);
                    $this->evolutionApi->notificarMudancaStatus($pedido->load('entregador', 'cliente'), 'saiu_entrega');
                } catch (Throwable) {}
            }
        }

        try {
            if (config('broadcasting.default') && !in_array(config('broadcasting.default'), ['null', 'log'], true)) {
                event(new PedidoAtualizado($pedido, $statusAnterior));
            }
        } catch (Throwable) {}

        return response()->json([
            'message' => "Pedido despachado com sucesso com {$entregador->nome}!",
            'pedido' => new PedidoPainelResource($pedido->load(['cliente', 'itens.produto', 'entregador', 'mesa', 'eventos.usuario'])),
        ]);
    }
}
