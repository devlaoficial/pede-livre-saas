<?php

declare(strict_types=1);

namespace App\Domain\Assinaturas\Services;

use App\Domain\Assinaturas\Models\Fatura;
use App\Domain\Assinaturas\Models\Loja;
use Carbon\CarbonImmutable;

final class ReguaInadimplenciaService
{
    /**
     * Processes the 3/7/15-day SaaS delinquency state machine across all platform invoices.
     * Never deletes merchant data.
     *
     * @param CarbonImmutable|null $hojeReferencia
     * @return array{lembretes_enviados: int, lojas_inadimplentes_7d: int, lojas_suspensas_15d: int}
     */
    public function processarInadimplencia(?CarbonImmutable $hojeReferencia = null): array
    {
        $hoje = $hojeReferencia ? $hojeReferencia->startOfDay() : CarbonImmutable::today();

        $lembretes = 0;
        $inadimplentes7d = 0;
        $suspensas15d = 0;

        $faturasAbertas = Fatura::withoutGlobalScopes()
            ->whereIn('status', ['pendente', 'vencida'])
            ->get();

        foreach ($faturasAbertas as $fatura) {
            $vencimento = CarbonImmutable::parse($fatura->vencimento)->startOfDay();
            // Positive diff means days overdue; negative means days before due date
            $diasAtraso = (int) $vencimento->diffInDays($hoje, false);

            /** @var Loja|null $loja */
            $loja = Loja::withoutGlobalScopes()->find($fatura->loja_id);
            if ($loja === null || $loja->status === 'cancelada') {
                continue;
            }

            // Mark invoice as overdue if past due date
            if ($diasAtraso > 0 && $fatura->status === 'pendente') {
                $fatura->update(['status' => 'vencida']);
            }

            // Rule 1: -3 days before due date, on due date (0), or +3 days overdue -> Reminder & Banner
            if (in_array($diasAtraso, [-3, 0, 3], true)) {
                $config = $loja->config_json ?? [];
                $config['aviso_fatura_vencida'] = [
                    'fatura_id' => $fatura->id,
                    'dias_atraso' => $diasAtraso,
                    'mensagem' => $diasAtraso < 0
                        ? 'Sua fatura PedeLivre vence em 3 dias.'
                        : ($diasAtraso === 0 ? 'Sua fatura PedeLivre vence hoje.' : 'Sua fatura está com 3 dias de atraso. Evite bloqueios.'),
                ];
                $loja->update(['config_json' => $config]);
                $lembretes++;
            }

            // Rule 2: +15 days overdue -> Suspend public digital menu & merchant panel
            if ($diasAtraso >= 15) {
                if ($loja->status !== 'suspensa') {
                    $loja->update(['status' => 'suspensa']);
                    $suspensas15d++;
                }
                continue;
            }

            // Rule 3: +7 days overdue -> Enter Read-Only Mode (inadimplente)
            if ($diasAtraso >= 7) {
                if ($loja->status !== 'inadimplente') {
                    $loja->update(['status' => 'inadimplente']);
                    $inadimplentes7d++;
                }
            }
        }

        return [
            'lembretes_enviados' => $lembretes,
            'lojas_inadimplentes_7d' => $inadimplentes7d,
            'lojas_suspensas_15d' => $suspensas15d,
        ];
    }
}
