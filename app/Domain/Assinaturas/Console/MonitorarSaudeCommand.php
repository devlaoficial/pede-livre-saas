<?php

declare(strict_types=1);

namespace App\Domain\Assinaturas\Console;

use App\Support\Monitoring\SystemMonitorService;
use Illuminate\Console\Command;

final class MonitorarSaudeCommand extends Command
{
    protected $signature = 'pedelivre:monitorar-saude';
    protected $description = 'Verifica a saúde do Banco de Dados, Cache, Disco e Gateway WhatsApp, disparando alerta por E-mail e WhatsApp em caso de falha';

    public function handle(SystemMonitorService $monitor): int
    {
        $this->info('Executando diagnóstico de saúde da infraestrutura PedeLivre OS...');
        $resultado = $monitor->verificarSaudeSistema(true);

        foreach ($resultado['checks'] as $nome => $check) {
            $status = strtoupper((string) ($check['status'] ?? 'ok'));
            $msg = (string) ($check['message'] ?? '');
            if ($status === 'OK') {
                $this->info(" [✓] {$nome}: {$msg}");
            } else {
                $this->error(" [!] {$nome} ({$status}): {$msg}");
            }
        }

        return $resultado['status'] === 'healthy' ? self::SUCCESS : self::FAILURE;
    }
}
