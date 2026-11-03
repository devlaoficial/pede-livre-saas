<?php

declare(strict_types=1);

namespace App\Domain\Fidelidade\Controllers;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Clientes\Models\Cliente;
use App\Domain\Pedidos\Models\Cupom;
use App\Domain\Pedidos\Models\Pedido;
use App\Support\Tenancy\LojaContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

final class RelatorioVendasController
{
    public function __construct(
        private readonly LojaContext $context
    ) {}

    /**
     * GET /api/relatorios/vendas
     * Merchant Analytics: Faturamento em centavos, Ticket Médio, Horários de Pico, CRM Clientes, Cashback e Cupons.
     */
    public function vendas(Request $request): JsonResponse
    {
        /** @var Usuario $user */
        $user = $request->user();
        if (!$user->isGerente()) {
            return response()->json([
                'error' => 'FORBIDDEN',
                'message' => 'Apenas gerentes e donos possuem acesso aos relatórios financeiros.',
            ], Response::HTTP_FORBIDDEN);
        }

        $loja = $user->loja ?? ($user->loja_id ? Loja::find($user->loja_id) : $this->context->getLoja());
        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NAO_ENCONTRADA'], Response::HTTP_NOT_FOUND);
        }

        $pedidosConcluidos = Pedido::where('loja_id', $loja->id)
            ->whereIn('status', ['entregue', 'retirado', 'servido'])
            ->with(['itens.produto'])
            ->get();

        $totalPedidos = $pedidosConcluidos->count();
        $faturamentoTotalCentavos = (int) $pedidosConcluidos->sum('total');
        $ticketMedioCentavos = $totalPedidos > 0 ? intdiv($faturamentoTotalCentavos, $totalPedidos) : 0;

        // Top Produtos Campeões (using exact PedidoItem columns: nome_snapshot, qtd, total, preco_unit)
        $produtosVendidos = [];
        foreach ($pedidosConcluidos as $p) {
            foreach ($p->itens as $item) {
                $pNome = $item->nome_snapshot ?: ($item->produto?->nome ?? 'Item');
                $qtd = (int) ($item->qtd ?: 1);
                $totalCent = (int) ($item->total ?: (($item->preco_unit ?? 0) * $qtd));
                if (!isset($produtosVendidos[$pNome])) {
                    $produtosVendidos[$pNome] = ['nome' => $pNome, 'qtd' => 0, 'total_centavos' => 0];
                }
                $produtosVendidos[$pNome]['qtd'] += $qtd;
                $produtosVendidos[$pNome]['total_centavos'] += $totalCent;
            }
        }
        usort($produtosVendidos, fn ($a, $b) => $b['qtd'] <=> $a['qtd']);
        $produtosCampeoes = array_slice($produtosVendidos, 0, 5);

        // Peak Hours Distribution (00h to 23h)
        $horariosPico = [];
        foreach ($pedidosConcluidos as $p) {
            $hora = $p->created_at ? $p->created_at->format('H:00') : '12:00';
            $horariosPico[$hora] = ($horariosPico[$hora] ?? 0) + 1;
        }
        arsort($horariosPico);

        // New vs Returning Customers
        $clientesNovos = Cliente::where('loja_id', $loja->id)->where('total_pedidos', 1)->count();
        $clientesRecorrentes = Cliente::where('loja_id', $loja->id)->where('total_pedidos', '>', 1)->count();

        // Win-back candidates: customers inactive for 30+ days who accept marketing (LGPD)
        $clientesInativos30d = Cliente::where('loja_id', $loja->id)
            ->where('aceita_marketing', true)
            ->whereNotNull('ultimo_pedido_em')
            ->where('ultimo_pedido_em', '<=', now()->subDays(30))
            ->count();

        // Full CRM Customer Ranking & Win-Back WhatsApp URLs
        $clientesLista = Cliente::where('loja_id', $loja->id)
            ->orderByDesc('total_gasto')
            ->orderByDesc('total_pedidos')
            ->limit(100)
            ->get()
            ->map(function (Cliente $c) use ($loja): array {
                $telDigits = $c->telefone ? preg_replace('/\D+/', '', (string) $c->telefone) : '';
                if ($telDigits !== '' && (strlen($telDigits) === 10 || strlen($telDigits) === 11)) {
                    $telDigits = '55' . $telDigits;
                }
                $inativo30d = $c->ultimo_pedido_em !== null && $c->ultimo_pedido_em->lte(now()->subDays(30));
                $diasSemPedir = $c->ultimo_pedido_em !== null ? (int) $c->ultimo_pedido_em->diffInDays(now()) : 0;
                $cashbackReais = number_format(((int) $c->saldo_cashback_centavos) / 100, 2, ',', '.');
                $cardapioUrl = url("/p/{$loja->slug}");

                $msgWinback = $c->saldo_cashback_centavos > 0
                    ? "Olá {$c->nome}! Tudo bem? Sentimos sua falta aqui na {$loja->nome}! Você ainda tem R$ {$cashbackReais} de Cashback disponível e preparamos o cupom VOLTE10 para você pedir hoje: {$cardapioUrl}"
                    : "Olá {$c->nome}! Tudo bem? Faz um tempinho que você não pede na {$loja->nome}! Preparamos um cupom especial VOLTE10 com 10% OFF para você matar a saudade hoje: {$cardapioUrl}";

                return [
                    'id' => $c->id,
                    'nome' => $c->nome,
                    'telefone' => $c->telefone,
                    'email' => $c->email,
                    'aceita_marketing' => (bool) $c->aceita_marketing,
                    'total_pedidos' => (int) $c->total_pedidos,
                    'total_gasto_centavos' => (int) $c->total_gasto,
                    'saldo_cashback_centavos' => (int) $c->saldo_cashback_centavos,
                    'ultimo_pedido_em' => $c->ultimo_pedido_em?->format('d/m/Y H:i'),
                    'dias_sem_pedir' => $diasSemPedir,
                    'inativo_30d' => $inativo30d,
                    'whatsapp_winback_url' => $telDigits !== '' ? ('https://wa.me/' . $telDigits . '?text=' . urlencode($msgWinback)) : null,
                ];
            });

        $cupons = Cupom::where('loja_id', $loja->id)
            ->orderByDesc('ativo')
            ->orderByDesc('id')
            ->get();

        $chamados = Cache::get("garcom_chamados:loja:{$loja->id}", []);

        return response()->json([
            'faturamento_total_centavos' => $faturamentoTotalCentavos,
            'total_pedidos_concluidos' => $totalPedidos,
            'ticket_medio_centavos' => $ticketMedioCentavos,
            'horarios_pico' => $horariosPico,
            'clientes' => [
                'novos' => $clientesNovos,
                'recorrentes' => $clientesRecorrentes,
                'elegiveis_winback_30d' => $clientesInativos30d,
            ],
            'clientes_lista' => $clientesLista,
            'cupons' => $cupons,
            'produtos_campeoes' => $produtosCampeoes,
            'chamados_garcom' => is_array($chamados) ? array_values($chamados) : [],
        ]);
    }

    /**
     * GET /api/cupons
     */
    public function listarCupons(Request $request): JsonResponse
    {
        /** @var Usuario|null $user */
        $user = $request->user();
        $loja = $user?->loja ?? ($user?->loja_id ? Loja::find($user->loja_id) : $this->context->getLoja());
        if ($loja === null) {
            return response()->json(['data' => []]);
        }
        $cupons = Cupom::where('loja_id', $loja->id)
            ->orderByDesc('ativo')
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $cupons]);
    }

    /**
     * POST /api/cupons
     */
    public function criarCupom(Request $request): JsonResponse
    {
        /** @var Usuario $user */
        $user = $request->user();
        if (!$user->isGerente()) {
            return response()->json(['error' => 'FORBIDDEN', 'message' => 'Apenas gerentes e donos podem criar cupons.'], Response::HTTP_FORBIDDEN);
        }

        $loja = $user->loja ?? ($user->loja_id ? Loja::find($user->loja_id) : $this->context->getLoja());
        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NAO_ENCONTRADA'], Response::HTTP_NOT_FOUND);
        }

        $dados = $request->validate([
            'codigo' => ['required', 'string', 'max:40'],
            'tipo' => ['required', 'string', 'in:percentual,valor,fixo_centavos,frete_gratis'],
            'valor' => ['required', 'integer', 'min:0'],
            'minimo_centavos' => ['nullable', 'integer', 'min:0'],
            'limite_uso_total' => ['nullable', 'integer', 'min:1'],
            'limite_por_cliente' => ['nullable', 'integer', 'min:1'],
            'validade' => ['nullable', 'date'],
        ]);

        $codigo = strtoupper(preg_replace('/\s+/', '', $dados['codigo']) ?? '');
        $tipoBanco = $dados['tipo'] === 'fixo_centavos' ? 'valor' : $dados['tipo'];

        $cupom = Cupom::updateOrCreate(
            ['loja_id' => $loja->id, 'codigo' => $codigo],
            [
                'tipo' => $tipoBanco,
                'valor' => $tipoBanco === 'frete_gratis' ? 0 : max(1, (int) $dados['valor']),
                'minimo_centavos' => (int) ($dados['minimo_centavos'] ?? 0),
                'limite_uso_total' => isset($dados['limite_uso_total']) ? (int) $dados['limite_uso_total'] : null,
                'limite_por_cliente' => isset($dados['limite_por_cliente']) ? (int) $dados['limite_por_cliente'] : 3,
                'validade' => $dados['validade'] ?? null,
                'canais' => ['delivery', 'retirada', 'mesa', 'balcao'],
                'ativo' => true,
            ]
        );

        return response()->json([
            'message' => "Cupom {$cupom->codigo} salvo com sucesso!",
            'cupom' => $cupom,
        ], Response::HTTP_CREATED);
    }

    /**
     * PATCH /api/cupons/{id}
     */
    public function alternarCupom(Request $request, int $id): JsonResponse
    {
        /** @var Usuario $user */
        $user = $request->user();
        if (!$user->isGerente()) {
            return response()->json(['error' => 'FORBIDDEN'], Response::HTTP_FORBIDDEN);
        }

        $loja = $this->context->getLoja();
        $cupom = Cupom::where('loja_id', $loja->id)->findOrFail($id);
        $cupom->update(['ativo' => !$cupom->ativo]);

        return response()->json([
            'message' => 'Status do cupom atualizado.',
            'cupom' => $cupom,
        ]);
    }

    /**
     * DELETE /api/cupons/{id}
     */
    public function excluirCupom(Request $request, int $id): JsonResponse
    {
        /** @var Usuario $user */
        $user = $request->user();
        if (!$user->isGerente()) {
            return response()->json(['error' => 'FORBIDDEN'], Response::HTTP_FORBIDDEN);
        }

        $loja = $this->context->getLoja();
        $cupom = Cupom::where('loja_id', $loja->id)->findOrFail($id);
        $cupom->delete();

        return response()->json(['message' => 'Cupom removido com sucesso.']);
    }
}
