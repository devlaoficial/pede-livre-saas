<?php

declare(strict_types=1);

namespace App\Domain\Entregas\Controllers;

use App\Domain\Entregas\Models\Entregador;
use App\Domain\Entregas\Requests\SalvarEntregadorRequest;
use App\Domain\Entregas\Resources\EntregadorResource;
use App\Support\Security\BlindIndexService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class EntregadorController
{
    public function __construct(
        private readonly BlindIndexService $blindIndex
    ) {}

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
        $telefoneBidx = $this->blindIndex->generateForPhone($telefone);

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
            $data['telefone_bidx'] = $this->blindIndex->generateForPhone($data['telefone']);
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
