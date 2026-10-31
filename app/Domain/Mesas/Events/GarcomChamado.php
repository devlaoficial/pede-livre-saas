<?php

declare(strict_types=1);

namespace App\Domain\Mesas\Events;

use App\Domain\Mesas\Models\Mesa;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class GarcomChamado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $lojaId,
        public readonly int $mesaId,
        public readonly int $mesaNumero,
        public readonly ?string $mesaNome,
        public readonly string $horario
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("loja.{$this->lojaId}.pedidos"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'garcom.chamado';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'tipo' => 'chamada_garcom',
            'mesa_id' => $this->mesaId,
            'mesa_numero' => $this->mesaNumero,
            'mesa_nome' => $this->mesaNome,
            'horario' => $this->horario,
        ];
    }
}
