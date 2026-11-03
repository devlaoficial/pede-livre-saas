<?php

declare(strict_types=1);

namespace App\Domain\Bot\Services;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Bot\Models\BotConversa;
use App\Domain\Bot\Models\BotMensagem;
use App\Domain\Clientes\Models\Cliente;
use App\Domain\Entregas\Models\Entregador;
use App\Domain\Fidelidade\Models\Avaliacao;
use App\Domain\Pedidos\Events\PedidoAtualizado;
use App\Domain\Pedidos\Models\Pedido;
use App\Domain\Pedidos\Models\PedidoEvento;
use App\Domain\Pedidos\Services\FluxoStatusPedidoService;
use App\Support\Security\BlindIndexService;
use DomainException;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class EvolutionBotService
{
    private const OPT_OUT_KEYWORDS = ['PARAR', 'SAIR', 'CANCELAR', 'STOP', 'DESCADASTRAR'];
    private const HUMAN_KEYWORDS = ['HUMANO', 'ATENDENTE', 'FALAR COM ALGUEM', 'FALAR COM ALGUÉM', 'PESSOA', 'SUPORTE', 'MOÇA', 'MOCA', 'RAPAZ', 'AJUDA'];
    private const STATUS_KEYWORDS = [
        'STATUS', 'ONDE ESTA', 'ONDE ESTÁ', 'DEMORA', 'DEMORANDO', 'CADÊ', 'CADE',
        'MEU PEDIDO', 'FALTA MUITO', 'ATRASOU', 'ATRASADO', 'TEMPO', 'CHEGAR',
        'PREVISAO', 'PREVISÃO', 'RASTREAR', 'RASTREIO', 'ACOMPANHAR', 'ACOMPANHAMENTO',
        'ANDAMENTO', 'MEU LANCHE', 'MINHA PIZZA', 'JA SAIU', 'JÁ SAIU',
        'SAIU PRA ENTREGA', 'SAIU PARA ENTREGA', 'MOTOBOY', 'ENTREGADOR',
    ];
    private const DELIVERY_CONFIRMED_KEYWORDS = [
        'JA CHEGOU', 'JÁ CHEGOU', 'JA RECEBI', 'JÁ RECEBI', 'JA FOI ENTREGUE',
        'JÁ FOI ENTREGUE', 'PEDIDO ENTREGUE', 'FOI ENTREGUE', 'CHEGOU AQUI',
        'CHEGOU SIM', 'CHEGOU TUDO CERTO', 'JA PEGUEI', 'JÁ PEGUEI', 'JA RETIREI',
        'JÁ RETIREI', 'RECEBI MEU PEDIDO', 'PEDIDO JA CHEGOU', 'PEDIDO JÁ CHEGOU',
        'CONFIRMAR ENTREGA', 'ENTREGA CONFIRMADA', 'SIM JA CHEGOU', 'SIM JÁ CHEGOU',
        'SIM JA RECEBI', 'SIM JÁ RECEBI', 'JA ENTREGOU', 'JÁ ENTREGOU',
    ];
    private const DELIVERY_NOT_YET_KEYWORDS = [
        'AINDA NAO', 'AINDA NÃO', 'NAO CHEGOU', 'NÃO CHEGOU', 'NAO RECEBI', 'NÃO RECEBI', 'NAO VEIO', 'NÃO VEIO',
    ];
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
        $candidatosBidx = $this->blindIndex->generatePhoneCandidates($telefoneRemetente);
        $textoUpper = mb_strtoupper(trim($textoMensagem));

        return DB::transaction(function () use ($loja, $telefoneNormalizado, $telefoneBidx, $candidatosBidx, $textoMensagem, $textoUpper, $meta): ?string {
            /** @var BotConversa|null $conversa */
            $conversa = BotConversa::where('loja_id', $loja->id)
                ->whereIn('telefone_bidx', $candidatosBidx)
                ->orderByDesc('updated_at')
                ->first();

            if ($conversa === null) {
                $conversa = BotConversa::create([
                    'loja_id' => $loja->id,
                    'telefone_bidx' => $telefoneBidx,
                    'telefone' => $telefoneNormalizado,
                    'estado' => 'ia_ativa',
                    'contexto_json' => ['inicio' => now()->toIso8601String()],
                ]);
            }

            // Registra mensagem recebida
            BotMensagem::create([
                'loja_id' => $loja->id,
                'conversa_id' => $conversa->id,
                'direcao' => 'entrada',
                'mensagem' => $textoMensagem,
            ]);

            // 0. Verifica se o remetente é um MOTOBOY da loja (usando todas as variações de 8/9 dígitos)
            $respostaMotoboy = $this->tratarComandoMotoboy($loja, $candidatosBidx, $textoUpper);
            if ($respostaMotoboy !== null) {
                $this->registrarResposta($loja, $conversa, $respostaMotoboy);
                return $respostaMotoboy;
            }

            // 1. Opt-out LGPD (PARAR / SAIR)
            if (in_array($textoUpper, self::OPT_OUT_KEYWORDS, true)) {
                Cliente::where('loja_id', $loja->id)
                    ->whereIn('telefone_bidx', $candidatosBidx)
                    ->update(['aceita_marketing' => false]);

                $resposta = "Você foi descadastrado de mensagens promocionais conforme a LGPD. "
                    . "Avisos essenciais de pedidos em andamento continuarão sendo enviados.";
                $this->registrarResposta($loja, $conversa, $resposta);
                return $resposta;
            }

            // Localiza todos os registros de Cliente vinculados às variações de 8/9 dígitos deste telefone
            $clientes = Cliente::where('loja_id', $loja->id)
                ->whereIn('telefone_bidx', $candidatosBidx)
                ->orderByDesc('ultimo_pedido_em')
                ->get();

            /** @var Cliente|null $cliente */
            $cliente = $clientes->first();
            $clienteIds = $clientes->pluck('id')->all();

            // Localiza o pedido ativo ou mais recente (por cliente_id, contexto da conversa ou código #ABCD citado no texto)
            $pedidoRecente = $this->localizarPedidoCliente($loja, $clienteIds, $conversa, $textoUpper);
            if ($pedidoRecente !== null && $cliente === null && $pedidoRecente->cliente !== null) {
                $cliente = $pedidoRecente->cliente;
                $clienteIds[] = $cliente->id;
            }

            $primeiroNome = $cliente?->nome ? explode(' ', trim($cliente->nome))[0] : 'amigo(a)';
            $contexto = $conversa->contexto_json ?? [];

            // 1.5 CONFIRMAÇÃO DE ENTREGA PELO CLIENTE NO WHATSAPP
            // (Funciona mesmo se a conversa estiver em modo humano ou se o lojista esqueceu de avançar no painel!)
            $respostaConfirmacaoEntrega = $this->tratarConfirmacaoEntregaPeloCliente(
                $loja,
                $conversa,
                $pedidoRecente,
                $primeiroNome,
                $textoUpper
            );
            if ($respostaConfirmacaoEntrega !== null) {
                $this->registrarResposta($loja, $conversa, $respostaConfirmacaoEntrega);
                return $respostaConfirmacaoEntrega;
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

            // 2.1 Se o cliente deu nota 1-3 anteriormente e está enviando os detalhes da insatisfação
            if (!empty($contexto['aguardando_feedback_negativo']) && !empty($contexto['avaliacao_pedido_id'])) {
                $pedidoId = (int) $contexto['avaliacao_pedido_id'];
                $avaliacao = Avaliacao::where('pedido_id', $pedidoId)->latest()->first();
                if ($avaliacao !== null) {
                    $comentAtual = trim((string) ($avaliacao->comentario ?? ''));
                    $novoComent = $comentAtual !== ''
                        ? "{$comentAtual}\n\nDetalhes do cliente: {$textoMensagem}"
                        : $textoMensagem;
                    $avaliacao->update(['comentario' => $novoComent]);
                }

                $conversa->estado = 'humano';
                $conversa->contexto_json = array_merge($contexto, [
                    'aguardando_feedback_negativo' => false,
                    'aguardando_avaliacao' => false,
                ]);
                $conversa->save();

                $resposta = "Muito obrigado por nos explicar em detalhes, *{$primeiroNome}*! 🙏\n\n"
                    . "Seu relato foi anexado diretamente ao pedido e nosso gerente já foi acionado para te dar total atenção e resolver isso. Conte conosco!";
                $this->registrarResposta($loja, $conversa, $resposta);
                return $resposta;
            }

            // Se a conversa estiver sob controle humano, ainda permite consulta explícita de STATUS se o cliente digitar STATUS
            if ($conversa->estado === 'humano') {
                if (in_array($textoUpper, ['MENU', 'ROBO', 'BOT', 'VOLTAR', 'INICIO'], true)) {
                    $conversa->update(['estado' => 'ia_ativa']);
                } elseif ($textoUpper === 'STATUS' || str_contains($textoUpper, 'MEU PEDIDO') || str_contains($textoUpper, 'CADÊ MEU PEDIDO') || str_contains($textoUpper, 'CADE MEU PEDIDO')) {
                    $respostaStatus = $this->montarRespostaStatusPedido($loja, $pedidoRecente, $primeiroNome);
                    $this->registrarResposta($loja, $conversa, $respostaStatus);
                    return $respostaStatus;
                } else {
                    return null; // Deixa o atendente humano conversar livremente
                }
            }

            // 2.5 AVALIAÇÃO NPS & FEEDBACK REVERSO (GOOGLE MAPS OU RETENÇÃO PRIVADA)
            $aguardandoAvaliacao = !empty($contexto['aguardando_avaliacao']) && !empty($contexto['avaliacao_pedido_id']);
            $avaliacaoEnviadaEm = isset($contexto['avaliacao_enviada_em']) ? Carbon::parse($contexto['avaliacao_enviada_em']) : null;
            $avaliacaoValida = $aguardandoAvaliacao && ($avaliacaoEnviadaEm === null || $avaliacaoEnviadaEm->diffInHours(now()) <= 24);

            if ($avaliacaoValida) {
                $notaDetectada = null;
                if (preg_match('/\b([1-5])\b/', $textoMensagem, $m)) {
                    $notaDetectada = (int) $m[1];
                } elseif (in_array($textoUpper, ['EXCELENTE', 'OTIMO', 'ÓTIMO', 'PERFEITO', 'MARAVILHOSO', 'ADOREI', 'MUITO BOM', '5 ESTRELAS'], true)) {
                    $notaDetectada = 5;
                } elseif (in_array($textoUpper, ['BOM', 'GOSTEI', '4 ESTRELAS'], true)) {
                    $notaDetectada = 4;
                } elseif (in_array($textoUpper, ['REGULAR', 'MEDIO', 'MÉDIO', 'MAIS OU MENOS', '3 ESTRELAS'], true)) {
                    $notaDetectada = 3;
                } elseif (in_array($textoUpper, ['RUIM', '2 ESTRELAS'], true)) {
                    $notaDetectada = 2;
                } elseif (in_array($textoUpper, ['PESSIMO', 'PÉSSIMO', 'HORRIVEL', 'HORRÍVEL', '1 ESTRELA'], true)) {
                    $notaDetectada = 1;
                }

                if ($notaDetectada !== null) {
                    $pedidoId = (int) $contexto['avaliacao_pedido_id'];
                    $pedidoAvaliado = Pedido::find($pedidoId);

                    if ($pedidoAvaliado !== null) {
                        Avaliacao::updateOrCreate(
                            ['loja_id' => $loja->id, 'pedido_id' => $pedidoAvaliado->id],
                            [
                                'nota' => $notaDetectada,
                                'comentario' => $textoMensagem,
                            ]
                        );

                        $googleMapsUrl = trim((string) ($loja->config_json['whatsapp']['google_maps_review_url'] ?? $loja->config_json['google_maps_review_url'] ?? ''));

                        if ($notaDetectada >= 4) {
                            $conversa->contexto_json = array_merge($contexto, [
                                'aguardando_avaliacao' => false,
                                'ultima_nota' => $notaDetectada,
                            ]);
                            $conversa->save();

                            if (!empty($googleMapsUrl)) {
                                $resposta = "Sensacional, *{$primeiroNome}*! Ficamos imensamente felizes que sua experiência foi nota {$notaDetectada}! ⭐⭐⭐⭐⭐\n\n"
                                    . "Sua opinião faz toda a diferença para o nosso restaurante local. Você poderia nos dar uma forcinha deixando essa avaliação 5 estrelas no nosso *Google Maps*? Leva menos de 30 segundos! 🙏❤️\n\n"
                                    . "👉 {$googleMapsUrl}\n\n"
                                    . "Muito obrigado pelo carinho e preferência! Até o próximo pedido! 🍔✨";
                            } else {
                                $resposta = "Sensacional, *{$primeiroNome}*! Ficamos imensamente felizes com sua nota {$notaDetectada} estrelas! ⭐⭐⭐⭐⭐\n\n"
                                    . "Muito obrigado pelo carinho e preferência! Conte sempre com o *{$loja->nome}* para sua próxima refeição! ❤️🍕";
                            }

                            $this->registrarResposta($loja, $conversa, $resposta);
                            return $resposta;
                        }

                        PedidoEvento::create([
                            'pedido_id' => $pedidoAvaliado->id,
                            'status_de' => $pedidoAvaliado->status,
                            'status_para' => 'alerta_insatisfacao_nps',
                            'observacao' => "Cliente avaliou com nota {$notaDetectada}/5 no WhatsApp: '{$textoMensagem}'",
                        ]);

                        $conversa->estado = 'humano';
                        $conversa->contexto_json = array_merge($contexto, [
                            'aguardando_avaliacao' => false,
                            'aguardando_feedback_negativo' => true,
                            'ultima_nota' => $notaDetectada,
                        ]);
                        $conversa->save();

                        $resposta = "Poxa, *{$primeiroNome}*... Sentimos muito por isso. 😔 Essa com certeza não é a experiência que desejamos para você no *{$loja->nome}*.\n\n"
                            . "Registrei sua nota {$notaDetectada} e nosso gerente de atendimento foi alertado agora mesmo.\n\n"
                            . "Se você puder me contar aqui em poucas palavras o que poderíamos ter feito melhor (se foi a comida, a entrega ou o tempo), nossa gerência vai ler com prioridade máxima para acertar com você! 🙏";

                        $this->registrarResposta($loja, $conversa, $resposta);
                        return $resposta;
                    }
                }
            }

            // 3. Agradecimentos / Feedback Positivo
            foreach (self::THANKS_KEYWORDS as $obg) {
                if ($textoUpper === $obg || str_starts_with($textoUpper, $obg . ' ')) {
                    $resposta = "Nós que agradecemos de coração pelo carinho e pela preferência, *{$primeiroNome}*! ❤️✨\n\n"
                        . "Esperamos que sua experiência tenha sido incrível. Se precisar de algo mais, estamos sempre por aqui!";
                    $this->registrarResposta($loja, $conversa, $resposta);
                    return $resposta;
                }
            }

            // 4. Alteração de Pagamento para Pedidos com PIX Pendente (Recuperação no Chat)
            $configWpp = $loja->config_json['whatsapp'] ?? [];
            $recuperacaoAtiva = (bool) ($configWpp['recuperar_carrinho'] ?? true);
            $recompraAtiva = (bool) ($configWpp['recompra_um_clique'] ?? true);
            $iaAtiva = (bool) ($configWpp['ia_atendente_ativa'] ?? true);

            $pedidoPixPendente = null;
            if ($pedidoRecente !== null && $pedidoRecente->status === 'novo' && $pedidoRecente->forma_pagamento === 'pix' && $pedidoRecente->pagamento_status === 'pendente') {
                $pedidoPixPendente = $pedidoRecente;
            } elseif (!empty($clienteIds)) {
                $pedidoPixPendente = Pedido::where('loja_id', $loja->id)
                    ->whereIn('cliente_id', $clienteIds)
                    ->where('status', 'novo')
                    ->where('forma_pagamento', 'pix')
                    ->where('pagamento_status', 'pendente')
                    ->where('created_at', '>=', now()->subHours(3))
                    ->orderByDesc('created_at')
                    ->first();
            }

            if ($pedidoPixPendente !== null && $recuperacaoAtiva) {
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

            // 5. Determina se o cliente pode recomprar o último pedido finalizado
            $ultimoPedidoFinalizado = null;
            if (!empty($clienteIds)) {
                $ultimoPedidoFinalizado = Pedido::where('loja_id', $loja->id)
                    ->whereIn('cliente_id', $clienteIds)
                    ->whereIn('status', ['entregue', 'retirado', 'servido'])
                    ->with(['itens', 'pagamento'])
                    ->orderByDesc('created_at')
                    ->first();
            }
            $podeRecomprar = $recompraAtiva && ($ultimoPedidoFinalizado !== null);

            // 6. Roteamento de Comandos e Opções Numéricas
            // A) STATUS / RASTREIO EM TEMPO REAL
            $pedeStatus = false;
            foreach (self::STATUS_KEYWORDS as $termo) {
                if (str_contains($textoUpper, $termo)) {
                    $pedeStatus = true;
                    break;
                }
            }
            // Se o usuário digitou apenas um código de pedido de 4 caracteres (ex: "#4F9A" ou "4F9A") e esse pedido existe
            if (!$pedeStatus && preg_match('/^#?([A-Z0-9]{4})$/', $textoUpper) && $pedidoRecente !== null) {
                $pedeStatus = true;
            }

            if ($pedeStatus || ($podeRecomprar && $textoUpper === '3') || (!$podeRecomprar && $textoUpper === '2')) {
                $respostaStatus = $this->montarRespostaStatusPedido($loja, $pedidoRecente, $primeiroNome);
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

                    $ctxAtual = $conversa->contexto_json ?? [];
                    $ctxAtual['ultimo_pedido_id'] = $novoPedido->id;
                    $ctxAtual['ultimo_codigo_pedido'] = $novoCodigo;
                    $conversa->update(['contexto_json' => $ctxAtual]);

                    if ($novoPedido->forma_pagamento === 'pix') {
                        $copiaCola = (string) ($novoPedido->pagamento?->pix_copia_cola ?? '');
                        $resposta = "🚀 *Show de bola, {$cliente->nome}!* Seu pedido *#{$novoCodigo}* foi confirmado e já entrou na fila da cozinha! 👨‍🍳🔥\n\n"
                            . "💵 *Total:* R$ {$totalFmt}\n"
                            . "📍 *Entrega:* {$endTexto}\n\n"
                            . "Copie o PIX abaixo para pagar no seu banco:\n\n"
                            . "```{$copiaCola}```\n\n"
                            . "⏱️ *Previsão estimada:* 30 a 45 min\n"
                            . "👉 Acompanhe ao vivo:\n{$trackingUrl}";
                    } else {
                        $formaFmt = strtoupper(str_replace('_', ' ', $novoPedido->forma_pagamento));
                        $resposta = "🚀 *Show de bola, {$cliente->nome}!* Seu pedido *#{$novoCodigo}* foi confirmado e já entrou na fila da cozinha! 👨‍🍳🔥\n\n"
                            . "💵 *Total:* R$ {$totalFmt} ({$formaFmt})\n"
                            . "📍 *Entrega:* {$endTexto}\n"
                            . "⏱️ *Previsão estimada:* 30 a 45 min\n"
                            . "👉 Acompanhe ao vivo:\n{$trackingUrl}";
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

            // E) ATENDENTE INTELIGENTE COM IA (Com contexto do Cardápio E do Pedido Ativo em Tempo Real!)
            $eApenasSaudacao = in_array($textoUpper, self::GREETING_KEYWORDS, true);

            if (!$eApenasSaudacao && $iaAtiva) {
                $respostaIa = $this->aiChatService->responderDuvidaCardapio($loja, $textoMensagem, [], $pedidoRecente);
                if (!empty($respostaIa)) {
                    if (!empty($meta['foi_audio'])) {
                        $respostaIa = "🎙️ *Entendi seu áudio!* 😊\n\n" . $respostaIa;
                    }
                    $this->registrarResposta($loja, $conversa, $respostaIa);
                    return $respostaIa;
                }
            }

            // F) SAUDAÇÃO INTELIGENTE (Se tiver pedido em andamento agora, já destaca no topo da saudação!)
            $linkCardapio = url("/p/{$loja->slug}");
            $blocoPedidoAtivo = '';
            if ($pedidoRecente !== null && !in_array($pedidoRecente->status, ['entregue', 'retirado', 'servido', 'cancelado'], true)) {
                $sigAtivo = hash_hmac('sha256', (string) $pedidoRecente->tracking_token_hash, (string) config('app.key'));
                $urlAtivo = url("/p/{$loja->slug}/pedidos/{$pedidoRecente->codigo_publico}?t={$sigAtivo}");
                $blocoPedidoAtivo = "📦 *Seu Pedido #{$pedidoRecente->codigo_publico} está em andamento* (Status: *" . strtoupper($pedidoRecente->status) . "*).\n"
                    . "📍 Acompanhe ao vivo: {$urlAtivo}\n"
                    . "✅ _Se já recebeu seu pedido, responda *JÁ RECEBI*!_\n\n";
            }

            if ($podeRecomprar && $ultimoPedidoFinalizado !== null && $cliente !== null) {
                $itensResumo = $ultimoPedidoFinalizado->itens->map(fn ($it) => "{$it->qtd}x {$it->nome_snapshot}")->join(', ');
                $totalFmt = number_format($ultimoPedidoFinalizado->total / 100, 2, ',', '.');
                $end = $ultimoPedidoFinalizado->endereco_json ?? [];
                $endTexto = !empty($end['rua']) ? "{$end['rua']}, {$end['numero']}" : 'Endereço cadastrado';

                $resposta = "Olá, *{$primeiroNome}*! Que alegria falar com você no *{$loja->nome}*! 👋🍔\n\n"
                    . $blocoPedidoAtivo
                    . "Quer repetir seu último pedido favorito?\n"
                    . "📦 *Itens:* {$itensResumo}\n"
                    . "📍 *Entrega:* {$endTexto}\n"
                    . "💵 *Total:* R$ {$totalFmt}\n\n"
                    . "1️⃣ Digite *1* para *REPETIR O MESMO PEDIDO* agora mesmo! ⚡\n"
                    . "2️⃣ Digite *2* para ver o cardápio completo\n"
                    . "3️⃣ Digite *STATUS* para acompanhar seu pedido em tempo real\n"
                    . "4️⃣ Digite *HUMANO* para falar com um atendente";
            } else {
                $resposta = "Olá, *{$primeiroNome}*! Seja muito bem-vindo(a) ao *{$loja->nome}*! 👋🍔\n\n"
                    . $blocoPedidoAtivo
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
     * Localiza o pedido ativo ou mais recente do cliente usando:
     * 1) Código #ABCD citado na mensagem;
     * 2) Todos os IDs de cliente das variações de telefone (com e sem 9º dígito);
     * 3) Pedido salvo no contexto da conversa (ultimo_pedido_id / confirmacao_pedido_id);
     * 4) Fallback pelos últimos 8 dígitos do telefone nos pedidos recentes da loja.
     */
    public function localizarPedidoCliente(
        Loja $loja,
        array $clienteIds,
        ?BotConversa $conversa = null,
        string $textoUpper = ''
    ): ?Pedido {
        // 1. Verifica se o cliente digitou explicitamente um código de pedido (ex: "#4F9A", "PEDIDO 4F9A")
        if ($textoUpper !== '' && preg_match('/(?:#|\bPEDIDO\s+|\bCODIGO\s+|\bCÓDIGO\s+|^)([A-Z0-9]{4})\b/', $textoUpper, $matches)) {
            $codigoBuscado = $matches[1];
            $ignorar = ['MENU', 'OI', 'OLA', 'OLÁ', 'PARA', 'SAIR', 'TUDO', 'BORA', 'AQUI', 'HOJE', 'MAIS', 'ONDE', 'CADE', 'CADÊ', 'TAXA'];
            if (!in_array($codigoBuscado, $ignorar, true)) {
                $porCodigo = Pedido::where('loja_id', $loja->id)
                    ->where('codigo_publico', $codigoBuscado)
                    ->where('created_at', '>=', now()->subHours(72))
                    ->with(['cliente', 'itens', 'pagamento', 'entregador'])
                    ->latest('created_at')
                    ->first();

                if ($porCodigo !== null) {
                    return $porCodigo;
                }
            }
        }

        // 2. Busca pedidos das últimas 36h associados a qualquer variação de Cliente ID
        if (!empty($clienteIds)) {
            // Prioriza pedidos ativos (não finalizados/cancelados)
            $pedidoAtivo = Pedido::where('loja_id', $loja->id)
                ->whereIn('cliente_id', $clienteIds)
                ->whereNotIn('status', ['entregue', 'retirado', 'servido', 'cancelado'])
                ->where('created_at', '>=', now()->subHours(24))
                ->with(['cliente', 'itens', 'pagamento', 'entregador'])
                ->orderByDesc('created_at')
                ->first();

            if ($pedidoAtivo !== null) {
                return $pedidoAtivo;
            }

            $pedidoRecente = Pedido::where('loja_id', $loja->id)
                ->whereIn('cliente_id', $clienteIds)
                ->where('created_at', '>=', now()->subHours(36))
                ->with(['cliente', 'itens', 'pagamento', 'entregador'])
                ->orderByDesc('created_at')
                ->first();

            if ($pedidoRecente !== null) {
                return $pedidoRecente;
            }
        }

        // 3. Verifica se a BotConversa possui confirmacao_pedido_id ou ultimo_pedido_id no contexto
        $ctx = $conversa?->contexto_json ?? [];
        $pedidoContextoId = $ctx['confirmacao_pedido_id'] ?? ($ctx['ultimo_pedido_id'] ?? null);
        if (!empty($pedidoContextoId)) {
            $porContexto = Pedido::where('loja_id', $loja->id)
                ->where('id', (int) $pedidoContextoId)
                ->with(['cliente', 'itens', 'pagamento', 'entregador'])
                ->first();

            if ($porContexto !== null) {
                return $porContexto;
            }
        }

        // 4. Fallback: compara os últimos 8 dígitos do telefone nos pedidos das últimas 24h da loja
        // (protege contra qualquer cadastro antigo salvo fora do padrão antes da normalização)
        if ($conversa !== null && strlen($conversa->telefone) >= 8) {
            $ultimos8 = substr(preg_replace('/\D/', '', $conversa->telefone), -8);
            $candidatosRecentes = Pedido::where('loja_id', $loja->id)
                ->where('created_at', '>=', now()->subHours(24))
                ->with(['cliente', 'itens', 'pagamento', 'entregador'])
                ->orderByDesc('created_at')
                ->limit(40)
                ->get();

            foreach ($candidatosRecentes as $cand) {
                $telCand = preg_replace('/\D/', '', (string) ($cand->cliente?->telefone ?? ''));
                if ($telCand !== '' && str_ends_with($telCand, $ultimos8)) {
                    return $cand;
                }
            }
        }

        return null;
    }

    /**
     * Detecta quando o cliente avisa no WhatsApp que o pedido já foi entregue (caso o lojista tenha esquecido de dar baixa no painel)
     * e atualiza automaticamente o status do pedido no Kanban do lojista em tempo real.
     */
    private function tratarConfirmacaoEntregaPeloCliente(
        Loja $loja,
        BotConversa $conversa,
        ?Pedido $pedido,
        string $primeiroNome,
        string $textoUpper
    ): ?string {
        if ($pedido === null) {
            return null;
        }

        // Só processa se o pedido ainda estiver aberto/em andamento no painel do lojista
        if (!in_array($pedido->status, ['novo', 'aceito', 'em_preparo', 'pronto', 'saiu_entrega'], true)) {
            return null;
        }

        $ctx = $conversa->contexto_json ?? [];
        $aguardandoConfirmacao = !empty($ctx['aguardando_confirmacao_entrega']);

        // 1. Verifica se o cliente respondeu que AINDA NÃO chegou quando perguntado
        if ($aguardandoConfirmacao) {
            foreach (self::DELIVERY_NOT_YET_KEYWORDS as $naoKw) {
                if (str_contains($textoUpper, $naoKw)) {
                    $conversa->estado = 'humano';
                    $ctx['aguardando_confirmacao_entrega'] = false;
                    $conversa->contexto_json = $ctx;
                    $conversa->save();

                    PedidoEvento::create([
                        'pedido_id' => $pedido->id,
                        'status_de' => $pedido->status,
                        'status_para' => $pedido->status,
                        'observacao' => '⚠️ Cliente informou via WhatsApp que o pedido ainda NÃO foi entregue.',
                    ]);

                    try { if (config('broadcasting.default') && !in_array(config('broadcasting.default'), ['null', 'log'], true)) { event(new PedidoAtualizado($pedido, $pedido->status)); } } catch (\Throwable) {}

                    return "Obrigado por nos avisar, *{$primeiroNome}*! 🙏\n\n"
                        . "Acabei de acionar nossa equipe no balcão com prioridade máxima para verificar a localização exata do seu pedido *#{$pedido->codigo_publico}* com o entregador. Já vamos te responder aqui em instantes!";
                }
            }
        }

        // 2. Verifica se o cliente afirmou que JÁ RECEBEU / JÁ FOI ENTREGUE
        $confirmouEntrega = false;
        foreach (self::DELIVERY_CONFIRMED_KEYWORDS as $kw) {
            if (str_contains($textoUpper, $kw)) {
                $confirmouEntrega = true;
                break;
            }
        }

        if (!$confirmouEntrega && $aguardandoConfirmacao) {
            if (in_array($textoUpper, ['SIM', 'S', 'CHEGOU', 'RECEBI', 'ENTREGUE', 'OK', 'JA', 'JÁ'], true)) {
                $confirmouEntrega = true;
            }
        }

        if (!$confirmouEntrega) {
            return null;
        }

        // Determina o status final correto para o canal do pedido
        $statusFinal = match ($pedido->canal) {
            'retirada' => 'retirado',
            'mesa' => 'servido',
            default => 'entregue',
        };

        /** @var FluxoStatusPedidoService $fluxo */
        $fluxo = app(FluxoStatusPedidoService::class);

        try {
            // Se o pagamento era na entrega (dinheiro ou cartão maquininha), já marca como aprovado
            if (in_array($pedido->forma_pagamento, ['dinheiro', 'cartao_maquina'], true) && $pedido->pagamento_status === 'pendente') {
                $pedido->pagamento_status = 'aprovado';
                $pedido->save();
                if ($pedido->pagamento !== null) {
                    $pedido->pagamento->update(['status' => 'aprovado']);
                }
            }

            // Transiciona o pedido no banco e dispara evento PedidoAtualizado para o Kanban do lojista!
            // Passamos $notificarWhatsApp = false porque já vamos responder com a mensagem completa + pesquisa NPS abaixo.
            $fluxo->transicionar(
                $pedido,
                $statusFinal,
                null,
                null,
                null,
                'Entrega confirmada pelo próprio cliente via WhatsApp.',
                false
            );

            // Ativa contexto de avaliação NPS para que o cliente já possa dar nota de 1 a 5
            $ctx['aguardando_confirmacao_entrega'] = false;
            $ctx['aguardando_avaliacao'] = true;
            $ctx['avaliacao_pedido_id'] = $pedido->id;
            $ctx['avaliacao_enviada_em'] = now()->toIso8601String();
            $conversa->contexto_json = $ctx;
            $conversa->save();

            return "Maravilha, *{$primeiroNome}*! 🎉 Muito obrigado por confirmar!\n\n"
                . "Já atualizamos o seu pedido *#{$pedido->codigo_publico}* como *ENTREGUE* no nosso painel! ✅🍔\n\n"
                . "Esperamos que a sua refeição esteja deliciosa! De *1 a 5 estrelas*, que nota você daria para o seu pedido hoje?\n\n"
                . "⭐ 5 - Excelente\n"
                . "⭐ 4 - Muito bom\n"
                . "⭐ 3 - Regular\n"
                . "⭐ 2 - Ruim\n"
                . "⭐ 1 - Péssimo\n\n"
                . "_(Basta responder com o número de 1 a 5)_";
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Monta resposta detalhada em tempo real sobre o status do pedido do cliente.
     */
    private function montarRespostaStatusPedido(Loja $loja, ?Pedido $pedido, string $primeiroNome): string
    {
        if ($pedido === null) {
            $linkCardapio = url("/p/{$loja->slug}");
            return "Oi, *{$primeiroNome}*! Tudo bem? 😊\n\n"
                . "Não localizei nenhum pedido recente associado a este número nas últimas horas.\n\n"
                . "💡 *Você tem o código do pedido?* Basta digitar o código de 4 letras/números aqui (ex: *#A1B2*) que eu localizo na hora!\n\n"
                . "Ou se quiser fazer um pedido agora, acesse nosso cardápio digital:\n"
                . "👉 {$linkCardapio}";
        }

        $pedido->loadMissing(['itens', 'entregador', 'pagamento']);
        $codigo = $pedido->codigo_publico;
        $status = $pedido->status;
        $minutosDecorridos = (int) $pedido->created_at->diffInMinutes(now());
        $itensResumo = $pedido->itens->map(fn ($it) => "• {$it->qtd}x {$it->nome_snapshot}")->join("\n");
        $totalFmt = number_format($pedido->total / 100, 2, ',', '.');

        $trackingSignature = hash_hmac('sha256', (string) $pedido->tracking_token_hash, (string) config('app.key'));
        $trackingUrl = url("/p/{$loja->slug}/pedidos/{$codigo}?t={$trackingSignature}");

        // Cenário 1: Já saiu para entrega
        if ($status === 'saiu_entrega') {
            $motoboyNome = $pedido->entregador?->nome ?? 'nosso entregador parceiro';
            return "🛵💨 *PEDIDO #{$codigo} EM ROTA DE ENTREGA!*\n\n"
                . "Oi, *{$primeiroNome}*! O seu pedido já saiu da loja e está a caminho com *{$motoboyNome}*!\n\n"
                . "📋 *Itens:*\n{$itensResumo}\n"
                . "💵 *Total:* R$ {$totalFmt}\n\n"
                . "📍 *Acompanhe ao vivo no mapa/painel:*\n👉 {$trackingUrl}\n\n"
                . "✅ _Se o entregador já tiver chegado e entregue seu pedido, basta responder *JÁ RECEBI* aqui!_";
        }

        // Cenário 2: Está pronto aguardando saída ou retirada
        if ($status === 'pronto') {
            if ($pedido->canal === 'retirada' || $pedido->canal === 'balcao') {
                return "🎉 *PEDIDO #{$codigo} PRONTO PARA RETIRADA!*\n\n"
                    . "Oi, *{$primeiroNome}*! Seu pedido já está *PRONTINHO* esperando por você no balcão do *{$loja->nome}*! 🛍️\n\n"
                    . "📋 *Itens:*\n{$itensResumo}\n\n"
                    . "📍 *Detalhes:* {$trackingUrl}\n"
                    . "✅ _Quando retirar, você pode responder *JÁ RETIREI* aqui!_";
            }

            return "🎉 *PEDIDO #{$codigo} PRONTO NA EXPEDIÇÃO!*\n\n"
                . "Oi, *{$primeiroNome}*! O seu pedido acabou de ficar pronto na cozinha e já está embalado na bolsa térmica! 🛍️\n\n"
                . "📋 *Itens:*\n{$itensResumo}\n\n"
                . "Nosso entregador já está saindo para levar até você.\n"
                . "📍 *Acompanhe em tempo real:*\n👉 {$trackingUrl}\n\n"
                . "✅ _Se você já tiver recebido o pedido, responda *JÁ RECEBI* aqui!_";
        }

        // Cenário 3: Aceito ou Em preparo na cozinha
        if (in_array($status, ['aceito', 'em_preparo'], true)) {
            $tempoMedio = (int) ($loja->config_json['tempo_medio_preparo_min'] ?? 35);
            if ($minutosDecorridos > $tempoMedio) {
                return "👨‍🍳🔥 *PEDIDO #{$codigo} NA RETA FINAL DE PREPARO*\n\n"
                    . "Oi, *{$primeiroNome}*! Hoje nossa cozinha está com bastante movimento, mas sua refeição está sendo finalizada com todo capricho!\n\n"
                    . "📋 *Itens:*\n{$itensResumo}\n\n"
                    . "O seu pedido *#{$codigo}* já é o **próximo da fila a ser embalado**! Assim que sair para entrega, te aviso aqui no mesmo segundo.\n\n"
                    . "📍 *Acompanhe ao vivo:*\n👉 {$trackingUrl}\n\n"
                    . "✅ _Se o seu pedido já chegou e esquecemos de atualizar, responda *JÁ RECEBI*!_";
            }

            $restante = max(10, ($tempoMedio + 5) - $minutosDecorridos);
            return "👨‍🍳🔥 *PEDIDO #{$codigo} EM PREPARO NA COZINHA*\n\n"
                . "Oi, *{$primeiroNome}*! Seu pedido está sendo preparado agora mesmo pela equipe do *{$loja->nome}*!\n\n"
                . "📋 *Itens:*\n{$itensResumo}\n"
                . "⏱️ *Tempo estimado restante:* cerca de {$restante} min\n\n"
                . "📍 *Acompanhe cada etapa em tempo real:*\n👉 {$trackingUrl}\n\n"
                . "Assim que ficar pronto e sair para entrega, eu te aviso aqui automaticamente!";
        }

        // Cenário 4: Pedido novo (aguardando confirmação ou PIX)
        if ($status === 'novo') {
            if ($pedido->forma_pagamento === 'pix' && $pedido->pagamento_status === 'pendente') {
                $copiaCola = (string) ($pedido->pagamento?->pix_copia_cola ?? '');
                return "⏳ *PEDIDO #{$codigo} AGUARDANDO CONFIRMAÇÃO PIX*\n\n"
                    . "Oi, *{$primeiroNome}*! Recebemos seu pedido (R$ {$totalFmt}) e estamos aguardando o pagamento via PIX para iniciar o preparo.\n\n"
                    . "Copie o código PIX abaixo:\n```{$copiaCola}```\n\n"
                    . "📍 *Link de acompanhamento:*\n👉 {$trackingUrl}\n\n"
                    . "💡 _Se já pagou ou se preferir pagar no *CARTÃO* ou *DINHEIRO* na entrega, basta me responder aqui!_";
            }

            return "🕒 *PEDIDO #{$codigo} RECEBIDO PELA LOJA!*\n\n"
                . "Oi, *{$primeiroNome}*! Seu pedido já chegou no painel do *{$loja->nome}* e nossa equipe já vai confirmar o início do preparo!\n\n"
                . "📋 *Itens:*\n{$itensResumo}\n"
                . "💵 *Total:* R$ {$totalFmt}\n\n"
                . "📍 *Acompanhe em tempo real:*\n👉 {$trackingUrl}";
        }

        // Cenário 5: Já entregue / retirado / servido
        if (in_array($status, ['entregue', 'retirado', 'servido'], true)) {
            return "✅ *PEDIDO #{$codigo} CONCLUÍDO E ENTREGUE!*\n\n"
                . "Oi, *{$primeiroNome}*! Consta no nosso sistema que o pedido *#{$codigo}* já foi entregue e finalizado! 😋❤️\n\n"
                . "📍 *Resumo do pedido:* {$trackingUrl}\n\n"
                . "Se precisar de qualquer ajuda com este pedido, digite *HUMANO* para falar diretamente com a nossa equipe!";
        }

        return "Oi, *{$primeiroNome}*! O seu pedido *#{$codigo}* está com status [*" . strtoupper($status) . "*].\n\n"
            . "📍 *Acompanhe aqui:* {$trackingUrl}\n"
            . "Se desejar falar com o balcão, digite *HUMANO*!";
    }

    /**
     * Comandos especiais para entregadores/motoboys da loja (usando todas as variações de 8/9 dígitos).
     */
    private function tratarComandoMotoboy(Loja $loja, array $candidatosBidx, string $textoUpper): ?string
    {
        /** @var Entregador|null $motoboy */
        $motoboy = Entregador::where('loja_id', $loja->id)
            ->whereIn('telefone_bidx', $candidatosBidx)
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
            $codigo = trim(ltrim($partes[1] ?? '', '#'));

            if (!empty($codigo)) {
                $pedido = Pedido::where('loja_id', $loja->id)
                    ->where('codigo_publico', $codigo)
                    ->first();

                if ($pedido !== null && in_array($pedido->status, ['aceito', 'em_preparo', 'pronto', 'saiu_entrega'], true)) {
                    /** @var FluxoStatusPedidoService $fluxo */
                    $fluxo = app(FluxoStatusPedidoService::class);
                    try {
                        $fluxo->transicionar($pedido, 'entregue', null, null, $motoboy->id, 'Confirmado via WhatsApp pelo entregador ' . $motoboy->nome);
                        return "✅ Show de bola, {$motoboy->nome}! Pedido *#{$codigo}* marcado como ENTREGUE com sucesso no painel da loja!";
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
