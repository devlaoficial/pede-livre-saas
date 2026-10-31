<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ModoLeituraInadimplenciaMiddleware
{
    public function __construct(
        private readonly LojaContext $context
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $loja = $this->context->getLoja();

        if ($loja !== null && $loja->status === 'inadimplente') {
            $isMutatingMethod = in_array(strtoupper($request->method()), ['POST', 'PUT', 'PATCH', 'DELETE'], true);
            $isBillingRoute = $request->is('api/assinatura*') || $request->is('api/faturas*');

            if ($isMutatingMethod && !$isBillingRoute) {
                return response()->json([
                    'error' => 'MODO_LEITURA_INADIMPLENCIA',
                    'message' => 'A loja está em modo somente leitura devido a pendências de pagamento. Regularize sua assinatura.',
                ], Response::HTTP_PAYMENT_REQUIRED);
            }
        }

        return $next($request);
    }
}
