<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Models;

use App\Domain\Clientes\Models\Cliente;
use App\Domain\Entregas\Models\Entregador;
use App\Domain\Mesas\Models\Comanda;
use App\Domain\Mesas\Models\Mesa;
use App\Domain\Pagamentos\Models\Pagamento;
use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Pedido extends Model
{
    use BelongsToLoja;

    protected $table = 'pedidos';

    protected static function booted(): void
    {
        // Safety net: every order must have a tracking token hash (the checkout normally provides it).
        static::creating(function (self $pedido): void {
            if (empty($pedido->tracking_token_hash)) {
                $pedido->tracking_token_hash = hash_hmac(
                    'sha256',
                    bin2hex(random_bytes(24)),
                    (string) config('app.blind_index_key')
                );
            }
        });
    }

    protected $fillable = [
        'loja_id',
        'codigo_publico',
        'tracking_token_hash',
        'canal',
        'status',
        'cliente_id',
        'mesa_id',
        'comanda_id',
        'entregador_id',
        'subtotal',
        'taxa_entrega',
        'desconto',
        'desconto_cashback',
        'taxa_servico',
        'total',
        'cupom_id',
        'forma_pagamento',
        'pagamento_status',
        'troco_para',
        'observacao',
        'endereco_json',
        'agendado_para',
        'origem',
        'aceito_em',
        'pronto_em',
        'entregue_em',
        'cancelado_motivo',
    ];

    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'tracking_token_hash',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'subtotal' => 'integer',
        'taxa_entrega' => 'integer',
        'desconto' => 'integer',
        'desconto_cashback' => 'integer',
        'taxa_servico' => 'integer',
        'total' => 'integer',
        'troco_para' => 'integer',
        'endereco_json' => 'encrypted:array',
        'agendado_para' => 'datetime',
        'aceito_em' => 'datetime',
        'pronto_em' => 'datetime',
        'entregue_em' => 'datetime',
    ];

    /**
     * @return HasMany<PedidoItem>
     */
    public function itens(): HasMany
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }

    /**
     * @return HasMany<PedidoEvento>
     */
    public function eventos(): HasMany
    {
        return $this->hasMany(PedidoEvento::class, 'pedido_id')->orderBy('created_at');
    }

    /**
     * @return HasOne<Pagamento>
     */
    public function pagamento(): HasOne
    {
        return $this->hasOne(Pagamento::class, 'pedido_id');
    }

    /**
     * @return BelongsTo<Cliente, Pedido>
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * @return BelongsTo<Cupom, Pedido>
     */
    public function cupom(): BelongsTo
    {
        return $this->belongsTo(Cupom::class, 'cupom_id');
    }

    /**
     * @return BelongsTo<Mesa, Pedido>
     */
    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class, 'mesa_id');
    }

    /**
     * @return BelongsTo<Comanda, Pedido>
     */
    public function comanda(): BelongsTo
    {
        return $this->belongsTo(Comanda::class, 'comanda_id');
    }

    /**
     * @return BelongsTo<Entregador, Pedido>
     */
    public function entregador(): BelongsTo
    {
        return $this->belongsTo(Entregador::class, 'entregador_id');
    }
}
