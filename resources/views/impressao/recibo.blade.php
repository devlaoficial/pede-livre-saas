<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Recibo 80mm - Pedido #{{ $pedido->codigo_publico }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 4mm;
            width: 72mm;
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            background: #fff;
            line-height: 1.25;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .title { font-size: 16px; font-weight: bold; margin-bottom: 2mm; }
        .divider { border-top: 1px dashed #000; margin: 2mm 0; }
        .double-divider { border-top: 2px solid #000; margin: 2mm 0; }
        .flex { display: flex; justify-content: space-between; }
        .item-row { margin-bottom: 1.5mm; }
        .addon-row { padding-left: 3mm; font-size: 10px; }
        @media screen {
            body { margin: 20px auto; border: 1px solid #ccc; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            .print-btn { display: block; margin: 10px auto; padding: 8px 16px; background: #e11d48; color: #fff; border: none; font-weight: bold; cursor: pointer; border-radius: 6px; }
        }
        @media print {
            .print-btn { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <button class="print-btn" onclick="window.print()">Imprimir Agora</button>

    @if ($tipo === 'cozinha')
        <!-- Comanda de Cozinha -->
        <div class="center title">PEDIDO #{{ $pedido->codigo_publico }}</div>
        <div class="center bold">
            @if ($pedido->canal === 'delivery')
                >>> DELIVERY <<<
            @elseif ($pedido->canal === 'mesa')
                >>> MESA {{ str_pad((string) ($pedido->mesa?->numero ?? 0), 2, '0', STR_PAD_LEFT) }} <<<
            @else
                >>> {{ strtoupper($pedido->canal) }} <<<
            @endif
        </div>
        <div class="center">Hora: {{ $pedido->created_at?->format('d/m/Y H:i:s') }}</div>
        @if ($pedido->cliente?->nome)
            <div>Cliente: {{ $pedido->cliente->nome }}</div>
        @endif

        <div class="divider"></div>

        @foreach ($pedido->itens as $item)
            <div class="item-row">
                <div class="bold">{{ $item->qtd }}x {{ strtoupper($item->nome_snapshot) }} @if($item->variacao_snapshot) [{{ $item->variacao_snapshot }}] @endif</div>
                @if (is_array($item->adicionais_json))
                    @foreach ($item->adicionais_json as $add)
                        <div class="addon-row">+ {{ $add['nome'] ?? '' }}</div>
                    @endforeach
                @endif
                @if ($item->observacao)
                    <div class="bold addon-row">** OBS: {{ $item->observacao }} **</div>
                @endif
            </div>
        @endforeach

        @if ($pedido->observacao)
            <div class="divider"></div>
            <div class="bold">OBSERVACOES GERAIS:</div>
            <div>{{ $pedido->observacao }}</div>
        @endif

    @else
        <!-- Via do Cliente & Conta -->
        <div class="center bold" style="font-size: 13px;">{{ strtoupper($pedido->loja?->nome ?? 'PEDELIVRE') }}</div>
        @if ($pedido->loja?->endereco)
            <div class="center">{{ $pedido->loja->endereco }}</div>
        @endif
        @if ($pedido->loja?->telefone_whatsapp)
            <div class="center">WhatsApp: {{ $pedido->loja->telefone_whatsapp }}</div>
        @endif

        <div class="double-divider"></div>
        <div class="center bold">PEDIDO #{{ $pedido->codigo_publico }}</div>
        <div>Data: {{ $pedido->created_at?->format('d/m/Y H:i') }} | Canal: {{ ucfirst($pedido->canal) }}</div>
        
        <div class="divider"></div>
        <div class="flex bold"><span>QTD ITEM</span><span>VALOR</span></div>
        <div class="divider"></div>

        @foreach ($pedido->itens as $item)
            <div class="item-row">
                <div class="flex">
                    <span>{{ $item->qtd }}x {{ $item->nome_snapshot }} @if($item->variacao_snapshot) ({{ $item->variacao_snapshot }}) @endif</span>
                    <span class="bold">R$ {{ number_format($item->total / 100, 2, ',', '.') }}</span>
                </div>
                @if (is_array($item->adicionais_json))
                    @foreach ($item->adicionais_json as $add)
                        <div class="flex addon-row">
                            <span>+ {{ $add['nome'] ?? '' }}</span>
                            <span>@if(($add['preco_centavos'] ?? 0) > 0) + R$ {{ number_format($add['preco_centavos'] / 100, 2, ',', '.') }} @else Incluso @endif</span>
                        </div>
                    @endforeach
                @endif
            </div>
        @endforeach

        <div class="divider"></div>
        <div class="flex"><span>Subtotal:</span><span>R$ {{ number_format($pedido->subtotal / 100, 2, ',', '.') }}</span></div>
        @if ($pedido->taxa_entrega > 0)
            <div class="flex"><span>Taxa de Entrega:</span><span>+ R$ {{ number_format($pedido->taxa_entrega / 100, 2, ',', '.') }}</span></div>
        @endif
        @if ($pedido->taxa_servico > 0)
            <div class="flex"><span>Taxa de Serviço:</span><span>+ R$ {{ number_format($pedido->taxa_servico / 100, 2, ',', '.') }}</span></div>
        @endif
        @if ($pedido->desconto > 0)
            <div class="flex"><span>Desconto:</span><span>- R$ {{ number_format($pedido->desconto / 100, 2, ',', '.') }}</span></div>
        @endif

        <div class="double-divider"></div>
        <div class="flex bold" style="font-size: 13px;">
            <span>TOTAL:</span>
            <span>R$ {{ number_format($pedido->total / 100, 2, ',', '.') }}</span>
        </div>

        <div class="divider"></div>
        <div>Forma de Pagamento: {{ strtoupper($pedido->forma_pagamento) }} ({{ strtoupper($pedido->pagamento_status) }})</div>

        @if ($pedido->canal === 'delivery' && is_array($pedido->endereco_json))
            <div class="divider"></div>
            <div class="bold">ENTREGA:</div>
            <div>Cliente: {{ $pedido->cliente?->nome }}</div>
            <div>{{ $pedido->endereco_json['rua'] ?? '' }}, {{ $pedido->endereco_json['numero'] ?? '' }}</div>
            <div>Bairro: {{ $pedido->endereco_json['bairro'] ?? '' }}</div>
        @endif

        <div class="double-divider"></div>
        <div class="center">Agradecemos a preferencia!</div>
        <div class="center">{{ request()->getHost() }}/p/{{ $pedido->loja?->slug }}</div>
    @endif
</body>
</html>
