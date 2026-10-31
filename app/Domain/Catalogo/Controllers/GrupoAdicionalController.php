<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Controllers;

use App\Domain\Catalogo\Models\GrupoAdicional;
use App\Domain\Catalogo\Models\OpcaoAdicional;
use App\Domain\Catalogo\Requests\SalvarGrupoAdicionalRequest;
use App\Domain\Catalogo\Resources\GrupoAdicionalResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class GrupoAdicionalController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', GrupoAdicional::class);

        $grupos = GrupoAdicional::with('opcoes')->orderBy('nome')->get();
        return GrupoAdicionalResource::collection($grupos);
    }

    public function store(SalvarGrupoAdicionalRequest $request): JsonResponse
    {
        Gate::authorize('create', GrupoAdicional::class);

        $data = $request->validated();
        $opcoes = $data['opcoes'] ?? [];
        unset($data['opcoes']);

        $grupo = DB::transaction(function () use ($data, $opcoes): GrupoAdicional {
            $grupo = GrupoAdicional::create($data);

            foreach ($opcoes as $opt) {
                OpcaoAdicional::create([
                    'grupo_id' => $grupo->id,
                    'nome' => $opt['nome'],
                    'preco_centavos' => $opt['preco_centavos'],
                    'ativo' => $opt['ativo'] ?? true,
                ]);
            }

            return $grupo->load('opcoes');
        });

        return response()->json([
            'message' => 'Grupo de adicionais criado com sucesso.',
            'grupo' => new GrupoAdicionalResource($grupo),
        ], Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $grupo = GrupoAdicional::with('opcoes')->findOrFail($id);
        Gate::authorize('view', $grupo);

        return response()->json(new GrupoAdicionalResource($grupo));
    }

    public function update(SalvarGrupoAdicionalRequest $request, int $id): JsonResponse
    {
        $grupo = GrupoAdicional::findOrFail($id);
        Gate::authorize('update', $grupo);

        $data = $request->validated();
        $opcoes = $data['opcoes'] ?? null;
        unset($data['opcoes']);

        DB::transaction(function () use ($grupo, $data, $opcoes): void {
            $grupo->update($data);

            if ($opcoes !== null) {
                $grupo->opcoes()->delete();
                foreach ($opcoes as $opt) {
                    OpcaoAdicional::create([
                        'grupo_id' => $grupo->id,
                        'nome' => $opt['nome'],
                        'preco_centavos' => $opt['preco_centavos'],
                        'ativo' => $opt['ativo'] ?? true,
                    ]);
                }
            }
        });

        return response()->json([
            'message' => 'Grupo de adicionais atualizado com sucesso.',
            'grupo' => new GrupoAdicionalResource($grupo->load('opcoes')),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $grupo = GrupoAdicional::findOrFail($id);
        Gate::authorize('delete', $grupo);

        $grupo->delete();

        return response()->json(['message' => 'Grupo removido com sucesso.']);
    }
}
