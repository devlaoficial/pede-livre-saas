<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CriarPedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Honeypot bot protection runs before any validation so bots never get field-level feedback.
     */
    protected function prepareForValidation(): void
    {
        if (!empty($this->input('website'))) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                response()->json(['error' => 'BOT_DETECTED'], 403)
            );
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'website' => ['nullable', 'string'], // Honeypot: non-empty values are rejected with 403 by CheckoutController
            'idempotency_key' => ['required', 'string', 'max:100'],
            'canal' => ['required', 'string', 'in:delivery,retirada,mesa,balcao'],
            'cliente' => ['required', 'array'],
            'cliente.nome' => ['required', 'string', 'max:120'],
            'cliente.telefone' => ['required', 'string', 'max:30'],
            'cliente.email' => ['nullable', 'string', 'email', 'max:120'],
            'endereco' => ['required_if:canal,delivery', 'nullable', 'array'],
            'endereco.cep' => ['required_with:endereco', 'string', 'max:10'],
            'endereco.rua' => ['required_with:endereco', 'string', 'max:150'],
            'endereco.numero' => ['required_with:endereco', 'string', 'max:20'],
            'endereco.complemento' => ['nullable', 'string', 'max:100'],
            'endereco.bairro' => ['required_with:endereco', 'string', 'max:100'],
            'endereco.cidade' => ['required_with:endereco', 'string', 'max:100'],
            'endereco.uf' => ['required_with:endereco', 'string', 'size:2'],
            'mesa_token' => ['required_if:canal,mesa', 'nullable', 'string', 'size:32'],
            'itens' => ['required', 'array', 'min:1'],
            'itens.*.produto_id' => ['required', 'integer'],
            'itens.*.variacao_id' => ['nullable', 'integer'],
            'itens.*.qtd' => ['required', 'integer', 'min:1'],
            'itens.*.observacao' => ['nullable', 'string', 'max:255'],
            'itens.*.adicionais' => ['nullable', 'array'],
            'cupom_codigo' => ['nullable', 'string', 'max:50'],
            'cashback_resgate_centavos' => ['nullable', 'integer', 'min:0'],
            'forma_pagamento' => ['required', 'string', 'in:pix,cartao_online,cartao_maquina,dinheiro,voucher'],
            'troco_para' => ['nullable', 'integer', 'min:0'],
            'observacao' => ['nullable', 'string', 'max:500'],
        ];
    }
}
