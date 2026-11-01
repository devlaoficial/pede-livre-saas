<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Controllers;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Bot\Services\EvolutionApiService;
use App\Domain\Clientes\Models\Cliente;
use App\Domain\Entregas\Models\Entregador;
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

        $query = Pedido::with(['cliente', 'itens', 'mesa', 'entregador', 'eventos.usuario'])
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
        $pedido = Pedido::with(['cliente', 'itens', 'mesa', 'entregador', 'eventos.usuario'])->findOrFail($id);

        return response()->json(new PedidoPainelResource($pedido));
    }

    public function atualizarStatus(AtualizarStatusPedidoRequest $request, int $id): JsonResponse
    {
        $pedido = Pedido::with('itens')->findOrFail($id);

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
                'pedido' => new PedidoPainelResource($pedidoAtualizado->load(['cliente', 'itens', 'mesa', 'entregador', 'eventos.usuario'])),
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'error' => 'TRANSICAO_INVALIDA',
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
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
        $loja = $usuario->loja;

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
        $dados['idempotency_key'] = 'pdv_' . now()->timestamp . '_' . Str::random(6);

        // Identifica ou cadastra o cliente com Blind Index
        $telRaw = trim((string) ($dados['cliente']['telefone'] ?? ''));
        $telNormalizado = $telRaw !== '' ? $this->blindIndex->normalizePhone($telRaw) : '5500000000000';
        $telBidx = $this->blindIndex->generate($telNormalizado);

        /** @var Cliente $cliente */
        $cliente = Cliente::firstOrCreate(
            ['loja_id' => $loja->id, 'telefone_bidx' => $telBidx],
            [
                'nome' => trim((string) ($dados['cliente']['nome'] ?: 'Cliente Balcão')),
                'telefone' => $telNormalizado,
                'aceita_marketing' => true,
            ]
        );

        if (!empty($dados['cliente']['nome']) && $cliente->nome !== $dados['cliente']['nome']) {
            $cliente->update(['nome' => trim((string) $dados['cliente']['nome'])]);
        }

        // Se canal for retirada, passa como balcão para validações de produto caso aplicável
        $dadosCalculo = $dados;
        if (($dadosCalculo['canal'] ?? '') === 'retirada') {
            $dadosCalculo['canal'] = 'balcao';
        }

        // Calcula itens, adicionais e subtotal com precisão
        $calculo = $this->calculoPedido->calcular($loja, $dadosCalculo);

        // Ajusta taxa de entrega manual se o lojista definiu
        if ($request->has('taxa_entrega_centavos')) {
            $taxaManual = (int) $request->input('taxa_entrega_centavos', 0);
            $difTaxa = $taxaManual - $calculo['taxa_entrega_centavos'];
            $calculo['taxa_entrega_centavos'] = $taxaManual;
            $calculo['total_centavos'] += $difTaxa;
        }

        // Ajusta desconto manual se fornecido
        if ($request->has('desconto_manual_centavos')) {
            $descontoManual = (int) $request->input('desconto_manual_centavos', 0);
            $calculo['desconto_centavos'] += $descontoManual;
            $calculo['total_centavos'] = max(0, $calculo['total_centavos'] - $descontoManual);
        }

        $statusInicial = (string) $request->input('status_inicial', 'aceito');
        $formaPagto = (string) $dados['forma_pagamento'];
        $pagamentoStatus = in_array($formaPagto, ['pix', 'cartao_maquina', 'cartao_online'], true) && $statusInicial !== 'novo'
            ? 'pago'
            : 'pendente';

        $pedido = DB::transaction(function () use ($loja, $cliente, $dados, $calculo, $statusInicial, $formaPagto, $pagamentoStatus): Pedido {
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

            PedidoEvento::create([
                'pedido_id' => $pedido->id,
                'status_de' => null,
                'status_para' => $statusInicial,
                'usuario_id' => auth()->id(),
                'observacao' => 'Pedido criado manualmente via PDV no Painel',
            ]);

            return $pedido;
        });

        // Atualiza métricas do cliente
        $cliente->increment('total_pedidos');
        $cliente->increment('total_gasto', $pedido->total);
        $cliente->update(['ultimo_pedido_em' => now()]);

        // Dispara evento em tempo real para sincronização com outros navegadores/telas
        event(new PedidoCriado($pedido->load(['cliente', 'itens', 'mesa'])));

        // Notifica cliente via WhatsApp se ativado
        $configWpp = $loja->config_json['whatsapp'] ?? [];
        if (!empty($configWpp['notificar_pedidos']) && !empty($configWpp['connected'])) {
            try {
                $instancia = $this->evolutionApi->obterNomeInstanciaLoja($loja);
                $this->evolutionApi->notificarMudancaStatus($pedido->load('cliente'), $statusInicial);
            } catch (\Throwable) {}
        }

        return response()->json([
            'message' => "Pedido #{$pedido->codigo_publico} lançado com sucesso!",
            'pedido' => new PedidoPainelResource($pedido->load(['cliente', 'itens', 'mesa', 'entregador', 'eventos.usuario'])),
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

        $pedido = Pedido::with('itens')->findOrFail($id);
        /** @var Usuario $usuario */
        $usuario = $request->user();

        try {
            $pedidoAtualizado = $this->fluxoService->transicionar(
                $pedido,
                'cancelado',
                $usuario,
                $request->input('motivo')
            );

            // Notifica cliente via WhatsApp se conectado
            $loja = $pedido->loja ?? Loja::find($pedido->loja_id);
            if ($loja !== null) {
                $configWpp = $loja->config_json['whatsapp'] ?? [];
                if (!empty($configWpp['notificar_pedidos']) && !empty($configWpp['connected'])) {
                    try {
                        $instancia = $this->evolutionApi->obterNomeInstanciaLoja($loja);
                        $this->evolutionApi->notificarMudancaStatus($pedido->load('cliente'), 'cancelado');
                    } catch (\Throwable) {}
                }
            }

            return response()->json([
                'message' => "Pedido #{$pedidoAtualizado->codigo_publico} cancelado com sucesso.",
                'pedido' => new PedidoPainelResource($pedidoAtualizado->load(['cliente', 'itens', 'mesa', 'entregador', 'eventos.usuario'])),
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
        $pedido = Pedido::findOrFail($id);
        $statusAnterior = $pedido->status;

        // Se o pedido estava pronto ou em preparo, avança automaticamente para "saiu_entrega" (Em Rota)
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
            $loja = $pedido->loja ?? Loja::find($pedido->loja_id);
            if ($loja !== null) {
                $instancia = $this->evolutionApi->obterNomeInstanciaLoja($loja);
                // 1. Notifica o motoboy com todos os dados da rota e GPS
                $this->evolutionApi->notificarMotoboyPedidoPronto($pedido->load('entregador', 'cliente'), $instancia, $loja);
                // 2. Notifica o cliente que o pedido saiu para entrega com link de rastreamento
                $this->evolutionApi->notificarMudancaStatus($pedido->load('cliente'), 'saiu_entrega');
            }
        }

        return response()->json([
            'message' => "Pedido despachado com sucesso com {$entregador->nome}!",
            'pedido' => new PedidoPainelResource($pedido->load(['cliente', 'itens', 'entregador'])),
        ]);
    }
}
