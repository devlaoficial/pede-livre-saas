<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Resources;

use App\Domain\Pedidos\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Pedido
 */
final class PedidoPublicoTrackingResource extends JsonResource
{
    /**
     * Public order tracking resource strictly masking customer PII.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $cliente = $this->cliente;
        $telefoneMascarado = null;
        if ($cliente?->telefone) {
            $raw = preg_replace('/\D+/', '', (string) $cliente->telefone);
            $telefoneMascarado = '(' . substr($raw, -11, 2) . ') 9****-' . substr($raw, -4);
        }

        $enderecoSnapshot = $this->endereco_json;
        $enderecoMascarado = null;
        if (is_array($enderecoSnapshot)) {
            $rua = $enderecoSnapshot['rua'] ?? '';
            $bairro = $enderecoSnapshot['bairro'] ?? '';
            $ruaMascarada = mb_substr($rua, 0, 4) . '***';
            $enderecoMascarado = "{$ruaMascarada}, *** - {$bairro}";
        }

        return [
            'codigo_publico' => $this->codigo_publico,
            'status' => $this->status,
            'canal' => $this->canal,
            'cliente' => [
                'nome' => $cliente?->nome,
                'telefone' => $telefoneMascarado,
            ],
            'endereco' => $enderecoMascarado,
            'subtotal_centavos' => $this->subtotal,
            'taxa_entrega_centavos' => $this->taxa_entrega,
            'desconto_centavos' => $this->desconto,
            'desconto_cashback_centavos' => $this->desconto_cashback,
            'taxa_servico_centavos' => $this->taxa_servico,
            'total_centavos' => $this->total,
            'forma_pagamento' => $this->forma_pagamento,
            'pagamento_status' => $this->pagamento_status,
            'itens' => $this->itens->map(fn ($item) => [
                'nome' => $item->nome_snapshot,
                'variacao' => $item->variacao_snapshot,
                'qtd' => $item->qtd,
                'preco_unit' => $item->preco_unit,
                'total' => $item->total,
                'adicionais' => $item->adicionais_json,
            ]),
            'eventos' => $this->eventos->map(fn ($e) => [
                'status' => $e->status_para,
                'data_hora' => $e->created_at?->toIso8601String(),
                'observacao' => $e->observacao,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
