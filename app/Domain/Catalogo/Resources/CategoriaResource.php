<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Resources;

use App\Domain\Catalogo\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Categoria
 */
final class CategoriaResource extends JsonResource
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
            'ordem' => $this->ordem,
            'ativa' => $this->ativa,
            'canais' => $this->canais,
            'horario_id' => $this->horario_id,
            'produtos' => ProdutoResource::collection($this->whenLoaded('produtos')),
        ];
    }
}
