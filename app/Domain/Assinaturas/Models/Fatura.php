<?php

declare(strict_types=1);

namespace App\Domain\Assinaturas\Models;

use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Fatura extends Model
{
    use BelongsToLoja;

    protected $table = 'faturas';

    protected $fillable = [
        'loja_id',
        'assinatura_id',
        'valor_centavos',
        'status',
        'vencimento',
        'pago_em',
        'mp_payment_id',
        'pix_copia_cola',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'pix_copia_cola',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'valor_centavos' => 'integer',
        'vencimento' => 'date',
        'pago_em' => 'datetime',
        'pix_copia_cola' => 'encrypted',
    ];

    /**
     * @return BelongsTo<Assinatura, Fatura>
     */
    public function assinatura(): BelongsTo
    {
        return $this->belongsTo(Assinatura::class, 'assinatura_id');
    }
}
