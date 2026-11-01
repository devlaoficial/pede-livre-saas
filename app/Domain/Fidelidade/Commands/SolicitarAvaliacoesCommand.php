<?php

declare(strict_types=1);

namespace App\Domain\Fidelidade\Commands;

use App\Domain\Bot\Services\EvolutionApiService;
use App\Domain\Fidelidade\Models\Avaliacao;
use App\Domain\Pedidos\Models\Pedido;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

final class SolicitarAvaliacoesCommand extends Command
{
    protected $signature = 'pedelivre:solicitar-avaliacoes';
    protected $description = 'Dispara pesquisa de satisfação NPS e convite para avaliação 5 estrelas no Google Maps via WhatsApp';

    public function handle(EvolutionApiService $evolutionApi): int
    {
        $this->info('Verificando pedidos finalizados elegíveis para pesquisa de satisfação...');

        // Busca pedidos entregues ou retirados recentes (últimas 4 horas)
        $pedidos = Pedido::whereIn('status', ['entregue', 'retirado'])
            ->where('updated_at', '>=', now()->subHours(4))
            ->whereNotNull('cliente_id')
            ->with(['cliente', 'loja'])
            ->get();

        $totalDisparados = 0;

        foreach ($pedidos as $pedido) {
            $loja = $pedido->loja;
            if ($loja === null) {
                continue;
            }

            $config = $loja->config_json['whatsapp'] ?? [];
            $pesquisaAtiva = (bool) ($config['solicitar_avaliacao'] ?? true);
            $notificarGeral = (bool) ($config['notificar_pedidos'] ?? true);

            if (!$pesquisaAtiva || !$notificarGeral) {
                continue;
            }

            // Minutos configurados pelo lojista (padrão 30 min)
            $minutosLimite = (int) ($config['minutos_solicitar_avaliacao'] ?? 30);

            // Determina tempo decorrido desde a conclusão
            $eventoConclusao = $pedido->eventos()
                ->whereIn('status_para', ['entregue', 'retirado'])
                ->latest('created_at')
                ->first();

            $minutosDecorridos = $eventoConclusao !== null
                ? (int) $eventoConclusao->created_at->diffInMinutes(now())
                : (int) $pedido->updated_at->diffInMinutes(now());

            if ($minutosDecorridos < $minutosLimite || $minutosDecorridos > ($minutosLimite + 90)) {
                continue;
            }

            // Já avaliado?
            if (Avaliacao::where('pedido_id', $pedido->id)->exists()) {
                continue;
            }

            // Já enviada solicitação para este pedido?
            if ($pedido->eventos()->where('status_para', 'solicitacao_avaliacao')->exists()) {
                continue;
            }

            // Prevenção de spam por cliente (máximo 1 pesquisa NPS por cliente a cada 24h)
            $cliente = $pedido->cliente;
            if (!$cliente || empty($cliente->telefone) || $cliente->aceita_marketing === false) {
                continue;
            }

            $cacheKey = "nps_enviado_cliente_{$loja->id}_{$cliente->id}";
            if (Cache::has($cacheKey)) {
                continue;
            }

            $enviado = $evolutionApi->notificarSolicitacaoAvaliacao($pedido);
            if ($enviado) {
                Cache::put($cacheKey, true, now()->addHours(24));
                $totalDisparados++;
                $this->info("Pesquisa NPS enviada para pedido #{$pedido->codigo_publico} da loja {$loja->nome} ({$minutosDecorridos}m decorridos)");
            }
        }

        $this->info("Processamento concluído. Total de pesquisas enviadas: {$totalDisparados}");
        return self::SUCCESS;
    }
}
