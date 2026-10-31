<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Models;

use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Combo extends Model
{
    use BelongsToLoja, SoftDeletes;

    protected $table = 'combos';

    protected $fillable = [
        'loja_id',
        'nome',
        'descricao',
        'preco_centavos',
        'imagem',
        'ativo',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'preco_centavos' => 'integer',
        'ativo' => 'boolean',
    ];

    /**
     * @return HasMany<ComboItem>
     */
    public function itens(): HasMany
    {
        return $this->hasMany(ComboItem::class, 'combo_id');
    }
}
