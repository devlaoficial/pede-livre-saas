<?php

declare(strict_types=1);

namespace App\Domain\Autenticacao\Controllers;

use App\Domain\Assinaturas\Models\Auditoria;
use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Autenticacao\Models\Usuario;
use App\Support\Security\SensitiveDataRedactor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ImpersonationController
{
    public function __construct(
        private readonly SensitiveDataRedactor $redactor
    ) {}

    /**
     * Audited Store Impersonation for Support and Super Admin.
     */
    public function impersonar(Request $request, int $lojaId): JsonResponse
    {
        /** @var Usuario $authUser */
        $authUser = $request->user();

        if (!$authUser->isSuperAdmin() && !$authUser->isSuporte()) {
            return response()->json([
                'error' => 'FORBIDDEN',
                'message' => 'Acesso restrito à equipe de suporte e super administradores.',
            ], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'ticket_id' => ['required', 'string', 'max:50'],
            'motivo' => ['required', 'string', 'min:10', 'max:255'],
        ]);

        $loja = Loja::withoutGlobalScopes()->findOrFail($lojaId);

        // Find primary store owner to impersonate or create tenant-scoped temporary token
        $dono = Usuario::withoutGlobalScopes()
            ->where('loja_id', $loja->id)
            ->where('papel', 'dono_loja')
            ->first();

        if ($dono === null) {
            return response()->json([
                'error' => 'DONO_NAO_ENCONTRADO',
                'message' => 'Nenhum usuário dono de loja encontrado para esta loja.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Write immutable audit log
        Auditoria::create([
            'loja_id' => $loja->id,
            'usuario_id' => $authUser->id,
            'acao' => 'IMPERSONAR_LOJA',
            'entidade' => 'lojas',
            'entidade_id' => $loja->id,
            'antes_json' => null,
            'depois_json' => $this->redactor->redact([
                'impersonated_user_id' => $dono->id,
                'ticket_id' => $request->string('ticket_id')->toString(),
                'motivo' => $request->string('motivo')->toString(),
                'operador_id' => $authUser->id,
                'operador_papel' => $authUser->papel,
            ]),
            'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
        ]);

        $impersonationToken = $dono->createToken(
            'impersonation-ticket-' . $request->string('ticket_id')->toString(),
            ['role:dono_loja', 'impersonated:true'],
            now()->addHours(2)
        )->plainTextToken;

        return response()->json([
            'impersonation_token' => $impersonationToken,
            'loja_id' => $loja->id,
            'loja_nome' => $loja->nome,
            'expires_in_minutes' => 120,
        ]);
    }
}
