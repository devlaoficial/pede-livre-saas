<?php

declare(strict_types=1);

namespace App\Domain\Autenticacao\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AtualizarUsuarioRequest extends FormRequest
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
            'nome' => ['sometimes', 'string', 'max:120'],
            'email' => ['sometimes', 'string', 'email', 'max:150'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'senha' => ['nullable', 'string', 'min:8'],
            'papel' => ['sometimes', 'string', 'in:gerente,caixa,cozinha,garcom,entregador'],
            'ativo' => ['sometimes', 'boolean'],
        ];
    }
}
