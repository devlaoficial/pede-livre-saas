<?php

declare(strict_types=1);

namespace App\Domain\Mesas\Models;

use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Comanda extends Model
{
    use BelongsToLoja;

    protected $table = 'comandas';

    protected $fillable = [
        'loja_id',
        'mesa_id',
        'cliente_id',
        'status',
        'aberta_em',
        'fechada_em',
        'taxa_servico_pct',
        'total_centavos',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'aberta_em' => 'datetime',
        'fechada_em' => 'datetime',
        'taxa_servico_pct' => 'integer',
        'total_centavos' => 'integer',
    ];

    /**
     * @return BelongsTo<Mesa, Comanda>
     */
    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class, 'mesa_id');
    }
}
