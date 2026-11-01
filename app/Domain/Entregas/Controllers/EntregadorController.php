<?php

declare(strict_types=1);

namespace App\Domain\Entregas\Controllers;

use App\Domain\Entregas\Models\Entregador;
use App\Domain\Entregas\Requests\SalvarEntregadorRequest;
use App\Domain\Entregas\Resources\EntregadorResource;
use App\Domain\Pedidos\Models\Pedido;
use App\Support\Security\BlindIndexService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class EntregadorController
{
    public function __construct(
        private readonly BlindIndexService $blindIndex
    ) {}

    public function fechamentoDia(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Entregador::class);

        /** @var \App\Domain\Autenticacao\Models\Usuario $usuario */
        $usuario = $request->user();
        $loja = $usuario->loja;

        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NAO_ENCONTRADA'], Response::HTTP_NOT_FOUND);
        }

        $hoje = now()->startOfDay();

        $pedidosHoje = Pedido::where('loja_id', $loja->id)
            ->where('canal', 'delivery')
            ->where('created_at', '>=', $hoje)
            ->where('status', '!=', 'cancelado')
            ->whereNotNull('entregador_id')
            ->get();

        $entregadores = Entregador::where('loja_id', $loja->id)
            ->orderBy('nome')
            ->get();

        $relatorio = $entregadores->map(function (Entregador $ent) use ($pedidosHoje, $loja) {
            $pedidosDoMotoboy = $pedidosHoje->where('entregador_id', $ent->id);
            $concluidas = $pedidosDoMotoboy->whereIn('status', ['entregue', 'finalizado', 'retirado', 'servido']);
            $emRota = $pedidosDoMotoboy->where('status', 'saiu_entrega');

            $taxaUnit = (int) ($ent->valor_por_entrega_centavos ?? 700);
            $totalTaxas = $concluidas->count() * $taxaUnit;
            $dinheiroColetado = (int) $concluidas->where('forma_pagamento', 'dinheiro')->sum('total');
            $saldoAcerto = $totalTaxas - $dinheiroColetado;

            $dataHoje = now()->format('d/m/Y');
            $qtdEntregas = $concluidas->count();
            $taxasFormatadas = number_format($totalTaxas / 100, 2, ',', '.');
            $dinheiroFormatado = number_format($dinheiroColetado / 100, 2, ',', '.');
            $saldoFormatado = number_format(abs($saldoAcerto) / 100, 2, ',', '.');

            $textoAcerto = $saldoAcerto >= 0
                ? "👉 *Acerto:* O restaurante repassará a você *R$ {$saldoFormatado}*."
                : "👉 *Acerto:* Você deve repassar ao caixa *R$ {$saldoFormatado}* das cobranças em dinheiro.";

            $msgWpp = "🛵 *Fechamento de Diária — {$loja->nome}*\n"
                . "📅 *Data:* {$dataHoje}\n"
                . "👤 *Entregador:* {$ent->nome}\n\n"
                . "✅ Entregas concluídas: *{$qtdEntregas}*\n"
                . "💵 Total em fretes: *R$ {$taxasFormatadas}*\n"
                . "💰 Cobrado em dinheiro: *R$ {$dinheiroFormatado}*\n\n"
                . "{$textoAcerto}\n\n"
                . "_Obrigado pelo trabalho de hoje!_";

            $telLimpo = preg_replace('/\D+/', '', (string) $ent->telefone);
            if (strlen($telLimpo) === 10 || strlen($telLimpo) === 11) {
                $telLimpo = '55' . $telLimpo;
            }
            $wppUrl = $telLimpo ? 'https://wa.me/' . $telLimpo . '?text=' . rawurlencode($msgWpp) : null;

            return [
                'id' => $ent->id,
                'nome' => $ent->nome,
                'telefone' => $ent->telefone,
                'tipo' => $ent->tipo,
                'ativo' => $ent->ativo,
                'taxa_por_entrega_centavos' => $taxaUnit,
                'entregas_concluidas' => $qtdEntregas,
                'entregas_em_rota' => $emRota->count(),
                'total_taxas_centavos' => $totalTaxas,
                'total_dinheiro_recebido_centavos' => $dinheiroColetado,
                'saldo_acerto_centavos' => $saldoAcerto,
                'direcao_acerto' => $saldoAcerto >= 0 ? 'loja_paga_motoboy' : 'motoboy_repassa_loja',
                'whatsapp_url' => $wppUrl,
            ];
        });

        return response()->json([
            'data' => $relatorio,
            'totais' => [
                'total_entregas_hoje' => $relatorio->sum('entregas_concluidas'),
                'total_taxas_frete_centavos' => $relatorio->sum('total_taxas_centavos'),
                'total_dinheiro_recebido_centavos' => $relatorio->sum('total_dinheiro_recebido_centavos'),
            ],
        ]);
    }

    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Entregador::class);

        $entregadores = Entregador::orderBy('nome')->get();
        return EntregadorResource::collection($entregadores);
    }

    public function store(SalvarEntregadorRequest $request): JsonResponse
    {
        Gate::authorize('create', Entregador::class);

        $data = $request->validated();
        $telefone = $data['telefone'];
        $telefoneBidx = $this->blindIndex->generate($this->blindIndex->normalizePhone($telefone));

        $entregador = Entregador::create([
            'nome' => $data['nome'],
            'telefone' => $telefone,
            'telefone_bidx' => $telefoneBidx,
            'tipo' => $data['tipo'],
            'valor_por_entrega_centavos' => $data['valor_por_entrega_centavos'],
            'ativo' => $data['ativo'] ?? true,
        ]);

        return response()->json([
            'message' => 'Entregador cadastrado com sucesso.',
            'entregador' => new EntregadorResource($entregador),
        ], Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $entregador = Entregador::findOrFail($id);
        Gate::authorize('view', $entregador);

        return response()->json(new EntregadorResource($entregador));
    }

    public function update(SalvarEntregadorRequest $request, int $id): JsonResponse
    {
        $entregador = Entregador::findOrFail($id);
        Gate::authorize('update', $entregador);

        $data = $request->validated();
        if (isset($data['telefone'])) {
            $data['telefone_bidx'] = $this->blindIndex->generate($this->blindIndex->normalizePhone($data['telefone']));
        }

        $entregador->update($data);

        return response()->json([
            'message' => 'Entregador atualizado com sucesso.',
            'entregador' => new EntregadorResource($entregador),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $entregador = Entregador::findOrFail($id);
        Gate::authorize('delete', $entregador);

        $entregador->delete();

        return response()->json(['message' => 'Entregador removido com sucesso.']);
    }
}
