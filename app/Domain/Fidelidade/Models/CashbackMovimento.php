<?php

declare(strict_types=1);

namespace App\Domain\Fidelidade\Models;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Pedidos\Models\Pedido;
use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CashbackMovimento extends Model
{
    use BelongsToLoja;

    protected $table = 'cashback_movimentos';

    protected $fillable = [
        'loja_id',
        'cliente_id',
        'tipo',
        'valor',
        'pedido_id',
        'expira_em',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'valor' => 'integer',
        'expira_em' => 'datetime',
    ];

    /**
     * @return BelongsTo<Cliente, CashbackMovimento>
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * @return BelongsTo<Pedido, CashbackMovimento>
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
