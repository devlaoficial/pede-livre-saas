<?php

declare(strict_types=1);

namespace App\Domain\Assinaturas\Services;

use App\Domain\Assinaturas\Models\Assinatura;
use App\Domain\Assinaturas\Models\Fatura;
use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Assinaturas\Models\Plano;
use App\Domain\Pagamentos\Services\MercadoPagoPixService;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

final class AssinaturaPlataformaService
{
    private const MP_PIX_URL = 'https://api.mercadopago.com/v1/payments';

    /**
     * Upgrades or downgrades store subscription plan immediately updating active feature flags.
     */
    public function alterarPlano(Loja $loja, int $novoPlanoId, string $ciclo = 'mensal'): Assinatura
    {
        if (!in_array($ciclo, ['mensal', 'anual'], true)) {
            throw new DomainException('Ciclo de cobrança inválido. Use mensal ou anual.');
        }

        /** @var Plano $novoPlano */
        $novoPlano = Plano::findOrFail($novoPlanoId);

        return DB::transaction(function () use ($loja, $novoPlano, $ciclo): Assinatura {
            $loja->update(['plano_id' => $novoPlano->id]);

            /** @var Assinatura|null $assinatura */
            $assinatura = Assinatura::where('loja_id', $loja->id)
                ->whereIn('status', ['ativa', 'trial'])
                ->first();

            if ($assinatura !== null) {
                $assinatura->update([
                    'plano_id' => $novoPlano->id,
                    'ciclo' => $ciclo,
                ]);
                return $assinatura;
            }

            return Assinatura::create([
                'loja_id' => $loja->id,
                'plano_id' => $novoPlano->id,
                'status' => 'ativa',
                'ciclo' => $ciclo,
                'proximo_vencimento' => now()->addMonth()->toDateString(),
            ]);
        });
    }

    /**
     * Self-service cancellation in at most 3 clicks with zero lock-in hurdles.
     */
    public function cancelarAssinatura(Loja $loja, string $motivo): Assinatura
    {
        if (empty(trim($motivo))) {
            throw new DomainException('Por favor, informe o motivo do cancelamento.');
        }

        return DB::transaction(function () use ($loja, $motivo): Assinatura {
            /** @var Assinatura|null $assinatura */
            $assinatura = Assinatura::where('loja_id', $loja->id)
                ->latest('id')
                ->first();

            if ($assinatura === null) {
                $assinatura = Assinatura::create([
                    'loja_id' => $loja->id,
                    'plano_id' => $loja->plano_id,
                    'status' => 'cancelada',
                    'ciclo' => 'mensal',
                    'proximo_vencimento' => now()->toDateString(),
                ]);
            }

            $assinatura->update([
                'status' => 'cancelada',
                'cancelada_em' => now(),
                'motivo_cancelamento' => $motivo,
            ]);

            $loja->update([
                'status' => 'cancelada',
            ]);

            return $assinatura;
        });
    }

    /**
     * Generates a platform SaaS invoice using PedeLivre's dedicated Mercado Pago credentials
     * (strictly isolated from merchant OAuth tokens).
     */
    public function gerarFaturaMensal(Assinatura $assinatura): Fatura
    {
        $plano = $assinatura->plano;
        $valorCentavos = $assinatura->ciclo === 'anual'
            ? (int) round($plano->preco_centavos * 12 * 0.85) // 15% discount on annual
            : $plano->preco_centavos;

        $platformToken = (string) config('services.mercadopago.platform_access_token', '');
        $pixService = app(MercadoPagoPixService::class);
        $copiaCola = $pixService->montarPayloadEmvPix(
            chavePix: 'financeiro@pedelivre.com.br',
            nomeRecebedor: 'PEDELIVRE SAAS',
            cidadeRecebedor: 'SAO PAULO',
            valorCentavos: $valorCentavos,
            txid: 'SAASINV' . $assinatura->id
        );
        $mpPaymentId = 'SAAS_INV_' . $assinatura->id . '_' . time();

        if ($platformToken !== '' && !app()->environment('testing')) {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$platformToken}",
                'X-Idempotency-Key' => $mpPaymentId,
            ])->timeout(10)->post(self::MP_PIX_URL, [
                'transaction_amount' => round($valorCentavos / 100, 2),
                'description' => "Assinatura PedeLivre - Plano {$plano->nome}",
                'payment_method_id' => 'pix',
                'payer' => [
                    'email' => "loja_{$assinatura->loja_id}@pedelivre.com.br",
                    'first_name' => 'Lojista',
                    'last_name' => 'PedeLivre',
                ],
            ]);

            if ($response->successful()) {
                $mpPaymentId = (string) $response->json('id');
                $copiaCola = (string) $response->json('point_of_interaction.transaction_data.qr_code', $copiaCola);
            }
        }

        return Fatura::create([
            'loja_id' => $assinatura->loja_id,
            'assinatura_id' => $assinatura->id,
            'valor_centavos' => $valorCentavos,
            'status' => 'pendente',
            'vencimento' => $assinatura->proximo_vencimento,
            'mp_payment_id' => $mpPaymentId,
            'pix_copia_cola' => $copiaCola,
        ]);
    }

    /**
     * Instant Reactivation upon invoice payment confirmation.
     */
    public function confirmarPagamentoFatura(Fatura $fatura): Loja
    {
        return DB::transaction(function () use ($fatura): Loja {
            $fatura->update([
                'status' => 'paga',
                'pago_em' => now(),
            ]);

            $assinatura = $fatura->assinatura;
            if ($assinatura !== null) {
                $assinatura->update([
                    'status' => 'ativa',
                    'proximo_vencimento' => $assinatura->ciclo === 'anual'
                        ? now()->addYear()->toDateString()
                        : now()->addMonth()->toDateString(),
                ]);
            }

            /** @var Loja $loja */
            $loja = Loja::withoutGlobalScopes()->findOrFail($fatura->loja_id);
            $config = $loja->config_json ?? [];
            unset($config['aviso_fatura_vencida']);

            $loja->update([
                'status' => 'ativa',
                'config_json' => $config,
            ]);

            return $loja;
        });
    }
}
