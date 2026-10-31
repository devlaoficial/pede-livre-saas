<?php

declare(strict_types=1);

namespace App\Domain\Mesas\Policies;

use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Mesas\Models\Mesa;

final class MesaPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->isGerente() || $usuario->isGarcom() || $usuario->isCaixa();
    }

    public function view(Usuario $usuario, Mesa $mesa): bool
    {
        return $usuario->loja_id === $mesa->loja_id;
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->isGerente();
    }

    public function update(Usuario $usuario, Mesa $mesa): bool
    {
        return $usuario->loja_id === $mesa->loja_id && $usuario->isGerente();
    }

    public function delete(Usuario $usuario, Mesa $mesa): bool
    {
        return $usuario->loja_id === $mesa->loja_id && $usuario->isGerente();
    }
}
