<?php

declare(strict_types=1);

namespace App\Domain\Assinaturas\Resources;

use App\Domain\Assinaturas\Models\Loja;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Loja
 */
final class LojaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'dominio_proprio' => $this->dominio_proprio,
            'nome' => $this->nome,
            'telefone_whatsapp' => $this->telefone_whatsapp,
            'logo' => $this->logo,
            'capa' => $this->capa,
            'cor_primaria' => $this->cor_primaria,
            'endereco' => $this->endereco,
            'cidade' => $this->cidade,
            'uf' => $this->uf,
            'status' => $this->status,
            'trial_ate' => $this->trial_ate?->toIso8601String(),
            'plano_id' => $this->plano_id,
            'mp_conectado' => $this->mp_access_token !== null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
