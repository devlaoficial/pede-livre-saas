<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Resources;

use App\Domain\Catalogo\Models\ProdutoVariacao;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProdutoVariacao
 */
final class ProdutoVariacaoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'preco_centavos' => $this->preco_centavos,
            'ordem' => $this->ordem,
        ];
    }
}
