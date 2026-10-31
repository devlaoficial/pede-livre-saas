<?php

declare(strict_types=1);

namespace App\Domain\Bot\Controllers;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Bot\Services\WhatsAppMetaCloudService;
use App\Domain\Pagamentos\Models\WebhookLog;
use App\Support\Security\SensitiveDataRedactor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class WhatsAppWebhookController
{
    public function __construct(
        private readonly WhatsAppMetaCloudService $botService,
        private readonly SensitiveDataRedactor $redactor
    ) {}

    /**
     * POST /webhooks/whatsapp
     * Verifies X-Hub-Signature-256 and routes incoming message to the store's conversational AI / human handoff engine.
     */
    public function processar(Request $request): JsonResponse
    {
        $signature = $request->header('X-Hub-Signature-256');
        $rawBody = $request->getContent();

        $appSecret = (string) config('services.whatsapp_meta.app_secret', 'test-meta-app-secret-key-32-chars!');

        if (!$this->botService->verificarAssinaturaMeta($signature, $rawBody, $appSecret)) {
            return response()->json([
                'error' => 'ASSINATURA_META_INVALIDA',
                'message' => 'Assinatura X-Hub-Signature-256 inválida.',
            ], Response::HTTP_FORBIDDEN);
        }

        $payload = $request->all();

        WebhookLog::create([
            'origem' => 'meta',
            'payload' => $this->redactor->redact($payload),
            'processado' => true,
        ]);

        $lojaSlug = (string) ($payload['loja_slug'] ?? '');
        $telefone = (string) ($payload['from'] ?? '');
        $texto = (string) ($payload['text'] ?? '');

        if ($lojaSlug === '' || $telefone === '') {
            return response()->json(['status' => 'ignored']);
        }

        /** @var Loja|null $loja */
        $loja = Loja::withoutGlobalScopes()->where('slug', $lojaSlug)->first();
        if ($loja === null || !$loja->temRecurso('bot_whatsapp')) {
            return response()->json(['error' => 'BOT_RECURSO_INDISPONIVEL'], Response::HTTP_FORBIDDEN);
        }

        $resultado = $this->botService->processarMensagemRecebida($loja, $telefone, $texto);

        return response()->json($resultado);
    }
}
