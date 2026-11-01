<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Commands;

use App\Domain\Bot\Services\RecuperadorVendasService;
use Illuminate\Console\Command;

final class RecuperarCarrinhosCommand extends Command
{
    protected $signature = 'pedelivre:recuperar-carrinhos';
    protected $description = 'Dispara lembretes de PIX pendente e cancela pedidos PIX expirados após 40 minutos';

    public function handle(RecuperadorVendasService $recuperador): int
    {
        $this->info('Verificando pedidos com PIX pendente para recuperação...');
        $recuperados = $recuperador->recuperarPixPendentes();
        $this->info("{$recuperados} lembretes de PIX pendente enviados no WhatsApp.");

        $this->info('Cancelando pedidos PIX expirados há mais de 40 minutos...');
        $cancelados = $recuperador->cancelarPixExpirados();
        $this->info("{$cancelados} pedidos PIX expirados foram cancelados e estoque liberado.");

        return Command::SUCCESS;
    }
}
