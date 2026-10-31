<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Services;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Catalogo\Models\OpcaoAdicional;
use InvalidArgumentException;

final class PrecificacaoPizzaService
{
    public const ESTRATEGIA_MAIOR_PRECO = 'maior_preco';
    public const ESTRATEGIA_MEDIA = 'media';

    /**
     * Calculates the price in integer centavos for a multi-flavor pizza based on store configuration.
     *
     * @param Loja $loja
     * @param int $precoBaseCentavos (e.g. from selected variation like P, M, G)
     * @param array<OpcaoAdicional> $saboresSelecionados
     * @return int
     */
    public function calcularPrecoPizza(Loja $loja, int $precoBaseCentavos, array $saboresSelecionados): int
    {
        if (empty($saboresSelecionados)) {
            return $precoBaseCentavos;
        }

        $config = $loja->config_json ?? [];
        $estrategia = $config['estrategia_pizza_meio_a_meio'] ?? self::ESTRATEGIA_MAIOR_PRECO;

        $precosSabores = array_map(
            static fn (OpcaoAdicional $opcao): int => $opcao->preco_centavos,
            $saboresSelecionados
        );

        if ($estrategia === self::ESTRATEGIA_MAIOR_PRECO) {
            $adicionalSabor = max($precosSabores);
        } elseif ($estrategia === self::ESTRATEGIA_MEDIA) {
            // Arithmetic mean rounded to integer centavos
            $adicionalSabor = (int) round(array_sum($precosSabores) / count($precosSabores));
        } else {
            throw new InvalidArgumentException("Estratégia de pizza inválida: {$estrategia}");
        }

        return $precoBaseCentavos + $adicionalSabor;
    }
}
