<?php

declare(strict_types=1);

namespace App\Domain\Bot\Services;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Bot\Models\BotConversa;
use App\Domain\Bot\Models\BotMensagem;
use App\Domain\Clientes\Models\Cliente;
use App\Domain\Pedidos\Models\Cupom;
use App\Support\Security\BlindIndexService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class WinBackService
{
    public function __construct(
        private readonly EvolutionApiService $evolutionApi,
        private readonly BlindIndexService $blindIndex
    ) {}

    /**
     * Retorna a lista de clientes inativos elegíveis para a campanha de Win-Back da loja.
     *
     * @return Collection<int, Cliente>
     */
    public function obterClientesElegiveis(Loja $loja, ?int $diasInatividadeOverride = null): Collection
    {
        $config = $loja->config_json['whatsapp'] ?? [];
        $diasInatividade = $diasInatividadeOverride ?? (int) ($config['winback_dias_inatividade'] ?? 30);
        $cooldownDias = (int) ($config['winback_cooldown_dias'] ?? 30);
        $limiteDiario = (int) ($config['winback_limite_diario'] ?? 30);

        // Clientes que já fizeram pedidos, mas o último foi há mais de X dias
        $dataCorte = now()->subDays($diasInatividade);

        // Busca clientes da loja ordenados pelos que compraram há mais tempo recente dentro da janela
        $clientes = Cliente::where('loja_id', $loja->id)
            ->where('aceita_marketing', true)
            ->whereNotNull('ultimo_pedido_em')
            ->where('ultimo_pedido_em', '<=', $dataCorte)
            ->orderBy('ultimo_pedido_em', 'desc')
            ->take($limiteDiario * 3) // Pega margem para filtrar quem está em cooldown
            ->get();

        // Filtra clientes em cooldown de winback
        $clientesElegiveis = $clientes->filter(function (Cliente $cliente) use ($loja, $cooldownDias) {
            if (empty($cliente->telefone)) {
                return false;
            }

            // Verifica chave de cache do disparo recente
            $cacheKey = "winback_enviado_cliente_{$loja->id}_{$cliente->id}";
            if (Cache::has($cacheKey)) {
                return false;
            }

            // Verifica registro histórico na conversa
            $conversa = BotConversa::where('loja_id', $loja->id)
                ->where('telefone_bidx', $cliente->telefone_bidx)
                ->first();

            if ($conversa !== null && !empty($conversa->contexto_json['ultimo_winback_em'])) {
                try {
                    $ultimoWinback = Carbon::parse($conversa->contexto_json['ultimo_winback_em']);
                    if ($ultimoWinback->diffInDays(now()) < $cooldownDias) {
                        return false;
                    }
                } catch (\Throwable) {}
            }

            return true;
        })->values();

        // Retorna respeitando o limite diário configurado para governança anti-ban
        return $clientesElegiveis->take($limiteDiario);
    }

    /**
     * Retorna resumo estatístico e preview para o painel do lojista.
     *
     * @return array{
     *     success: bool,
     *     total_clientes_base: int,
     *     total_inativos: int,
     *     dias_inatividade: int,
     *     cupom_codigo: string,
     *     desconto_texto: string,
     *     validade_horas: int,
     *     exemplo_mensagem: string,
     *     instancia_conectada: bool
     * }
     */
    public function obterResumoCampanha(Loja $loja): array
    {
        $config = $loja->config_json['whatsapp'] ?? [];
        $diasInatividade = (int) ($config['winback_dias_inatividade'] ?? 30);
        $tipo = (string) ($config['winback_desconto_tipo'] ?? 'percentual');
        $valor = (int) ($config['winback_desconto_valor'] ?? 10);
        $validadeHoras = (int) ($config['winback_validade_horas'] ?? 48);
        $codigoCupom = strtoupper(trim((string) ($config['winback_cupom_codigo'] ?? "VOLTEI{$valor}")));
        if (empty($codigoCupom)) {
            $codigoCupom = "VOLTEI{$valor}";
        }

        $descontoTexto = $tipo === 'percentual'
            ? "{$valor}% OFF"
            : 'R$ ' . number_format($valor, 2, ',', '.') . ' OFF';

        $totalBase = Cliente::where('loja_id', $loja->id)->where('aceita_marketing', true)->count();
        $clientesElegiveis = $this->obterClientesElegiveis($loja, $diasInatividade);

        // Cria um modelo fictício para renderizar o exemplo da mensagem
        $clienteExemplo = new Cliente([
            'nome' => 'Maria Silva',
            'telefone' => '11999998888',
        ]);

        $cupomExemplo = new Cupom([
            'codigo' => $codigoCupom,
            'tipo' => $tipo,
            'valor' => $tipo === 'percentual' ? $valor : ($valor * 100),
        ]);

        $exemploMsg = $this->formatarMensagem($loja, $clienteExemplo, $cupomExemplo);

        $instancia = $this->evolutionApi->obterNomeInstanciaLoja($loja);
        $statusApi = $this->evolutionApi->obterStatusInstancia($instancia);

        return [
            'success' => true,
            'total_clientes_base' => $totalBase,
            'total_inativos' => $clientesElegiveis->count(),
            'dias_inatividade' => $diasInatividade,
            'cupom_codigo' => $codigoCupom,
            'desconto_texto' => $descontoTexto,
            'validade_horas' => $validadeHoras,
            'exemplo_mensagem' => $exemploMsg,
            'instancia_conectada' => (bool) ($statusApi['connected'] ?? false),
        ];
    }

    /**
     * Garante a existência e validade do cupom promocional para a campanha.
     */
    public function garantirCupomWinBack(Loja $loja): Cupom
    {
        $config = $loja->config_json['whatsapp'] ?? [];
        $tipo = (string) ($config['winback_desconto_tipo'] ?? 'percentual');
        $valor = (int) ($config['winback_desconto_valor'] ?? 10);
        $validadeHoras = (int) ($config['winback_validade_horas'] ?? 48);
        $minimoReais = (float) ($config['winback_minimo_reais'] ?? 30.0);
        $codigo = strtoupper(trim((string) ($config['winback_cupom_codigo'] ?? "VOLTEI{$valor}")));

        if (empty($codigo)) {
            $codigo = "VOLTEI{$valor}";
        }

        /** @var Cupom|null $cupom */
        $cupom = Cupom::where('loja_id', $loja->id)->where('codigo', $codigo)->first();

        $valorFinal = $tipo === 'percentual' ? $valor : (int) round($valor * 100);
        $minimoCentavos = (int) round($minimoReais * 100);
        $dataExpiracao = now()->addHours($validadeHoras);

        if ($cupom === null) {
            $cupom = Cupom::create([
                'loja_id' => $loja->id,
                'codigo' => $codigo,
                'tipo' => in_array($tipo, ['percentual', 'valor'], true) ? $tipo : 'percentual',
                'valor' => $valorFinal,
                'minimo_centavos' => $minimoCentavos,
                'validade' => $dataExpiracao,
                'limite_por_cliente' => 1,
                'ativo' => true,
                'canais' => ['delivery', 'retirada'],
            ]);
        } else {
            // Se já existe, reativa e prorroga a validade para os clientes da campanha
            $cupom->ativo = true;
            $cupom->tipo = in_array($tipo, ['percentual', 'valor'], true) ? $tipo : 'percentual';
            $cupom->valor = $valorFinal;
            $cupom->minimo_centavos = $minimoCentavos;
            if ($cupom->validade === null || $cupom->validade->isPast()) {
                $cupom->validade = $dataExpiracao;
            }
            $cupom->save();
        }

        return $cupom;
    }

    /**
     * Monta o texto personalizado da mensagem para o cliente.
     */
    public function formatarMensagem(Loja $loja, Cliente $cliente, Cupom $cupom): string
    {
        $config = $loja->config_json['whatsapp'] ?? [];
        $templateCustom = !empty($config['winback_mensagem']) ? trim((string) $config['winback_mensagem']) : null;
        $validadeHoras = (int) ($config['winback_validade_horas'] ?? 48);

        $nomeCompleto = trim((string) $cliente->nome);
        $primeiroNome = $nomeCompleto !== '' ? explode(' ', $nomeCompleto)[0] : 'amigo(a)';

        $descontoTexto = $cupom->tipo === 'percentual'
            ? "{$cupom->valor}% OFF"
            : 'R$ ' . number_format($cupom->valor / 100, 2, ',', '.') . ' OFF';

        $baseUrl = config('app.url') ?? 'https://pedelivre.devla.com.br';
        $linkCardapio = rtrim($baseUrl, '/') . "/p/{$loja->slug}?cupom=" . urlencode($cupom->codigo);

        if ($templateCustom !== null && $templateCustom !== '') {
            return str_replace(
                ['{nome}', '{loja}', '{cupom}', '{desconto}', '{validade_horas}', '{link_cardapio}'],
                [$primeiroNome, $loja->nome, $cupom->codigo, $descontoTexto, (string) $validadeHoras, $linkCardapio],
                $templateCustom
            );
        }

        return "Oi *{$primeiroNome}*! Tudo bem? Faz um tempinho que a gente não se vê por aqui no *{$loja->nome}* e bateu aquela saudade! 😋🍕\n\n"
            . "Preparamos um presente especial e exclusivo para você hoje:\n"
            . "🎁 *{$descontoTexto} DE DESCONTO* no seu próximo pedido!\n\n"
            . "Utilize o cupom: *{$cupom->codigo}*\n"
            . "⏰ *Atenção:* Válido somente pelas próximas *{$validadeHoras} horas*!\n\n"
            . "👉 *Pedir com 1-Clique (cupom já aplicado):*\n"
            . "{$linkCardapio}\n\n"
            . "_Se não desejar mais receber ofertas e novidades, responda PARAR._";
    }

    /**
     * Executa a campanha de Win-Back para uma loja específica.
     * Retorna array com os resultados (total_elegiveis, enviados, erros, cupom).
     *
     * @return array{
     *     success: bool,
     *     total_elegiveis: int,
     *     enviados: int,
     *     erros: int,
     *     cupom: string,
     *     motivo?: string
     * }
     */
    public function executarCampanhaLoja(Loja $loja, bool $ignorarCooldown = false): array
    {
        $config = $loja->config_json['whatsapp'] ?? [];
        $instancia = $this->evolutionApi->obterNomeInstanciaLoja($loja);
        $statusApi = $this->evolutionApi->obterStatusInstancia($instancia);

        if (!$statusApi['connected']) {
            Log::warning("[WinBack] Loja {$loja->id} ignorada: WhatsApp não conectado.");
            return [
                'success' => false,
                'total_elegiveis' => 0,
                'enviados' => 0,
                'erros' => 0,
                'cupom' => '',
                'motivo' => 'whatsapp_desconectado',
            ];
        }

        $clientes = $this->obterClientesElegiveis($loja);
        if ($clientes->isEmpty()) {
            return [
                'success' => true,
                'total_elegiveis' => 0,
                'enviados' => 0,
                'erros' => 0,
                'cupom' => '',
                'motivo' => 'sem_clientes_elegiveis',
            ];
        }

        $cupom = $this->garantirCupomWinBack($loja);
        $enviados = 0;
        $erros = 0;
        $cooldownDias = (int) ($config['winback_cooldown_dias'] ?? 30);

        foreach ($clientes as $cliente) {
            $telefone = (string) $cliente->telefone;
            if (empty($telefone)) {
                continue;
            }

            $texto = $this->formatarMensagem($loja, $cliente, $cupom);

            try {
                $sucesso = $this->evolutionApi->enviarMensagem($instancia, $telefone, $texto);

                if ($sucesso) {
                    $enviados++;

                    // Marca no cache
                    $cacheKey = "winback_enviado_cliente_{$loja->id}_{$cliente->id}";
                    Cache::put($cacheKey, true, now()->addDays($cooldownDias));

                    // Cria ou atualiza BotConversa para ter rastreio no chat do painel
                    /** @var BotConversa $conversa */
                    $conversa = BotConversa::firstOrCreate(
                        ['loja_id' => $loja->id, 'telefone_bidx' => $cliente->telefone_bidx],
                        [
                            'telefone' => $telefone,
                            'estado' => 'ia_ativa',
                            'contexto_json' => ['inicio' => now()->toIso8601String()],
                        ]
                    );

                    $ctx = $conversa->contexto_json ?? [];
                    $ctx['ultimo_winback_em'] = now()->toIso8601String();
                    $ctx['ultimo_cupom_winback'] = $cupom->codigo;
                    $conversa->contexto_json = $ctx;
                    $conversa->save();

                    // Registra a mensagem de saída para aparecer no Live Chat
                    BotMensagem::create([
                        'loja_id' => $loja->id,
                        'conversa_id' => $conversa->id,
                        'direcao' => 'saida',
                        'mensagem' => $texto,
                    ]);

                    // Rate Limiter Anti-Ban com Delay Humanizado (5 a 10 segundos)
                    if ($enviados < $clientes->count()) {
                        sleep(random_int(5, 10));
                    }
                } else {
                    $erros++;
                }
            } catch (\Throwable $e) {
                Log::error("[WinBack] Erro ao enviar mensagem para cliente {$cliente->id}: " . $e->getMessage());
                $erros++;
            }
        }

        return [
            'success' => true,
            'total_elegiveis' => $clientes->count(),
            'enviados' => $enviados,
            'erros' => $erros,
            'cupom' => $cupom->codigo,
        ];
    }
}
