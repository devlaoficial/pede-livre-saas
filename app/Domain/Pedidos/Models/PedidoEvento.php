<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Models;

use App\Domain\Autenticacao\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PedidoEvento extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'pedido_eventos';

    protected $fillable = [
        'pedido_id',
        'status_de',
        'status_para',
        'usuario_id',
        'observacao',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Pedido, PedidoEvento>
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    /**
     * @return BelongsTo<Usuario, PedidoEvento>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
