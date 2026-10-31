<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Events;

use App\Domain\Pedidos\Models\Pedido;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class PedidoCriado implements ShouldBroadcastNow
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
        return 'pedido.criado';
    }

    /**
     * Minimal, non-sensitive payload to prevent PII exposure over WebSockets.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->pedido->id,
            'codigo_publico' => $this->pedido->codigo_publico,
            'canal' => $this->pedido->canal,
            'status' => $this->pedido->status,
            'total_centavos' => $this->pedido->total,
            'cliente_nome' => $this->pedido->cliente?->nome,
            'total_itens' => $this->pedido->itens()->count(),
            'created_at' => $this->pedido->created_at?->toIso8601String(),
        ];
    }
}
