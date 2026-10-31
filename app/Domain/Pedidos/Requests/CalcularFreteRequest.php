<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CalcularFreteRequest extends FormRequest
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
            'cep' => ['nullable', 'string', 'max:10'],
            'bairro' => ['nullable', 'string', 'max:100'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'subtotal_centavos' => ['required', 'integer', 'min:0'],
        ];
    }
}
