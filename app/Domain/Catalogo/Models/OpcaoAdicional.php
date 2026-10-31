<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Models;

use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OpcaoAdicional extends Model
{
    use BelongsToLoja;

    protected $table = 'opcoes_adicionais';

    protected $fillable = [
        'loja_id',
        'grupo_id',
        'nome',
        'preco_centavos',
        'ativo',
        'esgotado',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'preco_centavos' => 'integer',
        'ativo' => 'boolean',
        'esgotado' => 'boolean',
    ];

    /**
     * @return BelongsTo<GrupoAdicional, OpcaoAdicional>
     */
    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoAdicional::class, 'grupo_id');
    }
}
