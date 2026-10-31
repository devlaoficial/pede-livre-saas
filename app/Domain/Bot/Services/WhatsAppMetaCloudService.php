<?php

declare(strict_types=1);

namespace App\Domain\Bot\Services;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Bot\Models\BotConversa;
use App\Domain\Bot\Models\BotMensagem;
use App\Domain\Clientes\Models\Cliente;
use App\Support\Security\BlindIndexService;
use Illuminate\Support\Facades\DB;

final class WhatsAppMetaCloudService
{
    private const OPT_OUT_KEYWORDS = ['PARAR', 'SAIR', 'CANCELAR', 'STOP', 'DESCADASTRAR'];
    private const HUMAN_HANDOFF_KEYWORDS = ['ATENDENTE', 'HUMANO', 'SUPORTE', 'FALAR COM PESSOA', 'AJUDA'];

    public function __construct(
        private readonly BlindIndexService $blindIndex
    ) {}

    /**
     * Verifies Official Meta Cloud API Webhook Signature (X-Hub-Signature-256: sha256=...).
     */
    public function verificarAssinaturaMeta(?string $signatureHeader, string $rawPayload, ?string $appSecret = null): bool
    {
        if (empty($signatureHeader) || !str_starts_with($signatureHeader, 'sha256=')) {
            return false;
        }

        $secret = $appSecret ?? (string) config('services.whatsapp_meta.app_secret', '');
        if ($secret === '') {
            return false;
        }

        $hashRecebido = substr($signatureHeader, 7);
        $hashCalculado = hash_hmac('sha256', $rawPayload, $secret);

        return hash_equals($hashCalculado, $hashRecebido);
    }

    /**
     * Processes an incoming customer message on the store's official WhatsApp number.
     *
     * @return array{estado: string, resposta: string, opt_out_aplicado: bool, transbordo_humano: bool}
     */
    public function processarMensagemRecebida(Loja $loja, string $telefoneCliente, string $textoMensagem): array
    {
        $telefoneNormalizado = $this->blindIndex->normalizePhone($telefoneCliente);
        $telefoneBidx = $this->blindIndex->generateForPhone($telefoneNormalizado);
        $textoUpper = mb_strtoupper(trim($textoMensagem));

        return DB::transaction(function () use ($loja, $telefoneNormalizado, $telefoneBidx, $textoMensagem, $textoUpper): array {
            /** @var BotConversa $conversa */
            $conversa = BotConversa::firstOrCreate(
                ['loja_id' => $loja->id, 'telefone_bidx' => $telefoneBidx],
                [
                    'telefone' => $telefoneNormalizado,
                    'estado' => 'ia_ativa',
                    'contexto_json' => ['carrinho' => []],
                ]
            );

            // Log incoming encrypted message
            BotMensagem::create([
                'loja_id' => $loja->id,
                'conversa_id' => $conversa->id,
                'direcao' => 'entrada',
                'mensagem' => $textoMensagem,
            ]);

            // 1. Check LGPD Immediate Opt-Out Keywords (PARAR / SAIR)
            if (in_array($textoUpper, self::OPT_OUT_KEYWORDS, true)) {
                Cliente::where('loja_id', $loja->id)
                    ->where('telefone_bidx', $telefoneBidx)
                    ->update(['aceita_marketing' => false]);

                $resposta = 'Você foi descadastrado das nossas mensagens promocionais conforme a LGPD. Você continuará recebendo apenas avisos essenciais de pedidos ativos.';

                BotMensagem::create([
                    'loja_id' => $loja->id,
                    'conversa_id' => $conversa->id,
                    'direcao' => 'saida',
                    'mensagem' => $resposta,
                ]);

                return [
                    'estado' => $conversa->estado,
                    'resposta' => $resposta,
                    'opt_out_aplicado' => true,
                    'transbordo_humano' => false,
                ];
            }

            // 2. Check Human Handoff Request
            foreach (self::HUMAN_HANDOFF_KEYWORDS as $keyword) {
                if (str_contains($textoUpper, $keyword)) {
                    $conversa->update(['estado' => 'humano']);
                    $resposta = 'Transferindo seu atendimento para um atendente humano da nossa equipe. Um instante, por favor! 👨‍💼';

                    BotMensagem::create([
                        'loja_id' => $loja->id,
                        'conversa_id' => $conversa->id,
                        'direcao' => 'saida',
                        'mensagem' => $resposta,
                    ]);

                    return [
                        'estado' => 'humano',
                        'resposta' => $resposta,
                        'opt_out_aplicado' => false,
                        'transbordo_humano' => true,
                    ];
                }
            }

            // 3. If already in human mode, do not auto-reply with AI
            if ($conversa->estado === 'humano') {
                return [
                    'estado' => 'humano',
                    'resposta' => '',
                    'opt_out_aplicado' => false,
                    'transbordo_humano' => true,
                ];
            }

            // 4. Conversational AI State Transitions (ia_ativa -> montando_pedido -> aguardando_pix)
            if (str_contains($textoUpper, 'PEDIR') || str_contains($textoUpper, 'CARDAPIO') || str_contains($textoUpper, 'BURGER') || str_contains($textoUpper, 'PIZZA')) {
                $conversa->update(['estado' => 'montando_pedido']);
                $resposta = "Olá! Bem-vindo ao *{$loja->nome}*! 🍔🍕\nAcesse nosso cardápio rápido ou me diga os itens que deseja adicionar ao seu pedido: " . url("/p/{$loja->slug}");
            } elseif (str_contains($textoUpper, 'PIX') || str_contains($textoUpper, 'FECHAR')) {
                $conversa->update(['estado' => 'aguardando_pix']);
                $resposta = "Perfeito! Geramos seu código PIX Copia e Cola para pagamento instantâneo.";
            } else {
                $resposta = "Olá! Sou o assistente virtual do *{$loja->nome}*. Digite *CARDAPIO* para fazer um pedido ou *HUMANO* para falar com nossa equipe.";
            }

            BotMensagem::create([
                'loja_id' => $loja->id,
                'conversa_id' => $conversa->id,
                'direcao' => 'saida',
                'mensagem' => $resposta,
            ]);

            return [
                'estado' => $conversa->estado,
                'resposta' => $resposta,
                'opt_out_aplicado' => false,
                'transbordo_humano' => false,
            ];
        });
    }
}
