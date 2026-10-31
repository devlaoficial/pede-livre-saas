<?php

declare(strict_types=1);

namespace App\Domain\Autenticacao\Resources;

use App\Domain\Autenticacao\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Usuario
 */
final class UsuarioResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'loja_id' => $this->loja_id,
            'nome' => $this->nome,
            'email' => $this->email, // Decrypted via cast
            'telefone' => $this->telefone, // Decrypted via cast
            'papel' => $this->papel,
            'ativo' => $this->ativo,
            'two_factor_enabled' => $this->two_factor_secret !== null,
            'ultimo_login' => $this->ultimo_login?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
