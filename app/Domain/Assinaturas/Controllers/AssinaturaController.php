<?php

declare(strict_types=1);

namespace App\Domain\Assinaturas\Controllers;

use App\Domain\Assinaturas\Models\Fatura;
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
