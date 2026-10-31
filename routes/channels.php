<?php

declare(strict_types=1);

use App\Domain\Autenticacao\Models\Usuario;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels - Laravel Reverb & Private Tenant Isolation
|--------------------------------------------------------------------------
*/

Broadcast::channel('loja.{lojaId}.pedidos', function (Usuario $usuario, int|string $lojaId): bool {
    // Super Admin has audited access across tenants
    if ($usuario->isSuperAdmin()) {
        return true;
    }

    // Authenticated store staff strictly authorized to their own store channel
    return (int) $usuario->loja_id === (int) $lojaId;
});
