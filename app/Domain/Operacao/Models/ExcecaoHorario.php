<?php

declare(strict_types=1);

namespace App\Domain\Operacao\Models;

use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;

final class ExcecaoHorario extends Model
{
    use BelongsToLoja;

    protected $table = 'excecoes_horario';

    protected $fillable = [
        'loja_id',
        'data',
        'fechado',
        'abre',
        'fecha',
        'descricao',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'date',
        'fechado' => 'boolean',
    ];
}
