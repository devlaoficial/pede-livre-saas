<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Models;

use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PrintJob extends Model
{
    use BelongsToLoja;

    protected $table = 'print_jobs';

    protected $fillable = [
        'loja_id',
        'pedido_id',
        'tipo',
        'conteudo',
        'status',
        'tentativas',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'conteudo' => 'encrypted',
        'tentativas' => 'integer',
    ];

    /**
     * @return BelongsTo<Pedido, PrintJob>
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
