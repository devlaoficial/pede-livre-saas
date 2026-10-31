<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ComboItem extends Model
{
    protected $table = 'combo_itens';

    protected $fillable = [
        'combo_id',
        'produto_id',
        'qtd',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'qtd' => 'integer',
    ];

    /**
     * @return BelongsTo<Combo, ComboItem>
     */
    public function combo(): BelongsTo
    {
        return $this->belongsTo(Combo::class, 'combo_id');
    }

    /**
     * @return BelongsTo<Produto, ComboItem>
     */
    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}
