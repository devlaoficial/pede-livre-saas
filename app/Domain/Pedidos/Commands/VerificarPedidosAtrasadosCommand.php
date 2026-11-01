<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Commands;

use App\Domain\Bot\Services\EvolutionApiService;
use App\Domain\Pedidos\Models\Pedido;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

final class VerificarPedidosAtrasadosCommand extends Command
{
    protected $signature = 'pedelivre:verificar-atrasos';
    protected $description = 'Verifica pedidos em preparo com tempo elevado e aciona aviso proativo tranquilizador via WhatsApp';

    public function handle(EvolutionApiService $evolutionApi): int
    {
        $this->info('Verificando pedidos em preparo com tempo elevado na cozinha...');

        // Busca pedidos em preparo recentes (últimas 3 horas)
        $pedidos = Pedido::whereIn('canal', ['delivery', 'retirada', 'balcao'])
            ->whereIn('status', ['aceito', 'em_preparo'])
            ->where('created_at', '>=', now()->subHours(3))
            ->with(['cliente', 'loja', 'entregador'])
            ->get();

        $totalNotificados = 0;

        foreach ($pedidos as $pedido) {
            $loja = $pedido->loja;
            if ($loja === null) {
                continue;
            }

            $config = $loja->config_json['whatsapp'] ?? [];

            // Verifica se o alerta proativo está habilitado na loja
            $alertaAtivo = (bool) ($config['alerta_atraso_ativo'] ?? true);
            $notificarGeral = (bool) ($config['notificar_pedidos'] ?? true);
            if (!$alertaAtivo || !$notificarGeral) {
                continue;
            }

            // Minutos configurados pelo lojista (padrão 35 min)
            $minutosLimite = (int) ($config['minutos_alerta_atraso'] ?? 35);
            $minutosDecorridos = (int) $pedido->created_at->diffInMinutes(now());

            if ($minutosDecorridos < $minutosLimite) {
                continue;
            }

            // Prevenção de spam: notifica apenas UMA vez por pedido
            $cacheKey = "aviso_atraso_pedido_{$pedido->id}";
            if (Cache::has($cacheKey)) {
                continue;
            }

            // Dispara aviso gentil e tranquilizador para o cliente
            $enviado = $evolutionApi->notificarAtraso($pedido, $minutosDecorridos);

            if ($enviado) {
                // Marca cache por 24 horas para nunca reenviar no mesmo pedido
                Cache::put($cacheKey, now()->toIso8601String(), now()->addDay());
                $totalNotificados++;
                $this->line("✓ Alerta proativo enviado para Pedido #{$pedido->codigo_publico} da loja [{$loja->nome}] ({$minutosDecorridos} min decorridos).");
            }
        }

        $this->info("Total de alertas proativos enviados: {$totalNotificados}.");
        return Command::SUCCESS;
    }
}
