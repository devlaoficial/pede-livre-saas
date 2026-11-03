<?php

declare(strict_types=1);

namespace App\Domain\Assinaturas\Resources;

use App\Domain\Assinaturas\Models\Loja;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Loja
 */
final class LojaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $config = is_array($this->config_json) ? $this->config_json : [];

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
            'status' => $this->status,
            'trial_ate' => $this->trial_ate?->toIso8601String(),
            'plano_id' => $this->plano_id,
            'plano' => $this->plano?->nome ?? 'premium',
            'plano_preco_centavos' => $this->plano?->preco_centavos ?? 24990,
            'mp_conectado' => $this->mp_access_token !== null,
            'chave_pix' => $config['chave_pix'] ?? null,
            'config_json' => [
                'timezone' => $config['timezone'] ?? 'America/Sao_Paulo',
                'tempo_medio_preparo_min' => (int) ($config['tempo_medio_preparo_min'] ?? 35),
                'aceita_pedidos' => (bool) ($config['aceita_pedidos'] ?? true),
                'estrategia_pizza_meio_a_meio' => $config['estrategia_pizza_meio_a_meio'] ?? 'maior_preco',
                'taxa_servico_pct' => (int) ($config['taxa_servico_pct'] ?? 10),
                'cashback_pct' => (float) ($config['cashback_pct'] ?? 5.0),
                'cashback_max_resgate_pct' => (float) ($config['cashback_max_resgate_pct'] ?? 50.0),
                'pedido_minimo_centavos' => (int) ($config['pedido_minimo_centavos'] ?? 0),
                'impressao_automatica_ao_aceitar' => (bool) ($config['impressao_automatica_ao_aceitar'] ?? false),
                'chave_pix' => $config['chave_pix'] ?? null,
                'aviso_fatura_vencida' => $config['aviso_fatura_vencida'] ?? null,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
