<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Controllers;

use App\Domain\Catalogo\Models\Categoria;
use App\Domain\Catalogo\Requests\SalvarCategoriaRequest;
use App\Domain\Catalogo\Resources\CategoriaResource;
use App\Domain\Catalogo\Services\CardapioCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class CategoriaController
{
    public function __construct(
        private readonly CardapioCacheService $cacheService
    ) {}
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Categoria::class);

        $query = Categoria::with(['produtos' => fn ($q) => $q->orderBy('ordem')])->orderBy('ordem');

        if ($request->has('canal')) {
            $query->porCanal($request->string('canal')->toString());
        }

        return CategoriaResource::collection($query->get());
    }

    public function store(SalvarCategoriaRequest $request): JsonResponse
    {
        Gate::authorize('create', Categoria::class);

        $categoria = Categoria::create($request->validated());
        $this->cacheService->invalidar((int) $categoria->loja_id);

        return response()->json([
            'message' => 'Categoria criada com sucesso.',
            'categoria' => new CategoriaResource($categoria),
        ], Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $categoria = Categoria::with(['produtos.variacoes', 'produtos.gruposAdicionais.opcoes'])->findOrFail($id);
        Gate::authorize('view', $categoria);

        return response()->json(new CategoriaResource($categoria));
    }

    public function update(SalvarCategoriaRequest $request, int $id): JsonResponse
    {
        $categoria = Categoria::findOrFail($id);
        Gate::authorize('update', $categoria);

        $categoria->update($request->validated());
        $this->cacheService->invalidar((int) $categoria->loja_id);

        return response()->json([
            'message' => 'Categoria atualizada com sucesso.',
            'categoria' => new CategoriaResource($categoria),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $categoria = Categoria::findOrFail($id);
        Gate::authorize('delete', $categoria);

        $lojaId = (int) $categoria->loja_id;
        $categoria->delete();
        $this->cacheService->invalidar($lojaId);

        return response()->json(['message' => 'Categoria removida com sucesso.']);
    }
}
