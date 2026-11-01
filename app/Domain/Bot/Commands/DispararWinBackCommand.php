<?php

declare(strict_types=1);

namespace App\Domain\Bot\Commands;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Bot\Services\WinBackService;
use Illuminate\Console\Command;

final class DispararWinBackCommand extends Command
{
    protected $signature = 'pedelivre:disparar-winback {--loja= : ID específico de uma loja para disparar} {--forcar : Ignora restrição de horário comercial}';
    protected $description = 'Dispara campanhas automáticas de reativação (Win-Back) para clientes inativos via WhatsApp com controle anti-ban';

    public function handle(WinBackService $winBackService): int
    {
        $this->info('Iniciando rotina de Campanhas Win-Back no WhatsApp...');

        $lojaId = $this->option('loja');
        $forcar = (bool) $this->option('forcar');

        // Por boas práticas e respeito ao cliente (LGPD e Anti-Ban), disparos automáticos
        // ocorrem preferencialmente entre 11:00 e 21:30
        $horaAtual = (int) now()->format('H');
        if (!$forcar && ($horaAtual < 11 || $horaAtual >= 22)) {
            $this->warn("Horário atual ({$horaAtual}h) fora da janela recomendada (11h às 21h). Use --forcar para ignorar.");
            return self::SUCCESS;
        }

        $query = Loja::where('status', 'ativo');
        if ($lojaId) {
            $query->where('id', (int) $lojaId);
        }

        $lojas = $query->get();
        $totalLojasProcessadas = 0;
        $totalMensagensEnviadas = 0;

        foreach ($lojas as $loja) {
            $config = $loja->config_json['whatsapp'] ?? [];
            $winbackAtivo = (bool) ($config['winback_ativo'] ?? false);

            if (!$winbackAtivo && !$lojaId) {
                // Se a loja não ativou a automação e não foi solicitada explicitamente por ID
                continue;
            }

            $this->line("Avaliando loja: {$loja->nome} (ID #{$loja->id})...");

            $resultado = $winBackService->executarCampanhaLoja($loja);

            if (!empty($resultado['motivo'])) {
                $this->comment(" -> Loja {$loja->nome}: {$resultado['motivo']}");
                continue;
            }

            $totalLojasProcessadas++;
            $totalMensagensEnviadas += $resultado['enviados'];

            $this->info(" -> [{$loja->nome}] {$resultado['enviados']} enviados, {$resultado['erros']} erros. Cupom gerado: {$resultado['cupom']}");
        }

        $this->info("Rotina concluída! {$totalLojasProcessadas} lojas processadas. Total de {$totalMensagensEnviadas} clientes reativados com sucesso.");

        return self::SUCCESS;
    }
}
