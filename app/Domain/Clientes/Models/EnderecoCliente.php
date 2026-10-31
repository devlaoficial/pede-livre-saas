<?php

declare(strict_types=1);

namespace App\Domain\Clientes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EnderecoCliente extends Model
{
    protected $table = 'enderecos_cliente';

    protected $fillable = [
        'cliente_id',
        'apelido',
        'cep',
        'rua',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'uf',
        'lat',
        'lng',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'cep' => 'encrypted',
        'rua' => 'encrypted',
        'numero' => 'encrypted',
        'complemento' => 'encrypted',
        'lat' => 'float',
        'lng' => 'float',
    ];

    /**
     * @return BelongsTo<Cliente, EnderecoCliente>
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
