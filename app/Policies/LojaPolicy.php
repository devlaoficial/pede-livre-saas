<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Autenticacao\Models\Usuario;

final class LojaPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->isSuperAdmin() || $usuario->isSuporte();
    }

    public function view(Usuario $usuario, Loja $loja): bool
    {
        if ($usuario->isSuperAdmin() || $usuario->isSuporte()) {
            return true;
        }

        return $usuario->loja_id === $loja->id;
    }

    public function update(Usuario $usuario, Loja $loja): bool
    {
        if ($usuario->isSuperAdmin()) {
            return true;
        }

        return $usuario->loja_id === $loja->id && $usuario->isDonoLoja();
    }

    public function delete(Usuario $usuario, Loja $loja): bool
    {
        return $usuario->isSuperAdmin();
    }
}
