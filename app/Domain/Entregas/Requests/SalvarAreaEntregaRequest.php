<?php

declare(strict_types=1);

namespace App\Domain\Entregas\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SalvarAreaEntregaRequest extends FormRequest
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
            'tipo' => ['required', 'string', 'in:bairro,raio'],
            'nome_bairro' => ['required_if:tipo,bairro', 'nullable', 'string', 'max:100'],
            'raio_km_de' => ['required_if:tipo,raio', 'nullable', 'numeric', 'min:0'],
            'raio_km_ate' => ['required_if:tipo,raio', 'nullable', 'numeric', 'gte:raio_km_de'],
            'taxa_centavos' => ['required', 'integer', 'min:0'],
            'pedido_minimo_centavos' => ['nullable', 'integer', 'min:0'],
            'tempo_estimado_min' => ['nullable', 'integer', 'min:5'],
            'frete_gratis_acima_centavos' => ['nullable', 'integer', 'min:0'],
            'ativa' => ['nullable', 'boolean'],
        ];
    }
}
