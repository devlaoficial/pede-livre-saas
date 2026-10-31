<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use App\Domain\Assinaturas\Models\Loja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * Trait to enforce automatic multi-tenant scoping and loja_id assignment.
 */
trait BelongsToLoja
{
    public static function bootBelongsToLoja(): void
    {
        static::addGlobalScope(new LojaScope());

        static::creating(function (Model $model): void {
            /** @var LojaContext $context */
            $context = app(LojaContext::class);

            if (!isset($model->loja_id) || $model->loja_id === null) {
                if (isset($model->papel) && in_array($model->papel, ['super_admin', 'suporte'], true)) {
                    return;
                }
                $lojaId = $context->getLojaId();
                if ($lojaId === null) {
                    throw new RuntimeException(
                        sprintf('Cannot create [%s] without an active LojaContext.', get_class($model))
                    );
                }
                $model->loja_id = $lojaId;
            }
        });
    }

    /**
     * Get the store that owns this model.
     *
     * @return BelongsTo<Loja, Model>
     */
    public function loja(): BelongsTo
    {
        return $this->belongsTo(Loja::class, 'loja_id');
    }
}
