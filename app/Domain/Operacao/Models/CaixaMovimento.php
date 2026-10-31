<?php

declare(strict_types=1);

namespace App\Domain\Operacao\Models;

use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CaixaMovimento extends Model
{
    use BelongsToLoja;

    protected $table = 'caixa_movimentos';

    protected $fillable = [
        'loja_id',
        'sessao_id',
        'tipo',
        'valor_centavos',
        'observacao',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'valor_centavos' => 'integer',
    ];

    /**
     * @return BelongsTo<CaixaSessao, CaixaMovimento>
     */
    public function sessao(): BelongsTo
    {
        return $this->belongsTo(CaixaSessao::class, 'sessao_id');
    }
}
