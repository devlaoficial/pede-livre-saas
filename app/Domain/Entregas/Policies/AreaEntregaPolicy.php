<?php

declare(strict_types=1);

namespace App\Domain\Entregas\Policies;

use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Entregas\Models\AreaEntrega;

final class AreaEntregaPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return true;
    }

    public function view(Usuario $usuario, AreaEntrega $area): bool
    {
        return $usuario->loja_id === $area->loja_id;
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->isGerente();
    }

    public function update(Usuario $usuario, AreaEntrega $area): bool
    {
        return $usuario->loja_id === $area->loja_id && $usuario->isGerente();
    }

    public function delete(Usuario $usuario, AreaEntrega $area): bool
    {
        return $usuario->loja_id === $area->loja_id && $usuario->isGerente();
    }
}
