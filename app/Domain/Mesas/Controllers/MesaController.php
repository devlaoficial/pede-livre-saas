<?php

declare(strict_types=1);

namespace App\Domain\Mesas\Controllers;

use App\Domain\Mesas\Events\GarcomChamado;
use App\Domain\Mesas\Models\Mesa;
use App\Domain\Mesas\Resources\MesaPublicaResource;
use App\Support\Tenancy\LojaContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

final class MesaController
{
    public function __construct(
        private readonly LojaContext $context
    ) {}

    /**
     * Validates the 32-char cryptographic mesas.qr_token and resolves table session.
     */
    public function identificarPorToken(string $slug, string $token): JsonResponse
    {
        $loja = $this->context->getLoja();
        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        $mesa = Mesa::where('loja_id', $loja->id)
            ->where('qr_token', $token)
            ->with('comandaAtiva')
            ->first();

        if ($mesa === null) {
            return response()->json([
                'error' => 'MESA_INVALIDA',
                'message' => 'Código QR da mesa inválido ou expirado. Solicite suporte da equipe.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'loja_nome' => $loja->nome,
            'mesa' => new MesaPublicaResource($mesa),
        ]);
    }

    /**
     * Rate-limited Call Waiter action (1 call per 2 minutes per table token + IP).
     */
    public function chamarGarcom(Request $request, string $slug, string $token): JsonResponse
    {
        $loja = $this->context->getLoja();
        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        $mesa = Mesa::where('loja_id', $loja->id)
            ->where('qr_token', $token)
            ->first();

        if ($mesa === null) {
            return response()->json(['error' => 'MESA_NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        // Anti-abuse rate limit: 1 call per 2 minutes (120 seconds)
        $throttleKey = "chamar_garcom:{$token}:" . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'error' => 'CHAMADA_LIMITADA',
                'message' => "O garçom já foi notificado. Aguarde {$seconds} segundos para chamar novamente.",
                'tentar_em_segundos' => $seconds,
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        RateLimiter::hit($throttleKey, 120);

        $horario = now()->format('H:i:s');

        // Persist active waiter call in cache for real-time merchant panel polling & WebSockets
        $cacheKey = "garcom_chamados:loja:{$loja->id}";
        $chamados = Cache::get($cacheKey, []);
        if (!is_array($chamados)) {
            $chamados = [];
        }
        $chamados[(string) $mesa->id] = [
            'mesa_id' => $mesa->id,
            'mesa_numero' => $mesa->numero,
            'mesa_nome' => $mesa->nome ?: "Mesa {$mesa->numero}",
            'horario' => $horario,
            'timestamp' => time(),
        ];
        Cache::put($cacheKey, $chamados, now()->addMinutes(30));

        // Broadcast event to Merchant KDS / POS channel
        event(new GarcomChamado(
            lojaId: $loja->id,
            mesaId: $mesa->id,
            mesaNumero: $mesa->numero,
            mesaNome: $mesa->nome,
            horario: $horario
        ));

        return response()->json([
            'message' => 'Garçom notificado! Um atendente virá à sua mesa em instantes.',
            'mesa_numero' => $mesa->numero,
            'horario' => $horario,
        ]);
    }
}
