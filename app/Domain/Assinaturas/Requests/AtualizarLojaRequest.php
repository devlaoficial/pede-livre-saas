<?php

declare(strict_types=1);

namespace App\Domain\Assinaturas\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AtualizarLojaRequest extends FormRequest
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
            'telefone_whatsapp' => ['sometimes', 'string', 'max:30'],
            'cor_primaria' => ['sometimes', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'endereco' => ['sometimes', 'string', 'max:255'],
            'cidade' => ['sometimes', 'string', 'max:100'],
            'uf' => ['sometimes', 'string', 'size:2'],
            'config_json' => ['sometimes', 'array'],
        ];
    }
}
