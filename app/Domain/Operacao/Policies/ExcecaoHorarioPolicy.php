<?php

declare(strict_types=1);

namespace App\Domain\Operacao\Policies;

use App\Domain\Autenticacao\Models\Usuario;

final class ExcecaoHorarioPolicy
{
    public function viewAny(Usuario $usuario): bool
    {
        return true;
    }

    public function manage(Usuario $usuario): bool
    {
        return $usuario->isGerente();
    }
}
