<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

final class ProdutoGrupo extends Pivot
{
    protected $table = 'produto_grupos';

    public $timestamps = false;

    protected $fillable = [
        'produto_id',
        'grupo_id',
        'ordem',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'ordem' => 'integer',
    ];
}
