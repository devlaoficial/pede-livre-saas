<?php

declare(strict_types=1);

namespace App\Domain\Entregas\Services;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Entregas\Models\AreaEntrega;
use DomainException;
use Illuminate\Support\Facades\Cache;

final class CalculadoraFreteService
{
    private const RAIO_TERRA_KM = 6371.0;

    /**
     * Calculate delivery fee based on neighborhood matching or Haversine distance.
     *
     * @param Loja $loja
     * @param array{cep?: ?string, bairro?: ?string, lat?: ?float, lng?: ?float} $endereco
     * @param int $subtotalCentavos
     * @return array{taxa_centavos: int, tempo_estimado_min: int, distancia_km: ?float, frete_gratis: bool, pedido_minimo_centavos: int, area_id: int}
     */
    public function calcular(Loja $loja, array $endereco, int $subtotalCentavos): array
    {
        $bairro = isset($endereco['bairro']) ? trim(mb_strtolower($endereco['bairro'])) : null;
        $lat = $endereco['lat'] ?? null;
        $lng = $endereco['lng'] ?? null;
        $cep = isset($endereco['cep']) ? preg_replace('/\D+/', '', $endereco['cep']) : null;

        // 1. Check Neighborhood matching first
        if ($bairro !== null && $bairro !== '') {
            $areaBairro = AreaEntrega::where('loja_id', $loja->id)
                ->where('ativa', true)
                ->where('tipo', 'bairro')
                ->whereRaw('LOWER(nome_bairro) = ?', [$bairro])
                ->first();

            if ($areaBairro !== null) {
                return $this->formatarRespostaArea($areaBairro, $subtotalCentavos, null);
            }
        }

        // 2. Check Haversine Distance by Coordinates or Geocoded CEP
        if (($lat === null || $lng === null) && $cep !== null) {
            [$lat, $lng] = $this->geocodificarCepCached($cep);
        }

        if ($lat !== null && $lng !== null && $loja->lat !== null && $loja->lng !== null) {
            $distanciaKm = $this->calcularHaversine(
                (float) $loja->lat,
                (float) $loja->lng,
                (float) $lat,
                (float) $lng
            );

            $areaRaio = AreaEntrega::where('loja_id', $loja->id)
                ->where('ativa', true)
                ->where('tipo', 'raio')
                ->where('raio_km_de', '<=', $distanciaKm)
                ->where('raio_km_ate', '>=', $distanciaKm)
                ->orderBy('taxa_centavos')
                ->first();

            if ($areaRaio !== null) {
                return $this->formatarRespostaArea($areaRaio, $subtotalCentavos, $distanciaKm);
            }
        }

        throw new DomainException('Desculpe, o endereço informado está fora do nosso raio de entrega.');
    }

    /**
     * Haversine formula calculation.
     */
    public function calcularHaversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $latFrom = deg2rad($lat1);
        $lngFrom = deg2rad($lng1);
        $latTo = deg2rad($lat2);
        $lngTo = deg2rad($lng2);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lngDelta / 2), 2)
        ));

        return round($angle * self::RAIO_TERRA_KM, 2);
    }

    /**
     * @return array{0: ?float, 1: ?float}
     */
    private function geocodificarCepCached(string $cep): array
    {
        return Cache::remember("geocoding:cep:{$cep}", 86400 * 30, function () use ($cep): array {
            // Simulated local geocoding cache fallback or mock coordinates
            // Default offset around Minas Gerais / Vale do Aço
            return [-19.5186 + (intval(substr($cep, -3)) * 0.0001), -42.6306 + (intval(substr($cep, -3)) * 0.0001)];
        });
    }

    /**
     * @return array{taxa_centavos: int, tempo_estimado_min: int, distancia_km: ?float, frete_gratis: bool, pedido_minimo_centavos: int, area_id: int}
     */
    private function formatarRespostaArea(AreaEntrega $area, int $subtotalCentavos, ?float $distanciaKm): array
    {
        if ($subtotalCentavos < $area->pedido_minimo_centavos) {
            $minimoReais = number_format($area->pedido_minimo_centavos / 100, 2, ',', '.');
            throw new DomainException("O pedido mínimo para esta região é de R$ {$minimoReais}.");
        }

        $freteGratis = false;
        $taxaFinal = $area->taxa_centavos;

        if ($area->frete_gratis_acima_centavos !== null && $subtotalCentavos >= $area->frete_gratis_acima_centavos) {
            $freteGratis = true;
            $taxaFinal = 0;
        }

        return [
            'taxa_centavos' => $taxaFinal,
            'tempo_estimado_min' => $area->tempo_estimado_min,
            'distancia_km' => $distanciaKm,
            'frete_gratis' => $freteGratis,
            'pedido_minimo_centavos' => $area->pedido_minimo_centavos,
            'area_id' => $area->id,
        ];
    }
}
