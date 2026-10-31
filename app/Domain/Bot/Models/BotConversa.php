<?php

declare(strict_types=1);

namespace App\Domain\Bot\Models;

use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class BotConversa extends Model
{
    use BelongsToLoja;

    protected $table = 'bot_conversas';

    protected $fillable = [
        'loja_id',
        'telefone',
        'telefone_bidx',
        'estado',
        'contexto_json',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'telefone_bidx',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'telefone' => 'encrypted',
        'contexto_json' => 'encrypted:array',
    ];

    /**
     * @return HasMany<BotMensagem>
     */
    public function mensagens(): HasMany
    {
        return $this->hasMany(BotMensagem::class, 'conversa_id');
    }
}
