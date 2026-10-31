<?php

declare(strict_types=1);

namespace App\Domain\Pagamentos\Models;

use App\Domain\Pedidos\Models\Pedido;
use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Pagamento extends Model
{
    use BelongsToLoja;

    protected $table = 'pagamentos';

    protected $fillable = [
        'loja_id',
        'pedido_id',
        'metodo',
        'valor',
        'status',
        'mp_payment_id',
        'pix_copia_cola',
        'pix_qr_base64',
        'expira_em',
        'idempotency_key',
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
        'valor' => 'integer',
        'pix_copia_cola' => 'encrypted',
        'expira_em' => 'datetime',
    ];

    /**
     * @return BelongsTo<Pedido, Pagamento>
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
