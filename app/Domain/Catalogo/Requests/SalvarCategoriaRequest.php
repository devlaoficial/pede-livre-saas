<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SalvarCategoriaRequest extends FormRequest
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
            'nome' => ['required', 'string', 'max:100'],
            'descricao' => ['nullable', 'string', 'max:255'],
            'ordem' => ['nullable', 'integer', 'min:0'],
            'ativa' => ['nullable', 'boolean'],
            'canais' => ['required', 'array', 'min:1'],
            'canais.*' => ['string', 'in:delivery,mesa,balcao,retirada'],
            'horario_id' => ['nullable', 'integer'],
        ];
    }
}
