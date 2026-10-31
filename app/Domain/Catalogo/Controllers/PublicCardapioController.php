<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Controllers;

use App\Domain\Catalogo\Resources\CardapioPublicoLojaResource;
use App\Domain\Catalogo\Services\CardapioCacheService;
use App\Support\Tenancy\LojaContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PublicCardapioController
{
    public function __construct(
        private readonly LojaContext $context,
        private readonly CardapioCacheService $cacheService
    ) {}

    /**
     * Public store metadata for digital menu header (zero PII / secrets leaked + Restaurant SEO schema).
     */
    public function loja(): JsonResponse
    {
        $loja = $this->context->getLoja();
        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NOT_FOUND'], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'loja' => new CardapioPublicoLojaResource($loja),
        ]);
    }

    /**
     * Public catalog served via Redis 6-hour cache filtered by requested sales channel.
     */
    public function cardapio(Request $request): JsonResponse
    {
        $canal = $request->string('canal', 'delivery')->toString();
        $lojaId = (int) $this->context->getLojaId();

        $dadosCardapio = $this->cacheService->getCardapio($lojaId, $canal);

        return response()->json($dadosCardapio);
    }
}
