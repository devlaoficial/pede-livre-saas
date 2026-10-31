<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Requests;

use App\Support\Tenancy\LojaContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SalvarComboRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $lojaId = (int) app(LojaContext::class)->getLojaId();

        return [
            'nome' => ['required', 'string', 'max:120'],
            'descricao' => ['nullable', 'string'],
            'preco_centavos' => ['required', 'integer', 'min:0'],
            'imagem' => ['nullable', 'string', 'max:255'],
            'ativo' => ['nullable', 'boolean'],
            'itens' => ['required', 'array', 'min:1'],
            'itens.*.produto_id' => [
                'required',
                'integer',
                Rule::exists('produtos', 'id')->where('loja_id', $lojaId),
            ],
            'itens.*.qtd' => ['required', 'integer', 'min:1'],
        ];
    }
}
