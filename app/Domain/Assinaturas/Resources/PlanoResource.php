<?php

declare(strict_types=1);

namespace App\Domain\Assinaturas\Resources;

use App\Domain\Assinaturas\Models\Plano;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Plano
 */
final class PlanoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'preco_centavos' => $this->preco_centavos,
            'recursos_json' => $this->recursos_json,
            'limite_usuarios' => $this->limite_usuarios,
        ];
    }
}
