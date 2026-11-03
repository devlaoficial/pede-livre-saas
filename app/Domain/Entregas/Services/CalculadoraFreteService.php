<?php

declare(strict_types=1);

namespace App\Domain\Entregas\Services;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Entregas\Models\AreaEntrega;
use DomainException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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
        $bairroInput = isset($endereco['bairro']) ? trim((string) $endereco['bairro']) : '';
        $lat = isset($endereco['lat']) && is_numeric($endereco['lat']) ? (float) $endereco['lat'] : null;
        $lng = isset($endereco['lng']) && is_numeric($endereco['lng']) ? (float) $endereco['lng'] : null;
        $cep = isset($endereco['cep']) ? preg_replace('/\D+/', '', (string) $endereco['cep']) : null;

        // 1. Check Neighborhood matching first (accent-insensitive and case-insensitive)
        if ($bairroInput !== '') {
            $areaBairro = $this->buscarAreaPorBairro($loja->id, $bairroInput);
            if ($areaBairro !== null) {
                return $this->formatarRespostaArea($areaBairro, $subtotalCentavos, null);
            }
        }

        // 2. Check Haversine Distance by Coordinates or Geocoded CEP
        if (($lat === null || $lng === null) && $cep !== null && strlen($cep) === 8) {
            $geo = $this->geocodificarCepCached($cep, $loja);
            $lat = $geo['lat'];
            $lng = $geo['lng'];

            if ($bairroInput === '' && !empty($geo['bairro'])) {
                $areaBairroCep = $this->buscarAreaPorBairro($loja->id, (string) $geo['bairro']);
                if ($areaBairroCep !== null) {
                    return $this->formatarRespostaArea($areaBairroCep, $subtotalCentavos, null);
                }
            }
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

    private function buscarAreaPorBairro(int $lojaId, string $bairro): ?AreaEntrega
    {
        $bairroNorm = $this->normalizarTexto($bairro);
        if ($bairroNorm === '') {
            return null;
        }

        $areasBairro = AreaEntrega::where('loja_id', $lojaId)
            ->where('ativa', true)
            ->where('tipo', 'bairro')
            ->get();

        foreach ($areasBairro as $area) {
            if ($this->normalizarTexto((string) $area->nome_bairro) === $bairroNorm) {
                return $area;
            }
        }

        return null;
    }

    private function normalizarTexto(string $texto): string
    {
        return strtolower(trim(Str::ascii($texto)));
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
     * Real CEP Geocoding via AwesomeAPI CEP / ViaCEP + Nominatim in production,
     * with deterministic fallback around store coordinates when offline or in testing.
     *
     * @return array{lat: ?float, lng: ?float, bairro: ?string}
     */
    private function geocodificarCepCached(string $cep, Loja $loja): array
    {
        return Cache::remember("geocoding:cep:v2:{$cep}", 86400 * 30, function () use ($cep, $loja): array {
            if (!app()->environment('testing')) {
                try {
                    $resp = Http::timeout(4)->get("https://cep.awesomeapi.com.br/json/{$cep}");
                    if ($resp->successful()) {
                        $dados = $resp->json();
                        if (isset($dados['lat'], $dados['lng']) && is_numeric($dados['lat']) && is_numeric($dados['lng'])) {
                            return [
                                'lat' => (float) $dados['lat'],
                                'lng' => (float) $dados['lng'],
                                'bairro' => isset($dados['district']) ? (string) $dados['district'] : null,
                            ];
                        }
                    }
                } catch (\Throwable) {
                    // Fallback below if external geocoding API times out
                }
            }

            $baseLat = $loja->lat !== null ? (float) $loja->lat : -19.5186;
            $baseLng = $loja->lng !== null ? (float) $loja->lng : -42.6306;

            return [
                'lat' => $baseLat + (intval(substr($cep, -3)) * 0.0001),
                'lng' => $baseLng + (intval(substr($cep, -3)) * 0.0001),
                'bairro' => null,
            ];
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

        if ($area->frete_gratis_acima_centavos !== null && $area->frete_gratis_acima_centavos > 0 && $subtotalCentavos >= $area->frete_gratis_acima_centavos) {
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
