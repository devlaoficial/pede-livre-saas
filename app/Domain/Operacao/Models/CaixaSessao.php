<?php

declare(strict_types=1);

namespace App\Domain\Operacao\Models;

use App\Domain\Autenticacao\Models\Usuario;
use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CaixaSessao extends Model
{
    use BelongsToLoja;

    protected $table = 'caixa_sessoes';

    protected $fillable = [
        'loja_id',
        'usuario_id',
        'aberto_em',
        'fechado_em',
        'valor_inicial_centavos',
        'valor_final_centavos',
        'diferenca_centavos',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'aberto_em' => 'datetime',
        'fechado_em' => 'datetime',
        'valor_inicial_centavos' => 'integer',
        'valor_final_centavos' => 'integer',
        'diferenca_centavos' => 'integer',
    ];

    /**
     * @return BelongsTo<Usuario, CaixaSessao>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * @return HasMany<CaixaMovimento>
     */
    public function movimentos(): HasMany
    {
        return $this->hasMany(CaixaMovimento::class, 'sessao_id');
    }
}
