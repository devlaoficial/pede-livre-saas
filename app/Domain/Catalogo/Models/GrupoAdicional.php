<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Models;

use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class GrupoAdicional extends Model
{
    use BelongsToLoja, SoftDeletes;

    protected $table = 'grupos_adicionais';

    protected $fillable = [
        'loja_id',
        'nome',
        'minimo',
        'maximo',
        'obrigatorio',
        'tipo',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'minimo' => 'integer',
        'maximo' => 'integer',
        'obrigatorio' => 'boolean',
    ];

    /**
     * @return HasMany<OpcaoAdicional>
     */
    public function opcoes(): HasMany
    {
        return $this->hasMany(OpcaoAdicional::class, 'grupo_id');
    }

    /**
     * @return BelongsToMany<Produto>
     */
    public function produtos(): BelongsToMany
    {
        return $this->belongsToMany(Produto::class, 'produto_grupos', 'grupo_id', 'produto_id');
    }

    public function isMeioAMeio(): bool
    {
        return $this->tipo === 'sabor_meio_a_meio';
    }

    public function isEscolhaUnica(): bool
    {
        return $this->tipo === 'escolha_unica';
    }
}
