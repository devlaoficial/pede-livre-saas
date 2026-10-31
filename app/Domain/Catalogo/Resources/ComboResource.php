<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Resources;

use App\Domain\Catalogo\Models\Combo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Combo
 */
final class ComboResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'preco_centavos' => $this->preco_centavos,
            'imagem' => $this->imagem,
            'ativo' => $this->ativo,
            'itens' => $this->itens->map(fn ($item) => [
                'produto_id' => $item->produto_id,
                'nome' => $item->produto?->nome,
                'qtd' => $item->qtd,
            ]),
        ];
    }
}
