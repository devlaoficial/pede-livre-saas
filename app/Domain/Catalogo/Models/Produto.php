<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Models;

use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Produto extends Model
{
    use BelongsToLoja, SoftDeletes;

    protected $table = 'produtos';

    protected $fillable = [
        'loja_id',
        'categoria_id',
        'nome',
        'descricao',
        'preco_centavos',
        'preco_promocional_centavos',
        'imagem',
        'ativo',
        'esgotado',
        'controla_estoque',
        'estoque_qtd',
        'canais',
        'ordem',
        'tempo_preparo_min',
        'estacao_kds',
        'alergenos',
        'codigo_pdv',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'preco_centavos' => 'integer',
        'preco_promocional_centavos' => 'integer',
        'ativo' => 'boolean',
        'esgotado' => 'boolean',
        'controla_estoque' => 'boolean',
        'estoque_qtd' => 'integer',
        'canais' => 'array',
        'ordem' => 'integer',
        'tempo_preparo_min' => 'integer',
        'alergenos' => 'array',
    ];

    /**
     * @return BelongsTo<Categoria, Produto>
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * @return HasMany<ProdutoVariacao>
     */
    public function variacoes(): HasMany
    {
        return $this->hasMany(ProdutoVariacao::class, 'produto_id')->orderBy('ordem');
    }

    /**
     * @return BelongsToMany<GrupoAdicional>
     */
    public function gruposAdicionais(): BelongsToMany
    {
        return $this->belongsToMany(GrupoAdicional::class, 'produto_grupos', 'produto_id', 'grupo_id')
            ->withPivot('ordem')
            ->orderByPivot('ordem');
    }

    /**
     * Effective price returning promotional price if set and lower, else base price.
     */
    public function getPrecoEfetivoCentavosAttribute(): int
    {
        if ($this->preco_promocional_centavos !== null && $this->preco_promocional_centavos > 0) {
            return $this->preco_promocional_centavos;
        }

        return $this->preco_centavos;
    }

    /**
     * Scope to filter products by sales channel.
     *
     * @param Builder<Produto> $query
     */
    public function scopePorCanal(Builder $query, string $canal): Builder
    {
        return $query->whereJsonContains('canais', $canal);
    }

    /**
     * Scope to return only available products (active and not paused/sold out).
     *
     * @param Builder<Produto> $query
     */
    public function scopeDisponiveis(Builder $query): Builder
    {
        return $query->where('ativo', true)->where('esgotado', false);
    }
}
