<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class VerificarRecursoPlanoMiddleware
{
    public function __construct(
        private readonly LojaContext $context
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next, string $featureFlag): Response
    {
        $loja = $this->context->getLoja();

        if ($loja === null) {
            return response()->json([
                'error' => 'TENANT_NOT_FOUND',
                'message' => 'Contexto de loja não identificado.',
            ], Response::HTTP_NOT_FOUND);
        }

        if (!$loja->temRecurso($featureFlag)) {
            return response()->json([
                'error' => 'RECURSO_NAO_DISPONIVEL',
                'message' => "O recurso [{$featureFlag}] não está disponível no plano atual da sua loja.",
                'required_feature' => $featureFlag,
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
