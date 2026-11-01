<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Commands;

use App\Domain\Bot\Services\EvolutionApiService;
use App\Domain\Pedidos\Models\Pedido;
use Illuminate\Console\Command;

final class VerificarPedidosAtrasadosCommand extends Command
{
    protected $signature = 'pedelivre:verificar-atrasos';
    protected $description = 'Verifica pedidos em preparo com tempo elevado e aciona aviso tranquilizador via WhatsApp';

    public function handle(EvolutionApiService $evolutionApi): int
    {
        $this->info('Verificando pedidos com tempo de preparo superior a 35 minutos...');

        $pedidos = Pedido::where('canal', 'delivery')
            ->whereIn('status', ['aceito', 'em_preparo'])
            ->where('created_at', '<=', now()->subMinutes(35))
            ->with(['cliente', 'loja', 'entregador'])
            ->get();

        $total = 0;
        foreach ($pedidos as $pedido) {
            $minutos = (int) $pedido->created_at->diffInMinutes(now());

            // Verifica se a loja tem o bot de WhatsApp habilitado
            $loja = $pedido->loja;
            if ($loja === null) {
                continue;
            }

            $config = $loja->config_json['whatsapp'] ?? [];
            if (!($config['notificar_pedidos'] ?? true)) {
                continue;
            }

            $evolutionApi->notificarAtraso($pedido, $minutos);
            $total++;
            $this->line("Pedido #{$pedido->codigo_publico} da loja {$loja->nome} notificado ({$minutos} min decorridos).");
        }

        $this->info("Total de pedidos acolhidos e notificados: {$total}.");
        return Command::SUCCESS;
    }
}
