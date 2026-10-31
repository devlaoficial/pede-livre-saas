<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Controllers;

use App\Domain\Pedidos\Models\Pedido;
use App\Domain\Pedidos\Models\PrintJob;
use App\Domain\Pedidos\Services\ImpressaoEscPosService;
use App\Support\Tenancy\LojaContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ImpressaoFilaController
{
    public function __construct(
        private readonly ImpressaoEscPosService $impressaoService,
        private readonly LojaContext $context
    ) {}

    /**
     * Tier 2: Local ESC/POS Agent Poll endpoint (fetches pending print jobs for store).
     */
    public function obterFila(): JsonResponse
    {
        $loja = $this->context->getLoja();
        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        $jobs = PrintJob::where('loja_id', $loja->id)
            ->where('status', 'pendente')
            ->orderBy('id', 'asc')
            ->limit(20)
            ->get();

        return response()->json([
            'total_pendentes' => $jobs->count(),
            'jobs' => $jobs->map(fn (PrintJob $j) => [
                'id' => $j->id,
                'pedido_id' => $j->pedido_id,
                'tipo' => $j->tipo,
                'conteudo_escpos_base64' => base64_encode((string) $j->conteudo),
                'conteudo_texto' => (string) $j->conteudo,
                'status' => $j->status,
                'tentativas' => $j->tentativas,
                'created_at' => $j->created_at?->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Confirms successful hardware print.
     */
    public function confirmarImpressao(int $jobId): JsonResponse
    {
        $loja = $this->context->getLoja();

        $job = PrintJob::where('loja_id', $loja->id)->findOrFail($jobId);
        $job->update([
            'status' => 'impresso',
        ]);

        return response()->json([
            'message' => 'Impressão confirmada com sucesso.',
            'job_id' => $job->id,
            'status' => 'impresso',
        ]);
    }

    /**
     * Flags hardware print error.
     */
    public function registrarFalha(int $jobId): JsonResponse
    {
        $loja = $this->context->getLoja();

        $job = PrintJob::where('loja_id', $loja->id)->findOrFail($jobId);
        $job->increment('tentativas');
        $job->update([
            'status' => $job->tentativas >= 3 ? 'erro' : 'pendente',
        ]);

        return response()->json([
            'message' => 'Falha de impressão registrada.',
            'job_id' => $job->id,
            'status' => $job->status,
            'tentativas' => $job->tentativas,
        ]);
    }

    /**
     * Manual Trigger from Merchant Kanban or Modal.
     */
    public function imprimirManualmente(Request $request, int $pedidoId): JsonResponse
    {
        $loja = $this->context->getLoja();

        $pedido = Pedido::where('loja_id', $loja->id)->with(['itens', 'cliente', 'mesa'])->findOrFail($pedidoId);
        $tipo = $request->string('tipo', 'ambas')->toString();

        $jobs = $this->impressaoService->enfileirarImpressao($pedido, $tipo);

        return response()->json([
            'message' => 'Trabalho de impressão enviado para a fila.',
            'jobs_criados' => count($jobs),
        ], Response::HTTP_CREATED);
    }
}
