<?php

declare(strict_types=1);

namespace App\Domain\Entregas\Resources;

use App\Domain\Entregas\Models\Entregador;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Entregador
 */
final class EntregadorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'telefone' => $this->telefone, // Decrypted via cast
            'tipo' => $this->tipo,
            'valor_por_entrega_centavos' => $this->valor_por_entrega_centavos,
            'ativo' => $this->ativo,
        ];
    }
}
