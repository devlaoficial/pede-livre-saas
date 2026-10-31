<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Autenticacao\Models\Usuario;

final class UsuarioPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->isSuperAdmin() || $usuario->isGerente();
    }

    public function view(Usuario $usuario, Usuario $target): bool
    {
        if ($usuario->isSuperAdmin()) {
            return true;
        }

        return $usuario->loja_id === $target->loja_id && $usuario->isGerente();
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->isSuperAdmin() || $usuario->isDonoLoja() || $usuario->isGerente();
    }

    public function update(Usuario $usuario, Usuario $target): bool
    {
        if ($usuario->isSuperAdmin()) {
            return true;
        }

        return $usuario->loja_id === $target->loja_id && ($usuario->isDonoLoja() || $usuario->id === $target->id);
    }

    public function delete(Usuario $usuario, Usuario $target): bool
    {
        if ($usuario->isSuperAdmin()) {
            return true;
        }

        return $usuario->loja_id === $target->loja_id && $usuario->isDonoLoja() && $usuario->id !== $target->id;
    }
}
