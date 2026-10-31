<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Observers;

use App\Domain\Catalogo\Services\CardapioCacheService;
use Illuminate\Database\Eloquent\Model;

final class CatalogoCacheObserver
{
    public function __construct(
        private readonly CardapioCacheService $cacheService
    ) {}

    public function saved(Model $model): void
    {
        $this->evictCache($model);
    }

    public function deleted(Model $model): void
    {
        $this->evictCache($model);
    }

    public function restored(Model $model): void
    {
        $this->evictCache($model);
    }

    private function evictCache(Model $model): void
    {
        /** @var int|null $lojaId */
        $lojaId = $model->getAttribute('loja_id');

        if ($lojaId !== null) {
            $this->cacheService->invalidar((int) $lojaId);
        }
    }
}
