<?php

declare(strict_types=1);

namespace App\Domain\Clientes\Models;

use App\Domain\Pedidos\Models\Pedido;
use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Cliente extends Model
{
    use BelongsToLoja;

    protected $table = 'clientes';

    protected $fillable = [
        'loja_id',
        'nome',
        'telefone',
        'telefone_bidx',
        'email',
        'email_bidx',
        'aceita_marketing',
        'consentimento_em',
        'total_pedidos',
        'total_gasto',
        'saldo_cashback_centavos',
        'ultimo_pedido_em',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'telefone_bidx',
        'email_bidx',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'telefone' => 'encrypted',
        'email' => 'encrypted',
        'aceita_marketing' => 'boolean',
        'consentimento_em' => 'datetime',
        'total_pedidos' => 'integer',
        'total_gasto' => 'integer',
        'saldo_cashback_centavos' => 'integer',
        'ultimo_pedido_em' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Cliente $cliente): void {
            $blindService = app(\App\Support\Security\BlindIndexService::class);
            if ($cliente->isDirty('telefone') && !empty($cliente->telefone)) {
                $cliente->telefone_bidx = $blindService->generateForPhone((string) $cliente->telefone);
            }
            if ($cliente->isDirty('email') && !empty($cliente->email)) {
                $cliente->email_bidx = $blindService->generateForEmail((string) $cliente->email);
            }
        });
    }

    /**
     * @return HasMany<EnderecoCliente>
     */
    public function enderecos(): HasMany
    {
        return $this->hasMany(EnderecoCliente::class, 'cliente_id');
    }

    /**
     * @return HasMany<Pedido>
     */
    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class, 'cliente_id');
    }
}
