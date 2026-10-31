<?php

declare(strict_types=1);

namespace App\Domain\Operacao\Resources;

use App\Domain\Operacao\Models\ExcecaoHorario;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ExcecaoHorario
 */
final class ExcecaoHorarioResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'data' => $this->data?->format('Y-m-d'),
            'fechado' => $this->fechado,
            'abre' => $this->abre ? substr($this->abre, 0, 5) : null,
            'fecha' => $this->fecha ? substr($this->fecha, 0, 5) : null,
            'descricao' => $this->descricao,
        ];
    }
}
