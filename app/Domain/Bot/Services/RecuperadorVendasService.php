<?php

declare(strict_types=1);

namespace App\Domain\Bot\Services;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Catalogo\Models\Produto;
use App\Domain\Clientes\Models\Cliente;
use App\Domain\Pagamentos\Models\Pagamento;
use App\Domain\Pagamentos\Services\MercadoPagoPixService;
use App\Domain\Pedidos\Events\PedidoAtualizado;
use App\Domain\Pedidos\Events\PedidoCriado;
use App\Domain\Pedidos\Models\Pedido;
use App\Domain\Pedidos\Models\PedidoEvento;
use App\Domain\Pedidos\Models\PedidoItem;
use App\Domain\Pedidos\Services\FluxoStatusPedidoService;
use DomainException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class RecuperadorVendasService
{
    public function __construct(
        private readonly EvolutionApiService $evolutionApi,
        private readonly MercadoPagoPixService $pixService,
        private readonly FluxoStatusPedidoService $fluxoService
    ) {}

    /**
     * Envia lembrete amigável para pedidos com PIX pendente entre 5 e 25 minutos.
     */
    public function recuperarPixPendentes(): int
    {
        $limiteInferior = now()->subMinutes(25);
        $limiteSuperior = now()->subMinutes(5);

        $pedidos = Pedido::where('forma_pagamento', 'pix')
            ->where('pagamento_status', 'pendente')
            ->where('status', 'novo')
            ->whereBetween('created_at', [$limiteInferior, $limiteSuperior])
            ->with(['cliente', 'loja', 'pagamento'])
            ->get();

        $enviados = 0;

        foreach ($pedidos as $pedido) {
            // Verifica se já enviamos lembrete para este pedido
            $jaNotificado = PedidoEvento::where('pedido_id', $pedido->id)
                ->where('status_para', 'recuperacao_pix')
                ->exists();

            if ($jaNotificado) {
                continue;
            }

            $loja = $pedido->loja;
            if ($loja === null) {
                continue;
            }

            $config = $loja->config_json['whatsapp'] ?? [];
            if (!($config['recuperar_carrinho'] ?? true)) {
                continue;
            }

            $instancia = $this->evolutionApi->obterNomeInstanciaLoja($loja);
            $cliente = $pedido->cliente;
            $telCliente = $cliente?->telefone ? (string) $cliente->telefone : null;

            if (empty($telCliente)) {
                continue;
            }

            $primeiroNome = explode(' ', trim($cliente->nome ?? 'Cliente'))[0];
            $codigo = $pedido->codigo_publico;
            $totalFormatado = number_format($pedido->total / 100, 2, ',', '.');
            $copiaCola = (string) ($pedido->pagamento?->pix_copia_cola ?? '');

            $trackingSignature = hash_hmac('sha256', (string) $pedido->tracking_token_hash, (string) config('app.key'));
            $trackingUrl = url("/p/{$loja->slug}/pedidos/{$codigo}?t={$trackingSignature}");

            $msgLembrete = "Oi, *{$primeiroNome}*! 👋 Passando para avisar que recebemos seu pedido *#{$codigo}* no *{$loja->nome}*!\n\n"
                . "🍔 Separamos seus itens aqui na cozinha com todo cuidado para você não perder sua vaga na fila de preparo.\n\n"
                . "Notamos que o pagamento via PIX ainda não foi concluído. Para pagar agora com aprovação imediata, copie o código PIX abaixo:\n\n"
                . "💵 *Valor:* R$ {$totalFormatado}\n\n"
                . "```{$copiaCola}```\n\n"
                . "👉 Acompanhe ao vivo: {$trackingUrl}\n\n"
                . "💡 *Prefere pagar no Cartão ou Dinheiro na entrega?* Basta me responder aqui dizendo *CARTÃO* ou *DINHEIRO* que alteramos para você agora mesmo! 😊";

            $sucesso = $this->evolutionApi->enviarMensagem($instancia, $telCliente, $msgLembrete);

            if ($sucesso) {
                PedidoEvento::create([
                    'pedido_id' => $pedido->id,
                    'status_de' => 'novo',
                    'status_para' => 'recuperacao_pix',
                    'observacao' => 'Lembrete automático de recuperação de PIX enviado no WhatsApp.',
                ]);
                $enviados++;
                Log::info("[RecuperadorVendas] Lembrete PIX enviado para pedido #{$codigo} (+{$telCliente})");
            }
        }

        return $enviados;
    }

    /**
     * Cancela pedidos PIX que continuaram sem pagamento após 40 minutos para liberar estoque.
     */
    public function cancelarPixExpirados(): int
    {
        $limiteExpiracao = now()->subMinutes(40);

        $pedidosExpirados = Pedido::where('forma_pagamento', 'pix')
            ->where('pagamento_status', 'pendente')
            ->where('status', 'novo')
            ->where('created_at', '<=', $limiteExpiracao)
            ->with(['cliente', 'loja'])
            ->get();

        $cancelados = 0;

        foreach ($pedidosExpirados as $pedido) {
            $loja = $pedido->loja;
            $cliente = $pedido->cliente;
            $codigo = $pedido->codigo_publico;

            try {
                $this->fluxoService->transicionar(
                    $pedido,
                    'cancelado',
                    null,
                    'Tempo limite de 40 minutos para pagamento PIX esgotado.'
                );

                $cancelados++;
            } catch (Exception $e) {
                Log::warning("[RecuperadorVendas] Erro ao cancelar pedido expirado #{$codigo}: " . $e->getMessage());
            }
        }

        return $cancelados;
    }

    /**
     * Clona um pedido anterior com perfeição para a Re-compra em 1 Clique.
     */
    public function recriarPedidoAnterior(Loja $loja, Cliente $cliente, Pedido $pedidoAnterior): Pedido
    {
        // Verifica previamente se todos os produtos do pedido anterior ainda estão disponíveis
        foreach ($pedidoAnterior->itens as $item) {
            /** @var Produto|null $prod */
            $prod = Produto::where('loja_id', $loja->id)->find($item->produto_id);
            if ($prod === null || !$prod->ativo || $prod->esgotado) {
                $nomeItem = $item->nome_snapshot;
                throw new DomainException("O item '{$nomeItem}' está esgotado ou indisponível no momento.");
            }
        }

        return DB::transaction(function () use ($loja, $cliente, $pedidoAnterior): Pedido {
            // Gera código público único de 4 caracteres (sem ambiguidades)
            do {
                $codigoPublico = strtoupper(Str::random(4));
            } while (Pedido::where('loja_id', $loja->id)->where('codigo_publico', $codigoPublico)->where('created_at', '>=', now()->subHours(24))->exists());

            $tokenHash = hash_hmac('sha256', bin2hex(random_bytes(24)), (string) config('app.blind_index_key'));
            $idempotencyKey = 'recompra_' . $loja->id . '_' . $cliente->id . '_' . time();

            // 1. Cria o novo pedido
            $novoPedido = Pedido::create([
                'loja_id' => $loja->id,
                'codigo_publico' => $codigoPublico,
                'tracking_token_hash' => $tokenHash,
                'canal' => $pedidoAnterior->canal,
                'status' => 'novo',
                'cliente_id' => $cliente->id,
                'mesa_id' => null,
                'comanda_id' => null,
                'entregador_id' => null,
                'subtotal' => $pedidoAnterior->subtotal,
                'taxa_entrega' => $pedidoAnterior->taxa_entrega,
                'desconto' => 0,
                'desconto_cashback' => 0,
                'taxa_servico' => 0,
                'total' => $pedidoAnterior->subtotal + $pedidoAnterior->taxa_entrega,
                'cupom_id' => null,
                'forma_pagamento' => $pedidoAnterior->forma_pagamento,
                'pagamento_status' => 'pendente',
                'troco_para' => $pedidoAnterior->troco_para,
                'observacao' => 'Re-compra rápida realizada via WhatsApp (1-Clique).',
                'endereco_json' => $pedidoAnterior->endereco_json,
                'origem' => 'bot',
            ]);

            // 2. Clona os itens do pedido anterior
            foreach ($pedidoAnterior->itens as $item) {
                PedidoItem::create([
                    'pedido_id' => $novoPedido->id,
                    'produto_id' => $item->produto_id,
                    'nome_snapshot' => $item->nome_snapshot,
                    'variacao_snapshot' => $item->variacao_snapshot,
                    'qtd' => $item->qtd,
                    'preco_unit' => $item->preco_unit,
                    'adicionais_json' => $item->adicionais_json,
                    'observacao' => $item->observacao,
                    'total' => $item->total,
                    'status_cozinha' => 'fila',
                ]);
            }

            // 3. Registra evento de criação
            PedidoEvento::create([
                'pedido_id' => $novoPedido->id,
                'status_de' => null,
                'status_para' => 'novo',
                'observacao' => "Re-compra 1-Clique WhatsApp clonada do pedido #{$pedidoAnterior->codigo_publico}",
            ]);

            // 4. Cria o pagamento
            $pagamento = Pagamento::create([
                'loja_id' => $loja->id,
                'pedido_id' => $novoPedido->id,
                'metodo' => $novoPedido->forma_pagamento,
                'valor' => $novoPedido->total,
                'status' => 'pendente',
                'idempotency_key' => $idempotencyKey,
            ]);

            // Se for PIX, gera a cobrança imediatamente
            if ($novoPedido->forma_pagamento === 'pix') {
                try {
                    $this->pixService->gerarPix($loja, $novoPedido, $pagamento);
                } catch (Exception $e) {
                    Log::warning("[RecuperadorVendas] Falha ao gerar PIX para re-compra #{$codigoPublico}: " . $e->getMessage());
                }
            }

            // Atualiza métricas do cliente
            $cliente->increment('total_pedidos');
            $cliente->update(['ultimo_pedido_em' => now()]);

            // Dispara evento em tempo real para o Kanban do lojista
            event(new PedidoCriado($novoPedido->load(['cliente', 'itens'])));

            return $novoPedido;
        });
    }

    /**
     * Altera a forma de pagamento de um pedido pendente (ex: PIX -> Cartão na entrega).
     */
    public function alterarFormaPagamento(Pedido $pedido, string $novaForma): bool
    {
        if ($pedido->status !== 'novo' || $pedido->pagamento_status === 'aprovado') {
            return false;
        }

        $pedido->forma_pagamento = $novaForma;
        $pedido->save();

        if ($pedido->pagamento) {
            $pedido->pagamento->update(['metodo' => $novaForma]);
        }

        PedidoEvento::create([
            'pedido_id' => $pedido->id,
            'status_de' => 'novo',
            'status_para' => 'novo',
            'observacao' => "Forma de pagamento alterada para [{$novaForma}] via WhatsApp pelo cliente.",
        ]);

        // Dispara atualização em tempo real para o Kanban do lojista
        event(new PedidoAtualizado($pedido, 'novo'));

        return true;
    }
}
