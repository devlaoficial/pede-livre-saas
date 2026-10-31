<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Resources;

use App\Domain\Catalogo\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Produto
 */
final class ProdutoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'categoria_id' => $this->categoria_id,
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'preco_centavos' => $this->preco_centavos,
            'preco_promocional_centavos' => $this->preco_promocional_centavos,
            'preco_efetivo_centavos' => $this->preco_efetivo_centavos,
            'imagem' => $this->imagem,
            'ativo' => $this->ativo,
            'esgotado' => $this->esgotado,
            'controla_estoque' => $this->controla_estoque,
            'estoque_qtd' => $this->estoque_qtd,
            'canais' => $this->canais,
            'ordem' => $this->ordem,
            'tempo_preparo_min' => $this->tempo_preparo_min,
            'estacao_kds' => $this->estacao_kds,
            'alergenos' => $this->alergenos,
            'codigo_pdv' => $this->codigo_pdv,
            'variacoes' => ProdutoVariacaoResource::collection($this->whenLoaded('variacoes')),
            'grupos_adicionais' => GrupoAdicionalResource::collection($this->whenLoaded('gruposAdicionais')),
        ];
    }
}
