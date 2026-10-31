<?php

declare(strict_types=1);

namespace App\Domain\Entregas\Resources;

use App\Domain\Entregas\Models\AreaEntrega;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AreaEntrega
 */
final class AreaEntregaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo' => $this->tipo,
            'nome_bairro' => $this->nome_bairro,
            'raio_km_de' => $this->raio_km_de,
            'raio_km_ate' => $this->raio_km_ate,
            'taxa_centavos' => $this->taxa_centavos,
            'pedido_minimo_centavos' => $this->pedido_minimo_centavos,
            'tempo_estimado_min' => $this->tempo_estimado_min,
            'frete_gratis_acima_centavos' => $this->frete_gratis_acima_centavos,
            'ativa' => $this->ativa,
        ];
    }
}
