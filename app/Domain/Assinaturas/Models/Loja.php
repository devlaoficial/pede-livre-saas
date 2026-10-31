<?php

declare(strict_types=1);

namespace App\Domain\Assinaturas\Models;

use App\Domain\Autenticacao\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Loja extends Model
{
    use SoftDeletes;

    protected $table = 'lojas';

    protected $fillable = [
        'slug',
        'dominio_proprio',
        'nome',
        'cnpj_cpf',
        'cnpj_cpf_bidx',
        'telefone_whatsapp',
        'logo',
        'capa',
        'cor_primaria',
        'endereco',
        'cidade',
        'uf',
        'lat',
        'lng',
        'plano_id',
        'status',
        'trial_ate',
        'config_json',
        'mp_access_token',
        'mp_refresh_token',
        'mp_user_id',
    ];

    /**
     * Strict hidden attributes to prevent zero secret/PII leakage.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'cnpj_cpf',
        'cnpj_cpf_bidx',
        'mp_access_token',
        'mp_refresh_token',
        'mp_user_id',
        'deleted_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'cnpj_cpf' => 'encrypted',
        'mp_access_token' => 'encrypted',
        'mp_refresh_token' => 'encrypted',
        'mp_user_id' => 'encrypted',
        'config_json' => 'array',
        'trial_ate' => 'datetime',
        'lat' => 'float',
        'lng' => 'float',
    ];

    protected static function booted(): void
    {
        static::saving(function (Loja $loja): void {
            if ($loja->isDirty('cnpj_cpf') && !empty($loja->cnpj_cpf)) {
                $loja->cnpj_cpf_bidx = app(\App\Support\Security\BlindIndexService::class)
                    ->generateForDocument((string) $loja->cnpj_cpf);
            }
        });
    }

    public function temRecurso(string $featureFlag): bool
    {
        $recursos = $this->plano?->recursos_json ?? [];
        return (bool) ($recursos[$featureFlag] ?? false);
    }

    /**
     * @return BelongsTo<Plano, Loja>
     */
    public function plano(): BelongsTo
    {
        return $this->belongsTo(Plano::class, 'plano_id');
    }

    /**
     * @return HasMany<Usuario>
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'loja_id');
    }

    /**
     * @return HasMany<Assinatura>
     */
    public function assinaturas(): HasMany
    {
        return $this->hasMany(Assinatura::class, 'loja_id');
    }

    /**
     * @return HasMany<Fatura>
     */
    public function faturas(): HasMany
    {
        return $this->hasMany(Fatura::class, 'loja_id');
    }

    /**
     * @return HasMany<Auditoria>
     */
    public function auditorias(): HasMany
    {
        return $this->hasMany(Auditoria::class, 'loja_id');
    }
}
