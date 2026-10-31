<?php

declare(strict_types=1);

namespace App\Domain\Bot\Models;

use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BotMensagem extends Model
{
    use BelongsToLoja;

    public const UPDATED_AT = null;

    protected $table = 'bot_mensagens';

    protected $fillable = [
        'loja_id',
        'conversa_id',
        'direcao',
        'mensagem',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'mensagem' => 'encrypted',
        'created_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<BotConversa, BotMensagem>
     */
    public function conversa(): BelongsTo
    {
        return $this->belongsTo(BotConversa::class, 'conversa_id');
    }
}
