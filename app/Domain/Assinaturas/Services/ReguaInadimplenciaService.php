<?php

declare(strict_types=1);

namespace App\Domain\Assinaturas\Services;

use App\Domain\Assinaturas\Models\Assinatura;
use App\Domain\Assinaturas\Models\Fatura;
use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Bot\Services\EvolutionApiService;
use App\Support\Monitoring\SystemMonitorService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class ReguaInadimplenciaService
{
    private readonly AssinaturaPlataformaService $assinaturaService;
    private readonly EvolutionApiService $evolutionApi;
    private readonly SystemMonitorService $monitor;

    public function __construct(
        ?AssinaturaPlataformaService $assinaturaService = null,
        ?EvolutionApiService $evolutionApi = null,
        ?SystemMonitorService $monitor = null
    ) {
        $this->assinaturaService = $assinaturaService ?? app(AssinaturaPlataformaService::class);
        $this->evolutionApi = $evolutionApi ?? app(EvolutionApiService::class);
        $this->monitor = $monitor ?? app(SystemMonitorService::class);
    }

    /**
     * Motor 100% Autônomo de Faturamento e Régua de Cobrança SaaS (Geração de Faturas + Avisos PIX WhatsApp/E-mail + 3/7/15 dias).
     * Nunca apaga dados do lojista.
     *
     * @param CarbonImmutable|null $hojeReferencia
     * @return array{faturas_geradas: int, lembretes_enviados: int, lojas_inadimplentes_7d: int, lojas_suspensas_15d: int}
     */
    public function processarInadimplencia(?CarbonImmutable $hojeReferencia = null): array
    {
        $hoje = $hojeReferencia ? $hojeReferencia->startOfDay() : CarbonImmutable::today();

        $faturasGeradas = 0;
        $lembretes = 0;
        $inadimplentes7d = 0;
        $suspensas15d = 0;

        // 1. Self-Healing: Garante que toda loja ativa/trial tenha registro de Assinatura
        $lojasAtivas = Loja::withoutGlobalScopes()
            ->whereIn('status', ['ativa', 'trial'])
            ->get();

        foreach ($lojasAtivas as $loja) {
            $temAssinatura = Assinatura::withoutGlobalScopes()
                ->where('loja_id', $loja->id)
                ->exists();

            if (!$temAssinatura) {
                Assinatura::withoutGlobalScopes()->create([
                    'loja_id' => $loja->id,
                    'plano_id' => $loja->plano_id ?: 3,
                    'status' => $loja->status,
                    'ciclo' => 'mensal',
                    'proximo_vencimento' => $loja->trial_ate
                        ? $loja->trial_ate->toDateString()
                        : $hoje->addMonth()->toDateString(),
                ]);
            }
        }

        // 2. Emissão Automática de Faturas (5 dias antes do vencimento ou fim do Trial)
        $limiteEmissao = $hoje->addDays(5)->toDateString();
        $assinaturasParaFaturar = Assinatura::withoutGlobalScopes()
            ->whereIn('status', ['ativa', 'trial'])
            ->whereNotNull('proximo_vencimento')
            ->where('proximo_vencimento', '<=', $limiteEmissao)
            ->with('plano')
            ->get();

        foreach ($assinaturasParaFaturar as $assinatura) {
            if ($assinatura->plano === null) {
                continue;
            }

            $jaTemFaturaAberta = Fatura::withoutGlobalScopes()
                ->where('loja_id', $assinatura->loja_id)
                ->whereIn('status', ['pendente', 'vencida'])
                ->exists();

            if (!$jaTemFaturaAberta) {
                try {
                    $this->assinaturaService->gerarFaturaMensal($assinatura);
                    $faturasGeradas++;
                } catch (Throwable $e) {
                    Log::warning("[ReguaSaaS] Falha ao gerar fatura automática para loja #{$assinatura->loja_id}: " . $e->getMessage());
                }
            }
        }

        // 3. Processamento de Faturas Abertas e Régua de Cobrança (-3d, -1d, 0d, +3d, +7d, +15d)
        $faturasAbertas = Fatura::withoutGlobalScopes()
            ->whereIn('status', ['pendente', 'vencida'])
            ->get();

        foreach ($faturasAbertas as $fatura) {
            $vencimento = CarbonImmutable::parse($fatura->vencimento)->startOfDay();
            // Positivo = dias em atraso; Negativo = dias que faltam para vencer
            $diasAtraso = (int) $vencimento->diffInDays($hoje, false);

            /** @var Loja|null $loja */
            $loja = Loja::withoutGlobalScopes()->with('plano')->find($fatura->loja_id);
            if ($loja === null || $loja->status === 'cancelada') {
                continue;
            }

            // Marca fatura como vencida se passou do vencimento
            if ($diasAtraso > 0 && $fatura->status === 'pendente') {
                $fatura->update(['status' => 'vencida']);
            }

            // Regra 1: Marcos de notificação (-3, -1, 0, +3, +7, +15 dias) -> Banner no Painel + WhatsApp/E-mail com PIX Copia e Cola
            if (in_array($diasAtraso, [-3, -1, 0, 3, 7, 15], true)) {
                $config = $loja->config_json ?? [];
                $mensagemBanner = match (true) {
                    $diasAtraso < 0 => "Sua fatura PedeLivre vence em " . abs($diasAtraso) . " dia(s). Pague via PIX com 1 clique.",
                    $diasAtraso === 0 => 'Sua fatura PedeLivre vence hoje. Pague via PIX para manter sua operação ativa.',
                    $diasAtraso >= 15 => 'Assinatura suspensa por inadimplência (+15 dias). Regularize via PIX para reativação instantânea.',
                    $diasAtraso >= 7 => 'Loja em modo somente leitura (+7 dias de atraso). Pague via PIX para liberar edições imediatamente.',
                    default => "Sua fatura está com {$diasAtraso} dias de atraso. Regularize via PIX para evitar bloqueios.",
                };

                $config['aviso_fatura_vencida'] = [
                    'fatura_id' => $fatura->id,
                    'dias_atraso' => $diasAtraso,
                    'valor_centavos' => $fatura->valor_centavos,
                    'vencimento' => $vencimento->format('d/m/Y'),
                    'pix_copia_cola' => $fatura->pix_copia_cola,
                    'mensagem' => $mensagemBanner,
                ];
                $loja->update(['config_json' => $config]);
                $lembretes++;

                $this->enviarCobrancaAutonomaLojista($loja, $fatura, $diasAtraso, $vencimento);
            }

            // Regra 2: +15 dias de atraso -> Suspende cardápio público e painel
            if ($diasAtraso >= 15) {
                if ($loja->status !== 'suspensa') {
                    $loja->update(['status' => 'suspensa']);
                    $suspensas15d++;
                }
                continue;
            }

            // Regra 3: +7 dias de atraso -> Modo Somente Leitura (inadimplente)
            if ($diasAtraso >= 7) {
                if ($loja->status !== 'inadimplente') {
                    $loja->update(['status' => 'inadimplente']);
                    $inadimplentes7d++;
                }
            }
        }

        return [
            'faturas_geradas' => $faturasGeradas,
            'lembretes_enviados' => $lembretes,
            'lojas_inadimplentes_7d' => $inadimplentes7d,
            'lojas_suspensas_15d' => $suspensas15d,
        ];
    }

    /**
     * Dispara mensagem automática de cobrança com PIX Copia e Cola para o WhatsApp e E-mail do lojista.
     */
    private function enviarCobrancaAutonomaLojista(
        Loja $loja,
        Fatura $fatura,
        int $diasAtraso,
        CarbonImmutable $vencimento
    ): void {
        if (app()->environment('testing')) {
            return;
        }

        $cacheKey = "saas_cobranca_fatura_{$fatura->id}_dia_{$diasAtraso}";
        if (Cache::has($cacheKey)) {
            return;
        }
        Cache::put($cacheKey, true, now()->addHours(20));

        $valorFmt = number_format($fatura->valor_centavos / 100, 2, ',', '.');
        $vencFmt = $vencimento->format('d/m/Y');
        $planoNome = strtoupper((string) ($loja->plano?->nome ?? 'Premium'));
        $painelUrl = url('/painel');

        $tituloSituacao = match (true) {
            $diasAtraso < 0 => "⏳ *Lembrete de Vencimento — PedeLivre SaaS*",
            $diasAtraso === 0 => "📅 *Sua Fatura PedeLivre Vence Hoje!*",
            $diasAtraso >= 15 => "🚨 *Aviso de Suspensão — Assinatura PedeLivre*",
            $diasAtraso >= 7 => "⚠️ *Loja em Modo Leitura — Regularize sua Assinatura*",
            default => "🔔 *Fatura em Aberto ({$diasAtraso} dias) — PedeLivre SaaS*",
        };

        $texto = "{$tituloSituacao}\n\n"
            . "Olá, equipe *{$loja->nome}*!\n"
            . "Segue o resumo da sua assinatura do *Plano {$planoNome}* (0% de comissão sobre vendas):\n\n"
            . "💳 *Valor:* R$ {$valorFmt}\n"
            . "📆 *Vencimento:* {$vencFmt}\n\n"
            . "⚡ *PIX Copia e Cola para pagamento e baixa automática:*\n"
            . "```{$fatura->pix_copia_cola}```\n\n"
            . "Assim que o PIX for confirmado, sua assinatura é renovada e liberada automaticamente em segundos!\n"
            . "👉 *Acesse seu painel:* {$painelUrl}";

        // 1. Envia no WhatsApp do Lojista
        if (!empty($loja->telefone_whatsapp)) {
            try {
                $instancia = $this->evolutionApi->obterNomeInstanciaLoja($loja);
                $this->evolutionApi->enviarMensagem($instancia, (string) $loja->telefone_whatsapp, $texto, $loja->id);
            } catch (Throwable) {}
        }

        // 2. Envia no E-mail do Dono da Loja
        try {
            $dono = Usuario::withoutGlobalScopes()
                ->where('loja_id', $loja->id)
                ->where('papel', 'dono_loja')
                ->first();

            if ($dono !== null && !empty($dono->email) && filter_var($dono->email, FILTER_VALIDATE_EMAIL)) {
                Mail::raw($texto, function ($mail) use ($dono, $loja, $vencFmt): void {
                    $mail->to($dono->email)->subject("[PedeLivre SaaS] Fatura Assinatura {$loja->nome} - Venc: {$vencFmt}");
                });
            }
        } catch (Throwable) {}
    }
}
