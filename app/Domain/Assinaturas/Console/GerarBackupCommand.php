<?php

declare(strict_types=1);

namespace App\Domain\Assinaturas\Console;

use App\Support\Monitoring\SystemMonitorService;
use Illuminate\Console\Command;

final class GerarBackupCommand extends Command
{
    protected $signature = 'pedelivre:backup {--sem-email : Gera apenas o arquivo local sem enviar por e-mail}';
    protected $description = 'Gera backup completo do banco de dados (.zip com SQL + JSON) e envia automaticamente para o e-mail do administrador';

    public function handle(SystemMonitorService $monitor): int
    {
        $enviarEmail = !$this->option('sem-email');
        $this->info('Iniciando geração do Backup Completo do PedeLivre OS...');

        $res = $monitor->gerarBackupCompleto($enviarEmail);

        $this->info("✓ Backup gerado com sucesso: {$res['arquivo']} ({$res['tamanho_humano']})");
        $this->line("  Tabelas: {$res['tabelas']} | Total de registros: {$res['total_registros']} | SHA-256: {$res['sha256']}");

        if ($res['email_enviado']) {
            $this->info('✓ Cópia do backup enviada com sucesso para o e-mail do administrador!');
        }
        if ($res['whatsapp_enviado']) {
            $this->info('✓ Aviso de conclusão do backup enviado para o WhatsApp do administrador!');
        }

        return self::SUCCESS;
    }
}
