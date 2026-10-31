<?php

declare(strict_types=1);

namespace App\Domain\Mesas\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SalvarMesaRequest extends FormRequest
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
            'numero' => ['required', 'integer', 'min:1'],
            'nome' => ['nullable', 'string', 'max:60'],
            'capacidade' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'in:livre,ocupada,aguardando_pagamento'],
        ];
    }
}
