<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Requests;

use App\Support\Tenancy\LojaContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ReordenarCategoriasRequest extends FormRequest
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
            'itens' => ['required', 'array', 'min:1'],
            'itens.*.id' => [
                'required',
                'integer',
                Rule::exists('categorias', 'id')->where('loja_id', $lojaId),
            ],
            'itens.*.ordem' => ['required', 'integer', 'min:0'],
        ];
    }
}
