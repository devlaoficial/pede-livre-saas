<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Services;

use App\Domain\Catalogo\Models\Categoria;
use App\Domain\Catalogo\Resources\CategoriaResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

final class CardapioCacheService
{
    private const TTL_SECONDS = 21600; // 6 hours

    /**
     * @param int $lojaId
     * @param string $canal
     * @return array<string, mixed>
     */
    public function getCardapio(int $lojaId, string $canal): array
    {
        $cacheKey = $this->buildKey($lojaId, $canal);

        /** @var array<string, mixed> $result */
        $result = Cache::store($this->store())->remember($cacheKey, self::TTL_SECONDS, function () use ($canal): array {
            $categorias = Categoria::ativas()
                ->porCanal($canal)
                ->with([
                    'produtos' => fn ($q) => $q->disponiveis()->porCanal($canal)->orderBy('ordem'),
                    'produtos.variacoes',
                    'produtos.gruposAdicionais.opcoes' => fn ($q) => $q->where('ativo', true),
                ])
                ->orderBy('ordem')
                ->get();

            return [
                'canal' => $canal,
                'cached_at' => now()->toIso8601String(),
                'categorias' => CategoriaResource::collection($categorias)->resolve(),
            ];
        });

        return $result;
    }

    public function invalidar(int $lojaId): void
    {
        foreach (['delivery', 'mesa', 'balcao', 'retirada'] as $canal) {
            Cache::store($this->store())->forget($this->buildKey($lojaId, $canal));
        }

        $pattern = "*pedelivre:loja:{$lojaId}:cardapio:v1*";

        try {
            $redis = Redis::connection('cache');
            $keys = $redis->keys($pattern);

            if (!empty($keys)) {
                $redis->del($keys);
            }
        } catch (\Throwable) {
            // Already forgotten via Cache::store()->forget() above
        }
    }

    /**
     * Cache store used for the public menu (Redis in production, configurable for tests).
     */
    private function store(): string
    {
        return (string) config('cache.cardapio_store', 'redis');
    }

    private function buildKey(int $lojaId, string $canal): string
    {
        return "pedelivre:loja:{$lojaId}:cardapio:v1:{$canal}";
    }
}
