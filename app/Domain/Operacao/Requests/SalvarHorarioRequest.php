<?php

declare(strict_types=1);

namespace App\Domain\Operacao\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SalvarHorarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'turnos' => ['required', 'array', 'min:1'],
            'turnos.*.canal' => ['required', 'string', 'in:delivery,mesa,balcao,retirada'],
            'turnos.*.dia_semana' => ['required', 'integer', 'between:0,6'],
            'turnos.*.abre' => ['required', 'date_format:H:i'],
            'turnos.*.fecha' => ['required', 'date_format:H:i'],
        ];
    }
}
