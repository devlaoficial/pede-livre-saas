<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Models;

use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Categoria extends Model
{
    use BelongsToLoja, SoftDeletes;

    protected $table = 'categorias';

    protected $fillable = [
        'loja_id',
        'nome',
        'descricao',
        'ordem',
        'ativa',
        'canais',
        'horario_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'ordem' => 'integer',
        'ativa' => 'boolean',
        'canais' => 'array',
        'horario_id' => 'integer',
    ];

    /**
     * @return HasMany<Produto>
     */
    public function produtos(): HasMany
    {
        return $this->hasMany(Produto::class, 'categoria_id')->orderBy('ordem');
    }

    /**
     * Scope to filter categories by sales channel (delivery, mesa, balcao).
     *
     * @param Builder<Categoria> $query
     */
    public function scopePorCanal(Builder $query, string $canal): Builder
    {
        return $query->whereJsonContains('canais', $canal);
    }

    /**
     * Scope to return only active categories.
     *
     * @param Builder<Categoria> $query
     */
    public function scopeAtivas(Builder $query): Builder
    {
        return $query->where('ativa', true);
    }
}
