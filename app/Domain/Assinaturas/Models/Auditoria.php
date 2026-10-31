<?php

declare(strict_types=1);

namespace App\Domain\Assinaturas\Models;

use App\Domain\Autenticacao\Models\Usuario;
use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Auditoria extends Model
{
    use BelongsToLoja;

    public const UPDATED_AT = null;

    protected $table = 'auditoria';

    protected $fillable = [
        'loja_id',
        'usuario_id',
        'acao',
        'entidade',
        'entidade_id',
        'antes_json',
        'depois_json',
        'ip_hash',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'antes_json' => 'encrypted:array',
        'depois_json' => 'encrypted:array',
        'created_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Usuario, Auditoria>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
