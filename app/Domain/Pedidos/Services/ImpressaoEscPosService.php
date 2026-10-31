<?php

declare(strict_types=1);

namespace App\Domain\Pedidos\Services;

use App\Domain\Pedidos\Models\Pedido;
use App\Domain\Pedidos\Models\PrintJob;

final class ImpressaoEscPosService
{
    private const LARGURA_COLUNAS = 48; // Standard 80mm thermal paper width

    // ESC/POS Command Constants
    private const ESC = "\x1b";
    private const GS = "\x1d";
    private const INIT = "\x1b\x40";
    private const ALIGN_LEFT = "\x1b\x61\x00";
    private const ALIGN_CENTER = "\x1b\x61\x01";
    private const ALIGN_RIGHT = "\x1b\x61\x02";
    private const FONT_NORMAL = "\x1b\x21\x00";
    private const FONT_BOLD = "\x1b\x21\x08";
    private const FONT_DOUBLE_HEIGHT = "\x1b\x21\x10";
    private const FONT_DOUBLE_WIDTH = "\x1b\x21\x20";
    private const FONT_DOUBLE = "\x1b\x21\x30";
    private const FEED_4 = "\x1b\x64\x04";
    private const CUT_PAPER = "\x1d\x56\x41\x03"; // Partial cut

    /**
     * Layout 1: Comanda de Cozinha (80mm) - Zero Financial Clutter.
     */
    public function gerarComandaCozinha(Pedido $pedido): string
    {
        $buffer = self::INIT;

        // Big Bold Order Header
        $buffer .= self::ALIGN_CENTER . self::FONT_DOUBLE . "PEDIDO #{$pedido->codigo_publico}\n" . self::FONT_NORMAL;

        // Channel / Table
        $canalFormatado = match ($pedido->canal) {
            'delivery' => '>>> DELIVERY <<<',
            'retirada' => '>>> BALCAO / RETIRADA <<<',
            'mesa' => '>>> MESA ' . ($pedido->mesa ? str_pad((string) $pedido->mesa->numero, 2, '0', STR_PAD_LEFT) : '00') . ' <<<',
            default => '>>> ' . strtoupper($pedido->canal) . ' <<<',
        };
        $buffer .= self::FONT_BOLD . "{$canalFormatado}\n" . self::FONT_NORMAL;

        // Date and Time
        $dataHora = $pedido->created_at ? $pedido->created_at->format('d/m/Y H:i:s') : date('d/m/Y H:i:s');
        $buffer .= "Hora: {$dataHora}\n";
        if ($pedido->cliente?->nome) {
            $buffer .= "Cliente: {$pedido->cliente->nome}\n";
        }

        $buffer .= $this->separador('-') . "\n";
        $buffer .= self::ALIGN_LEFT;

        // Items and Addons
        foreach ($pedido->itens as $item) {
            $tituloItem = "{$item->qtd}x " . strtoupper($item->nome_snapshot);
            if ($item->variacao_snapshot) {
                $tituloItem .= " [{$item->variacao_snapshot}]";
            }
            $buffer .= self::FONT_BOLD . "{$tituloItem}\n" . self::FONT_NORMAL;

            // Addons snapshot
            $adicionais = is_array($item->adicionais_json) ? $item->adicionais_json : [];
            foreach ($adicionais as $adicional) {
                $nomeAdicional = $adicional['nome'] ?? '';
                $buffer .= "   + {$nomeAdicional}\n";
            }

            // Item Note
            if (!empty($item->observacao)) {
                $buffer .= self::FONT_BOLD . "   ** OBS: {$item->observacao} **\n" . self::FONT_NORMAL;
            }
            $buffer .= "\n";
        }

        // General Order Notes
        if (!empty($pedido->observacao)) {
            $buffer .= $this->separador('-') . "\n";
            $buffer .= self::FONT_BOLD . "OBSERVACOES GERAIS:\n" . self::FONT_NORMAL;
            $buffer .= "{$pedido->observacao}\n";
        }

        $buffer .= $this->separador('=') . "\n";
        $buffer .= self::ALIGN_CENTER . "PEDELIVRE - COZINHA\n";
        $buffer .= self::FEED_4 . self::CUT_PAPER;

        return $buffer;
    }

    /**
     * Layout 2: Via do Cliente / Conta (80mm) - Itemized prices, financials and masked delivery info.
     */
    public function gerarViaCliente(Pedido $pedido): string
    {
        $loja = $pedido->loja;
        $buffer = self::INIT;

        // Store Header
        $buffer .= self::ALIGN_CENTER . self::FONT_BOLD . strtoupper($loja?->nome ?? 'PEDELIVRE') . "\n" . self::FONT_NORMAL;
        if ($loja?->endereco) {
            $buffer .= "{$loja->endereco} - {$loja->cidade}/{$loja->uf}\n";
        }
        if ($loja?->telefone_whatsapp) {
            $buffer .= "WhatsApp: {$loja->telefone_whatsapp}\n";
        }

        $buffer .= $this->separador('=') . "\n";
        $buffer .= self::FONT_BOLD . "COMPROVANTE DE PEDIDO #{$pedido->codigo_publico}\n" . self::FONT_NORMAL;

        $dataHora = $pedido->created_at ? $pedido->created_at->format('d/m/Y H:i:s') : date('d/m/Y H:i:s');
        $buffer .= "Data: {$dataHora} | Canal: " . ucfirst($pedido->canal) . "\n";
        $buffer .= $this->separador('-') . "\n";

        // Itemized table: QTD / ITEM / TOTAL (R$)
        $buffer .= self::ALIGN_LEFT;
        $buffer .= $this->alinharDuasColunas("QTD ITEM", "VALOR (R$)") . "\n";
        $buffer .= $this->separador('-') . "\n";

        foreach ($pedido->itens as $item) {
            $nomeCompleto = "{$item->qtd}x {$item->nome_snapshot}";
            if ($item->variacao_snapshot) {
                $nomeCompleto .= " ({$item->variacao_snapshot})";
            }
            $valorFormatado = $this->formatarMoeda($item->total);
            $buffer .= $this->alinharDuasColunas($nomeCompleto, $valorFormatado) . "\n";

            // Addon details
            $adicionais = is_array($item->adicionais_json) ? $item->adicionais_json : [];
            foreach ($adicionais as $adicional) {
                $nomeOpt = " + " . ($adicional['nome'] ?? '');
                $precoOpt = (int) ($adicional['preco_centavos'] ?? 0);
                $precoFormatado = $precoOpt > 0 ? "+ " . $this->formatarMoeda($precoOpt) : "Incluso";
                $buffer .= $this->alinharDuasColunas($nomeOpt, $precoFormatado) . "\n";
            }
        }

        // Financial Summary
        $buffer .= $this->separador('-') . "\n";
        $buffer .= $this->alinharDuasColunas("Subtotal:", $this->formatarMoeda($pedido->subtotal)) . "\n";

        if ($pedido->taxa_entrega > 0) {
            $buffer .= $this->alinharDuasColunas("Taxa de Entrega:", "+ " . $this->formatarMoeda($pedido->taxa_entrega)) . "\n";
        }
        if ($pedido->taxa_servico > 0) {
            $buffer .= $this->alinharDuasColunas("Taxa de Servico:", "+ " . $this->formatarMoeda($pedido->taxa_servico)) . "\n";
        }
        if ($pedido->desconto > 0) {
            $buffer .= $this->alinharDuasColunas("Desconto (Cupom):", "- " . $this->formatarMoeda($pedido->desconto)) . "\n";
        }
        if ($pedido->desconto_cashback > 0) {
            $buffer .= $this->alinharDuasColunas("Desconto (Cashback):", "- " . $this->formatarMoeda($pedido->desconto_cashback)) . "\n";
        }

        $buffer .= $this->separador('=') . "\n";
        $buffer .= self::FONT_DOUBLE . $this->alinharDuasColunas("TOTAL:", $this->formatarMoeda($pedido->total)) . "\n" . self::FONT_NORMAL;

        // Payment Details
        $buffer .= $this->separador('-') . "\n";
        $metodoNome = match ($pedido->forma_pagamento) {
            'pix' => 'PIX',
            'cartao_online' => 'Cartao Online',
            'cartao_maquina' => 'Cartao na Maquininha',
            'dinheiro' => 'Dinheiro',
            'voucher' => 'Vale Refeicao / Alimentacao',
            default => strtoupper($pedido->forma_pagamento)
        };
        $statusPagamento = $pedido->pagamento_status === 'aprovado' ? 'PAGO' : 'PENDENTE NO ATO';
        $buffer .= "Forma de Pagamento: {$metodoNome} ({$statusPagamento})\n";

        if ($pedido->forma_pagamento === 'dinheiro' && $pedido->troco_para !== null && $pedido->troco_para > $pedido->total) {
            $trocoLevar = $pedido->troco_para - $pedido->total;
            $buffer .= $this->alinharDuasColunas("Troco para:", $this->formatarMoeda($pedido->troco_para)) . "\n";
            $buffer .= self::FONT_BOLD . $this->alinharDuasColunas("LEVAR TROCO:", $this->formatarMoeda($trocoLevar)) . "\n" . self::FONT_NORMAL;
        }

        // Customer Delivery Info (Masked PII)
        if ($pedido->canal === 'delivery' && is_array($pedido->endereco_json)) {
            $buffer .= $this->separador('-') . "\n";
            $buffer .= self::FONT_BOLD . "DADOS DE ENTREGA:\n" . self::FONT_NORMAL;

            $cliente = $pedido->cliente;
            $nomeCliente = $cliente?->nome ?? 'Cliente';
            $tel = $cliente?->telefone ? (string) $cliente->telefone : '';
            $telDigits = preg_replace('/\D+/', '', $tel);
            $telMasked = strlen($telDigits) >= 10
                ? '(' . substr($telDigits, -11, 2) . ') 9****-' . substr($telDigits, -4)
                : 'Telefone protegido';

            $rua = (string) ($pedido->endereco_json['rua'] ?? '');
            $numero = (string) ($pedido->endereco_json['numero'] ?? '');
            $bairro = (string) ($pedido->endereco_json['bairro'] ?? '');
            $comp = (string) ($pedido->endereco_json['complemento'] ?? '');

            $buffer .= "Destinatario: {$nomeCliente}\n";
            $buffer .= "Telefone: {$telMasked}\n";
            $buffer .= "Endereco: {$rua}, {$numero}\n";
            if ($comp) {
                $buffer .= "Complemento: {$comp}\n";
            }
            $buffer .= "Bairro: {$bairro}\n";
        } elseif ($pedido->canal === 'mesa') {
            $buffer .= $this->separador('-') . "\n";
            $buffer .= "Consumo no Salão - Mesa " . ($pedido->mesa?->numero ?? '00') . "\n";
        }

        // Footer
        $buffer .= $this->separador('=') . "\n";
        $buffer .= self::ALIGN_CENTER;
        $buffer .= "Agradecemos a preferencia! Bom apetite!\n";
        $slug = $loja?->slug ?? '';
        $buffer .= "pedelivre.com.br/{$slug}\n";
        $buffer .= self::FEED_4 . self::CUT_PAPER;

        return $buffer;
    }

    /**
     * Enqueues print jobs in print_jobs table (AES-256 encrypted at rest).
     *
     * @param Pedido $pedido
     * @param string $tipo ('comanda_cozinha', 'via_cliente', 'ambas')
     * @return array<int, PrintJob>
     */
    public function enfileirarImpressao(Pedido $pedido, string $tipo = 'ambas'): array
    {
        $lojaId = $pedido->loja_id;
        $jobs = [];

        if (in_array($tipo, ['comanda_cozinha', 'ambas'], true)) {
            $conteudoCozinha = $this->gerarComandaCozinha($pedido);
            $jobs[] = PrintJob::create([
                'loja_id' => $lojaId,
                'pedido_id' => $pedido->id,
                'tipo' => 'comanda_cozinha',
                'conteudo' => $conteudoCozinha,
                'status' => 'pendente',
                'tentativas' => 0,
            ]);
        }

        if (in_array($tipo, ['via_cliente', 'ambas'], true)) {
            $conteudoCliente = $this->gerarViaCliente($pedido);
            $jobs[] = PrintJob::create([
                'loja_id' => $lojaId,
                'pedido_id' => $pedido->id,
                'tipo' => 'via_cliente',
                'conteudo' => $conteudoCliente,
                'status' => 'pendente',
                'tentativas' => 0,
            ]);
        }

        return $jobs;
    }

    private function formatarMoeda(int $centavos): string
    {
        return 'R$ ' . number_format($centavos / 100, 2, ',', '.');
    }

    private function separador(string $char): string
    {
        return str_repeat($char, self::LARGURA_COLUNAS);
    }

    private function alinharDuasColunas(string $esquerda, string $direita): string
    {
        $tamanhoDir = mb_strlen($direita);
        $espacoDisponivel = self::LARGURA_COLUNAS - $tamanhoDir - 1;

        if (mb_strlen($esquerda) > $espacoDisponivel) {
            $esquerda = mb_substr($esquerda, 0, $espacoDisponivel - 3) . '...';
        }

        return str_pad($esquerda, self::LARGURA_COLUNAS - $tamanhoDir, ' ') . $direita;
    }
}
