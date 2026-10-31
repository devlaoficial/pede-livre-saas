<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Resources;

use App\Domain\Catalogo\Models\OpcaoAdicional;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OpcaoAdicional
 */
final class OpcaoAdicionalResource extends JsonResource
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
            'ativo' => $this->ativo,
            'esgotado' => $this->esgotado,
        ];
    }
}
