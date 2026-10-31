<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SalvarGrupoAdicionalRequest extends FormRequest
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
            'minimo' => ['required', 'integer', 'min:0'],
            'maximo' => ['required', 'integer', 'min:1', 'gte:minimo'],
            'obrigatorio' => ['nullable', 'boolean'],
            'tipo' => ['required', 'string', 'in:adicional,escolha_unica,sabor_meio_a_meio'],
            'opcoes' => ['nullable', 'array'],
            'opcoes.*.nome' => ['required_with:opcoes', 'string', 'max:100'],
            'opcoes.*.preco_centavos' => ['required_with:opcoes', 'integer', 'min:0'],
            'opcoes.*.ativo' => ['nullable', 'boolean'],
        ];
    }
}
