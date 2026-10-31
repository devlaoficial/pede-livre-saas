<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Policies;

use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Catalogo\Models\Produto;

final class ProdutoPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return true;
    }

    public function view(Usuario $usuario, Produto $produto): bool
    {
        return $usuario->loja_id === $produto->loja_id;
    }

    public function create(Usuario $usuario): bool
    {
        return $usuario->isGerente();
    }

    public function update(Usuario $usuario, Produto $produto): bool
    {
        return $usuario->loja_id === $produto->loja_id && $usuario->isGerente();
    }

    public function delete(Usuario $usuario, Produto $produto): bool
    {
        return $usuario->loja_id === $produto->loja_id && $usuario->isGerente();
    }
}
