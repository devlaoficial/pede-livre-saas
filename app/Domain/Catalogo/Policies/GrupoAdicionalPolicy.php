<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Policies;

use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Catalogo\Models\GrupoAdicional;

final class GrupoAdicionalPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return true;
    }

    public function view(Usuario $usuario, GrupoAdicional $grupo): bool
    {
        return $usuario->loja_id === $grupo->loja_id;
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->isGerente();
    }

    public function update(Usuario $usuario, GrupoAdicional $grupo): bool
    {
        return $usuario->loja_id === $grupo->loja_id && $usuario->isGerente();
    }

    public function delete(Usuario $usuario, GrupoAdicional $grupo): bool
    {
        return $usuario->loja_id === $grupo->loja_id && $usuario->isGerente();
    }
}
