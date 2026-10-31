<?php

declare(strict_types=1);

namespace App\Domain\Operacao\Policies;

use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Operacao\Models\Horario;

final class HorarioPolicy
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
