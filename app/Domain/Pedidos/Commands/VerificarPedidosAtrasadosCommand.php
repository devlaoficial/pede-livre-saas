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
    protected $description = 'Verifica pedidos em preparo com tempo elevado e solicita confirmação de entrega ao cliente caso o lojista esqueça de dar baixa no painel';

    public function handle(EvolutionApiService $evolutionApi): int
    {
        $this->info('1. Verificando pedidos em preparo com tempo elevado na cozinha...');

        $pedidosPreparo = Pedido::withoutGlobalScopes()
            ->whereIn('canal', ['delivery', 'retirada', 'balcao'])
            ->whereIn('status', ['aceito', 'em_preparo'])
            ->where('created_at', '>=', now()->subHours(3))
            ->with(['cliente', 'loja', 'entregador'])
            ->get();

        $totalNotificados = 0;

        foreach ($pedidosPreparo as $pedido) {
            $loja = $pedido->loja;
            if ($loja === null) {
                continue;
            }

            $config = $loja->config_json['whatsapp'] ?? [];
            $alertaAtivo = (bool) ($config['alerta_atraso_ativo'] ?? true);
            $notificarGeral = (bool) ($config['notificar_pedidos'] ?? true);
            if (!$alertaAtivo || !$notificarGeral) {
                continue;
            }

            $minutosLimite = (int) ($config['minutos_alerta_atraso'] ?? 35);
            $minutosDecorridos = (int) $pedido->created_at->diffInMinutes(now());

            if ($minutosDecorridos < $minutosLimite) {
                continue;
            }

            $cacheKey = "aviso_atraso_pedido_{$pedido->id}";
            if (Cache::has($cacheKey)) {
                continue;
            }

            $enviado = $evolutionApi->notificarAtraso($pedido, $minutosDecorridos);

            if ($enviado) {
                Cache::put($cacheKey, now()->toIso8601String(), now()->addDay());
                $totalNotificados++;
                $this->line("✓ Alerta proativo enviado para Pedido #{$pedido->codigo_publico} da loja [{$loja->nome}] ({$minutosDecorridos} min decorridos).");
            }
        }

        $this->info('2. Verificando pedidos em rota ou esquecidos abertos no painel para confirmação de entrega com o cliente...');

        $pedidosAbertos = Pedido::withoutGlobalScopes()
            ->whereIn('canal', ['delivery', 'retirada', 'balcao'])
            ->whereIn('status', ['saiu_entrega', 'pronto', 'em_preparo', 'aceito'])
            ->where('created_at', '>=', now()->subHours(6))
            ->where('created_at', '<=', now()->subMinutes(40))
            ->with(['cliente', 'loja'])
            ->get();

        $totalConfirmacoes = 0;

        foreach ($pedidosAbertos as $pedido) {
            $loja = $pedido->loja;
            if ($loja === null) {
                continue;
            }

            $config = $loja->config_json['whatsapp'] ?? [];
            if (!($config['notificar_pedidos'] ?? true)) {
                continue;
            }

            $minutosDesdeCriacao = (int) $pedido->created_at->diffInMinutes(now());
            $minutosDesdeUpdate = (int) $pedido->updated_at->diffInMinutes(now());

            // Se está em "saiu_entrega" há pelo menos 35 min, OU se ficou esquecido em "pronto"/"em_preparo"/"aceito" há pelo menos 65 min
            $elegivelCheckin = ($pedido->status === 'saiu_entrega' && $minutosDesdeUpdate >= 35)
                || (in_array($pedido->status, ['pronto', 'em_preparo', 'aceito'], true) && $minutosDesdeCriacao >= 65);

            if (!$elegivelCheckin) {
                continue;
            }

            $cacheKeyConfirmacao = "confirmacao_entrega_pedido_{$pedido->id}";
            if (Cache::has($cacheKeyConfirmacao)) {
                continue;
            }

            $enviado = $evolutionApi->solicitarConfirmacaoEntregaCliente($pedido);
            if ($enviado) {
                Cache::put($cacheKeyConfirmacao, now()->toIso8601String(), now()->addDay());
                $totalConfirmacoes++;
                $this->line("✓ Check-in de confirmação de entrega enviado para Pedido #{$pedido->codigo_publico} da loja [{$loja->nome}].");
            }
        }

        $this->info("Total de alertas de preparo: {$totalNotificados} | Check-ins de entrega: {$totalConfirmacoes}.");
        return Command::SUCCESS;
    }
}
