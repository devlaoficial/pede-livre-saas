<?php

declare(strict_types=1);

namespace App\Domain\Bot\Services;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Bot\Models\BotConversa;
use App\Domain\Bot\Models\BotMensagem;
use App\Domain\Clientes\Models\Cliente;
use App\Domain\Entregas\Models\Entregador;
use App\Domain\Pedidos\Models\Pedido;
use App\Domain\Pedidos\Models\PedidoEvento;
use App\Support\Security\BlindIndexService;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class EvolutionApiService
{
    private string $baseUrl;
    private string $apiKey;
    private readonly BlindIndexService $blindIndex;

    public function __construct(?BlindIndexService $blindIndex = null)
    {
        $this->baseUrl = rtrim((string) config('services.evolution.url', 'http://127.0.0.1:8085'), '/');
        $this->apiKey = (string) (config('services.evolution.api_key') ?: 'devla_evolution_secret_2026');
        $this->blindIndex = $blindIndex ?? app(BlindIndexService::class);
    }

    /**
     * Resolve o nome da instância padrão para uma loja.
     */
    public function obterNomeInstanciaLoja(Loja $loja): string
    {
        return 'pedelivre-loja-' . $loja->id;
    }

    /**
     * Consulta o status da conexão da instância (compatível com Gateway Node e Evolution API v1/v2).
     *
     * @return array{instance: string, state: string, connected: bool, phone: ?string, qrBase64: ?string}
     */
    public function obterStatusInstancia(string $instancia): array
    {
        try {
            $response = Http::withHeaders(['apikey' => $this->apiKey])
                ->connectTimeout(2)
                ->timeout(4)
                ->get("{$this->baseUrl}/instance/status/{$instancia}");

            if (!$response->successful() && in_array($response->status(), [400, 404, 405], true)) {
                $response = Http::withHeaders(['apikey' => $this->apiKey])
                    ->connectTimeout(2)
                    ->timeout(4)
                    ->get("{$this->baseUrl}/instance/connectionState/{$instancia}");
            }

            if ($response->successful()) {
                $dados = (array) $response->json();
                $state = (string) ($dados['state'] ?? ($dados['instance']['state'] ?? ($dados['instance']['status'] ?? 'disconnected')));
                $connected = (bool) ($dados['connected'] ?? in_array(strtolower($state), ['open', 'connected'], true));
                $rawPhone = $dados['phone'] ?? ($dados['instance']['owner'] ?? ($dados['instance']['wuid'] ?? null));
                $phone = is_string($rawPhone) && $rawPhone !== '' ? preg_replace('/@.*$/', '', $rawPhone) : null;
                $qrBase64 = $dados['qrBase64'] ?? ($dados['qrcode']['base64'] ?? ($dados['base64'] ?? null));

                return [
                    'instance' => (string) ($dados['instance']['instanceName'] ?? (is_string($dados['instance'] ?? null) ? $dados['instance'] : $instancia)),
                    'state' => $connected ? 'open' : $state,
                    'connected' => $connected,
                    'phone' => $phone,
                    'qrBase64' => is_string($qrBase64) && $qrBase64 !== '' ? $qrBase64 : null,
                ];
            }
        } catch (Throwable $e) {
            Log::warning("[EvolutionApi] Falha ao consultar status da instância {$instancia}: " . $e->getMessage());
        }

        return [
            'instance' => $instancia,
            'state' => 'disconnected',
            'connected' => false,
            'phone' => null,
            'qrBase64' => null,
        ];
    }

    /**
     * Inicia a sessão de pareamento, garante webhook configurado e obtém o QR Code em Base64.
     *
     * @return array{instance: string, state: string, connected: bool, phone: ?string, qrBase64: ?string}
     */
    public function conectarInstancia(string $instancia): array
    {
        try {
            $response = Http::withHeaders(['apikey' => $this->apiKey])
                ->connectTimeout(3)
                ->timeout(10)
                ->post("{$this->baseUrl}/instance/connect/{$instancia}");

            // Fallback automático para Evolution API nativa v1/v2 (GET /instance/connect/{instancia} ou POST /instance/create)
            if (!$response->successful() && in_array($response->status(), [400, 404, 405], true)) {
                $response = Http::withHeaders(['apikey' => $this->apiKey])
                    ->connectTimeout(3)
                    ->timeout(10)
                    ->get("{$this->baseUrl}/instance/connect/{$instancia}");

                if (!$response->successful()) {
                    $webhookUrl = url('/api/webhooks/whatsapp/evolution');
                    $response = Http::withHeaders(['apikey' => $this->apiKey])
                        ->connectTimeout(3)
                        ->timeout(10)
                        ->post("{$this->baseUrl}/instance/create", [
                            'instanceName' => $instancia,
                            'qrcode' => true,
                            'integration' => 'WHATSAPP-BAILEYS',
                            'webhook' => $webhookUrl,
                            'webhook_by_events' => false,
                            'events' => ['MESSAGES_UPSERT', 'CONNECTION_UPDATE', 'QRCODE_UPDATED'],
                        ]);
                }
            }

            if ($response->successful()) {
                $dados = (array) $response->json();
                $qrBase64 = $dados['qrBase64']
                    ?? ($dados['qrcode']['base64'] ?? null)
                    ?? ($dados['base64'] ?? null);

                if (is_string($qrBase64) && $qrBase64 !== '' && !str_starts_with($qrBase64, 'data:image')) {
                    $qrBase64 = 'data:image/png;base64,' . $qrBase64;
                }

                $state = (string) ($dados['state'] ?? ($dados['instance']['state'] ?? 'connecting'));
                $connected = (bool) ($dados['connected'] ?? in_array(strtolower($state), ['open', 'connected'], true));

                return [
                    'instance' => $instancia,
                    'state' => $state,
                    'connected' => $connected,
                    'phone' => !empty($dados['phone']) ? (string) $dados['phone'] : null,
                    'qrBase64' => is_string($qrBase64) ? $qrBase64 : null,
                ];
            }
        } catch (Throwable $e) {
            Log::error("[EvolutionApi] Falha ao iniciar conexão da instância {$instancia}: " . $e->getMessage());
        }

        return [
            'instance' => $instancia,
            'state' => 'error',
            'connected' => false,
            'phone' => null,
            'qrBase64' => null,
        ];
    }

    /**
     * Desconecta e remove a sessão da instância.
     */
    public function desconectarInstancia(string $instancia): bool
    {
        try {
            $response = Http::withHeaders(['apikey' => $this->apiKey])
                ->connectTimeout(2)
                ->timeout(6)
                ->post("{$this->baseUrl}/instance/disconnect/{$instancia}");

            if (!$response->successful() && in_array($response->status(), [400, 404, 405], true)) {
                $response = Http::withHeaders(['apikey' => $this->apiKey])
                    ->connectTimeout(2)
                    ->timeout(6)
                    ->delete("{$this->baseUrl}/instance/logout/{$instancia}");
            }

            return $response->successful();
        } catch (Throwable $e) {
            Log::warning("[EvolutionApi] Falha ao desconectar instância {$instancia}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envia mensagem de texto pelo WhatsApp com compatibilidade total (Gateway Node, Evolution v1 e Evolution v2)
     * e resolução automática do 9º dígito brasileiro (13 e 12 dígitos).
     */
    public function enviarMensagem(string $instancia, string $telefone, string $texto, ?int $lojaId = null): bool
    {
        $textoLimpo = trim($texto);
        $telefonePrincipal = $this->blindIndex->normalizePhone($telefone);

        if ($telefonePrincipal === '' || $telefonePrincipal === '5500000000000' || $textoLimpo === '') {
            return false;
        }

        // Constrói lista ordenada de números candidatos (priorizando conversa ativa do WhatsApp se existir, depois 13 e 12 dígitos com DDI 55)
        $numerosParaTentar = [];

        if ($lojaId !== null) {
            try {
                $candidatosBidx = $this->blindIndex->generatePhoneCandidates($telefone);
                $conversaExistente = BotConversa::withoutGlobalScopes()
                    ->where('loja_id', $lojaId)
                    ->whereIn('telefone_bidx', $candidatosBidx)
                    ->orderByDesc('updated_at')
                    ->first();

                if ($conversaExistente !== null && !empty($conversaExistente->telefone)) {
                    $telConv = $this->blindIndex->normalizePhone((string) $conversaExistente->telefone);
                    if (strlen($telConv) >= 12) {
                        $numerosParaTentar[] = $telConv;
                    }
                }
            } catch (Throwable) {}
        }

        $numerosParaTentar[] = $telefonePrincipal;
        foreach ($this->blindIndex->normalizePhoneVariants($telefone) as $variante) {
            if (str_starts_with($variante, '55') && strlen($variante) >= 12) {
                $numerosParaTentar[] = $variante;
            }
        }
        $numerosParaTentar = array_values(array_unique($numerosParaTentar));

        foreach ($numerosParaTentar as $numero) {
            if ($this->dispararRequisicaoSendText($instancia, $numero, $textoLimpo)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Executa o POST /message/sendText/{instancia} com fallback automático entre os formatos Evolution v2/Gateway e Evolution v1.
     */
    private function dispararRequisicaoSendText(string $instancia, string $numero, string $texto): bool
    {
        $url = "{$this->baseUrl}/message/sendText/{$instancia}";
        $headers = [
            'Content-Type' => 'application/json',
            'apikey' => $this->apiKey,
        ];

        try {
            // 1. Formato Híbrido (aceito pelo Gateway Node, Evolution v2 e maioria das builds v1)
            $response = Http::withHeaders($headers)
                ->connectTimeout(2)
                ->timeout(4)
                ->post($url, [
                    'number' => $numero,
                    'text' => $texto,
                    'textMessage' => [
                        'text' => $texto,
                    ],
                    'options' => [
                        'delay' => 250,
                        'presence' => 'composing',
                        'linkPreview' => false,
                    ],
                ]);

            if ($response->successful()) {
                Log::info("[EvolutionApi] Mensagem enviada via [{$instancia}] para [{$numero}]");
                return true;
            }

            // 2. Se retornou 400 ou 422 por validação estrita de schema, tenta formato puro Evolution v2 e depois puro Evolution v1
            if (in_array($response->status(), [400, 422], true)) {
                $respV2 = Http::withHeaders($headers)
                    ->connectTimeout(2)
                    ->timeout(4)
                    ->post($url, [
                        'number' => $numero,
                        'text' => $texto,
                    ]);

                if ($respV2->successful()) {
                    Log::info("[EvolutionApi] Mensagem enviada (v2) via [{$instancia}] para [{$numero}]");
                    return true;
                }

                $respV1 = Http::withHeaders($headers)
                    ->connectTimeout(2)
                    ->timeout(4)
                    ->post($url, [
                        'number' => $numero,
                        'options' => [
                            'delay' => 250,
                            'presence' => 'composing',
                        ],
                        'textMessage' => [
                            'text' => $texto,
                        ],
                    ]);

                if ($respV1->successful()) {
                    Log::info("[EvolutionApi] Mensagem enviada (v1) via [{$instancia}] para [{$numero}]");
                    return true;
                }
            }

            Log::warning("[EvolutionApi] Falha HTTP {$response->status()} ao enviar mensagem para [{$numero}]: " . $response->body());
        } catch (Throwable $e) {
            Log::error("[EvolutionApi] Exceção ao conectar em [{$url}] para [{$numero}]: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Localiza ou cria a conversa do cliente no histórico do bot e registra mensagens automáticas.
     */
    public function registrarMensagemAutomaticaNaConversa(
        Loja $loja,
        string $telefoneCliente,
        string $mensagem,
        ?Pedido $pedido = null,
        array $contextoExtra = []
    ): BotConversa {
        $telNormalizado = $this->blindIndex->normalizePhone($telefoneCliente);
        $telBidx = $this->blindIndex->generateForPhone($telNormalizado);
        $candidatosBidx = $this->blindIndex->generatePhoneCandidates($telefoneCliente);

        /** @var BotConversa|null $conversa */
        $conversa = BotConversa::withoutGlobalScopes()
            ->where('loja_id', $loja->id)
            ->whereIn('telefone_bidx', $candidatosBidx)
            ->orderByDesc('updated_at')
            ->first();

        if ($conversa === null) {
            $conversa = BotConversa::withoutGlobalScopes()->create([
                'loja_id' => $loja->id,
                'telefone_bidx' => $telBidx,
                'telefone' => $telNormalizado,
                'estado' => 'ia_ativa',
                'contexto_json' => ['inicio' => now()->toIso8601String()],
            ]);
        }

        $ctx = $conversa->contexto_json ?? [];
        if ($pedido !== null) {
            $ctx['ultimo_pedido_id'] = $pedido->id;
            $ctx['ultimo_codigo_pedido'] = $pedido->codigo_publico;
        }
        if (!empty($contextoExtra)) {
            $ctx = array_merge($ctx, $contextoExtra);
        }

        $conversa->contexto_json = $ctx;
        $conversa->updated_at = now();
        $conversa->save();

        BotMensagem::withoutGlobalScopes()->create([
            'loja_id' => $loja->id,
            'conversa_id' => $conversa->id,
            'direcao' => 'saida',
            'mensagem' => $mensagem,
        ]);

        return $conversa;
    }

    /**
     * Envia notificação automática de criação ou transição de status para o cliente e/ou motoboy.
     */
    public function notificarMudancaStatus(Pedido $pedido, string $novoStatus): void
    {
        try {
            /** @var Loja|null $loja */
            $loja = $pedido->relationLoaded('loja') && $pedido->loja !== null
                ? $pedido->loja
                : Loja::withoutGlobalScopes()->find($pedido->loja_id);

            if ($loja === null) {
                return;
            }

            $instancia = $this->obterNomeInstanciaLoja($loja);
            $configWpp = $loja->config_json['whatsapp'] ?? [];
            $notificarCliente = (bool) ($configWpp['notificar_pedidos'] ?? true);
            $notificarMotoboy = (bool) ($configWpp['notificar_motoboy'] ?? true);

            // Busca o cliente de forma imune ao GlobalScope (garante que nunca venha null mesmo fora do contexto HTTP)
            /** @var Cliente|null $cliente */
            $cliente = ($pedido->relationLoaded('cliente') && $pedido->cliente !== null)
                ? $pedido->cliente
                : ($pedido->cliente_id ? Cliente::withoutGlobalScopes()->find($pedido->cliente_id) : null);

            $telefoneCliente = $cliente?->telefone ? trim((string) $cliente->telefone) : null;
            $nomeCliente = $cliente?->nome ?: 'Cliente';
            $primeiroNome = explode(' ', trim($nomeCliente))[0] ?: 'Cliente';
            $codigo = $pedido->codigo_publico;
            $lojaNome = $loja->nome;
            $tempoMedio = (int) ($loja->config_json['tempo_medio_preparo_min'] ?? 35);

            // Link de rastreamento em tempo real com assinatura criptográfica
            $trackingSignature = hash_hmac('sha256', (string) $pedido->tracking_token_hash, (string) config('app.key'));
            $trackingUrl = url("/p/{$loja->slug}/pedidos/{$codigo}?t={$trackingSignature}");

            // 1. Mensagens em Tempo Real para o CLIENTE
            if ($notificarCliente && !empty($telefoneCliente) && $telefoneCliente !== '5500000000000') {
                $mensagemCliente = null;
                $contextoExtra = [];

                switch ($novoStatus) {
                    case 'novo':
                        $pedido->loadMissing(['itens', 'pagamento']);
                        $resumoItens = $pedido->itens->map(fn ($it) => "• {$it->qtd}x {$it->nome_snapshot}")->join("\n");
                        $totalFmt = number_format($pedido->total / 100, 2, ',', '.');

                        if ($pedido->forma_pagamento === 'pix' && $pedido->pagamento_status !== 'aprovado') {
                            $copiaCola = (string) ($pedido->pagamento?->pix_copia_cola ?? '');
                            $blocoPix = $copiaCola !== ''
                                ? "\n⚡ *PIX Copia e Cola (R$ {$totalFmt}):*\n```{$copiaCola}```\n"
                                : "\n💵 *Total:* R$ {$totalFmt} (PIX)\n";

                            $mensagemCliente = "Olá, *{$primeiroNome}*! Recebemos o seu pedido *#{$codigo}* no *{$lojaNome}*! 🎉\n\n"
                                . "📋 *Resumo do Pedido:*\n{$resumoItens}\n"
                                . "{$blocoPix}\n"
                                . "📍 *Acompanhe o status em tempo real pelo link:*\n👉 {$trackingUrl}\n\n"
                                . "💡 _A qualquer momento você pode digitar *STATUS* aqui no WhatsApp para consultar seu pedido!_";
                        } else {
                            $formaFmt = strtoupper(str_replace('_', ' ', (string) $pedido->forma_pagamento));
                            $mensagemCliente = "Olá, *{$primeiroNome}*! Recebemos o seu pedido *#{$codigo}* no *{$lojaNome}*! 🎉\n\n"
                                . "📋 *Resumo do Pedido:*\n{$resumoItens}\n\n"
                                . "💵 *Total:* R$ {$totalFmt} ({$formaFmt})\n"
                                . "⏱️ *Previsão média:* {$tempoMedio} a " . ($tempoMedio + 15) . " min\n\n"
                                . "📍 *Acompanhe cada etapa em tempo real:*\n👉 {$trackingUrl}\n\n"
                                . "💡 _Digite *STATUS* aqui no WhatsApp quando quiser consultar o andamento!_";
                        }
                        break;

                    case 'aceito':
                    case 'em_preparo':
                        $pedido->loadMissing(['itens']);
                        $resumoItens = $pedido->itens->map(fn ($it) => "• {$it->qtd}x {$it->nome_snapshot}")->join("\n");
                        $mensagemCliente = "✅ *Pedido #{$codigo} Aceito e em Preparo!* 👨‍🍳🔥\n\n"
                            . "Oi, *{$primeiroNome}*! O *{$lojaNome}* acabou de confirmar o seu pedido e nossa cozinha já está preparando tudo com muito capricho!\n\n"
                            . ($resumoItens !== '' ? "📋 *Itens em preparo:*\n{$resumoItens}\n\n" : '')
                            . "⏱️ *Previsão média:* {$tempoMedio} a " . ($tempoMedio + 15) . " min\n"
                            . "📍 *Acompanhe ao vivo:*\n👉 {$trackingUrl}";
                        break;

                    case 'pronto':
                        if ($pedido->canal === 'retirada' || $pedido->canal === 'balcao') {
                            $mensagemCliente = "Oba, *{$primeiroNome}*! 🎉\n\n"
                                . "Seu pedido *#{$codigo}* está *PRONTINHO* esperando por você no balcão do *{$lojaNome}*! 🛍️\n\n"
                                . "Pode vir retirar! Quando pegar seu pedido, você também pode responder *JÁ RETIREI* aqui no WhatsApp.";
                            $contextoExtra['aguardando_confirmacao_entrega'] = true;
                            $contextoExtra['confirmacao_pedido_id'] = $pedido->id;
                        } else {
                            $mensagemCliente = "Oba, *{$primeiroNome}*! 🎉\n\n"
                                . "Seu pedido *#{$codigo}* acabou de ficar pronto na cozinha e já foi embalado na bolsa térmica! 🛍️\n\n"
                                . "Nosso entregador já está sendo acionado para levar até você!\n"
                                . "👉 *Acompanhe:* {$trackingUrl}";
                        }
                        break;

                    case 'saiu_entrega':
                        $entregador = $pedido->entregador ?? ($pedido->entregador_id ? Entregador::withoutGlobalScopes()->find($pedido->entregador_id) : null);
                        $nomeEntregador = $entregador?->nome ?? 'Nosso entregador';
                        $mensagemCliente = "🛵💨 *SEU PEDIDO SAIU PARA ENTREGA!* 🛵💨\n\n"
                            . "Oi, *{$primeiroNome}*! O entregador *{$nomeEntregador}* acabou de sair com o seu pedido *#{$codigo}* do *{$lojaNome}*!\n\n"
                            . "Fique atento(a) ao interfone ou campainha.\n"
                            . "📍 *Rastreio ao vivo:* {$trackingUrl}\n\n"
                            . "✅ _Assim que receber, você pode responder *JÁ RECEBI* aqui no WhatsApp para confirmar sua entrega!_";
                        $contextoExtra['aguardando_confirmacao_entrega'] = true;
                        $contextoExtra['confirmacao_pedido_id'] = $pedido->id;
                        break;

                    case 'entregue':
                    case 'retirado':
                    case 'servido':
                        $mensagemCliente = "✨ *Pedido #{$codigo} Entregue e Concluído!* ✨\n\n"
                            . "Esperamos que você ame a sua refeição, *{$primeiroNome}*! Feito com muito carinho por toda a equipe do *{$lojaNome}*! 🍔❤️\n\n"
                            . "Agradecemos pela preferência e bom apetite!";
                        $contextoExtra['aguardando_confirmacao_entrega'] = false;
                        break;

                    case 'cancelado':
                        $motivo = $pedido->cancelado_motivo ?: 'Imprevisto operacional na loja';
                        $mensagemCliente = "Olá, *{$primeiroNome}*.\n\n"
                            . "Informamos que o seu pedido *#{$codigo}* precisou ser cancelado pelo estabelecimento.\n"
                            . "⚠️ *Motivo:* {$motivo}\n\n"
                            . "Se realizou pagamento via PIX, nosso time já está à disposição para estorno imediato. Caso precise, basta nos responder aqui.";
                        $contextoExtra['aguardando_confirmacao_entrega'] = false;
                        break;
                }

                if ($mensagemCliente !== null) {
                    $this->registrarMensagemAutomaticaNaConversa($loja, $telefoneCliente, $mensagemCliente, $pedido, $contextoExtra);
                    $this->enviarMensagem($instancia, $telefoneCliente, $mensagemCliente, $loja->id);
                }
            }

            // 2. Notificação para o MOTOBOY quando o pedido fica PRONTO para entrega
            if ($notificarMotoboy && $pedido->canal === 'delivery' && $novoStatus === 'pronto') {
                $this->notificarMotoboyPedidoPronto($pedido, $instancia, $loja);
            }
        } catch (Throwable $e) {
            Log::error("[EvolutionApi] Falha ao notificar mudança de status do pedido #{$pedido->id}: " . $e->getMessage());
        }
    }

    /**
     * Envia pergunta proativa ao cliente quando o lojista esquece de dar baixa na entrega no painel.
     */
    public function solicitarConfirmacaoEntregaCliente(Pedido $pedido): bool
    {
        try {
            /** @var Loja|null $loja */
            $loja = $pedido->loja ?? Loja::withoutGlobalScopes()->find($pedido->loja_id);
            if ($loja === null) {
                return false;
            }

            /** @var Cliente|null $cliente */
            $cliente = $pedido->cliente ?? ($pedido->cliente_id ? Cliente::withoutGlobalScopes()->find($pedido->cliente_id) : null);
            $telCliente = $cliente?->telefone ? (string) $cliente->telefone : null;
            if (empty($telCliente) || $telCliente === '5500000000000') {
                return false;
            }

            $instancia = $this->obterNomeInstanciaLoja($loja);
            $primeiroNome = explode(' ', trim($cliente->nome ?? 'Cliente'))[0] ?: 'Cliente';
            $codigo = $pedido->codigo_publico;

            $msg = "Oi, *{$primeiroNome}*! Tudo bem? 😊\n\n"
                . "Passando rapidinho para confirmar sobre o seu pedido *#{$codigo}* no *{$loja->nome}*: ele já foi entregue tudo certinho por aí?\n\n"
                . "✅ Responda *JÁ RECEBI* (ou *SIM*) se o pedido já chegou!\n"
                . "⏳ Responda *AINDA NÃO* caso ainda esteja aguardando para verificarmos imediatamente com nossa equipe.";

            $this->registrarMensagemAutomaticaNaConversa($loja, $telCliente, $msg, $pedido, [
                'aguardando_confirmacao_entrega' => true,
                'confirmacao_pedido_id' => $pedido->id,
                'confirmacao_enviada_em' => now()->toIso8601String(),
            ]);

            return $this->enviarMensagem($instancia, $telCliente, $msg, $loja->id);
        } catch (Throwable $e) {
            Log::error("[EvolutionApi] Falha ao solicitar confirmação de entrega: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envia alerta detalhado para o motoboy com link de rota no Google Maps.
     */
    public function notificarMotoboyPedidoPronto(Pedido $pedido, string $instancia, Loja $loja): void
    {
        /** @var Entregador|null $entregador */
        $entregador = $pedido->entregador ?? ($pedido->entregador_id ? Entregador::withoutGlobalScopes()->find($pedido->entregador_id) : null);

        if ($entregador !== null && !empty($entregador->telefone)) {
            $this->enviarAlertaEntregaParaMotoboy($pedido, $entregador, $instancia, $loja);
            return;
        }

        $entregadoresAtivos = Entregador::withoutGlobalScopes()
            ->where('loja_id', $loja->id)
            ->where('ativo', true)
            ->get();

        foreach ($entregadoresAtivos as $motoboy) {
            if (!empty($motoboy->telefone)) {
                $this->enviarAlertaEntregaParaMotoboy($pedido, $motoboy, $instancia, $loja);
            }
        }
    }

    /**
     * Dispara mensagem com todos os dados da rota para o WhatsApp do Motoboy.
     */
    private function enviarAlertaEntregaParaMotoboy(Pedido $pedido, Entregador $motoboy, string $instancia, Loja $loja): void
    {
        $telMotoboy = (string) $motoboy->telefone;
        $codigo = $pedido->codigo_publico;
        $cliente = $pedido->cliente ?? ($pedido->cliente_id ? Cliente::withoutGlobalScopes()->find($pedido->cliente_id) : null);
        $nomeCliente = $cliente?->nome ?? 'Cliente';
        $telCliente = $cliente?->telefone ? (string) $cliente->telefone : 'Não informado';

        $end = $pedido->endereco_json ?? [];
        $rua = $end['rua'] ?? '';
        $num = $end['numero'] ?? 'S/N';
        $bairro = $end['bairro'] ?? '';
        $comp = !empty($end['complemento']) ? " ({$end['complemento']})" : '';
        $cidade = $end['cidade'] ?? $loja->cidade ?? '';

        $enderecoTexto = "{$rua}, {$num}{$comp} - {$bairro}, {$cidade}";
        $linkMaps = "https://www.google.com/maps/search/?api=1&query=" . urlencode("{$rua}, {$num} - {$bairro}, {$cidade}");

        $taxaCentavos = $pedido->taxa_entrega;
        $taxaFormatada = number_format($taxaCentavos / 100, 2, ',', '.');
        $totalPedido = number_format($pedido->total / 100, 2, ',', '.');
        $formaPagamento = strtoupper(str_replace('_', ' ', (string) $pedido->forma_pagamento));

        $msgMotoboy = "🛵 *NOVO PEDIDO PRONTO PARA ENTREGA!* 🛵\n\n"
            . "📦 *Pedido #{$codigo}* ({$loja->nome})\n"
            . "👤 *Cliente:* {$nomeCliente}\n"
            . "📞 *Tel Cliente:* {$telCliente}\n"
            . "📍 *Endereço:* {$enderecoTexto}\n"
            . "💵 *Valor do Pedido:* R$ {$totalPedido} ({$formaPagamento})\n"
            . "💰 *Taxa de Entrega:* R$ {$taxaFormatada}\n\n"
            . "🗺️ *Abrir GPS / Google Maps:*\n👉 {$linkMaps}\n\n"
            . "✅ _Ao entregar, responda: *ENTREGUE {$codigo}*_";

        $this->enviarMensagem($instancia, $telMotoboy, $msgMotoboy, $loja->id);
    }

    /**
     * Envia alerta gentil de atraso quando detectada alta demanda na cozinha.
     */
    public function notificarAtraso(Pedido $pedido, int $minutosDecorridos): bool
    {
        try {
            /** @var Loja|null $loja */
            $loja = $pedido->loja ?? Loja::withoutGlobalScopes()->find($pedido->loja_id);
            if ($loja === null) {
                return false;
            }

            $instancia = $this->obterNomeInstanciaLoja($loja);
            $cliente = $pedido->cliente ?? ($pedido->cliente_id ? Cliente::withoutGlobalScopes()->find($pedido->cliente_id) : null);
            $telCliente = $cliente?->telefone ? (string) $cliente->telefone : null;
            if (empty($telCliente) || $telCliente === '5500000000000') {
                return false;
            }

            $primeiroNome = explode(' ', trim($cliente->nome ?? 'Cliente'))[0] ?: 'Cliente';
            $codigo = $pedido->codigo_publico;
            $trackingSignature = hash_hmac('sha256', (string) $pedido->tracking_token_hash, (string) config('app.key'));
            $trackingUrl = url("/p/{$loja->slug}/pedidos/{$codigo}?t={$trackingSignature}");

            if ($pedido->canal === 'retirada' || $pedido->canal === 'balcao') {
                $msgProntidao = "Seu pedido *#{$codigo}* já é o **próximo da fila para ser finalizado**! 🍳🔥 Já estamos nos preparativos finais para você poder retirar quentinho no balcão.";
            } else {
                $msgProntidao = "Seu pedido *#{$codigo}* já é o **próximo da fila para ser embalado**! 🍳🔥 Nosso entregador já está de prontidão aguardando a saída para levar até você.";
            }

            $msg = "Oi, *{$primeiroNome}*! Passando aqui rapidinho para te dar uma satisfação! 🙏\n\n"
                . "Hoje nossa cozinha está com uma procura super alta e todos os pedidos estão sendo feitos com muito carinho para saírem com a máxima qualidade.\n\n"
                . "{$msgProntidao}\n\n"
                . "📍 *Acompanhe em tempo real:* {$trackingUrl}\n"
                . "_(Se o seu pedido já tiver sido entregue, basta responder *JÁ RECEBI* aqui!)_ ❤️";

            $this->registrarMensagemAutomaticaNaConversa($loja, $telCliente, $msg, $pedido, [
                'aguardando_confirmacao_entrega' => true,
                'confirmacao_pedido_id' => $pedido->id,
            ]);

            return $this->enviarMensagem($instancia, $telCliente, $msg, $loja->id);
        } catch (Throwable $e) {
            Log::error("[EvolutionApi] Falha ao notificar atraso: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envia pesquisa de satisfação NPS e convite para avaliação 5 estrelas.
     */
    public function notificarSolicitacaoAvaliacao(Pedido $pedido): bool
    {
        try {
            $loja = $pedido->loja ?? Loja::withoutGlobalScopes()->find($pedido->loja_id);
            if ($loja === null) {
                return false;
            }

            $instancia = $this->obterNomeInstanciaLoja($loja);
            $cliente = $pedido->cliente ?? ($pedido->cliente_id ? Cliente::withoutGlobalScopes()->find($pedido->cliente_id) : null);
            $telCliente = $cliente?->telefone ? (string) $cliente->telefone : null;
            if (empty($telCliente) || $telCliente === '5500000000000' || $cliente->aceita_marketing === false) {
                return false;
            }

            $primeiroNome = explode(' ', trim($cliente->nome ?: 'amigo(a)'))[0];
            $codigo = $pedido->codigo_publico;

            $msg = "Oi, *{$primeiroNome}*! Esperamos que sua refeição do *{$loja->nome}* tenha sido uma delícia! 😋✨\n\n"
                . "De 1 a 5 estrelas, que nota você daria para o seu pedido *#{$codigo}* hoje?\n\n"
                . "⭐ 5 - Excelente\n"
                . "⭐ 4 - Muito bom\n"
                . "⭐ 3 - Regular\n"
                . "⭐ 2 - Ruim\n"
                . "⭐ 1 - Péssimo\n\n"
                . "_(Basta responder aqui com o número da sua nota de 1 a 5)_";

            $this->registrarMensagemAutomaticaNaConversa($loja, $telCliente, $msg, $pedido, [
                'aguardando_avaliacao' => true,
                'aguardando_confirmacao_entrega' => false,
                'avaliacao_pedido_id' => $pedido->id,
                'avaliacao_enviada_em' => now()->toIso8601String(),
            ]);

            $enviado = $this->enviarMensagem($instancia, $telCliente, $msg, $loja->id);
            if (!$enviado) {
                return false;
            }

            PedidoEvento::create([
                'pedido_id' => $pedido->id,
                'status_de' => $pedido->status,
                'status_para' => 'solicitacao_avaliacao',
                'observacao' => 'Pesquisa de satisfação NPS enviada via WhatsApp',
            ]);

            return true;
        } catch (Throwable $e) {
            Log::error("[EvolutionApi] Falha ao enviar solicitação de avaliação: " . $e->getMessage());
            return false;
        }
    }
}
