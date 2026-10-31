<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Resources;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Catalogo\Services\HorarioFuncionamentoService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Loja
 */
final class CardapioPublicoLojaResource extends JsonResource
{
    /**
     * Strictly public metadata with zero secret/PII exposure and Google SEO Restaurant Schema.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var HorarioFuncionamentoService $horarioService */
        $horarioService = app(HorarioFuncionamentoService::class);
        $statusOperacao = $horarioService->avaliarStatusLoja($this->resource, 'delivery');

        $config = $this->config_json ?? [];
        $tempoMedioPreparo = (int) ($config['tempo_medio_preparo_min'] ?? 30);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'dominio_proprio' => $this->dominio_proprio,
            'nome' => $this->nome,
            'telefone_whatsapp' => $this->telefone_whatsapp,
            'logo' => $this->logo,
            'capa' => $this->capa,
            'cor_primaria' => $this->cor_primaria,
            'endereco' => $this->endereco,
            'cidade' => $this->cidade,
            'uf' => $this->uf,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'tempo_medio_preparo_min' => $tempoMedioPreparo,
            'status_operacao' => $statusOperacao,
            'seo_json_ld' => [
                '@context' => 'https://schema.org',
                '@type' => 'Restaurant',
                'name' => $this->nome,
                'image' => $this->logo ? url($this->logo) : null,
                'telephone' => $this->telefone_whatsapp,
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $this->endereco,
                    'addressLocality' => $this->cidade,
                    'addressRegion' => $this->uf,
                    'addressCountry' => 'BR',
                ],
                'servesCuisine' => 'Gastronomia Brasileira / Delivery',
                'priceRange' => '$$',
            ],
        ];
    }
}
