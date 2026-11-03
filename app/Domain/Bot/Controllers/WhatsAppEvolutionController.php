<?php

declare(strict_types=1);

namespace App\Domain\Bot\Controllers;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Bot\Models\BotConversa;
use App\Domain\Bot\Models\BotMensagem;
use App\Domain\Bot\Services\AiChatService;
use App\Domain\Bot\Services\EvolutionApiService;
use App\Domain\Bot\Services\EvolutionBotService;
use App\Domain\Bot\Services\WinBackService;
use App\Domain\Clientes\Models\Cliente;
use App\Domain\Fidelidade\Models\Avaliacao;
use App\Support\Security\BlindIndexService;
use App\Support\Tenancy\LojaContext;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class WhatsAppEvolutionController
{
    public function __construct(
        private readonly EvolutionApiService $evolutionApi,
        private readonly EvolutionBotService $botService,
        private readonly AiChatService $aiChatService,
        private readonly BlindIndexService $blindIndex,
        private readonly LojaContext $lojaContext
    ) {}

    /**
     * GET /api/painel/whatsapp/status
     */
    public function status(Request $request): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        /** @var Loja|null $loja */
        $loja = $usuario->loja;

        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NAO_ENCONTRADA'], Response::HTTP_NOT_FOUND);
        }

        $instancia = $this->evolutionApi->obterNomeInstanciaLoja($loja);
        $statusApi = $this->evolutionApi->obterStatusInstancia($instancia);
        $configWpp = $loja->config_json['whatsapp'] ?? [];

        return response()->json([
            'instance' => $instancia,
            'connected' => $statusApi['connected'],
            'state' => $statusApi['state'],
            'phone' => $statusApi['phone'],
            'qrBase64' => $statusApi['qrBase64'],
            'config' => [
                'notificar_pedidos' => (bool) ($configWpp['notificar_pedidos'] ?? true),
                'notificar_motoboy' => (bool) ($configWpp['notificar_motoboy'] ?? true),
                'bot_ativo' => (bool) ($configWpp['bot_ativo'] ?? true),
                'recuperar_carrinho' => (bool) ($configWpp['recuperar_carrinho'] ?? true),
                'recompra_um_clique' => (bool) ($configWpp['recompra_um_clique'] ?? true),
                'ia_atendente_ativa' => (bool) ($configWpp['ia_atendente_ativa'] ?? true),
                'alerta_atraso_ativo' => (bool) ($configWpp['alerta_atraso_ativo'] ?? true),
                'minutos_alerta_atraso' => (int) ($configWpp['minutos_alerta_atraso'] ?? 35),
                'solicitar_avaliacao' => (bool) ($configWpp['solicitar_avaliacao'] ?? true),
                'minutos_solicitar_avaliacao' => (int) ($configWpp['minutos_solicitar_avaliacao'] ?? 30),
                'google_maps_review_url' => (string) ($configWpp['google_maps_review_url'] ?? ''),
                'winback_ativo' => (bool) ($configWpp['winback_ativo'] ?? false),
                'winback_dias_inatividade' => (int) ($configWpp['winback_dias_inatividade'] ?? 30),
                'winback_cupom_codigo' => (string) ($configWpp['winback_cupom_codigo'] ?? 'VOLTEI10'),
                'winback_desconto_tipo' => (string) ($configWpp['winback_desconto_tipo'] ?? 'percentual'),
                'winback_desconto_valor' => (int) ($configWpp['winback_desconto_valor'] ?? 10),
                'winback_validade_horas' => (int) ($configWpp['winback_validade_horas'] ?? 48),
                'winback_minimo_reais' => (float) ($configWpp['winback_minimo_reais'] ?? 30.0),
                'winback_mensagem' => (string) ($configWpp['winback_mensagem'] ?? ''),
                'winback_limite_diario' => (int) ($configWpp['winback_limite_diario'] ?? 30),
            ],
        ]);
    }

    /**
     * POST /api/painel/whatsapp/conectar
     * Inicia sessão e gera QR Code para pareamento.
     */
    public function conectar(Request $request): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        /** @var Loja|null $loja */
        $loja = $usuario->loja;

        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NAO_ENCONTRADA'], Response::HTTP_NOT_FOUND);
        }

        $instancia = $this->evolutionApi->obterNomeInstanciaLoja($loja);
        $resultado = $this->evolutionApi->conectarInstancia($instancia);

        return response()->json($resultado);
    }

    /**
     * POST /api/painel/whatsapp/desconectar
     */
    public function desconectar(Request $request): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        /** @var Loja|null $loja */
        $loja = $usuario->loja;

        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NAO_ENCONTRADA'], Response::HTTP_NOT_FOUND);
        }

        $instancia = $this->evolutionApi->obterNomeInstanciaLoja($loja);
        $sucesso = $this->evolutionApi->desconectarInstancia($instancia);

        $config = $loja->config_json ?? [];
        if (isset($config['whatsapp'])) {
            $config['whatsapp']['connected'] = false;
            $config['whatsapp']['phone'] = null;
            $loja->config_json = $config;
            $loja->save();
        }

        return response()->json([
            'success' => $sucesso,
            'message' => $sucesso ? 'Instância do WhatsApp desconectada com sucesso.' : 'Falha ao desconectar instância.',
        ]);
    }

    /**
     * POST /api/painel/whatsapp/config
     */
    public function salvarConfig(Request $request): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        /** @var Loja|null $loja */
        $loja = $usuario->loja;

        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NAO_ENCONTRADA'], Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'notificar_pedidos' => ['required', 'boolean'],
            'notificar_motoboy' => ['required', 'boolean'],
            'bot_ativo' => ['required', 'boolean'],
            'recuperar_carrinho' => ['nullable', 'boolean'],
            'recompra_um_clique' => ['nullable', 'boolean'],
            'ia_atendente_ativa' => ['nullable', 'boolean'],
            'alerta_atraso_ativo' => ['nullable', 'boolean'],
            'minutos_alerta_atraso' => ['nullable', 'integer', 'min:15', 'max:120'],
            'solicitar_avaliacao' => ['nullable', 'boolean'],
            'minutos_solicitar_avaliacao' => ['nullable', 'integer', 'min:10', 'max:180'],
            'google_maps_review_url' => ['nullable', 'string', 'max:500'],
            'winback_ativo' => ['nullable', 'boolean'],
            'winback_dias_inatividade' => ['nullable', 'integer', 'min:7', 'max:180'],
            'winback_cupom_codigo' => ['nullable', 'string', 'max:30'],
            'winback_desconto_tipo' => ['nullable', 'string', 'in:percentual,valor'],
            'winback_desconto_valor' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'winback_validade_horas' => ['nullable', 'integer', 'min:6', 'max:336'],
            'winback_minimo_reais' => ['nullable', 'numeric', 'min:0'],
            'winback_mensagem' => ['nullable', 'string', 'max:1500'],
            'winback_limite_diario' => ['nullable', 'integer', 'min:5', 'max:200'],
        ]);

        $config = $loja->config_json ?? [];
        $wpp = $config['whatsapp'] ?? [];

        $wpp['notificar_pedidos'] = $request->boolean('notificar_pedidos');
        $wpp['notificar_motoboy'] = $request->boolean('notificar_motoboy');
        $wpp['bot_ativo'] = $request->boolean('bot_ativo');
        $wpp['recuperar_carrinho'] = $request->boolean('recuperar_carrinho', true);
        $wpp['recompra_um_clique'] = $request->boolean('recompra_um_clique', true);
        $wpp['ia_atendente_ativa'] = $request->boolean('ia_atendente_ativa', true);
        $wpp['alerta_atraso_ativo'] = $request->boolean('alerta_atraso_ativo', true);
        $wpp['minutos_alerta_atraso'] = $request->integer('minutos_alerta_atraso', 35);
        $wpp['solicitar_avaliacao'] = $request->boolean('solicitar_avaliacao', true);
        $wpp['minutos_solicitar_avaliacao'] = $request->integer('minutos_solicitar_avaliacao', 30);
        $wpp['google_maps_review_url'] = trim((string) $request->input('google_maps_review_url', ''));
        $wpp['winback_ativo'] = $request->boolean('winback_ativo', false);
        $wpp['winback_dias_inatividade'] = $request->integer('winback_dias_inatividade', 30);
        $wpp['winback_cupom_codigo'] = strtoupper(trim((string) $request->input('winback_cupom_codigo', 'VOLTEI10')));
        $wpp['winback_desconto_tipo'] = $request->input('winback_desconto_tipo', 'percentual') === 'valor' ? 'valor' : 'percentual';
        $wpp['winback_desconto_valor'] = $request->integer('winback_desconto_valor', 10);
        $wpp['winback_validade_horas'] = $request->integer('winback_validade_horas', 48);
        $wpp['winback_minimo_reais'] = (float) $request->input('winback_minimo_reais', 30.0);
        $wpp['winback_mensagem'] = $request->filled('winback_mensagem') ? trim((string) $request->input('winback_mensagem')) : null;
        $wpp['winback_limite_diario'] = $request->integer('winback_limite_diario', 30);

        $config['whatsapp'] = $wpp;
        $loja->config_json = $config;
        $loja->save();

        return response()->json([
            'success' => true,
            'message' => 'Configurações de automação do WhatsApp salvas com sucesso.',
            'config' => $wpp,
        ]);
    }

    /**
     * POST /api/painel/whatsapp/teste
     */
    public function enviarTeste(Request $request): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        /** @var Loja|null $loja */
        $loja = $usuario->loja;

        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NAO_ENCONTRADA'], Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'telefone' => ['required', 'string', 'min:10', 'max:20'],
            'mensagem' => ['nullable', 'string', 'max:500'],
        ]);

        $instancia = $this->evolutionApi->obterNomeInstanciaLoja($loja);
        $telefone = $request->string('telefone')->toString();
        $texto = $request->filled('mensagem')
            ? $request->string('mensagem')->toString()
            : "Olá! 🚀 Teste de conexão WhatsApp do PedeLivre para a loja *{$loja->nome}* concluído com sucesso!";

        $enviado = $this->evolutionApi->enviarMensagem($instancia, $telefone, $texto, $loja->id);

        return response()->json([
            'success' => $enviado,
            'message' => $enviado ? 'Mensagem de teste enviada com sucesso!' : 'Falha ao enviar mensagem. Verifique se o WhatsApp está conectado.',
        ]);
    }

    /**
     * POST /api/webhooks/whatsapp/evolution
     * Endpoint chamado pelo microserviço Evolution API (suporta formato gateway customizado e formato nativo Baileys v1/v2).
     */
    public function webhook(Request $request): JsonResponse
    {
        $secretRecebido = (string) ($request->header('x-webhook-secret') ?: $request->header('x-api-key') ?: $request->header('apikey') ?: $request->input('secret') ?: $request->input('apikey'));
        $secretEsperado = (string) config('services.evolution.webhook_secret', 'devla_pedelivre_wpp_secret_2026');

        // Aceita se o segredo bater ou se vier da Evolution API local com apikey padrão
        if ($secretRecebido !== '' && $secretRecebido !== $secretEsperado && $secretRecebido !== 'devla_evolution_secret_2026' && $secretRecebido !== config('services.evolution.api_key')) {
            Log::warning('[WhatsAppEvolutionController] Webhook rejeitado: segredo inválido.');
            return response()->json(['error' => 'SEGREDO_INVALIDO'], Response::HTTP_FORBIDDEN);
        }

        $payload = $request->all();
        $evento = strtoupper(str_replace('.', '_', (string) ($payload['event'] ?? 'MESSAGES_UPSERT')));
        $instancia = (string) ($payload['instance'] ?? ($payload['data']['instance'] ?? $request->header('x-instance') ?? ''));
        $dados = (array) ($payload['data'] ?? $payload);

        // Identifica a loja pelo nome da instância (ex: pedelivre-loja-1)
        $lojaId = null;
        if (preg_match('/pedelivre-loja-(\d+)/', $instancia, $matches)) {
            $lojaId = (int) $matches[1];
        }

        /** @var Loja|null $loja */
        $loja = $lojaId ? Loja::withoutGlobalScopes()->find($lojaId) : null;
        if ($loja === null) {
            return response()->json(['status' => 'loja_nao_encontrada', 'instance' => $instancia]);
        }

        // Define a loja ativa no contexto Multi-Tenant para que todos os models (Cliente, Pedido, etc.) filtrem corretamente
        $this->lojaContext->setLoja($loja);

        // 1. Atualização de conexão
        if ($evento === 'CONNECTION_UPDATE') {
            $estado = (string) ($dados['state'] ?? 'close');
            $telefone = !empty($dados['phone']) ? (string) $dados['phone'] : null;

            $config = $loja->config_json ?? [];
            $wpp = $config['whatsapp'] ?? [];
            $wpp['connected'] = ($estado === 'open');
            if ($telefone) {
                $wpp['phone'] = $telefone;
            }
            $config['whatsapp'] = $wpp;
            $loja->config_json = $config;
            $loja->save();

            return response()->json(['status' => 'conexao_atualizada', 'state' => $estado]);
        }

        // 2. Mensagens recebidas (Suporta formato simplificado e formato nativo Baileys / Evolution v1/v2)
        if ($evento === 'MESSAGES_UPSERT') {
            $fromMe = (bool) ($dados['fromMe'] ?? ($dados['key']['fromMe'] ?? false));
            if ($fromMe) {
                return response()->json(['status' => 'ignorado_from_me']);
            }

            $remoteJid = (string) ($dados['phone'] ?? ($dados['key']['remoteJid'] ?? ($dados['sender'] ?? '')));
            if (str_contains($remoteJid, '@g.us') || str_contains($remoteJid, 'status@broadcast')) {
                return response()->json(['status' => 'ignorado_grupo_ou_broadcast']);
            }

            $telefoneRemetente = $this->blindIndex->normalizePhone($remoteJid);
            $ehPayloadNativoEvolution = isset($dados['key']['remoteJid']) && !isset($dados['phone']);

            // Extrai texto da mensagem de todos os formatos possíveis (simplificado ou Baileys)
            $textoMensagem = (string) (
                $dados['text']
                ?? $dados['message']['conversation']
                ?? $dados['message']['extendedTextMessage']['text']
                ?? $dados['message']['buttonsResponseMessage']['selectedDisplayText']
                ?? $dados['message']['listResponseMessage']['title']
                ?? (is_string($dados['message'] ?? null) ? $dados['message'] : '')
            );

            $isAudio = (bool) ($dados['is_audio'] ?? isset($dados['message']['audioMessage']));
            $audioBase64 = (string) ($dados['audio_base64'] ?? ($dados['message']['base64'] ?? ''));
            $audioMimetype = (string) ($dados['audio_mimetype'] ?? ($dados['message']['audioMessage']['mimetype'] ?? 'audio/ogg'));

            // Se for áudio, executa transcrição instantânea via Groq Whisper
            if (($isAudio || !empty($audioBase64)) && !empty($audioBase64)) {
                $transcricao = $this->aiChatService->transcreverAudio($audioBase64, $audioMimetype);
                if (!empty($transcricao)) {
                    $textoMensagem = $transcricao;
                    $isAudio = true;
                } else {
                    $linkCardapio = url("/p/{$loja->slug}");
                    $msgFallback = "Olá! Não consegui ouvir seu áudio com clareza. 🎙️😅\n\nPoderia enviar sua mensagem por texto ou tentar gravar novamente? Se preferir ver nossos produtos e fazer um pedido direto, acesse:\n👉 {$linkCardapio}";
                    if ($ehPayloadNativoEvolution) {
                        $this->evolutionApi->enviarMensagem($instancia, $telefoneRemetente, $msgFallback, $loja->id);
                    }
                    return response()->json([
                        'status' => 'audio_nao_compreendido',
                        'replyText' => $msgFallback,
                    ]);
                }
            }

            if (empty($telefoneRemetente) || empty(trim($textoMensagem))) {
                return response()->json(['status' => 'mensagem_vazia']);
            }

            $configWpp = $loja->config_json['whatsapp'] ?? [];
            $botAtivo = (bool) ($configWpp['bot_ativo'] ?? true);

            if (!$botAtivo) {
                return response()->json(['status' => 'bot_desativado']);
            }

            try {
                $meta = [
                    'foi_audio' => $isAudio,
                    'timestamp' => $dados['messageTimestamp'] ?? time(),
                ];

                $respostaTexto = $this->botService->processarMensagem($loja, $telefoneRemetente, $textoMensagem, $meta);

                // Se veio diretamente da Evolution API nativa (que não envia replyText sozinha), dispara via sendText
                if ($ehPayloadNativoEvolution && !empty($respostaTexto)) {
                    $this->evolutionApi->enviarMensagem($instancia, $telefoneRemetente, $respostaTexto, $loja->id);
                }

                return response()->json([
                    'status' => 'processado',
                    'replyText' => $respostaTexto,
                    'transcricao' => $isAudio ? $textoMensagem : null,
                ]);
            } catch (Exception $e) {
                Log::error("[WhatsAppEvolutionController] Erro ao processar mensagem do bot: " . $e->getMessage());
                return response()->json(['status' => 'erro', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return response()->json(['status' => 'evento_ignorado', 'event' => $evento]);
    }

    /**
     * GET /api/painel/whatsapp/conversas
     * Lista conversas do WhatsApp com suporte a busca e filtros por estado.
     */
    public function listarConversas(Request $request): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        /** @var Loja|null $loja */
        $loja = $usuario->loja;

        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NAO_ENCONTRADA'], Response::HTTP_NOT_FOUND);
        }

        $estadoFiltro = (string) $request->input('estado', 'todos');
        $busca = trim((string) $request->input('busca', ''));

        $query = BotConversa::where('loja_id', $loja->id)
            ->with(['mensagens' => function ($q): void {
                $q->latest('created_at')->limit(1);
            }]);

        if ($estadoFiltro === 'humano') {
            $query->where('estado', 'humano');
        } elseif ($estadoFiltro === 'ia_ativa') {
            $query->where('estado', 'ia_ativa');
        }

        $conversas = $query->orderByDesc('updated_at')->limit(60)->get();

        // Coleta todas as variantes de 8/9 dígitos para associar o nome do cliente com precisão
        $todosCandidatosBidx = [];
        foreach ($conversas as $conv) {
            foreach ($this->blindIndex->generatePhoneCandidates($conv->telefone) as $bidx) {
                $todosCandidatosBidx[] = $bidx;
            }
            if ($conv->telefone_bidx) {
                $todosCandidatosBidx[] = $conv->telefone_bidx;
            }
        }

        $clientesMap = Cliente::where('loja_id', $loja->id)
            ->whereIn('telefone_bidx', array_values(array_unique($todosCandidatosBidx)))
            ->get()
            ->keyBy('telefone_bidx');

        $lista = $conversas->map(function (BotConversa $c) use ($clientesMap) {
            $cliente = $clientesMap->get($c->telefone_bidx);
            if ($cliente === null) {
                foreach ($this->blindIndex->generatePhoneCandidates($c->telefone) as $candBidx) {
                    if ($clientesMap->has($candBidx)) {
                        $cliente = $clientesMap->get($candBidx);
                        break;
                    }
                }
            }

            $nomeCliente = $cliente?->nome ?: 'Cliente WhatsApp';
            $ultimaMsg = $c->mensagens->first();

            return [
                'id' => $c->id,
                'telefone' => $c->telefone,
                'cliente_nome' => $nomeCliente,
                'cliente_id' => $cliente?->id,
                'estado' => $c->estado,
                'ultima_mensagem' => $ultimaMsg ? $ultimaMsg->mensagem : null,
                'ultima_direcao' => $ultimaMsg ? $ultimaMsg->direcao : null,
                'ultimo_horario' => $ultimaMsg ? $ultimaMsg->created_at->format('H:i') : $c->updated_at->format('H:i'),
                'ultimo_tempo_humano' => $ultimaMsg ? $ultimaMsg->created_at->diffForHumans() : $c->updated_at->diffForHumans(),
                'updated_at' => $c->updated_at->toIso8601String(),
            ];
        });

        if ($busca !== '') {
            $buscaUpper = mb_strtoupper($busca);
            $lista = $lista->filter(function ($item) use ($buscaUpper) {
                return str_contains(mb_strtoupper((string) $item['cliente_nome']), $buscaUpper)
                    || str_contains((string) $item['telefone'], $buscaUpper)
                    || str_contains(mb_strtoupper((string) $item['ultima_mensagem']), $buscaUpper);
            })->values();
        }

        $totalHumano = BotConversa::where('loja_id', $loja->id)->where('estado', 'humano')->count();

        return response()->json([
            'conversas' => $lista,
            'total_aguardando_humano' => $totalHumano,
        ]);
    }

    /**
     * GET /api/painel/whatsapp/conversas/{id}/mensagens
     * Retorna o histórico cronológico de mensagens da conversa.
     */
    public function obterMensagensConversa(Request $request, int $id): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        /** @var Loja|null $loja */
        $loja = $usuario->loja;

        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NAO_ENCONTRADA'], Response::HTTP_NOT_FOUND);
        }

        $conversa = BotConversa::where('loja_id', $loja->id)->find($id);
        if ($conversa === null) {
            return response()->json(['error' => 'CONVERSA_NAO_ENCONTRADA'], Response::HTTP_NOT_FOUND);
        }

        $candidatosBidx = $this->blindIndex->generatePhoneCandidates($conversa->telefone);
        if ($conversa->telefone_bidx) {
            $candidatosBidx[] = $conversa->telefone_bidx;
        }

        $clientes = Cliente::where('loja_id', $loja->id)
            ->whereIn('telefone_bidx', array_values(array_unique($candidatosBidx)))
            ->orderByDesc('ultimo_pedido_em')
            ->get();

        $cliente = $clientes->first();
        $clienteIds = $clientes->pluck('id')->all();

        $pedidoRecente = $this->botService->localizarPedidoCliente($loja, $clienteIds, $conversa);
        if ($pedidoRecente !== null && $cliente === null && $pedidoRecente->cliente !== null) {
            $cliente = $pedidoRecente->cliente;
        }

        $mensagens = BotMensagem::where('conversa_id', $conversa->id)
            ->orderBy('created_at', 'asc')
            ->limit(100)
            ->get()
            ->map(fn (BotMensagem $m) => [
                'id' => $m->id,
                'direcao' => $m->direcao,
                'mensagem' => $m->mensagem,
                'horario' => $m->created_at->format('H:i'),
                'data' => $m->created_at->format('d/m'),
                'created_at' => $m->created_at->toIso8601String(),
            ]);

        return response()->json([
            'conversa' => [
                'id' => $conversa->id,
                'telefone' => $conversa->telefone,
                'estado' => $conversa->estado,
                'cliente_nome' => $cliente?->nome ?: 'Cliente WhatsApp',
                'cliente_id' => $cliente?->id,
                'pedido_recente' => $pedidoRecente ? [
                    'codigo' => $pedidoRecente->codigo_publico,
                    'status' => $pedidoRecente->status,
                    'total' => number_format($pedidoRecente->total / 100, 2, ',', '.'),
                    'hora' => $pedidoRecente->created_at->format('H:i'),
                ] : null,
            ],
            'mensagens' => $mensagens,
        ]);
    }

    /**
     * POST /api/painel/whatsapp/conversas/{id}/responder
     */
    public function responderConversa(Request $request, int $id): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        /** @var Loja|null $loja */
        $loja = $usuario->loja;

        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NAO_ENCONTRADA'], Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'mensagem' => ['required', 'string', 'max:2000'],
        ]);

        $conversa = BotConversa::where('loja_id', $loja->id)->find($id);
        if ($conversa === null) {
            return response()->json(['error' => 'CONVERSA_NAO_ENCONTRADA'], Response::HTTP_NOT_FOUND);
        }

        $texto = trim($request->string('mensagem')->toString());
        $instancia = $this->evolutionApi->obterNomeInstanciaLoja($loja);

        $enviado = $this->evolutionApi->enviarMensagem($instancia, $conversa->telefone, $texto, $loja->id);
        if (!$enviado) {
            return response()->json([
                'error' => 'FALHA_ENVIO_WHATSAPP',
                'message' => 'Não foi possível enviar a mensagem. Verifique se o WhatsApp está conectado.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $msg = BotMensagem::create([
            'loja_id' => $loja->id,
            'conversa_id' => $conversa->id,
            'direcao' => 'saida',
            'mensagem' => $texto,
        ]);

        $conversa->update([
            'estado' => 'humano',
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'mensagem' => [
                'id' => $msg->id,
                'direcao' => 'saida',
                'mensagem' => $msg->mensagem,
                'horario' => now()->format('H:i'),
                'data' => now()->format('d/m'),
                'created_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * POST /api/painel/whatsapp/conversas/{id}/assumir
     */
    public function assumirAtendimento(Request $request, int $id): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        /** @var Loja|null $loja */
        $loja = $usuario->loja;

        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NAO_ENCONTRADA'], Response::HTTP_NOT_FOUND);
        }

        $conversa = BotConversa::where('loja_id', $loja->id)->find($id);
        if ($conversa === null) {
            return response()->json(['error' => 'CONVERSA_NAO_ENCONTRADA'], Response::HTTP_NOT_FOUND);
        }

        $conversa->update([
            'estado' => 'humano',
            'updated_at' => now(),
        ]);

        $avisarCliente = $request->boolean('avisar_cliente', false);
        if ($avisarCliente) {
            $msg = "Olá! Um de nossos atendentes acabou de entrar nesta conversa para te atender pessoalmente! 👨‍💼 Como podemos te ajudar agora?";
            $instancia = $this->evolutionApi->obterNomeInstanciaLoja($loja);
            $this->evolutionApi->enviarMensagem($instancia, $conversa->telefone, $msg, $loja->id);

            BotMensagem::create([
                'loja_id' => $loja->id,
                'conversa_id' => $conversa->id,
                'direcao' => 'saida',
                'mensagem' => $msg,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Atendimento humano assumido com sucesso.',
        ]);
    }

    /**
     * POST /api/painel/whatsapp/conversas/{id}/devolver-bot
     */
    public function devolverBot(Request $request, int $id): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        /** @var Loja|null $loja */
        $loja = $usuario->loja;

        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NAO_ENCONTRADA'], Response::HTTP_NOT_FOUND);
        }

        $conversa = BotConversa::where('loja_id', $loja->id)->find($id);
        if ($conversa === null) {
            return response()->json(['error' => 'CONVERSA_NAO_ENCONTRADA'], Response::HTTP_NOT_FOUND);
        }

        $conversa->update([
            'estado' => 'ia_ativa',
            'updated_at' => now(),
        ]);

        $avisarCliente = $request->boolean('avisar_cliente', true);
        if ($avisarCliente) {
            $msg = "Atendimento humano finalizado! Se precisar de algo mais, nosso assistente virtual está pronto para te ajudar a qualquer momento. Basta digitar *MENU*! 🤖✨";
            $instancia = $this->evolutionApi->obterNomeInstanciaLoja($loja);
            $this->evolutionApi->enviarMensagem($instancia, $conversa->telefone, $msg, $loja->id);

            BotMensagem::create([
                'loja_id' => $loja->id,
                'conversa_id' => $conversa->id,
                'direcao' => 'saida',
                'mensagem' => $msg,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Conversa devolvida para o assistente automático com sucesso.',
        ]);
    }

    /**
     * GET /api/painel/avaliacoes
     */
    public function listarAvaliacoes(Request $request): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        /** @var Loja|null $loja */
        $loja = $usuario->loja;

        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NAO_ENCONTRADA'], Response::HTTP_NOT_FOUND);
        }

        $avaliacoes = Avaliacao::where('loja_id', $loja->id)
            ->with(['pedido.cliente'])
            ->latest('created_at')
            ->limit(30)
            ->get()
            ->map(fn (Avaliacao $a) => [
                'id' => $a->id,
                'nota' => $a->nota,
                'comentario' => $a->comentario,
                'pedido_codigo' => $a->pedido?->codigo_publico,
                'cliente_nome' => $a->pedido?->cliente?->nome ?: 'Cliente',
                'data' => $a->created_at->format('d/m/Y H:i'),
            ]);

        $mediaNotas = Avaliacao::where('loja_id', $loja->id)->avg('nota');
        $totalAvaliacoes = Avaliacao::where('loja_id', $loja->id)->count();

        return response()->json([
            'avaliacoes' => $avaliacoes,
            'media' => round((float) $mediaNotas, 1),
            'total' => $totalAvaliacoes,
        ]);
    }

    /**
     * GET /api/painel/whatsapp/campanhas/preview
     */
    public function previewCampanhaWinback(Request $request, WinBackService $winBackService): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        /** @var Loja|null $loja */
        $loja = $usuario->loja;

        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NAO_ENCONTRADA'], Response::HTTP_NOT_FOUND);
        }

        $resumo = $winBackService->obterResumoCampanha($loja);
        return response()->json($resumo);
    }

    /**
     * POST /api/painel/whatsapp/campanhas/disparar
     */
    public function dispararCampanhaWinback(Request $request, WinBackService $winBackService): JsonResponse
    {
        /** @var Usuario $usuario */
        $usuario = $request->user();
        /** @var Loja|null $loja */
        $loja = $usuario->loja;

        if ($loja === null) {
            return response()->json(['error' => 'LOJA_NAO_ENCONTRADA'], Response::HTTP_NOT_FOUND);
        }

        $resultado = $winBackService->executarCampanhaLoja($loja);

        if (!$resultado['success']) {
            $msg = match ($resultado['motivo'] ?? '') {
                'whatsapp_desconectado' => 'O WhatsApp da sua loja não está conectado. Conecte pelo QR Code antes de disparar.',
                default => 'Não foi possível disparar a campanha.'
            };
            return response()->json([
                'success' => false,
                'message' => $msg,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (($resultado['enviados'] ?? 0) === 0 && ($resultado['total_elegiveis'] ?? 0) === 0) {
            return response()->json([
                'success' => true,
                'message' => 'Nenhum cliente inativo elegível encontrado para reativação no momento.',
                'enviados' => 0,
                'total_elegiveis' => 0,
                'cupom' => $resultado['cupom'] ?? '',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Campanha executada! {$resultado['enviados']} mensagens enviadas com sucesso.",
            'enviados' => $resultado['enviados'],
            'erros' => $resultado['erros'],
            'total_elegiveis' => $resultado['total_elegiveis'],
            'cupom' => $resultado['cupom'],
        ]);
    }
}
