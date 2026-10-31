<?php

declare(strict_types=1);

namespace App\Domain\Assinaturas\Models;

use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Assinatura extends Model
{
    use BelongsToLoja;

    protected $table = 'assinaturas';

    protected $fillable = [
        'loja_id',
        'plano_id',
        'status',
        'ciclo',
        'proximo_vencimento',
        'mp_subscription_id',
        'cancelada_em',
        'motivo_cancelamento',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'mp_subscription_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'mp_subscription_id' => 'encrypted',
        'proximo_vencimento' => 'date',
        'cancelada_em' => 'datetime',
    ];

    /**
     * @return BelongsTo<Plano, Assinatura>
     */
    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class, 'plano_id');
    }

    /**
     * @return HasMany<Fatura>
     */
    public function faturas(): HasMany
    {
        return $this->hasMany(Fatura::class, 'assinatura_id');
    }
}
