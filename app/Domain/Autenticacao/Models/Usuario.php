<?php

declare(strict_types=1);

namespace App\Domain\Autenticacao\Models;

use App\Domain\Assinaturas\Models\Loja;
use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

final class Usuario extends Authenticatable
{
    use HasApiTokens, SoftDeletes, BelongsToLoja;

    protected $table = 'usuarios';

    protected $fillable = [
        'loja_id',
        'nome',
        'email',
        'email_bidx',
        'telefone',
        'senha_hash',
        'papel',
        'two_factor_secret',
        'ativo',
        'ultimo_login',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'senha_hash',
        'two_factor_secret',
        'email_bidx',
        'deleted_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'email' => 'encrypted',
        'telefone' => 'encrypted',
        'two_factor_secret' => 'encrypted',
        'ativo' => 'boolean',
        'ultimo_login' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Usuario $usuario): void {
            if ($usuario->isDirty('email') && !empty($usuario->email)) {
                $usuario->email_bidx = app(\App\Support\Security\BlindIndexService::class)
                    ->generateForEmail((string) $usuario->email);
            }
        });
    }

    public function getAuthPassword(): string
    {
        return $this->senha_hash;
    }

    public function isSuperAdmin(): bool
    {
        return $this->papel === 'super_admin';
    }

    public function isSuporte(): bool
    {
        return $this->papel === 'suporte';
    }

    public function isDonoLoja(): bool
    {
        return in_array($this->papel, ['super_admin', 'dono_loja'], true);
    }

    public function isGerente(): bool
    {
        return in_array($this->papel, ['super_admin', 'dono_loja', 'gerente'], true);
    }

    public function isCaixa(): bool
    {
        return in_array($this->papel, ['super_admin', 'dono_loja', 'gerente', 'caixa'], true);
    }

    public function isCozinha(): bool
    {
        return $this->papel === 'cozinha';
    }

    public function isGarcom(): bool
    {
        return $this->papel === 'garcom';
    }

    public function isEntregador(): bool
    {
        return $this->papel === 'entregador';
    }
}
