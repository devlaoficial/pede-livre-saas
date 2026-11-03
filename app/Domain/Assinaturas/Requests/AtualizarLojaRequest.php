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
            'logo' => ['sometimes', 'nullable', 'string', 'max:255'],
            'capa' => ['sometimes', 'nullable', 'string', 'max:255'],
            'dominio_proprio' => ['sometimes', 'nullable', 'string', 'max:120'],
            'endereco' => ['sometimes', 'nullable', 'string', 'max:255'],
            'cidade' => ['sometimes', 'nullable', 'string', 'max:100'],
            'uf' => ['sometimes', 'nullable', 'string', 'size:2'],
            'lat' => ['sometimes', 'nullable', 'numeric'],
            'lng' => ['sometimes', 'nullable', 'numeric'],
            'config_json' => ['sometimes', 'array'],
        ];
    }
}
