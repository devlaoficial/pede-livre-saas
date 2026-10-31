<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use App\Domain\Assinaturas\Models\Loja;

final class LojaContext
{
    private ?Loja $loja = null;

    public function setLoja(Loja $loja): void
    {
        $this->loja = $loja;
    }

    public function getLoja(): ?Loja
    {
        return $this->loja;
    }

    public function getLojaId(): ?int
    {
        return $this->loja?->id;
    }

    public function hasLoja(): bool
    {
        return $this->loja !== null;
    }

    public function clear(): void
    {
        $this->loja = null;
    }
}
