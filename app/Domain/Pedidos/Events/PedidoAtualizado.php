<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Events;

use App\Domain\Pedidos\Models\Pedido;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class PedidoAtualizado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Pedido $pedido,
        public readonly ?string $statusAnterior = null
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
        return 'pedido.atualizado';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->pedido->id,
            'codigo_publico' => $this->pedido->codigo_publico,
            'canal' => $this->pedido->canal,
            'status' => $this->pedido->status,
            'status_anterior' => $this->statusAnterior,
            'entregador_id' => $this->pedido->entregador_id,
            'pagamento_status' => $this->pedido->pagamento_status,
            'updated_at' => $this->pedido->updated_at?->toIso8601String(),
        ];
    }
}
