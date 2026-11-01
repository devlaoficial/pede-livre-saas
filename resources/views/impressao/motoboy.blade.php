<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Acerto Diário de Motoboy - {{ $entregador->nome }}</title>
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
        .title { font-size: 14px; font-weight: bold; margin-bottom: 2mm; }
        .divider { border-top: 1px dashed #000; margin: 2mm 0; }
        .double-divider { border-top: 2px solid #000; margin: 2mm 0; }
        .flex { display: flex; justify-content: space-between; }
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

    <div class="center title">{{ strtoupper($loja->nome) }}</div>
    <div class="center bold">ACERTO DIÁRIO DE MOTOBOY</div>
    <div class="center">Data: {{ now()->format('d/m/Y H:i') }}</div>
    <div class="divider"></div>

    <div>Entregador: <b>{{ $entregador->nome }}</b></div>
    <div>Telefone: {{ $entregador->telefone }}</div>
    <div>Tipo: {{ strtoupper($entregador->tipo ?? 'proprio') }}</div>

    <div class="double-divider"></div>
    <div class="bold">RESUMO DE CORRIDAS (FRETES)</div>
    <div class="divider"></div>

    <div class="flex">
        <span>Entregas Concluídas:</span>
        <span class="bold">{{ $concluidas->count() }}</span>
    </div>
    <div class="flex">
        <span>Taxa Acordada / Entrega:</span>
        <span>R$ {{ number_format($taxaUnit / 100, 2, ',', '.') }}</span>
    </div>
    <div class="flex bold">
        <span>TOTAL EM FRETES A RECEBER:</span>
        <span>R$ {{ number_format($totalTaxas / 100, 2, ',', '.') }}</span>
    </div>

    <div class="double-divider"></div>
    <div class="bold">COBRANÇAS EM DINHEIRO FEITAS</div>
    <div class="divider"></div>

    <div class="flex">
        <span>Dinheiro Recebido dos Clientes:</span>
        <span class="bold">R$ {{ number_format($dinheiroColetado / 100, 2, ',', '.') }}</span>
    </div>

    <div class="double-divider"></div>
    <div class="bold" style="font-size: 13px;">ACERTO FINAL:</div>
    <div class="divider"></div>

    @if ($saldoAcerto >= 0)
        <div class="flex bold" style="font-size: 13px;">
            <span>RESTAURANTE PAGA AO MOTOBOY:</span>
            <span>R$ {{ number_format($saldoAcerto / 100, 2, ',', '.') }}</span>
        </div>
        <div style="font-size: 10px; margin-top: 1mm;">
            (Fretes R$ {{ number_format($totalTaxas / 100, 2, ',', '.') }} - Dinheiro na mão R$ {{ number_format($dinheiroColetado / 100, 2, ',', '.') }})
        </div>
    @else
        <div class="flex bold" style="font-size: 13px;">
            <span>MOTOBOY DEVOLVE AO CAIXA:</span>
            <span>R$ {{ number_format(abs($saldoAcerto) / 100, 2, ',', '.') }}</span>
        </div>
        <div style="font-size: 10px; margin-top: 1mm;">
            (Dinheiro cobrado R$ {{ number_format($dinheiroColetado / 100, 2, ',', '.') }} excede os fretes R$ {{ number_format($totalTaxas / 100, 2, ',', '.') }})
        </div>
    @endif

    @if ($concluidas->count() > 0)
        <div class="divider" style="margin-top: 4mm;"></div>
        <div class="bold">PEDIDOS ENTREGUES HOJE:</div>
        @foreach ($concluidas as $p)
            <div class="flex" style="font-size: 10px; margin-top: 1mm;">
                <span>#{{ $p->codigo_publico }} • {{ $p->cliente?->nome ?? 'Cliente' }}</span>
                <span>{{ strtoupper($p->forma_pagamento) }} (R$ {{ number_format($p->total / 100, 2, ',', '.') }})</span>
            </div>
        @endforeach
    @endif

    <div class="divider" style="margin-top: 6mm;"></div>
    <div class="center" style="font-size: 10px;">
        _____________________________________<br>
        Assinatura do Entregador
    </div>

    <div class="divider" style="margin-top: 6mm;"></div>
    <div class="center" style="font-size: 10px;">
        _____________________________________<br>
        Assinatura do Operador / Caixa
    </div>

    <div class="center" style="margin-top: 4mm; font-size: 9px;">
        PedeLivre OS • Sistema de Gestão para Restaurantes
    </div>
</body>
</html>
