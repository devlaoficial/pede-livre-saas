<?php

declare(strict_types=1);

namespace App\Domain\Entregas\Controllers;

use App\Domain\Entregas\Models\AreaEntrega;
use App\Domain\Entregas\Requests\SalvarAreaEntregaRequest;
use App\Domain\Entregas\Resources\AreaEntregaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class AreaEntregaController
{
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', AreaEntrega::class);

        $areas = AreaEntrega::orderBy('tipo')->orderBy('raio_km_ate')->get();
        return AreaEntregaResource::collection($areas);
    }

    public function store(SalvarAreaEntregaRequest $request): JsonResponse
    {
        Gate::authorize('create', AreaEntrega::class);

        $area = AreaEntrega::create($request->validated());

        return response()->json([
            'message' => 'Área de entrega criada com sucesso.',
            'area' => new AreaEntregaResource($area),
        ], Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $area = AreaEntrega::findOrFail($id);
        Gate::authorize('view', $area);

        return response()->json(new AreaEntregaResource($area));
    }

    public function update(SalvarAreaEntregaRequest $request, int $id): JsonResponse
    {
        $area = AreaEntrega::findOrFail($id);
        Gate::authorize('update', $area);

        $area->update($request->validated());

        return response()->json([
            'message' => 'Área de entrega atualizada com sucesso.',
            'area' => new AreaEntregaResource($area),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $area = AreaEntrega::findOrFail($id);
        Gate::authorize('delete', $area);

        $area->delete();

        return response()->json(['message' => 'Área de entrega removida com sucesso.']);
    }
}
