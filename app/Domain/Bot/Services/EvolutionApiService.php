<?php

declare(strict_types=1);

namespace App\Domain\Bot\Services;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Entregas\Models\Entregador;
use App\Domain\Pedidos\Models\Pedido;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class EvolutionApiService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.evolution.url', 'http://127.0.0.1:8085'), '/');
        $this->apiKey = (string) config('services.evolution.api_key', 'devla_evolution_secret_2026');
    }

    /**
     * Resolve o nome da instância padrão para uma loja.
     */
    public function obterNomeInstanciaLoja(Loja $loja): string
    {
        return 'pedelivre-loja-' . $loja->id;
    }

    /**
     * Consulta o status da conexão da instância no microserviço Evolution.
     *
     * @return array{instance: string, state: string, connected: bool, phone: ?string, qrBase64: ?string}
     */
    public function obterStatusInstancia(string $instancia): array
    {
        try {
            $response = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(8)
                ->get("{$this->baseUrl}/instance/status/{$instancia}");

            if ($response->successful()) {
                $dados = $response->json();
                return [
                    'instance' => (string) ($dados['instance'] ?? $instancia),
                    'state' => (string) ($dados['state'] ?? 'disconnected'),
                    'connected' => (bool) ($dados['connected'] ?? false),
                    'phone' => !empty($dados['phone']) ? (string) $dados['phone'] : null,
                    'qrBase64' => !empty($dados['qrBase64']) ? (string) $dados['qrBase64'] : null,
                ];
            }
        } catch (Exception $e) {
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
     * Inicia a sessão de pareamento e obtém o QR Code em Base64.
     *
     * @return array{instance: string, state: string, connected: bool, phone: ?string, qrBase64: ?string}
     */
    public function conectarInstancia(string $instancia): array
    {
        try {
            $response = Http::withHeaders(['apikey' => $this->apiKey])
                ->timeout(15)
                ->post("{$this->baseUrl}/instance/connect/{$instancia}");

            if ($response->successful()) {
                $dados = $response->json();
                $qrBase64 = $dados['qrBase64'] ?? $dados['qrcode']['base64'] ?? $dados['base64'] ?? null;

                return [
                    'instance' => (string) ($dados['instance'] ?? $instancia),
                    'state' => (string) ($dados['state'] ?? 'connecting'),
                    'connected' => (bool) ($dados['connected'] ?? false),
                    'phone' => !empty($dados['phone']) ? (string) $dados['phone'] : null,
                    'qrBase64' => is_string($qrBase64) ? $qrBase64 : null,
                ];
            }
        } catch (Exception $e) {
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
                ->timeout(10)
                ->post("{$this->baseUrl}/instance/disconnect/{$instancia}");

            return $response->successful();
        } catch (Exception $e) {
            Log::warning("[EvolutionApi] Falha ao desconectar instância {$instancia}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envia mensagem de texto simples pelo WhatsApp da instância.
     */
    public function enviarMensagem(string $instancia, string $telefone, string $texto): bool
    {
        $telefoneLimpo = preg_replace('/\D/', '', $telefone);
        if (empty($telefoneLimpo) || empty(trim($texto))) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'apikey' => $this->apiKey,
            ])
            ->timeout(10)
            ->post("{$this->baseUrl}/message/sendText/{$instancia}", [
                'number' => $telefoneLimpo,
                'text' => trim($texto),
            ]);

            if ($response->successful()) {
                Log::info("[EvolutionApi] Mensagem enviada via [{$instancia}] para [{$telefoneLimpo}]");
                return true;
            }

            Log::warning("[EvolutionApi] Erro ao enviar mensagem: " . $response->body());
        } catch (Exception $e) {
            Log::error("[EvolutionApi] Exceção ao enviar mensagem via [{$instancia}]: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Envia notificação automática de transição de status para o cliente e/ou motoboy.
     */
    public function notificarMudancaStatus(Pedido $pedido, string $novoStatus): void
    {
        try {
            /** @var Loja|null $loja */
            $loja = $pedido->loja ?? Loja::find($pedido->loja_id);
            if ($loja === null) {
                return;
            }

            $instancia = $this->obterNomeInstanciaLoja($loja);
            $configWpp = $loja->config_json['whatsapp'] ?? [];
            $notificarCliente = (bool) ($configWpp['notificar_pedidos'] ?? true);
            $notificarMotoboy = (bool) ($configWpp['notificar_motoboy'] ?? true);

            // Verifica se a instância está conectada antes de tentar disparar
            $statusInstancia = $this->obterStatusInstancia($instancia);
            if (!$statusInstancia['connected']) {
                return;
            }

            $cliente = $pedido->cliente;
            $telefoneCliente = $cliente?->telefone ? (string) $cliente->telefone : null;
            $nomeCliente = $cliente?->nome ?? 'Cliente';
            $primeiroNome = explode(' ', trim($nomeCliente))[0];
            $codigo = $pedido->codigo_publico;
            $lojaNome = $loja->nome;

            // Link de rastreamento com assinatura criptográfica
            $trackingSignature = hash_hmac('sha256', (string) $pedido->tracking_token_hash, (string) config('app.key'));
            $trackingUrl = url("/p/{$loja->slug}/pedidos/{$codigo}?t={$trackingSignature}");

            // 1. Mensagens para o CLIENTE
            if ($notificarCliente && !empty($telefoneCliente)) {
                $mensagemCliente = null;

                switch ($novoStatus) {
                    case 'aceito':
                    case 'em_preparo':
                        $mensagemCliente = "Olá, *{$primeiroNome}*! 👋\n\n"
                            . "Seu pedido *#{$codigo}* foi confirmado e já entrou em preparo com todo carinho na cozinha do *{$lojaNome}*! 👨‍🍳🔥\n\n"
                            . "⏱️ *Previsão estimada:* 30 a 45 min\n"
                            . "📍 Acompanhe cada etapa ao vivo:\n👉 {$trackingUrl}";
                        break;

                    case 'pronto':
                        if ($pedido->canal === 'retirada') {
                            $mensagemCliente = "Oba, *{$primeiroNome}*! 🎉\n\n"
                                . "Seu pedido *#{$codigo}* está *PRONTINHO* esperando por você no balcão do *{$lojaNome}*! 🛍️\n\n"
                                . "Pode vir retirar quando quiser. Esperamos por você!";
                        } else {
                            $mensagemCliente = "Oba, *{$primeiroNome}*! 🎉\n\n"
                                . "Seu pedido *#{$codigo}* já ficou pronto e foi embalado na bolsa térmica com todo cuidado! 🛍️\n\n"
                                . "Nosso entregador já foi acionado e está saindo para levar até você! 🚀";
                        }
                        break;

                    case 'saiu_entrega':
                        $nomeEntregador = $pedido->entregador?->nome ?? 'Nosso entregador';
                        $mensagemCliente = "🛵💨 *SEU PEDIDO ESTÁ A CAMINHO!* 🛵💨\n\n"
                            . "Oi, *{$primeiroNome}*! O entregador *{$nomeEntregador}* acabou de sair com o seu pedido *#{$codigo}*!\n\n"
                            . "Fique atento(a) ao interfone ou campainha. Qualquer dúvida, pode mandar mensagem aqui!\n\n"
                            . "👉 Acompanhe o pedido: {$trackingUrl}";
                        break;

                    case 'entregue':
                    case 'retirado':
                        $mensagemCliente = "✨ *Pedido #{$codigo} Finalizado!* ✨\n\n"
                            . "Esperamos que você ame a sua refeição! Feito com muito amor por toda a equipe do *{$lojaNome}*! 🍔❤️\n\n"
                            . "Agradecemos de coração pela preferência e bom apetite!";
                        break;

                    case 'cancelado':
                        $motivo = $pedido->cancelado_motivo ?: 'Imprevisto operacional na loja';
                        $mensagemCliente = "Olá, *{$primeiroNome}*.\n\n"
                            . "Informamos que o seu pedido *#{$codigo}* precisou ser cancelado pelo estabelecimento.\n"
                            . "⚠️ *Motivo:* {$motivo}\n\n"
                            . "Se realizou pagamento via PIX, nosso time já está à disposição para estorno imediato. Caso precise, basta nos responder aqui.";
                        break;
                }

                if ($mensagemCliente !== null) {
                    $this->enviarMensagem($instancia, $telefoneCliente, $mensagemCliente);
                }
            }

            // 2. Notificação para o MOTOBOY quando o pedido fica PRONTO para entrega
            if ($notificarMotoboy && $pedido->canal === 'delivery' && $novoStatus === 'pronto') {
                $this->notificarMotoboyPedidoPronto($pedido, $instancia, $loja);
            }

        } catch (Exception $e) {
            Log::error("[EvolutionApi] Falha ao notificar mudança de status do pedido #{$pedido->id}: " . $e->getMessage());
        }
    }

    /**
     * Envia alerta detalhado para o motoboy com link de rota no Google Maps.
     */
    public function notificarMotoboyPedidoPronto(Pedido $pedido, string $instancia, Loja $loja): void
    {
        /** @var Entregador|null $entregador */
        $entregador = $pedido->entregador;

        // Se já tiver entregador atribuído, notifica diretamente o entregador
        if ($entregador !== null && !empty($entregador->telefone)) {
            $this->enviarAlertaEntregaParaMotoboy($pedido, $entregador, $instancia, $loja);
            return;
        }

        // Se não tiver entregador específico atribuído, notifica todos os entregadores ativos da loja
        $entregadoresAtivos = Entregador::where('loja_id', $loja->id)
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
        $cliente = $pedido->cliente;
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
        $formaPagamento = strtoupper(str_replace('_', ' ', $pedido->forma_pagamento));

        $msgMotoboy = "🛵 *NOVO PEDIDO PRONTO PARA ENTREGA!* 🛵\n\n"
            . "📦 *Pedido #{$codigo}* ({$loja->nome})\n"
            . "👤 *Cliente:* {$nomeCliente}\n"
            . "📞 *Tel Cliente:* {$telCliente}\n"
            . "📍 *Endereço:* {$enderecoTexto}\n"
            . "💵 *Valor do Pedido:* R$ {$totalPedido} ({$formaPagamento})\n"
            . "💰 *Taxa de Entrega:* R$ {$taxaFormatada}\n\n"
            . "🗺️ *Abrir GPS / Google Maps:*\n👉 {$linkMaps}";

        $this->enviarMensagem($instancia, $telMotoboy, $msgMotoboy);
    }

    /**
     * Envia alerta gentil de atraso quando detectada alta demanda na cozinha.
     */
    public function notificarAtraso(Pedido $pedido, int $minutosDecorridos): bool
    {
        try {
            /** @var Loja|null $loja */
            $loja = $pedido->loja ?? Loja::find($pedido->loja_id);
            if ($loja === null) {
                return false;
            }

            $instancia = $this->obterNomeInstanciaLoja($loja);

            // Verifica se a instância está conectada antes de disparar
            $statusInstancia = $this->obterStatusInstancia($instancia);
            if (!$statusInstancia['connected']) {
                return false;
            }

            $cliente = $pedido->cliente;
            $telCliente = $cliente?->telefone ? (string) $cliente->telefone : null;
            if (empty($telCliente)) {
                return false;
            }

            $primeiroNome = explode(' ', trim($cliente->nome ?? 'Cliente'))[0];
            $codigo = $pedido->codigo_publico;

            if ($pedido->canal === 'retirada' || $pedido->canal === 'balcao') {
                $msgProntidao = "Seu pedido *#{$codigo}* já é o **próximo da fila para ser finalizado**! 🍳🔥 Já estamos nos preparativos finais para você poder retirar quentinho no balcão.";
            } else {
                $msgProntidao = "Seu pedido *#{$codigo}* já é o **próximo da fila para ser embalado**! 🍳🔥 Nosso entregador já está de prontidão aguardando a saída para levar até você.";
            }

            $msg = "Oi, *{$primeiroNome}*! Passando aqui rapidinho para te dar uma satisfação! 🙏\n\n"
                . "Hoje nossa cozinha está com uma procura super alta e todos os pedidos estão sendo feitos com muito carinho para saírem com a máxima qualidade.\n\n"
                . "{$msgProntidao}\n\n"
                . "Agradecemos imensamente pela sua paciência e compreensão! ❤️";

            return $this->enviarMensagem($instancia, $telCliente, $msg);
        } catch (Exception $e) {
            Log::error("[EvolutionApi] Falha ao notificar atraso: " . $e->getMessage());
            return false;
        }
    }
}
