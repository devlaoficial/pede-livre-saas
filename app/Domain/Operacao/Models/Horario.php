<?php

declare(strict_types=1);

namespace App\Domain\Operacao\Models;

use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;

final class Horario extends Model
{
    use BelongsToLoja;

    protected $table = 'horarios';

    protected $fillable = [
        'loja_id',
        'canal',
        'dia_semana',
        'abre',
        'fecha',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'dia_semana' => 'integer',
    ];
}
