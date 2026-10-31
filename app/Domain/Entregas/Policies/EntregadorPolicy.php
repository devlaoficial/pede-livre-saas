<?php

declare(strict_types=1);

namespace App\Domain\Entregas\Policies;

use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Entregas\Models\Entregador;

final class EntregadorPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->isGerente();
    }

    public function view(Usuario $usuario, Entregador $entregador): bool
    {
        return $usuario->loja_id === $entregador->loja_id && $usuario->isGerente();
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->isGerente();
    }

    public function update(Usuario $usuario, Entregador $entregador): bool
    {
        return $usuario->loja_id === $entregador->loja_id && $usuario->isGerente();
    }

    public function delete(Usuario $usuario, Entregador $entregador): bool
    {
        return $usuario->loja_id === $entregador->loja_id && $usuario->isGerente();
    }
}
