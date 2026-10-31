<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Policies;

use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Catalogo\Models\Combo;

final class ComboPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return true;
    }

    public function view(Usuario $usuario, Combo $combo): bool
    {
        return $usuario->loja_id === $combo->loja_id;
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->isGerente();
    }

    public function update(Usuario $usuario, Combo $combo): bool
    {
        return $usuario->loja_id === $combo->loja_id && $usuario->isGerente();
    }

    public function delete(Usuario $usuario, Combo $combo): bool
    {
        return $usuario->loja_id === $combo->loja_id && $usuario->isGerente();
    }
}
