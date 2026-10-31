<?php

declare(strict_types=1);

namespace App\Domain\Mesas\Resources;

use App\Domain\Mesas\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Mesa
 */
final class MesaPublicaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'mesa_id' => $this->id,
            'numero' => $this->numero,
            'nome' => $this->nome,
            'status' => $this->status,
            'comanda_ativa' => $this->comandaAtiva ? [
                'comanda_id' => $this->comandaAtiva->id,
                'aberta_em' => $this->comandaAtiva->aberta_em?->toIso8601String(),
                'total_centavos' => $this->comandaAtiva->total_centavos,
            ] : null,
            'chamada_garcom_habilitada' => true,
        ];
    }
}
