<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Controllers;

use App\Domain\Catalogo\Models\Categoria;
use App\Domain\Catalogo\Models\Produto;
use App\Domain\Catalogo\Models\ProdutoVariacao;
use App\Domain\Catalogo\Requests\ImportarCatalogoRequest;
use App\Domain\Catalogo\Services\CardapioCacheService;
use App\Support\Tenancy\LojaContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class ImportacaoCatalogoController
{
    public function __construct(
        private readonly CardapioCacheService $cacheService,
        private readonly LojaContext $context
    ) {}

    public function importar(ImportarCatalogoRequest $request): JsonResponse
    {
        Gate::authorize('create', Categoria::class);

        $data = $request->validated();
        $lojaId = (int) $this->context->getLojaId();

        $totalCategorias = 0;
        $totalProdutos = 0;

        DB::transaction(function () use ($data, $lojaId, &$totalCategorias, &$totalProdutos): void {
            foreach ($data['categorias'] as $catIndex => $catData) {
                $categoria = Categoria::create([
                    'loja_id' => $lojaId,
                    'nome' => $catData['nome'],
                    'descricao' => $catData['descricao'] ?? null,
                    'ordem' => $catData['ordem'] ?? $catIndex,
                    'ativa' => true,
                    'canais' => $catData['canais'] ?? ['delivery', 'mesa', 'balcao'],
                ]);
                $totalCategorias++;

                foreach ($catData['produtos'] as $prodIndex => $prodData) {
                    $produto = Produto::create([
                        'loja_id' => $lojaId,
                        'categoria_id' => $categoria->id,
                        'nome' => $prodData['nome'],
                        'descricao' => $prodData['descricao'] ?? null,
                        'preco_centavos' => $prodData['preco_centavos'],
                        'preco_promocional_centavos' => $prodData['preco_promocional_centavos'] ?? null,
                        'ativo' => true,
                        'esgotado' => false,
                        'controla_estoque' => false,
                        'canais' => $prodData['canais'] ?? ['delivery', 'mesa', 'balcao'],
                        'ordem' => $prodIndex,
                        'tempo_preparo_min' => $prodData['tempo_preparo_min'] ?? 15,
                        'estacao_kds' => $prodData['estacao_kds'] ?? 'geral',
                    ]);
                    $totalProdutos++;

                    if (!empty($prodData['variacoes'])) {
                        foreach ($prodData['variacoes'] as $varIndex => $varData) {
                            ProdutoVariacao::create([
                                'loja_id' => $lojaId,
                                'produto_id' => $produto->id,
                                'nome' => $varData['nome'],
                                'preco_centavos' => $varData['preco_centavos'],
                                'ordem' => $varIndex,
                            ]);
                        }
                    }
                }
            }
        });

        $this->cacheService->invalidar($lojaId);

        return response()->json([
            'message' => 'Catálogo importado com sucesso.',
            'total_categorias_criadas' => $totalCategorias,
            'total_produtos_criados' => $totalProdutos,
        ], Response::HTTP_CREATED);
    }
}
