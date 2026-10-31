<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Requests;

use App\Support\Tenancy\LojaContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AtualizarStatusPedidoRequest extends FormRequest
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
            'status' => [
                'required',
                'string',
                'in:aceito,em_preparo,pronto,saiu_entrega,entregue,retirado,servido,cancelado'
            ],
            'cancelado_motivo' => ['nullable', 'required_if:status,cancelado', 'string', 'min:3', 'max:255'],
            'entregador_id' => [
                'nullable',
                'integer',
                Rule::exists('entregadores', 'id')->where('loja_id', $lojaId),
            ],
            'observacao' => ['nullable', 'string', 'max:255'],
        ];
    }
}
