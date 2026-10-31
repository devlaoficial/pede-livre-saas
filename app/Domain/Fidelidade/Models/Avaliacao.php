<?php

declare(strict_types=1);

namespace App\Domain\Fidelidade\Models;

use App\Domain\Pedidos\Models\Pedido;
use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Avaliacao extends Model
{
    use BelongsToLoja;

    protected $table = 'avaliacoes';

    protected $fillable = [
        'loja_id',
        'pedido_id',
        'nota',
        'comentario',
        'resposta_loja',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'nota' => 'integer',
    ];

    /**
     * @return BelongsTo<Pedido, Avaliacao>
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
