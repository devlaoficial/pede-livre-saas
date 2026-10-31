<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Controllers;

use App\Domain\Catalogo\Models\Categoria;
use App\Domain\Catalogo\Models\Produto;
use App\Domain\Catalogo\Requests\ReordenarCategoriasRequest;
use App\Domain\Catalogo\Requests\ReordenarProdutosRequest;
use App\Domain\Catalogo\Services\CardapioCacheService;
use App\Support\Tenancy\LojaContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class ReordenarCatalogoController
{
    public function __construct(
        private readonly CardapioCacheService $cacheService,
        private readonly LojaContext $context
    ) {}

    public function reordenarCategorias(ReordenarCategoriasRequest $request): JsonResponse
    {
        Gate::authorize('create', Categoria::class);

        /** @var array<int, array{id: int, ordem: int}> $itens */
        $itens = $request->validated('itens');

        DB::transaction(function () use ($itens): void {
            foreach ($itens as $item) {
                Categoria::where('id', $item['id'])->update(['ordem' => $item['ordem']]);
            }
        });

        $this->cacheService->invalidar((int) $this->context->getLojaId());

        return response()->json(['message' => 'Ordem das categorias atualizada com sucesso.']);
    }

    public function reordenarProdutos(ReordenarProdutosRequest $request): JsonResponse
    {
        Gate::authorize('create', Produto::class);

        /** @var array<int, array{id: int, ordem: int}> $itens */
        $itens = $request->validated('itens');

        DB::transaction(function () use ($itens): void {
            foreach ($itens as $item) {
                Produto::where('id', $item['id'])->update(['ordem' => $item['ordem']]);
            }
        });

        $this->cacheService->invalidar((int) $this->context->getLojaId());

        return response()->json(['message' => 'Ordem dos produtos atualizada com sucesso.']);
    }
}
