<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use App\Domain\Assinaturas\Models\Loja;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class IdentificarTenantMiddleware
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
        $loja = null;

        // 1. Resolve via route slug parameter (e.g., /api/p/{slug}/*)
        $slug = $request->route('slug');
        if (is_string($slug) && $slug !== '') {
            $loja = Loja::withoutGlobalScopes()
                ->where('slug', $slug)
                ->first();
        }

        // 2. Resolve via custom CNAME domain (dominio_proprio)
        if ($loja === null) {
            $host = $request->getHost();
            $loja = Loja::withoutGlobalScopes()
                ->where('dominio_proprio', $host)
                ->first();
        }

        // 3. Resolve via authenticated user's loja_id (if API panel request)
        if ($loja === null && $request->user()?->loja_id !== null) {
            $loja = Loja::withoutGlobalScopes()
                ->find($request->user()->loja_id);
        }

        if ($loja !== null) {
            // Block public / tenant access if store is suspended or cancelled
            if (in_array($loja->status, ['suspensa', 'cancelada'], true)) {
                return response()->json([
                    'error' => 'LOJA_INDISPONIVEL',
                    'message' => 'Esta loja encontra-se suspensa ou cancelada.',
                ], Response::HTTP_FORBIDDEN);
            }

            $this->context->setLoja($loja);
        }

        return $next($request);
    }
}
