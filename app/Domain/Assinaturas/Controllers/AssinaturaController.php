<?php

declare(strict_types=1);

namespace App\Domain\Assinaturas\Controllers;

use App\Domain\Assinaturas\Models\Assinatura;
use App\Domain\Assinaturas\Models\Fatura;
use App\Domain\Assinaturas\Models\Plano;
use App\Domain\Assinaturas\Services\AssinaturaPlataformaService;
use App\Support\Tenancy\LojaContext;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class AssinaturaController
{
    public function __construct(
        private readonly AssinaturaPlataformaService $assinaturaService,
        private readonly LojaContext $context
    ) {}

    /**
     * GET /api/assinatura/resumo
     * Retorna a assinatura atual, planos disponíveis e faturas da loja com PIX Copia e Cola.
     */
    public function resumo(): JsonResponse
    {
        $loja = $this->context->getLoja();
        Gate::authorize('view', $loja);

        $assinatura = Assinatura::where('loja_id', $loja->id)
            ->with('plano')
            ->latest('id')
            ->first();

        if ($assinatura === null) {
            $assinatura = Assinatura::create([
                'loja_id' => $loja->id,
                'plano_id' => $loja->plano_id ?: 3,
                'status' => in_array($loja->status, ['ativa', 'trial'], true) ? $loja->status : 'ativa',
                'ciclo' => 'mensal',
                'proximo_vencimento' => $loja->trial_ate
                    ? $loja->trial_ate->toDateString()
                    : now()->addMonth()->toDateString(),
            ]);
            $assinatura->load('plano');
        }

        $planos = Plano::query()->orderBy('preco_centavos')->get(['id', 'nome', 'preco_centavos', 'limite_usuarios']);
        $faturas = Fatura::where('loja_id', $loja->id)
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return response()->json([
            'assinatura' => [
                'id' => $assinatura->id,
                'status' => $assinatura->status,
                'ciclo' => $assinatura->ciclo,
                'proximo_vencimento' => $assinatura->proximo_vencimento?->format('d/m/Y'),
                'plano_id' => $assinatura->plano_id,
                'plano_nome' => $assinatura->plano?->nome ?? 'premium',
                'preco_centavos' => $assinatura->plano?->preco_centavos ?? 24990,
            ],
            'planos' => $planos,
            'faturas' => $faturas->map(fn (Fatura $f) => [
                'id' => $f->id,
                'valor_centavos' => $f->valor_centavos,
                'status' => $f->status,
                'vencimento' => $f->vencimento?->format('d/m/Y'),
                'pago_em' => $f->pago_em?->format('d/m/Y H:i'),
                'pix_copia_cola' => $f->pix_copia_cola,
                'mp_payment_id' => $f->mp_payment_id,
            ]),
        ]);
    }

    /**
     * POST /api/assinatura/gerar-fatura
     * Gera ou retorna a fatura PIX pendente da assinatura da loja para pagamento autônomo em 1 clique.
     */
    public function gerarFatura(): JsonResponse
    {
        $loja = $this->context->getLoja();
        Gate::authorize('update', $loja);

        $faturaAberta = Fatura::where('loja_id', $loja->id)
            ->whereIn('status', ['pendente', 'vencida'])
            ->latest('id')
            ->first();

        if ($faturaAberta !== null) {
            return response()->json([
                'message' => 'Fatura PIX pronta para pagamento!',
                'fatura' => [
                    'id' => $faturaAberta->id,
                    'valor_centavos' => $faturaAberta->valor_centavos,
                    'status' => $faturaAberta->status,
                    'vencimento' => $faturaAberta->vencimento?->format('d/m/Y'),
                    'pix_copia_cola' => $faturaAberta->pix_copia_cola,
                ],
            ]);
        }

        $assinatura = Assinatura::where('loja_id', $loja->id)
            ->with('plano')
            ->latest('id')
            ->first();

        if ($assinatura === null) {
            $assinatura = Assinatura::create([
                'loja_id' => $loja->id,
                'plano_id' => $loja->plano_id ?: 3,
                'status' => 'ativa',
                'ciclo' => 'mensal',
                'proximo_vencimento' => now()->addDays(5)->toDateString(),
            ]);
            $assinatura->load('plano');
        }

        $novaFatura = $this->assinaturaService->gerarFaturaMensal($assinatura);

        return response()->json([
            'message' => 'Fatura PIX gerada com sucesso!',
            'fatura' => [
                'id' => $novaFatura->id,
                'valor_centavos' => $novaFatura->valor_centavos,
                'status' => $novaFatura->status,
                'vencimento' => $novaFatura->vencimento?->format('d/m/Y'),
                'pix_copia_cola' => $novaFatura->pix_copia_cola,
            ],
        ]);
    }

    public function alterarPlano(Request $request): JsonResponse
    {
        $loja = $this->context->getLoja();
        Gate::authorize('update', $loja);

        $request->validate([
            'plano_id' => ['required', 'integer', 'exists:planos,id'],
            'ciclo' => ['nullable', 'string', 'in:mensal,anual'],
        ]);

        $assinatura = $this->assinaturaService->alterarPlano(
            $loja,
            $request->integer('plano_id'),
            $request->string('ciclo', 'mensal')->toString()
        );

        return response()->json([
            'message' => 'Plano alterado com sucesso! Os novos recursos já estão ativos.',
            'assinatura_id' => $assinatura->id,
            'plano_id' => $assinatura->plano_id,
            'ciclo' => $assinatura->ciclo,
        ]);
    }

    /**
     * 3-Click Self-Service Cancellation Endpoint.
     */
    public function cancelar(Request $request): JsonResponse
    {
        $loja = $this->context->getLoja();
        Gate::authorize('update', $loja);

        $request->validate([
            'motivo' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        try {
            $assinatura = $this->assinaturaService->cancelarAssinatura(
                $loja,
                $request->string('motivo')->toString()
            );

            return response()->json([
                'message' => 'Assinatura cancelada imediatamente sem multas ou fidelidade.',
                'status' => $assinatura->status,
                'cancelada_em' => $assinatura->cancelada_em?->toIso8601String(),
            ]);
        } catch (DomainException $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Pay overdue SaaS invoice & trigger instant store reactivation.
     */
    public function pagarFatura(int $faturaId): JsonResponse
    {
        $loja = $this->context->getLoja();
        Gate::authorize('update', $loja);
        $fatura = Fatura::where('loja_id', $loja->id)->findOrFail($faturaId);

        $lojaReativada = $this->assinaturaService->confirmarPagamentoFatura($fatura);

        return response()->json([
            'message' => 'Pagamento confirmado! Sua loja foi reativada instantaneamente.',
            'loja_status' => $lojaReativada->status,
            'fatura_status' => $fatura->fresh()->status,
        ]);
    }
}
