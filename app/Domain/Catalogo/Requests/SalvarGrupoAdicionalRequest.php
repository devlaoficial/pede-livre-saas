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

    protected function prepareForValidation(): void
    {
        $minimo = $this->input('minimo', $this->input('min_escolhas', 0));
        $maximo = $this->input('maximo', $this->input('max_escolhas', 1));
        $obrigatorio = (bool) $this->input('obrigatorio', false);

        $minInt = max(0, (int) $minimo);
        $maxInt = max(1, (int) $maximo);
        if ($obrigatorio && $minInt < 1) {
            $minInt = 1;
        }
        if ($maxInt < $minInt) {
            $maxInt = $minInt;
        }

        $tipo = $this->input('tipo');
        if (!in_array($tipo, ['adicional', 'escolha_unica', 'sabor_meio_a_meio'], true)) {
            $tipo = $maxInt === 1 ? 'escolha_unica' : 'adicional';
        }

        $this->merge([
            'minimo' => $minInt,
            'maximo' => $maxInt,
            'obrigatorio' => $obrigatorio,
            'tipo' => $tipo,
        ]);
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
