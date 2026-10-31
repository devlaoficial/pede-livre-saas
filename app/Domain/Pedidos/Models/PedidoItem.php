<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Models;

use App\Domain\Catalogo\Models\Produto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PedidoItem extends Model
{
    protected $table = 'pedido_itens';

    protected $fillable = [
        'pedido_id',
        'produto_id',
        'nome_snapshot',
        'variacao_snapshot',
        'qtd',
        'preco_unit',
        'adicionais_json',
        'observacao',
        'total',
        'status_cozinha',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'qtd' => 'integer',
        'preco_unit' => 'integer',
        'adicionais_json' => 'array',
        'total' => 'integer',
    ];

    /**
     * @return BelongsTo<Pedido, PedidoItem>
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    /**
     * @return BelongsTo<Produto, PedidoItem>
     */
    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}
