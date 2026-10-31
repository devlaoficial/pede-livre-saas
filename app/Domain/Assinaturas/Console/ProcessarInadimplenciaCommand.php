<?php

declare(strict_types=1);

namespace App\Domain\Assinaturas\Console;

use App\Domain\Assinaturas\Services\ReguaInadimplenciaService;
use Illuminate\Console\Command;

final class ProcessarInadimplenciaCommand extends Command
{
    protected $signature = 'pedelivre:processar-inadimplencia';
    protected $description = 'Executa a régua diária de cobrança e inadimplência (3, 7 e 15 dias) do SaaS PedeLivre';

    public function handle(ReguaInadimplenciaService $reguaService): int
    {
        $resultado = $reguaService->processarInadimplencia();

        $this->info(sprintf(
            'Régua processada: %d lembretes | %d lojas em modo leitura (7d+) | %d lojas suspensas (15d+).',
            $resultado['lembretes_enviados'],
            $resultado['lojas_inadimplentes_7d'],
            $resultado['lojas_suspensas_15d']
        ));

        return self::SUCCESS;
    }
}
