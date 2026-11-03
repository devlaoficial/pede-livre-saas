<?php

declare(strict_types=1);

namespace App\Domain\Bot\Services;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Catalogo\Models\Categoria;
use App\Domain\Pedidos\Models\Pedido;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class AiChatService
{
    private const GROQ_CHAT_URL = 'https://api.groq.com/openai/v1/chat/completions';
    private const GROQ_WHISPER_URL = 'https://api.groq.com/openai/v1/audio/transcriptions';

    /**
     * Transcreve áudio do WhatsApp (base64 ou binário) em texto em português via Groq Whisper.
     */
    public function transcreverAudio(string $audioBase64OuBinario, string $mimetype = 'audio/ogg'): ?string
    {
        $apiKey = (string) config('services.groq.api_key');
        if (empty($apiKey)) {
            Log::warning('[AiChatService] GROQ_API_KEY não configurada para transcrição de áudio.');
            return null;
        }

        try {
            $binario = $this->extrairBinarioAudio($audioBase64OuBinario);
            if (empty($binario)) {
                return null;
            }

            $model = (string) config('services.groq.whisper_model', 'whisper-large-v3-turbo');
            $extensao = match (true) {
                str_contains($mimetype, 'mp4') || str_contains($mimetype, 'm4a') => 'm4a',
                str_contains($mimetype, 'mp3') || str_contains($mimetype, 'mpeg') => 'mp3',
                str_contains($mimetype, 'wav') => 'wav',
                default => 'ogg',
            };

            $response = Http::withToken($apiKey)
                ->withHeaders([
                    'User-Agent' => 'PedeLivre-Bot/1.0',
                    'Accept' => 'application/json',
                ])
                ->timeout(12)
                ->attach('file', $binario, "audio.{$extensao}")
                ->post(self::GROQ_WHISPER_URL, [
                    'model' => $model,
                    'language' => 'pt',
                    'response_format' => 'json',
                    'temperature' => '0.2',
                ]);

            if ($response->successful()) {
                $dados = $response->json();
                $texto = trim((string) ($dados['text'] ?? ''));
                if (!empty($texto)) {
                    Log::info("[AiChatService] Áudio transcrito com sucesso ({$model}): \"{$texto}\"");
                    return $texto;
                }
            } else {
                Log::warning("[AiChatService] Falha na transcrição Groq (HTTP {$response->status()}): " . $response->body());
            }
        } catch (Exception $e) {
            Log::error("[AiChatService] Exceção ao transcrever áudio: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Responde dúvidas sobre o cardápio, loja e pedido ativo utilizando IA conversacional (Groq LPU).
     */
    public function responderDuvidaCardapio(
        Loja $loja,
        string $pergunta,
        array $historico = [],
        ?Pedido $pedidoAtivo = null
    ): ?string {
        $apiKey = (string) config('services.groq.api_key');
        if (empty($apiKey)) {
            return null;
        }

        try {
            $cardapioResumo = $this->obterResumoCardapio($loja);
            $linkCardapio = url("/p/{$loja->slug}");

            $contextoPedido = '';
            if ($pedidoAtivo !== null) {
                $pedidoAtivo->loadMissing(['itens', 'entregador']);
                $codigo = $pedidoAtivo->codigo_publico;
                $status = $pedidoAtivo->status;
                $minutos = (int) $pedidoAtivo->created_at->diffInMinutes(now());
                $itensTxt = $pedidoAtivo->itens->map(fn ($i) => "{$i->qtd}x {$i->nome_snapshot}")->join(', ');
                $sig = hash_hmac('sha256', (string) $pedidoAtivo->tracking_token_hash, (string) config('app.key'));
                $trackingUrl = url("/p/{$loja->slug}/pedidos/{$codigo}?t={$sig}");
                $motoboyTxt = $pedidoAtivo->entregador?->nome ? " | Entregador: {$pedidoAtivo->entregador->nome}" : '';

                $contextoPedido = <<<PEDIDO_INFO

PEDIDO ATUAL DESTE CLIENTE EM TEMPO REAL:
- Código: #{$codigo}
- Status atual: {$status} (feito há {$minutos} min){$motoboyTxt}
- Itens: {$itensTxt}
- Link oficial para acompanhar em tempo real: {$trackingUrl}
- Importante: Se o cliente perguntar sobre o pedido dele, informe o status real acima e envie o link de acompanhamento {$trackingUrl}. Se o cliente disser que o pedido já foi entregue, oriente-o a responder "JÁ RECEBI".
PEDIDO_INFO;
            }

            $systemPrompt = <<<PROMPT
Você é o atendente virtual inteligente, educado, caloroso e ágil do restaurante "{$loja->nome}".
Seu papel é ajudar clientes no WhatsApp com dúvidas sobre o cardápio, ingredientes, opções, sabores, horários e acompanhamento de pedidos.

DIRETRIZES DE COMUNICAÇÃO:
1. Responda em português brasileiro de forma acolhedora, objetiva e simpática (máximo de 2 a 3 frases curtas).
2. Baseie sua resposta ESTRITAMENTE no cardápio real e nos dados de pedido informados abaixo. NUNCA invente itens, adicionais ou preços que não constem na lista.
3. Se o cliente perguntar sobre um item ou sabor que o restaurante NÃO possui, avise com gentileza que não tem no momento e sugira uma opção similar do cardápio.
4. Se o cliente estiver perguntando sobre o pedido em andamento e houver dados em "PEDIDO ATUAL DESTE CLIENTE", informe o status exato e envie o link de acompanhamento em tempo real. Caso contrário, convide-o a pedir pelo cardápio online: {$linkCardapio}
5. Use poucos emojis com moderação (🍔, 🍕, 😋, ❤️, ✨). Mantenha tom de excelência e hospitalidade.
{$contextoPedido}

{$cardapioResumo}
PROMPT;

            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
            ];

            foreach ($historico as $msg) {
                if (isset($msg['role'], $msg['content'])) {
                    $messages[] = [
                        'role' => $msg['role'] === 'user' ? 'user' : 'assistant',
                        'content' => (string) $msg['content'],
                    ];
                }
            }

            $messages[] = [
                'role' => 'user',
                'content' => trim($pergunta),
            ];

            $modelos = array_unique(array_filter([
                (string) config('services.groq.chat_model', 'llama-3.3-70b-versatile'),
                'llama-3.3-70b-versatile',
                'llama-3.1-8b-instant',
                'openai/gpt-oss-120b',
                'openai/gpt-oss-20b',
            ]));

            foreach ($modelos as $chatModel) {
                try {
                    $response = Http::withToken($apiKey)
                        ->withHeaders([
                            'User-Agent' => 'PedeLivre-Bot/1.0',
                            'Content-Type' => 'application/json',
                        ])
                        ->timeout(9)
                        ->post(self::GROQ_CHAT_URL, [
                            'model' => $chatModel,
                            'messages' => $messages,
                            'temperature' => 0.4,
                            'max_tokens' => 700,
                        ]);

                    if ($response->successful()) {
                        $dados = $response->json();
                        $conteudo = (string) ($dados['choices'][0]['message']['content'] ?? '');
                        $textoLimpo = trim($conteudo);

                        if (!empty($textoLimpo)) {
                            if (!str_contains($textoLimpo, $loja->slug) && !str_contains($textoLimpo, 'http')) {
                                $textoLimpo .= "\n\n👉 *Acesse nosso cardápio completo:* {$linkCardapio}";
                            }
                            return $textoLimpo;
                        }
                    } else {
                        Log::warning("[AiChatService] Groq Chat com {$chatModel} falhou (HTTP {$response->status()}): " . $response->body());
                    }
                } catch (Exception $callEx) {
                    Log::warning("[AiChatService] Falha ao consultar {$chatModel}: " . $callEx->getMessage());
                }
            }
        } catch (Exception $e) {
            Log::error("[AiChatService] Exceção no chat de IA: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Gera resumo textual compacto do cardápio da loja para injeção no prompt da IA.
     */
    public function obterResumoCardapio(Loja $loja): string
    {
        return Cache::remember("bot_cardapio_resumo_loja_{$loja->id}", 1800, function () use ($loja): string {
            $categorias = Categoria::where('loja_id', $loja->id)
                ->where('ativa', true)
                ->with(['produtos' => function ($query) {
                    $query->where('ativo', true)->where('esgotado', false)->orderBy('ordem');
                }])
                ->orderBy('ordem')
                ->get();

            $linhas = [];
            $linhas[] = "CARDÁPIO OFICIAL DE \"{$loja->nome}\":";
            $linhas[] = "Taxa / Região de atendimento: Delivery e Retirada no Balcão.";

            if ($categorias->isEmpty()) {
                $linhas[] = "(Cardápio sendo atualizado neste momento).";
                return implode("\n", $linhas);
            }

            foreach ($categorias as $cat) {
                if ($cat->produtos->isEmpty()) {
                    continue;
                }

                $linhas[] = "\n--- CATEGORIA: {$cat->nome} ---";
                if (!empty($cat->descricao)) {
                    $linhas[] = "Obs: {$cat->descricao}";
                }

                foreach ($cat->produtos as $prod) {
                    $preco = number_format(($prod->preco_promocional_centavos ?: $prod->preco_centavos) / 100, 2, ',', '.');
                    $desc = !empty($prod->descricao) ? " - {$prod->descricao}" : '';
                    $alergenos = !empty($prod->alergenos) && is_array($prod->alergenos) ? ' [Contém: ' . implode(', ', $prod->alergenos) . ']' : '';
                    $linhas[] = "* {$prod->nome} (R$ {$preco}){$desc}{$alergenos}";
                }
            }

            return implode("\n", $linhas);
        });
    }

    /**
     * Converte base64 para binário ou retorna string se já for binária.
     */
    private function extrairBinarioAudio(string $conteudo): ?string
    {
        if (str_starts_with($conteudo, 'data:audio') || str_contains($conteudo, ';base64,')) {
            $partes = explode(';base64,', $conteudo);
            $conteudo = $partes[1] ?? $conteudo;
        }

        $decodificado = base64_decode($conteudo, true);
        if ($decodificado !== false && strlen($decodificado) > 20) {
            return $decodificado;
        }

        if (strlen($conteudo) > 20) {
            return $conteudo;
        }

        return null;
    }
}
