<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class LojaScope implements Scope
{
    /**
     * Apply the tenant scope to a given Eloquent query builder.
     *
     * @param Builder<Model> $builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        /** @var LojaContext $context */
        $context = app(LojaContext::class);

        if ($context->hasLoja()) {
            $table = $model->getTable();
            $builder->where("{$table}.loja_id", '=', $context->getLojaId());
        }
    }
}
