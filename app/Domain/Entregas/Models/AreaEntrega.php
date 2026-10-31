<?php

declare(strict_types=1);

namespace App\Domain\Entregas\Models;

use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;

final class AreaEntrega extends Model
{
    use BelongsToLoja;

    protected $table = 'areas_entrega';

    protected $fillable = [
        'loja_id',
        'tipo',
        'nome_bairro',
        'raio_km_de',
        'raio_km_ate',
        'taxa_centavos',
        'pedido_minimo_centavos',
        'tempo_estimado_min',
        'frete_gratis_acima_centavos',
        'ativa',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'raio_km_de' => 'float',
        'raio_km_ate' => 'float',
        'taxa_centavos' => 'integer',
        'pedido_minimo_centavos' => 'integer',
        'tempo_estimado_min' => 'integer',
        'frete_gratis_acima_centavos' => 'integer',
        'ativa' => 'boolean',
    ];
}
