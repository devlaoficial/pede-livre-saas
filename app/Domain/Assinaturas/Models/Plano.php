<?php

declare(strict_types=1);

namespace App\Domain\Assinaturas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Plano extends Model
{
    protected $table = 'planos';

    protected $fillable = [
        'nome',
        'preco_centavos',
        'recursos_json',
        'limite_usuarios',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'preco_centavos' => 'integer',
        'recursos_json' => 'array',
        'limite_usuarios' => 'integer',
    ];

    /**
     * @return HasMany<Loja>
     */
    public function lojas(): HasMany
    {
        return $this->hasMany(Loja::class, 'plano_id');
    }

    /**
     * @return HasMany<Assinatura>
     */
    public function assinaturas(): HasMany
    {
        return $this->hasMany(Assinatura::class, 'plano_id');
    }
}
