<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Controllers;

use App\Domain\Catalogo\Models\Produto;
use App\Domain\Catalogo\Models\ProdutoVariacao;
use App\Domain\Catalogo\Requests\SalvarProdutoRequest;
use App\Domain\Catalogo\Resources\ProdutoResource;
use App\Domain\Catalogo\Services\CardapioCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class ProdutoController
{
    public function __construct(
        private readonly CardapioCacheService $cacheService
    ) {}
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Produto::class);

        $query = Produto::with(['variacoes', 'gruposAdicionais.opcoes'])->orderBy('ordem');

        if ($request->has('categoria_id')) {
            $query->where('categoria_id', $request->integer('categoria_id'));
        }

        if ($request->has('canal')) {
            $query->porCanal($request->string('canal')->toString());
        }

        if ($request->boolean('disponiveis_apenas')) {
            $query->disponiveis();
        }

        return ProdutoResource::collection($query->get());
    }

    public function store(SalvarProdutoRequest $request): JsonResponse
    {
        Gate::authorize('create', Produto::class);

        $data = $request->validated();
        $variacoes = $data['variacoes'] ?? [];
        $grupos = $data['grupos_adicionais'] ?? [];
        unset($data['variacoes'], $data['grupos_adicionais']);

        $produto = DB::transaction(function () use ($data, $variacoes, $grupos): Produto {
            $produto = Produto::create($data);

            foreach ($variacoes as $var) {
                ProdutoVariacao::create([
                    'produto_id' => $produto->id,
                    'nome' => $var['nome'],
                    'preco_centavos' => $var['preco_centavos'],
                    'ordem' => $var['ordem'] ?? 0,
                ]);
            }

            if (!empty($grupos)) {
                $syncData = [];
                foreach ($grupos as $g) {
                    $syncData[$g['grupo_id']] = ['ordem' => $g['ordem'] ?? 0];
                }
                $produto->gruposAdicionais()->sync($syncData);
            }

            return $produto->load(['variacoes', 'gruposAdicionais.opcoes']);
        });

        $this->cacheService->invalidar((int) $produto->loja_id);

        return response()->json([
            'message' => 'Produto criado com sucesso.',
            'produto' => new ProdutoResource($produto),
        ], Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $produto = Produto::with(['variacoes', 'gruposAdicionais.opcoes'])->findOrFail($id);
        Gate::authorize('view', $produto);

        return response()->json(new ProdutoResource($produto));
    }

    public function update(SalvarProdutoRequest $request, int $id): JsonResponse
    {
        $produto = Produto::findOrFail($id);
        Gate::authorize('update', $produto);

        $data = $request->validated();
        $variacoes = $data['variacoes'] ?? null;
        $grupos = $data['grupos_adicionais'] ?? null;
        unset($data['variacoes'], $data['grupos_adicionais']);

        DB::transaction(function () use ($produto, $data, $variacoes, $grupos): void {
            $produto->update($data);

            if ($variacoes !== null) {
                $produto->variacoes()->delete();
                foreach ($variacoes as $var) {
                    ProdutoVariacao::create([
                        'produto_id' => $produto->id,
                        'nome' => $var['nome'],
                        'preco_centavos' => $var['preco_centavos'],
                        'ordem' => $var['ordem'] ?? 0,
                    ]);
                }
            }

            if ($grupos !== null) {
                $syncData = [];
                foreach ($grupos as $g) {
                    $syncData[$g['grupo_id']] = ['ordem' => $g['ordem'] ?? 0];
                }
                $produto->gruposAdicionais()->sync($syncData);
            }
        });

        $this->cacheService->invalidar((int) $produto->loja_id);

        return response()->json([
            'message' => 'Produto atualizado com sucesso.',
            'produto' => new ProdutoResource($produto->load(['variacoes', 'gruposAdicionais.opcoes'])),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $produto = Produto::findOrFail($id);
        Gate::authorize('delete', $produto);

        $lojaId = (int) $produto->loja_id;
        $produto->delete();
        $this->cacheService->invalidar($lojaId);

        return response()->json(['message' => 'Produto removido com sucesso.']);
    }
}
