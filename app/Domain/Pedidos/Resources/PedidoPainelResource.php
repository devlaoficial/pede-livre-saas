<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Resources;

use App\Domain\Pedidos\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Pedido
 */
final class PedidoPainelResource extends JsonResource
{
    /**
     * Merchant Panel Order DTO with 1-tap WhatsApp notification links.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $cliente = $this->cliente;
        $telefoneRaw = $cliente?->telefone ? preg_replace('/\D+/', '', (string) $cliente->telefone) : null;
        if ($telefoneRaw !== null && (strlen($telefoneRaw) === 10 || strlen($telefoneRaw) === 11)) {
            $telefoneRaw = '55' . $telefoneRaw;
        }

        // WhatsApp notification message tailored to current status
        $whatsappUrl = null;
        if ($telefoneRaw) {
            $msg = match ($this->status) {
                'aceito' => "Olá {$cliente?->nome}! Seu pedido #{$this->codigo_publico} foi aceito e já está na fila de preparo. 👨‍🍳",
                'pronto' => "Olá {$cliente?->nome}! Seu pedido #{$this->codigo_publico} está pronto! 🎉",
                'saiu_entrega' => "Olá {$cliente?->nome}! Seu pedido #{$this->codigo_publico} acabou de sair para entrega com nosso motoboy! 🛵",
                'entregue' => "Olá {$cliente?->nome}! Pedido #{$this->codigo_publico} entregue. Bom apetite! Esperamos que goste. 🍔🍕",
                'cancelado' => "Olá {$cliente?->nome}. Infelizmente seu pedido #{$this->codigo_publico} precisou ser cancelado. Motivo: {$this->cancelado_motivo}",
                default => "Olá {$cliente?->nome}! Informações sobre seu pedido #{$this->codigo_publico}."
            };
            $whatsappUrl = 'https://wa.me/' . $telefoneRaw . '?text=' . urlencode($msg);
        }

        return [
            'id' => $this->id,
            'codigo_publico' => $this->codigo_publico,
            'canal' => $this->canal,
            'status' => $this->status,
            'cliente' => [
                'id' => $cliente?->id,
                'nome' => $cliente?->nome,
                'telefone' => $cliente?->telefone,
                'email' => $cliente?->email,
                'total_pedidos_historico' => $cliente?->total_pedidos ?? 1,
            ],
            'endereco' => $this->endereco_json,
            'mesa' => $this->mesa ? [
                'id' => $this->mesa->id,
                'numero' => $this->mesa->numero,
                'nome' => $this->mesa->nome,
            ] : null,
            'entregador' => $this->entregador ? [
                'id' => $this->entregador->id,
                'nome' => $this->entregador->nome,
            ] : null,
            'subtotal_centavos' => $this->subtotal,
            'taxa_entrega_centavos' => $this->taxa_entrega,
            'desconto_centavos' => $this->desconto,
            'desconto_cashback_centavos' => $this->desconto_cashback,
            'taxa_servico_centavos' => $this->taxa_servico,
            'total_centavos' => $this->total,
            'forma_pagamento' => $this->forma_pagamento,
            'pagamento_status' => $this->pagamento_status,
            'troco_para' => $this->troco_para,
            'observacao' => $this->observacao,
            'cancelado_motivo' => $this->cancelado_motivo,
            'tempo_decorrido_minutos' => $this->created_at ? (int) $this->created_at->diffInMinutes(now()) : 0,
            'whatsapp_notificacao_url' => $whatsappUrl,
            'itens' => $this->itens->map(fn ($item) => [
                'id' => $item->id,
                'produto_id' => $item->produto_id,
                'nome' => $item->nome_snapshot,
                'variacao' => $item->variacao_snapshot,
                'qtd' => $item->qtd,
                'preco_unit' => $item->preco_unit,
                'total' => $item->total,
                'observacao' => $item->observacao,
                'adicionais' => $item->adicionais_json,
                'status_cozinha' => $item->status_cozinha,
            ]),
            'eventos' => $this->eventos->map(fn ($e) => [
                'status_de' => $e->status_de,
                'status_para' => $e->status_para,
                'usuario' => $e->usuario?->nome,
                'observacao' => $e->observacao,
                'created_at' => $e->created_at?->toIso8601String(),
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
