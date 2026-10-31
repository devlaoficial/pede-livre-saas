<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ValidarCupomRequest extends FormRequest
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
            'codigo' => ['required', 'string', 'max:50'],
            'subtotal_centavos' => ['required', 'integer', 'min:0'],
            'canal' => ['required', 'string', 'in:delivery,retirada,mesa,balcao'],
            'telefone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
