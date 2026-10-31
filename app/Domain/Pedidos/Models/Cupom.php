<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Models;

use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;

final class Cupom extends Model
{
    use BelongsToLoja;

    protected $table = 'cupons';

    protected $fillable = [
        'loja_id',
        'codigo',
        'tipo',
        'valor',
        'minimo_centavos',
        'validade',
        'limite_uso_total',
        'limite_por_cliente',
        'usos',
        'canais',
        'ativo',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'valor' => 'integer',
        'minimo_centavos' => 'integer',
        'validade' => 'datetime',
        'limite_uso_total' => 'integer',
        'limite_por_cliente' => 'integer',
        'usos' => 'integer',
        'canais' => 'array',
        'ativo' => 'boolean',
    ];
}
