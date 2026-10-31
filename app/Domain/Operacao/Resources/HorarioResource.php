<?php

declare(strict_types=1);

namespace App\Domain\Operacao\Resources;

use App\Domain\Operacao\Models\Horario;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Horario
 */
final class HorarioResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'canal' => $this->canal,
            'dia_semana' => $this->dia_semana,
            'abre' => substr($this->abre, 0, 5),
            'fecha' => substr($this->fecha, 0, 5),
        ];
    }
}
