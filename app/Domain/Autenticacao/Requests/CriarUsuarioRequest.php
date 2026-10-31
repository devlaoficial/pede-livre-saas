<?php

declare(strict_types=1);

namespace App\Domain\Autenticacao\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CriarUsuarioRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:150'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'senha' => ['required', 'string', 'min:8'],
            'papel' => ['required', 'string', 'in:gerente,caixa,cozinha,garcom,entregador'],
        ];
    }
}
