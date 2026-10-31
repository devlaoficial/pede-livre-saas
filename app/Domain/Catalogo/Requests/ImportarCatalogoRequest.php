<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ImportarCatalogoRequest extends FormRequest
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
            'categorias' => ['required', 'array', 'min:1'],
            'categorias.*.nome' => ['required', 'string', 'max:100'],
            'categorias.*.descricao' => ['nullable', 'string', 'max:255'],
            'categorias.*.ordem' => ['nullable', 'integer', 'min:0'],
            'categorias.*.canais' => ['nullable', 'array'],
            'categorias.*.produtos' => ['required', 'array', 'min:1'],
            'categorias.*.produtos.*.nome' => ['required', 'string', 'max:120'],
            'categorias.*.produtos.*.descricao' => ['nullable', 'string'],
            'categorias.*.produtos.*.preco_centavos' => ['required', 'integer', 'min:0'],
            'categorias.*.produtos.*.preco_promocional_centavos' => ['nullable', 'integer', 'min:0'],
            'categorias.*.produtos.*.canais' => ['nullable', 'array'],
            'categorias.*.produtos.*.tempo_preparo_min' => ['nullable', 'integer', 'min:0'],
            'categorias.*.produtos.*.estacao_kds' => ['nullable', 'string', 'in:chapa,forno,sushi,bar,geral'],
            'categorias.*.produtos.*.variacoes' => ['nullable', 'array'],
            'categorias.*.produtos.*.variacoes.*.nome' => ['required_with:categorias.*.produtos.*.variacoes', 'string', 'max:60'],
            'categorias.*.produtos.*.variacoes.*.preco_centavos' => ['required_with:categorias.*.produtos.*.variacoes', 'integer', 'min:0'],
        ];
    }
}
