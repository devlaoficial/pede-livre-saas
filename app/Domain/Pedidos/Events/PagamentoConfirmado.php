<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Events;

use App\Domain\Pedidos\Models\Pedido;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class PagamentoConfirmado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Pedido $pedido
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("loja.{$this->pedido->loja_id}.pedidos"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'pagamento.confirmado';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'pedido_id' => $this->pedido->id,
            'codigo_publico' => $this->pedido->codigo_publico,
            'metodo' => $this->pedido->forma_pagamento,
            'valor_centavos' => $this->pedido->total,
            'pago_em' => now()->toIso8601String(),
        ];
    }
}
