<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Resources;

use App\Domain\Catalogo\Models\GrupoAdicional;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin GrupoAdicional
 */
final class GrupoAdicionalResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'minimo' => (int) $this->minimo,
            'maximo' => (int) $this->maximo,
            'min_escolhas' => (int) $this->minimo,
            'max_escolhas' => (int) $this->maximo,
            'obrigatorio' => (bool) $this->obrigatorio,
            'tipo' => $this->tipo,
            'opcoes' => OpcaoAdicionalResource::collection($this->whenLoaded('opcoes')),
        ];
    }
}
