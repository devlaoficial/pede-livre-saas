<?php

declare(strict_types=1);

namespace App\Domain\Entregas\Models;

use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;

final class Entregador extends Model
{
    use BelongsToLoja;

    protected $table = 'entregadores';

    protected $fillable = [
        'loja_id',
        'nome',
        'telefone',
        'telefone_bidx',
        'tipo',
        'valor_por_entrega_centavos',
        'ativo',
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
        'valor_por_entrega_centavos' => 'integer',
        'ativo' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Entregador $entregador): void {
            if ($entregador->isDirty('telefone') && !empty($entregador->telefone)) {
                $entregador->telefone_bidx = app(\App\Support\Security\BlindIndexService::class)
                    ->generateForPhone((string) $entregador->telefone);
            }
        });
    }
}
