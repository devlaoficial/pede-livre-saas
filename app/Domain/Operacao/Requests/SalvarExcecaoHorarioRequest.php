<?php

declare(strict_types=1);

namespace App\Domain\Operacao\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SalvarExcecaoHorarioRequest extends FormRequest
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
            'data' => ['required', 'date', 'date_format:Y-m-d'],
            'fechado' => ['required', 'boolean'],
            'abre' => ['nullable', 'required_if:fechado,false', 'date_format:H:i'],
            'fecha' => ['nullable', 'required_if:fechado,false', 'date_format:H:i'],
            'descricao' => ['nullable', 'string', 'max:150'],
        ];
    }
}
