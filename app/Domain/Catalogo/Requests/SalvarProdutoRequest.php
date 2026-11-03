<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Requests;

use App\Support\Tenancy\LojaContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SalvarProdutoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('grupos_adicionais') && is_array($this->input('grupos_adicionais'))) {
            $normalizados = [];
            foreach (array_values($this->input('grupos_adicionais')) as $idx => $item) {
                if (is_numeric($item)) {
                    $normalizados[] = [
                        'grupo_id' => (int) $item,
                        'ordem' => $idx,
                    ];
                } elseif (is_array($item) && isset($item['grupo_id'])) {
                    $normalizados[] = [
                        'grupo_id' => (int) $item['grupo_id'],
                        'ordem' => isset($item['ordem']) ? (int) $item['ordem'] : $idx,
                    ];
                } elseif (is_array($item) && isset($item['id'])) {
                    $normalizados[] = [
                        'grupo_id' => (int) $item['id'],
                        'ordem' => isset($item['ordem']) ? (int) $item['ordem'] : $idx,
                    ];
                }
            }
            $this->merge(['grupos_adicionais' => $normalizados]);
        }

        if (!$this->has('canais') || empty($this->input('canais'))) {
            $this->merge(['canais' => ['delivery', 'retirada', 'mesa', 'balcao']]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $lojaId = (int) app(LojaContext::class)->getLojaId();

        return [
            'categoria_id' => [
                'required',
                'integer',
                Rule::exists('categorias', 'id')->where('loja_id', $lojaId),
            ],
            'nome' => ['required', 'string', 'max:120'],
            'descricao' => ['nullable', 'string'],
            'preco_centavos' => ['required', 'integer', 'min:0'],
            'preco_promocional_centavos' => ['nullable', 'integer', 'min:0'],
            'imagem' => ['nullable', 'string', 'max:255'],
            'ativo' => ['nullable', 'boolean'],
            'esgotado' => ['nullable', 'boolean'],
            'controla_estoque' => ['nullable', 'boolean'],
            'estoque_qtd' => ['nullable', 'integer', 'min:0'],
            'canais' => ['required', 'array', 'min:1'],
            'canais.*' => ['string', 'in:delivery,mesa,balcao,retirada'],
            'ordem' => ['nullable', 'integer', 'min:0'],
            'tempo_preparo_min' => ['nullable', 'integer', 'min:0'],
            'estacao_kds' => ['nullable', 'string', 'in:chapa,forno,fritadeira,sushi,bar,sobremesa,geral'],
            'alergenos' => ['nullable', 'array'],
            'codigo_pdv' => ['nullable', 'string', 'max:50'],
            'grupos_adicionais' => ['nullable', 'array'],
            'grupos_adicionais.*.grupo_id' => [
                'required_with:grupos_adicionais',
                'integer',
                Rule::exists('grupos_adicionais', 'id')->where('loja_id', $lojaId),
            ],
            'grupos_adicionais.*.ordem' => ['nullable', 'integer'],
            'variacoes' => ['nullable', 'array'],
            'variacoes.*.nome' => ['required_with:variacoes', 'string', 'max:60'],
            'variacoes.*.preco_centavos' => ['required_with:variacoes', 'integer', 'min:0'],
            'variacoes.*.ordem' => ['nullable', 'integer'],
        ];
    }
}
