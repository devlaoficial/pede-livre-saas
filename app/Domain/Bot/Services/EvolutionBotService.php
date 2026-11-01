<?php

declare(strict_types=1);

namespace App\Domain\Bot\Services;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Bot\Models\BotConversa;
use App\Domain\Bot\Models\BotMensagem;
use App\Domain\Clientes\Models\Cliente;
use App\Domain\Entregas\Models\Entregador;
use App\Domain\Pedidos\Models\Pedido;
use App\Domain\Pedidos\Services\FluxoStatusPedidoService;
use App\Support\Security\BlindIndexService;
use DomainException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class EvolutionBotService
{
    private const OPT_OUT_KEYWORDS = ['PARAR', 'SAIR', 'CANCELAR', 'STOP', 'DESCADASTRAR'];
    private const HUMAN_KEYWORDS = ['HUMANO', 'ATENDENTE', 'FALAR COM ALGUEM', 'FALAR COM ALGUÉM', 'PESSOA', 'SUPORTE', 'MOÇA', 'MOCA', 'RAPAZ', 'AJUDA'];
    private const STATUS_KEYWORDS = ['STATUS', 'ONDE ESTA', 'ONDE ESTÁ', 'DEMORA', 'DEMORANDO', 'CADÊ', 'CADE', 'MEU PEDIDO', 'FALTA MUITO', 'ATRASOU', 'ATRASADO', 'TEMPO', 'CHEGAR', 'PREVISAO', 'PREVISÃO', 'RASTREAR'];
    private const MENU_KEYWORDS = ['CARDAPIO', 'CARDÁPIO', 'VER CARDAPIO', 'VER CARDÁPIO', 'LINK DO CARDAPIO', 'MANDA O CARDAPIO', 'MANDA O CARDÁPIO', 'ENVIAR CARDAPIO', 'MENU', 'CATALOGO', 'CATÁLOGO', 'VER O MENU'];
    private const THANKS_KEYWORDS = ['OBRIGADO', 'OBRIGADA', 'VALEU', 'TOP', 'OBG', 'TMJ', 'SHOW', 'MUITO BOM', 'DELICIA', 'DELÍCIA', 'MARAVILHA', 'EXCELENTE'];
    private const PIX_KEYWORDS = ['PIX', 'MANDA O PIX', 'CHAVE PIX', 'CODIGO PIX', 'CÓDIGO PIX', 'QR CODE', 'QRCODE', 'COPIA E COLA'];
    private const CARTAO_KEYWORDS = ['CARTAO', 'CARTÃO', 'MAQUININHA', 'MAQUINA', 'MÁQUINA', 'DEBITO', 'DÉBITO', 'CREDITO', 'CRÉDITO'];
    private const DINHEIRO_KEYWORDS = ['DINHEIRO', 'NOTA', 'TROCO'];
    private const RECOMPRA_KEYWORDS = ['REPETIR', 'DE SEMPRE', 'O DE SEMPRE', 'PEDIR O DE SEMPRE', 'QUERO O DE SEMPRE', 'REPETIR PEDIDO'];
    private const GREETING_KEYWORDS = ['OI', 'OLA', 'OLÁ', 'OII', 'OIII', 'BOA NOITE', 'BOM DIA', 'BOA TARDE', 'MENU', 'INICIO', 'INÍCIO', 'COMEÇAR', 'COMECAR', 'OPÇÕES', 'OPCOES', 'E AI', 'E AÍ', 'SALVE', 'HEY', 'HELLO', 'HI'];

    public function __construct(
        private readonly BlindIndexService $blindIndex,
        private readonly EvolutionApiService $evolutionApi,
        private readonly RecuperadorVendasService $recuperadorService,
        private readonly AiChatService $aiChatService
    ) {}

    /**
     * Processa mensagem recebida via Webhook da Evolution API.
     * Retorna a resposta que deve ser enviada para o cliente (ou null se não responder).
     */
    public function processarMensagem(Loja $loja, string $telefoneRemetente, string $textoMensagem, array $meta = []): ?string
    {
        $telefoneNormalizado = $this->blindIndex->normalizePhone($telefoneRemetente);
        $telefoneBidx = $this->blindIndex->generateForPhone($telefoneNormalizado);
        $textoUpper = mb_strtoupper(trim($textoMensagem));

        return DB::transaction(function () use ($loja, $telefoneNormalizado, $telefoneBidx, $textoMensagem, $textoUpper, $meta): ?string {
            /** @var BotConversa $conversa */
            $conversa = BotConversa::firstOrCreate(
                ['loja_id' => $loja->id, 'telefone_bidx' => $telefoneBidx],
                [
                    'telefone' => $telefoneNormalizado,
                    'estado' => 'ia_ativa',
                    'contexto_json' => ['inicio' => now()->toIso8601String()],
                ]
            );

            // Registra mensagem recebida
            BotMensagem::create([
                'loja_id' => $loja->id,
                'conversa_id' => $conversa->id,
                'direcao' => 'entrada',
                'mensagem' => $textoMensagem,
            ]);

            // 0. Verifica se o remetente é um MOTOBOY da loja
            $respostaMotoboy = $this->tratarComandoMotoboy($loja, $telefoneBidx, $textoUpper);
            if ($respostaMotoboy !== null) {
                $this->registrarResposta($loja, $conversa, $respostaMotoboy);
                return $respostaMotoboy;
            }

            // 1. Opt-out LGPD (PARAR / SAIR)
            if (in_array($textoUpper, self::OPT_OUT_KEYWORDS, true)) {
                Cliente::where('loja_id', $loja->id)
                    ->where('telefone_bidx', $telefoneBidx)
                    ->update(['aceita_marketing' => false]);

                $resposta = "Você foi descadastrado de mensagens promocionais conforme a LGPD. "
                    . "Avisos essenciais de pedidos em andamento continuarão sendo enviados.";
                $this->registrarResposta($loja, $conversa, $resposta);
                return $resposta;
            }

            // 2. Transbordo para Humano
            foreach (self::HUMAN_KEYWORDS as $palavra) {
                if (str_contains($textoUpper, $palavra)) {
                    $conversa->update(['estado' => 'humano']);
                    $resposta = "Entendido perfeitamente! 🤝 Já notifiquei nossa equipe no balcão que você deseja falar com um atendente.\n\n"
                        . "Um dos nossos colaboradores vai te responder aqui em instantes! Se quiser voltar ao menu automático a qualquer momento, digite *MENU*.";
                    $this->registrarResposta($loja, $conversa, $resposta);
                    return $resposta;
                }
            }

            // Se a conversa estiver sob controle humano, só retorna ao bot se cliente solicitar explicitamente
            if ($conversa->estado === 'humano') {
                if (in_array($textoUpper, ['MENU', 'ROBO', 'BOT', 'VOLTAR', 'INICIO'], true)) {
                    $conversa->update(['estado' => 'ia_ativa']);
                } else {
                    return null; // Deixa o atendente humano conversar livremente
                }
            }

            // 3. Agradecimentos / Feedback Positivo
            foreach (self::THANKS_KEYWORDS as $obg) {
                if (str_contains($textoUpper, $obg)) {
                    $resposta = "Nós que agradecemos de coração pelo carinho e pela preferência! ❤️✨\n\n"
                        . "Esperamos que sua experiência tenha sido incrível. Se precisar de algo mais, estamos sempre por aqui!";
                    $this->registrarResposta($loja, $conversa, $resposta);
                    return $resposta;
                }
            }

            /** @var Cliente|null $cliente */
            $cliente = Cliente::where('loja_id', $loja->id)->where('telefone_bidx', $telefoneBidx)->first();

            // 4. Alteração de Pagamento para Pedidos com PIX Pendente (Recuperação no Chat)
            $configWpp = $loja->config_json['whatsapp'] ?? [];
            $recuperacaoAtiva = (bool) ($configWpp['recuperar_carrinho'] ?? true);
            $recompraAtiva = (bool) ($configWpp['recompra_um_clique'] ?? true);
            $iaAtiva = (bool) ($configWpp['ia_atendente_ativa'] ?? true);

            $pedidoPixPendente = null;
            if ($cliente !== null) {
                $pedidoPixPendente = Pedido::where('loja_id', $loja->id)
                    ->where('cliente_id', $cliente->id)
                    ->where('status', 'novo')
                    ->where('forma_pagamento', 'pix')
                    ->where('pagamento_status', 'pendente')
                    ->where('created_at', '>=', now()->subHours(2))
                    ->orderByDesc('created_at')
                    ->first();
            }

            if ($pedidoPixPendente !== null && $recuperacaoAtiva) {
                // Cliente quer mudar para Cartão na Entrega
                foreach (self::CARTAO_KEYWORDS as $kw) {
                    if (str_contains($textoUpper, $kw)) {
                        $this->recuperadorService->alterarFormaPagamento($pedidoPixPendente, 'cartao_maquina');
                        $codigo = $pedidoPixPendente->codigo_publico;
                        $resposta = "✅ *Combinado!* Alteramos a forma de pagamento do seu pedido *#{$codigo}* para *CARTÃO NA ENTREGA*! 💳\n\n"
                            . "Nosso entregador levará a maquininha. O seu pedido já foi liberado e está entrando na fila da cozinha! 👨‍🍳🔥";
                        $this->registrarResposta($loja, $conversa, $resposta);
                        return $resposta;
                    }
                }

                // Cliente quer mudar para Dinheiro na Entrega
                foreach (self::DINHEIRO_KEYWORDS as $kw) {
                    if (str_contains($textoUpper, $kw)) {
                        $this->recuperadorService->alterarFormaPagamento($pedidoPixPendente, 'dinheiro');
                        $codigo = $pedidoPixPendente->codigo_publico;
                        $resposta = "✅ *Combinado!* Alteramos a forma de pagamento do seu pedido *#{$codigo}* para *DINHEIRO NA ENTREGA*! 💵\n\n"
                            . "Se precisar de troco, basta nos avisar o valor. Seu pedido já foi liberado para preparo na cozinha! 👨‍🍳🔥";
                        $this->registrarResposta($loja, $conversa, $resposta);
                        return $resposta;
                    }
                }

                // Cliente pede o código PIX novamente
                foreach (self::PIX_KEYWORDS as $kw) {
                    if (str_contains($textoUpper, $kw)) {
                        $codigo = $pedidoPixPendente->codigo_publico;
                        $totalFmt = number_format($pedidoPixPendente->total / 100, 2, ',', '.');
                        $copiaCola = (string) ($pedidoPixPendente->pagamento?->pix_copia_cola ?? '');

                        $resposta = "Aqui está o código PIX Copia e Cola do seu pedido *#{$codigo}*:\n\n"
                            . "💵 *Valor:* R$ {$totalFmt}\n\n"
                            . "```{$copiaCola}```\n\n"
                            . "Basta copiar o código acima e colar no seu aplicativo bancário! Assim que você pagar, o pedido é confirmado automaticamente! ⚡";
                        $this->registrarResposta($loja, $conversa, $resposta);
                        return $resposta;
                    }
                }
            }

            // 5. Determina se o cliente pode recomprar o último pedido
            $ultimoPedidoFinalizado = null;
            if ($cliente !== null) {
                $ultimoPedidoFinalizado = Pedido::where('loja_id', $loja->id)
                    ->where('cliente_id', $cliente->id)
                    ->whereIn('status', ['entregue', 'retirado'])
                    ->with(['itens', 'pagamento'])
                    ->orderByDesc('created_at')
                    ->first();
            }
            $podeRecomprar = $recompraAtiva && ($ultimoPedidoFinalizado !== null);

            // 6. Roteamento de Comandos e Opções Numéricas (Alinhado com o menu exibido):
            // Com Recompra: 1=Recompra, 2=Cardápio, 3=Status, 4=Humano
            // Sem Recompra: 1=Cardápio, 2=Status, 3=Humano

            // A) STATUS / RASTREIO
            $pedeStatus = false;
            foreach (self::STATUS_KEYWORDS as $termo) {
                if (str_contains($textoUpper, $termo)) {
                    $pedeStatus = true;
                    break;
                }
            }
            if ($pedeStatus || ($podeRecomprar && $textoUpper === '3') || (!$podeRecomprar && $textoUpper === '2')) {
                $respostaStatus = $this->tratarConsultaStatus($loja, $telefoneBidx);
                $this->registrarResposta($loja, $conversa, $respostaStatus);
                return $respostaStatus;
            }

            // B) HUMANO / SUPORTE
            $pedeHumano = false;
            foreach (self::HUMAN_KEYWORDS as $termo) {
                if (str_contains($textoUpper, $termo)) {
                    $pedeHumano = true;
                    break;
                }
            }
            if ($pedeHumano || ($podeRecomprar && $textoUpper === '4') || (!$podeRecomprar && $textoUpper === '3')) {
                $conversa->update(['estado' => 'humano']);
                $resposta = "Chamando nossa equipe agora mesmo! 👨‍💼 Em instantes alguém do nosso time vai te responder.\n\n"
                    . "Se desejar voltar ao menu automático a qualquer momento, basta digitar *MENU*.";
                $this->registrarResposta($loja, $conversa, $resposta);
                return $resposta;
            }

            // C) CARDÁPIO
            $pedeCardapio = false;
            foreach (self::MENU_KEYWORDS as $termo) {
                if (str_contains($textoUpper, $termo)) {
                    $pedeCardapio = true;
                    break;
                }
            }
            if ($pedeCardapio || ($podeRecomprar && $textoUpper === '2') || (!$podeRecomprar && $textoUpper === '1')) {
                $linkCardapio = url("/p/{$loja->slug}");
                $resposta = "Que maravilha falar com você! 🍔🍕😋\n\n"
                    . "Aqui está o link do nosso cardápio digital completo com fotos, combos, adicionais e promoções:\n"
                    . "👉 {$linkCardapio}\n\n"
                    . "Basta clicar no link, montar o seu pedido em poucos toques e escolher a forma de pagamento que preferir. Estamos prontos para preparar tudo com muito carinho!";
                $this->registrarResposta($loja, $conversa, $resposta);
                return $resposta;
            }

            // D) RE-COMPRA EM 1-CLIQUE ("Pedir o de Sempre")
            $querRecomprar = false;
            if ($podeRecomprar) {
                if ($textoUpper === '1') {
                    $querRecomprar = true;
                } else {
                    foreach (self::RECOMPRA_KEYWORDS as $kw) {
                        if (str_contains($textoUpper, $kw)) {
                            $querRecomprar = true;
                            break;
                        }
                    }
                }
            }

            if ($querRecomprar && $ultimoPedidoFinalizado !== null && $cliente !== null) {
                try {
                    $novoPedido = $this->recuperadorService->recriarPedidoAnterior($loja, $cliente, $ultimoPedidoFinalizado);
                    $novoCodigo = $novoPedido->codigo_publico;
                    $totalFmt = number_format($novoPedido->total / 100, 2, ',', '.');

                    $trackingSig = hash_hmac('sha256', (string) $novoPedido->tracking_token_hash, (string) config('app.key'));
                    $trackingUrl = url("/p/{$loja->slug}/pedidos/{$novoCodigo}?t={$trackingSig}");

                    $end = $novoPedido->endereco_json ?? [];
                    $endTexto = !empty($end['rua']) ? "{$end['rua']}, {$end['numero']} ({$end['bairro']})" : 'Mesmo endereço anterior';

                    if ($novoPedido->forma_pagamento === 'pix') {
                        $copiaCola = (string) ($novoPedido->pagamento?->pix_copia_cola ?? '');
                        $resposta = "🚀 *Show de bola, {$cliente->nome}!* Seu pedido *#{$novoCodigo}* foi confirmado e já entrou na fila da cozinha! 👨‍🍳🔥\n\n"
                            . "💵 *Total:* R$ {$totalFmt}\n"
                            . "📍 *Entrega:* {$endTexto}\n\n"
                            . "Copie o PIX abaixo para pagar no seu banco:\n\n"
                            . "```{$copiaCola}```\n\n"
                            . "⏱️ *Previsão estimada:* 30 a 45 min\n"
                            . "👉 Acompanhe ao vivo pelo GPS:\n{$trackingUrl}";
                    } else {
                        $formaFmt = strtoupper(str_replace('_', ' ', $novoPedido->forma_pagamento));
                        $resposta = "🚀 *Show de bola, {$cliente->nome}!* Seu pedido *#{$novoCodigo}* foi confirmado e já entrou na fila da cozinha! 👨‍🍳🔥\n\n"
                            . "💵 *Total:* R$ {$totalFmt} ({$formaFmt})\n"
                            . "📍 *Entrega:* {$endTexto}\n"
                            . "⏱️ *Previsão estimada:* 30 a 45 min\n"
                            . "👉 Acompanhe ao vivo pelo GPS:\n{$trackingUrl}";
                    }

                    $this->registrarResposta($loja, $conversa, $resposta);
                    return $resposta;
                } catch (DomainException $de) {
                    $linkCardapio = url("/p/{$loja->slug}");
                    $resposta = "Poxa, {$cliente->nome}! " . $de->getMessage() . " 😔\n\n"
                        . "Dê uma olhadinha no nosso cardápio atualizado para escolher outra opção saborosa:\n"
                        . "👉 {$linkCardapio}";
                    $this->registrarResposta($loja, $conversa, $resposta);
                    return $resposta;
                }
            }

            // E) ATENDENTE INTELIGENTE COM IA (Dúvidas do Cardápio, Ingredientes, Horários e Áudios)
            $eApenasSaudacao = in_array($textoUpper, self::GREETING_KEYWORDS, true);

            if (!$eApenasSaudacao && $iaAtiva) {
                $respostaIa = $this->aiChatService->responderDuvidaCardapio($loja, $textoMensagem);
                if (!empty($respostaIa)) {
                    if (!empty($meta['foi_audio'])) {
                        $respostaIa = "🎙️ *Entendi seu áudio!* 😊\n\n" . $respostaIa;
                    }
                    $this->registrarResposta($loja, $conversa, $respostaIa);
                    return $respostaIa;
                }
            }

            // F) SAUDAÇÃO INTELIGENTE (Menu Default alinhado com $podeRecomprar)
            $primeiroNome = $cliente?->nome ? explode(' ', trim($cliente->nome))[0] : 'amigo(a)';
            $linkCardapio = url("/p/{$loja->slug}");

            if ($podeRecomprar && $ultimoPedidoFinalizado !== null && $cliente !== null) {
                $itensResumo = $ultimoPedidoFinalizado->itens->map(fn ($it) => "{$it->qtd}x {$it->nome_snapshot}")->join(', ');
                $totalFmt = number_format($ultimoPedidoFinalizado->total / 100, 2, ',', '.');
                $end = $ultimoPedidoFinalizado->endereco_json ?? [];
                $endTexto = !empty($end['rua']) ? "{$end['rua']}, {$end['numero']}" : 'Endereço cadastrado';

                $resposta = "Olá, *{$primeiroNome}*! Que alegria falar com você de novo no *{$loja->nome}*! 👋🍔\n\n"
                    . "Quer repetir seu último pedido favorito?\n"
                    . "📦 *Itens:* {$itensResumo}\n"
                    . "📍 *Entrega:* {$endTexto}\n"
                    . "💵 *Total:* R$ {$totalFmt}\n\n"
                    . "1️⃣ Digite *1* para *REPETIR O MESMO PEDIDO* agora mesmo! ⚡\n"
                    . "2️⃣ Digite *2* para ver o cardápio completo e escolher outros itens\n"
                    . "3️⃣ Digite *STATUS* para acompanhar pedidos em andamento\n"
                    . "4️⃣ Digite *HUMANO* para falar com um atendente";
            } else {
                $resposta = "Olá, *{$primeiroNome}*! Seja muito bem-vindo(a) ao *{$loja->nome}*! 👋🍔\n\n"
                    . "Como posso te ajudar agora? Escolha uma das opções:\n\n"
                    . "1️⃣ *CARDÁPIO* — Ver itens e fazer pedido\n"
                    . "2️⃣ *STATUS* — Acompanhar pedido em andamento\n"
                    . "3️⃣ *HUMANO* — Falar com um atendente da loja\n\n"
                    . "Ou clique direto no nosso cardápio online:\n👉 {$linkCardapio}";
            }

            $this->registrarResposta($loja, $conversa, $resposta);
            return $resposta;
        });
    }

    /**
     * Resposta inteligente, acolhedora e desescaladora de ansiedade sobre pedidos.
     */
    private function tratarConsultaStatus(Loja $loja, string $telefoneBidx): string
    {
        $cliente = Cliente::where('loja_id', $loja->id)->where('telefone_bidx', $telefoneBidx)->first();

        $pedido = null;
        if ($cliente !== null) {
            $pedido = Pedido::where('loja_id', $loja->id)
                ->where('cliente_id', $cliente->id)
                ->where('created_at', '>=', now()->subHours(12))
                ->orderBy('created_at', 'desc')
                ->first();
        }

        if ($pedido === null) {
            $linkCardapio = url("/p/{$loja->slug}");
            return "Oi! Tudo bem? 😊\n\n"
                . "Não localizei nenhum pedido recente em andamento associado a este número nas últimas horas.\n\n"
                . "Gostaria de dar uma olhadinha no nosso cardápio para fazer um pedido delicioso agora? 😋👇\n"
                . "👉 {$linkCardapio}\n\n"
                . "Se você fez o pedido com outro telefone ou no balcão, digite *HUMANO* para que nossa equipe te localize!";
        }

        $primeiroNome = $cliente?->nome ? explode(' ', trim($cliente->nome))[0] : 'você';
        $codigo = $pedido->codigo_publico;
        $status = $pedido->status;
        $minutosDecorridos = (int) $pedido->created_at->diffInMinutes(now());

        $trackingSignature = hash_hmac('sha256', (string) $pedido->tracking_token_hash, (string) config('app.key'));
        $trackingUrl = url("/p/{$loja->slug}/pedidos/{$codigo}?t={$trackingSignature}");

        // Cenário 1: Já saiu para entrega
        if ($status === 'saiu_entrega') {
            $motoboyNome = $pedido->entregador?->nome ?? 'nosso motoboy parceiro';
            return "Oi, *{$primeiroNome}*! Compreendo super a sua ansiedade! 🥰🛵\n\n"
                . "O seu pedido *#{$codigo}* já está na rua a caminho da sua casa com o entregador *{$motoboyNome}*!\n\n"
                . "Ele está em trânsito e deve buzinar ou chamar na sua porta a qualquer instante! 💨\n\n"
                . "📍 Acompanhe pelo link:\n👉 {$trackingUrl}";
        }

        // Cenário 2: Está pronto aguardando saída ou retirada
        if ($status === 'pronto') {
            if ($pedido->canal === 'retirada') {
                return "Oi, *{$primeiroNome}*! 🎉 Seu pedido *#{$codigo}* já está *PRONTINHO* te esperando no balcão!\n\n"
                    . "Pode vir retirar quando quiser que já está quentinho e separado para você! 🛍️";
            }

            return "Oi, *{$primeiroNome}*! Ótima notícia! 🎉\n\n"
                . "O seu pedido *#{$codigo}* acabou de sair do forno/fogão e já está devidamente embalado na bolsa térmica! 🛍️\n\n"
                . "Nosso entregador já está pegando o seu pacote para sair imediatamente. Vai chegar bem quentinho!";
        }

        // Cenário 3: Em preparo na cozinha
        if (in_array($status, ['aceito', 'em_preparo'], true)) {
            if ($minutosDecorridos > 35) {
                return "Oi, *{$primeiroNome}*! Poxa, te peço mil desculpas pela espera! 🙏❤️\n\n"
                    . "Hoje estamos com uma demanda altíssima de pedidos na cozinha, mas o time está caprichando ao máximo para manter a qualidade e o sabor impecáveis que você merece.\n\n"
                    . "Já conferi aqui e o seu pedido *#{$codigo}* é o **próximo da fila a ser finalizado e embalado**! 🍳🔥 Nosso entregador já está de prontidão aguardando a saída.\n\n"
                    . "Agradeço demais pelo carinho e paciência. Se preferir falar com a gente, basta digitar *HUMANO*!";
            }

            $restante = max(10, 40 - $minutosDecorridos);
            return "Oi, *{$primeiroNome}*! 👨‍🍳🔥\n\n"
                . "O seu pedido *#{$codigo}* está sendo preparado com muito carinho e cuidado pelo nosso time de cozinha para chegar perfeito para você!\n\n"
                . "⏱️ *Tempo estimado restante:* cerca de {$restante} min.\n"
                . "Assim que o motoboy sair com ele, eu te aviso aqui no WhatsApp na mesma hora!\n\n"
                . "👉 Acompanhe ao vivo: {$trackingUrl}";
        }

        // Cenário 4: Pedido novo (aguardando confirmação ou PIX)
        if ($status === 'novo') {
            if ($pedido->forma_pagamento === 'pix' && $pedido->pagamento_status === 'pendente') {
                $copiaCola = (string) ($pedido->pagamento?->pix_copia_cola ?? '');
                return "Oi, *{$primeiroNome}*! Seu pedido *#{$codigo}* está aguardando a confirmação do pagamento via PIX para entrar na chapa.\n\n"
                    . "Copie o código PIX abaixo:\n```{$copiaCola}```\n\n"
                    . "Se desejar pagar em Cartão ou Dinheiro na entrega, basta me responder aqui dizendo *CARTÃO* ou *DINHEIRO*!";
            }

            return "Oi, *{$primeiroNome}*! Seu pedido *#{$codigo}* foi recebido pelo nosso sistema e nossa equipe já está conferindo para iniciar o preparo imediatamente! 🕒✨";
        }

        // Cenário 5: Já entregue
        if (in_array($status, ['entregue', 'retirado', 'servido'], true)) {
            return "Oi, *{$primeiroNome}*! Consta aqui que o pedido *#{$codigo}* já foi finalizado e entregue! 😋❤️\n\n"
                . "Esperamos que tenha gostado muito! Se precisar de algo ou desejar pedir novamente, estamos por aqui!";
        }

        return "Oi, *{$primeiroNome}*! O seu pedido *#{$codigo}* está com status [{$status}]. Se desejar mais detalhes, digite *HUMANO* para falar com o balcão!";
    }

    /**
     * Comandos especiais para entregadores/motoboys da loja.
     */
    private function tratarComandoMotoboy(Loja $loja, string $telefoneBidx, string $textoUpper): ?string
    {
        /** @var Entregador|null $motoboy */
        $motoboy = Entregador::where('loja_id', $loja->id)
            ->where('telefone_bidx', $telefoneBidx)
            ->where('ativo', true)
            ->first();

        if ($motoboy === null) {
            return null;
        }

        if (in_array($textoUpper, ['ENTREGAS', 'PEDIDOS', 'ROTAS', 'MINHAS ENTREGAS', 'FILA'], true)) {
            $pedidosProntos = Pedido::where('loja_id', $loja->id)
                ->where('canal', 'delivery')
                ->whereIn('status', ['pronto', 'saiu_entrega'])
                ->orderBy('created_at', 'asc')
                ->get();

            if ($pedidosProntos->isEmpty()) {
                return "Fala, {$motoboy->nome}! 🛵 No momento não há entregas pendentes na fila. Tudo limpo!";
            }

            $linhas = ["🛵 *Entregas Disponíveis / Em Rota ({$loja->nome}):*\n"];
            foreach ($pedidosProntos as $p) {
                $end = $p->endereco_json ?? [];
                $bairro = $end['bairro'] ?? 'Bairro';
                $num = $end['numero'] ?? '';
                $rua = $end['rua'] ?? '';
                $taxa = number_format($p->taxa_entrega / 100, 2, ',', '.');
                $linhas[] = "• *#{$p->codigo_publico}* [{$p->status}]: {$rua}, {$num} ({$bairro}) - Taxa R$ {$taxa}";
            }

            $linhas[] = "\nPara marcar como entregue, digite: *ENTREGUE {codigo}* (Ex: ENTREGUE {$pedidosProntos->first()->codigo_publico})";
            return implode("\n", $linhas);
        }

        if (str_starts_with($textoUpper, 'ENTREGUE ') || str_starts_with($textoUpper, 'OK ')) {
            $partes = explode(' ', $textoUpper);
            $codigo = trim($partes[1] ?? '');

            if (!empty($codigo)) {
                $pedido = Pedido::where('loja_id', $loja->id)
                    ->where('codigo_publico', $codigo)
                    ->first();

                if ($pedido !== null && in_array($pedido->status, ['pronto', 'saiu_entrega'], true)) {
                    /** @var FluxoStatusPedidoService $fluxo */
                    $fluxo = app(FluxoStatusPedidoService::class);
                    try {
                        $fluxo->transicionar($pedido, 'entregue', null, null, $motoboy->id, 'Confirmado via WhatsApp pelo entregador ' . $motoboy->nome);
                        return "✅ Show de bola, {$motoboy->nome}! Pedido *#{$codigo}* marcado como ENTREGUE com sucesso!";
                    } catch (Exception $e) {
                        return "⚠️ Não foi possível atualizar o pedido #{$codigo}: " . $e->getMessage();
                    }
                }
            }
        }

        return null;
    }

    private function registrarResposta(Loja $loja, BotConversa $conversa, string $resposta): void
    {
        BotMensagem::create([
            'loja_id' => $loja->id,
            'conversa_id' => $conversa->id,
            'direcao' => 'saida',
            'mensagem' => $resposta,
        ]);
    }
}
