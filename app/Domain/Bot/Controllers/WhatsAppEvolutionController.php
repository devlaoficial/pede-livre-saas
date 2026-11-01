<?php

declare(strict_types=1);

namespace App\Domain\Bot\Controllers;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Bot\Services\EvolutionApiService;
use App\Domain\Bot\Services\EvolutionBotService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class WhatsAppEvolutionController
{
    public function __construct(
        private readonly EvolutionApiService $evolutionApi,
        private readonly EvolutionBotService $botService
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

        // Atualiza config local
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
        ]);

        $config = $loja->config_json ?? [];
        $wpp = $config['whatsapp'] ?? [];

        $wpp['notificar_pedidos'] = $request->boolean('notificar_pedidos');
        $wpp['notificar_motoboy'] = $request->boolean('notificar_motoboy');
        $wpp['bot_ativo'] = $request->boolean('bot_ativo');

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

        $enviado = $this->evolutionApi->enviarMensagem($instancia, $telefone, $texto);

        return response()->json([
            'success' => $enviado,
            'message' => $enviado ? 'Mensagem de teste enviada com sucesso!' : 'Falha ao enviar mensagem. Verifique se o WhatsApp está conectado.',
        ]);
    }

    /**
     * POST /api/webhooks/whatsapp/evolution
     * Endpoint chamado pelo microserviço Evolution API para eventos e mensagens recebidas.
     */
    public function webhook(Request $request): JsonResponse
    {
        $secretRecebido = (string) ($request->header('x-webhook-secret') ?: $request->header('x-api-key') ?: $request->input('secret'));
        $secretEsperado = (string) config('services.evolution.webhook_secret', 'devla_pedelivre_wpp_secret_2026');

        if ($secretRecebido !== $secretEsperado && $secretRecebido !== 'devla_evolution_secret_2026') {
            Log::warning('[WhatsAppEvolutionController] Webhook rejeitado: segredo inválido.');
            return response()->json(['error' => 'SEGREDO_INVALIDO'], Response::HTTP_FORBIDDEN);
        }

        $payload = $request->all();
        $evento = (string) ($payload['event'] ?? 'messages.upsert');
        $instancia = (string) ($payload['instance'] ?? $request->header('x-instance') ?? '');
        $dados = (array) ($payload['data'] ?? $payload);

        // Identifica a loja pelo nome da instância (ex: pedelivre-loja-1)
        $lojaId = null;
        if (preg_match('/pedelivre-loja-(\d+)/', $instancia, $matches)) {
            $lojaId = (int) $matches[1];
        }

        /** @var Loja|null $loja */
        $loja = $lojaId ? Loja::find($lojaId) : null;
        if ($loja === null) {
            return response()->json(['status' => 'loja_nao_encontrada', 'instance' => $instancia]);
        }

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

        // 2. Mensagens recebidas
        if ($evento === 'MESSAGES_UPSERT' || $evento === 'messages.upsert') {
            $telefoneRemetente = (string) ($dados['phone'] ?? '');
            $textoMensagem = (string) ($dados['text'] ?? $dados['message'] ?? '');

            if (empty($telefoneRemetente) || empty(trim($textoMensagem))) {
                return response()->json(['status' => 'mensagem_vazia']);
            }

            // Verifica se o bot está ativo nas configurações da loja
            $configWpp = $loja->config_json['whatsapp'] ?? [];
            $botAtivo = (bool) ($configWpp['bot_ativo'] ?? true);

            if (!$botAtivo) {
                return response()->json(['status' => 'bot_desativado']);
            }

            try {
                $respostaTexto = $this->botService->processarMensagem($loja, $telefoneRemetente, $textoMensagem);

                return response()->json([
                    'status' => 'processado',
                    'replyText' => $respostaTexto,
                ]);
            } catch (Exception $e) {
                Log::error("[WhatsAppEvolutionController] Erro ao processar mensagem do bot: " . $e->getMessage());
                return response()->json(['status' => 'erro', 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return response()->json(['status' => 'evento_ignorado', 'event' => $evento]);
    }
}
