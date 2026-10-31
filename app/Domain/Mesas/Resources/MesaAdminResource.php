<?php

declare(strict_types=1);

namespace App\Domain\Mesas\Resources;

use App\Domain\Mesas\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Mesa
 */
final class MesaAdminResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lojaSlug = $this->loja?->slug ?? '';
        $qrUrl = url("/p/{$lojaSlug}/m/{$this->qr_token}");

        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'nome' => $this->nome,
            'qr_token' => $this->qr_token,
            'qr_url' => $qrUrl,
            'capacidade' => $this->capacidade,
            'status' => $this->status,
            'comanda_ativa' => $this->comandaAtiva ? [
                'id' => $this->comandaAtiva->id,
                'aberta_em' => $this->comandaAtiva->aberta_em?->toIso8601String(),
                'total_centavos' => $this->comandaAtiva->total_centavos,
            ] : null,
        ];
    }
}
