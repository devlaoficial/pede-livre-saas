<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Models;

use App\Support\Tenancy\BelongsToLoja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProdutoVariacao extends Model
{
    use BelongsToLoja;

    protected $table = 'produto_variacoes';

    protected $fillable = [
        'loja_id',
        'produto_id',
        'nome',
        'preco_centavos',
        'ordem',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'preco_centavos' => 'integer',
        'ordem' => 'integer',
    ];

    /**
     * @return BelongsTo<Produto, ProdutoVariacao>
     */
    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}
