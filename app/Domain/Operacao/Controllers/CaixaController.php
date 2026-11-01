<?php

declare(strict_types=1);

namespace App\Domain\Operacao\Controllers;

use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Operacao\Models\CaixaSessao;
use App\Domain\Pedidos\Models\Pedido;
use App\Domain\Pedidos\Services\CaixaService;
use App\Support\Tenancy\LojaContext;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CaixaController
{
    public function __construct(
        private readonly CaixaService $caixaService,
        private readonly LojaContext $context
    ) {}

    public function status(Request $request): JsonResponse
    {
        /** @var Usuario $user */
        $user = $request->user();
        $loja = $user->loja ?? $this->context->getLoja();

        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NAO_ENCONTRADA'], Response::HTTP_NOT_FOUND);
        }

        /** @var CaixaSessao|null $sessaoAberta */
        $sessaoAberta = CaixaSessao::where('loja_id', $loja->id)
            ->whereNull('fechado_em')
            ->with(['usuario', 'movimentos'])
            ->first();

        if ($sessaoAberta === null) {
            /** @var CaixaSessao|null $ultimaSessao */
            $ultimaSessao = CaixaSessao::where('loja_id', $loja->id)
                ->whereNotNull('fechado_em')
                ->with('usuario')
                ->latest('fechado_em')
                ->first();

            return response()->json([
                'aberto' => false,
                'sessao' => null,
                'ultima_sessao' => $ultimaSessao ? [
                    'id' => $ultimaSessao->id,
                    'operador_nome' => $ultimaSessao->usuario?->nome ?? 'Operador',
                    'aberto_em' => $ultimaSessao->aberto_em?->format('d/m/Y H:i'),
                    'fechado_em' => $ultimaSessao->fechado_em?->format('d/m/Y H:i'),
                    'valor_inicial_centavos' => $ultimaSessao->valor_inicial_centavos,
                    'valor_final_centavos' => $ultimaSessao->valor_final_centavos,
                    'diferenca_centavos' => $ultimaSessao->diferenca_centavos,
                ] : null,
            ]);
        }

        $abertoEm = $sessaoAberta->aberto_em;

        // Vendas registradas no sistema enquanto este turno esteve aberto
        $pedidosSessao = Pedido::where('loja_id', $loja->id)
            ->where('created_at', '>=', $abertoEm)
            ->where('status', '!=', 'cancelado')
            ->get();

        $vendasDinheiro = (int) $pedidosSessao->where('forma_pagamento', 'dinheiro')->sum('total');
        $vendasPix = (int) $pedidosSessao->where('forma_pagamento', 'pix')->sum('total');
        $vendasCartao = (int) $pedidosSessao->whereIn('forma_pagamento', ['cartao_maquina', 'cartao_online', 'voucher'])->sum('total');

        $suprimentos = (int) $sessaoAberta->movimentos->where('tipo', 'suprimento')->sum('valor_centavos');
        $sangrias = (int) $sessaoAberta->movimentos->where('tipo', 'sangria')->sum('valor_centavos');

        $saldoInicial = (int) $sessaoAberta->valor_inicial_centavos;
        $saldoEsperadoGaveta = $saldoInicial + $vendasDinheiro + $suprimentos - $sangrias;
        $faturamentoTotal = $vendasDinheiro + $vendasPix + $vendasCartao;

        return response()->json([
            'aberto' => true,
            'sessao' => [
                'id' => $sessaoAberta->id,
                'operador_nome' => $sessaoAberta->usuario?->nome ?? 'Operador',
                'aberto_em' => $sessaoAberta->aberto_em->format('d/m/Y H:i'),
                'aberto_em_hora' => $sessaoAberta->aberto_em->format('H:i'),
                'valor_inicial_centavos' => $saldoInicial,
                'total_vendas_dinheiro_centavos' => $vendasDinheiro,
                'total_vendas_pix_centavos' => $vendasPix,
                'total_vendas_cartao_centavos' => $vendasCartao,
                'total_suprimentos_centavos' => $suprimentos,
                'total_sangrias_centavos' => $sangrias,
                'saldo_esperado_gaveta_centavos' => $saldoEsperadoGaveta,
                'faturamento_total_centavos' => $faturamentoTotal,
                'total_pedidos' => $pedidosSessao->count(),
                'movimentos' => $sessaoAberta->movimentos->sortByDesc('created_at')->values()->map(fn($m) => [
                    'id' => $m->id,
                    'tipo' => $m->tipo,
                    'valor_centavos' => $m->valor_centavos,
                    'observacao' => $m->observacao,
                    'horario' => $m->created_at->format('H:i'),
                ]),
            ],
        ]);
    }

    public function abrir(Request $request): JsonResponse
    {
        /** @var Usuario $user */
        $user = $request->user();
        if (!$user->isCaixa()) {
            return response()->json([
                'error' => 'FORBIDDEN',
                'message' => 'Apenas operadores de caixa, gerentes ou donos podem abrir o caixa.',
            ], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'valor_inicial_centavos' => ['required', 'integer', 'min:0'],
        ]);

        $loja = $this->context->getLoja();

        try {
            $sessao = $this->caixaService->abrirSessao(
                $loja->id,
                $user,
                $request->integer('valor_inicial_centavos')
            );

            return response()->json([
                'message' => 'Caixa aberto com sucesso.',
                'sessao' => $sessao,
            ], Response::HTTP_CREATED);
        } catch (DomainException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function movimento(Request $request): JsonResponse
    {
        /** @var Usuario $user */
        $user = $request->user();
        if (!$user->isCaixa()) {
            return response()->json(['error' => 'FORBIDDEN'], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'sessao_id' => ['required', 'integer'],
            'tipo' => ['required', 'string', 'in:venda,sangria,suprimento'],
            'valor_centavos' => ['required', 'integer', 'min:1'],
            'observacao' => ['nullable', 'string', 'max:255'],
        ]);

        $loja = $this->context->getLoja();
        $sessao = CaixaSessao::where('loja_id', $loja->id)->findOrFail($request->integer('sessao_id'));

        try {
            $mov = $this->caixaService->registrarMovimento(
                $sessao,
                $request->string('tipo')->toString(),
                $request->integer('valor_centavos'),
                $request->input('observacao')
            );

            return response()->json([
                'message' => 'Movimento registrado no caixa.',
                'movimento' => $mov,
            ], Response::HTTP_CREATED);
        } catch (DomainException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function fechar(Request $request): JsonResponse
    {
        /** @var Usuario $user */
        $user = $request->user();
        if (!$user->isCaixa()) {
            return response()->json([
                'error' => 'FORBIDDEN',
                'message' => 'Garçons e equipe de cozinha não possuem permissão para fechar o caixa.',
            ], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'sessao_id' => ['required', 'integer'],
            'valor_final_centavos' => ['required', 'integer', 'min:0'],
        ]);

        $loja = $this->context->getLoja();
        $sessao = CaixaSessao::where('loja_id', $loja->id)->findOrFail($request->integer('sessao_id'));

        try {
            $sessaoFechada = $this->caixaService->fecharSessao(
                $sessao,
                $request->integer('valor_final_centavos')
            );

            return response()->json([
                'message' => 'Caixa fechado e conciliado com sucesso.',
                'sessao' => $sessaoFechada,
            ]);
        } catch (DomainException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
