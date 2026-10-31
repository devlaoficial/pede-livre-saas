<?php

declare(strict_types=1);

namespace App\Domain\Entregas\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SalvarEntregadorRequest extends FormRequest
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
            'nome' => ['required', 'string', 'max:120'],
            'telefone' => ['required', 'string', 'max:30'],
            'tipo' => ['required', 'string', 'in:proprio,freelancer'],
            'valor_por_entrega_centavos' => ['required', 'integer', 'min:0'],
            'ativo' => ['nullable', 'boolean'],
        ];
    }
}
