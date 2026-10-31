<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Models;

use App\Domain\Catalogo\Models\Produto;
use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EstoqueMovimento extends Model
{
    use BelongsToLoja;

    protected $table = 'estoque_movimentos';

    protected $fillable = [
        'loja_id',
        'produto_id',
        'tipo',
        'qtd',
        'pedido_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'qtd' => 'integer',
    ];

    /**
     * @return BelongsTo<Produto, EstoqueMovimento>
     */
    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}
