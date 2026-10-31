<?php

declare(strict_types=1);

namespace App\Domain\Operacao\Controllers;

use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Operacao\Models\CaixaSessao;
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
