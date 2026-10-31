<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Controllers;

use App\Domain\Catalogo\Models\OpcaoAdicional;
use App\Domain\Catalogo\Models\Produto;
use App\Domain\Catalogo\Services\CardapioCacheService;
use App\Support\Tenancy\LojaContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

final class EstoqueRapidoController
{
    public function __construct(
        private readonly CardapioCacheService $cacheService,
        private readonly LojaContext $context
    ) {}

    public function pausarProduto(int $id): JsonResponse
    {
        $produto = Produto::findOrFail($id);
        Gate::authorize('update', $produto);

        $novoStatus = !$produto->esgotado;
        $produto->update(['esgotado' => $novoStatus]);

        $this->cacheService->invalidar((int) $this->context->getLojaId());

        return response()->json([
            'id' => $produto->id,
            'nome' => $produto->nome,
            'esgotado' => $novoStatus,
            'message' => $novoStatus ? 'Produto pausado (esgotado) no cardápio.' : 'Produto reativado no cardápio.',
        ]);
    }

    public function pausarOpcaoAdicional(int $id): JsonResponse
    {
        $opcao = OpcaoAdicional::findOrFail($id);
        Gate::authorize('update', $opcao->grupo);

        $novoStatus = !$opcao->esgotado;
        $opcao->update(['esgotado' => $novoStatus]);

        $this->cacheService->invalidar((int) $this->context->getLojaId());

        return response()->json([
            'id' => $opcao->id,
            'nome' => $opcao->nome,
            'esgotado' => $novoStatus,
            'message' => $novoStatus ? 'Adicional pausado (esgotado).' : 'Adicional reativado.',
        ]);
    }
}
