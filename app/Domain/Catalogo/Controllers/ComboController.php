<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Controllers;

use App\Domain\Catalogo\Models\Combo;
use App\Domain\Catalogo\Models\ComboItem;
use App\Domain\Catalogo\Requests\SalvarComboRequest;
use App\Domain\Catalogo\Resources\ComboResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class ComboController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Combo::class);

        $combos = Combo::with('itens.produto')->orderBy('nome')->get();
        return ComboResource::collection($combos);
    }

    public function store(SalvarComboRequest $request): JsonResponse
    {
        Gate::authorize('create', Combo::class);

        $data = $request->validated();
        $itens = $data['itens'];
        unset($data['itens']);

        $combo = DB::transaction(function () use ($data, $itens): Combo {
            $combo = Combo::create($data);

            foreach ($itens as $item) {
                ComboItem::create([
                    'combo_id' => $combo->id,
                    'produto_id' => $item['produto_id'],
                    'qtd' => $item['qtd'],
                ]);
            }

            return $combo->load('itens.produto');
        });

        return response()->json([
            'message' => 'Combo criado com sucesso.',
            'combo' => new ComboResource($combo),
        ], Response::HTTP_CREATED);
    }

    public function show(int $id): JsonResponse
    {
        $combo = Combo::with('itens.produto')->findOrFail($id);
        Gate::authorize('view', $combo);

        return response()->json(new ComboResource($combo));
    }

    public function update(SalvarComboRequest $request, int $id): JsonResponse
    {
        $combo = Combo::findOrFail($id);
        Gate::authorize('update', $combo);

        $data = $request->validated();
        $itens = $data['itens'];
        unset($data['itens']);

        DB::transaction(function () use ($combo, $data, $itens): void {
            $combo->update($data);
            $combo->itens()->delete();

            foreach ($itens as $item) {
                ComboItem::create([
                    'combo_id' => $combo->id,
                    'produto_id' => $item['produto_id'],
                    'qtd' => $item['qtd'],
                ]);
            }
        });

        return response()->json([
            'message' => 'Combo atualizado com sucesso.',
            'combo' => new ComboResource($combo->load('itens.produto')),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $combo = Combo::findOrFail($id);
        Gate::authorize('delete', $combo);

        $combo->delete();

        return response()->json(['message' => 'Combo removido com sucesso.']);
    }
}
