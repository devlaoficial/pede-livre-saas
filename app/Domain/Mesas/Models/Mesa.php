<?php

declare(strict_types=1);

namespace App\Domain\Mesas\Models;

use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

final class Mesa extends Model
{
    use BelongsToLoja;

    protected $table = 'mesas';

    protected $fillable = [
        'loja_id',
        'numero',
        'nome',
        'qr_token',
        'capacidade',
        'status',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'numero' => 'integer',
        'capacidade' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Mesa $mesa): void {
            if (empty($mesa->qr_token)) {
                $mesa->qr_token = Str::random(32);
            }
        });
    }

    /**
     * @return HasMany<Comanda>
     */
    public function comandas(): HasMany
    {
        return $this->hasMany(Comanda::class, 'mesa_id');
    }

    /**
     * Active comanda for this table.
     *
     * @return HasOne<Comanda>
     */
    public function comandaAtiva(): HasOne
    {
        return $this->hasOne(Comanda::class, 'mesa_id')
            ->where('status', 'aberta')
            ->latest('aberta_em');
    }

    public function regenerarQrToken(): string
    {
        $this->qr_token = Str::random(32);
        $this->save();

        return $this->qr_token;
    }
}
