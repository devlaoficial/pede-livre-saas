<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Extrato de Fechamento de Caixa #{{ $sessao->id }}</title>
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
    <div class="center bold">FECHAMENTO DE TURNO DE CAIXA</div>
    <div class="center">Turno #{{ $sessao->id }}</div>
    <div class="divider"></div>

    <div>Operador: <b>{{ $sessao->usuario?->nome ?? 'Operador' }}</b></div>
    <div>Aberto em: {{ $sessao->aberto_em?->format('d/m/Y H:i:s') }}</div>
    <div>Fechado em: {{ $sessao->fechado_em?->format('d/m/Y H:i:s') ?? now()->format('d/m/Y H:i:s') }}</div>
    <div>Emissão: {{ now()->format('d/m/Y H:i:s') }}</div>

    <div class="double-divider"></div>
    <div class="bold">MOVIMENTAÇÃO EM DINHEIRO (GAVETA)</div>
    <div class="divider"></div>

    @php
        $inicial = $sessao->valor_inicial_centavos;
        $suprimentos = (int) $sessao->movimentos->where('tipo', 'suprimento')->sum('valor_centavos');
        $sangrias = (int) $sessao->movimentos->where('tipo', 'sangria')->sum('valor_centavos');
        $esperado = $inicial + $vendasDinheiro + $suprimentos - $sangrias;
        $final = $sessao->valor_final_centavos ?? $esperado;
        $dif = $sessao->diferenca_centavos ?? ($final - $esperado);
    @endphp

    <div class="flex">
        <span>Fundo de Troco Inicial:</span>
        <span>R$ {{ number_format($inicial / 100, 2, ',', '.') }}</span>
    </div>
    <div class="flex">
        <span>(+) Vendas em Dinheiro:</span>
        <span>R$ {{ number_format($vendasDinheiro / 100, 2, ',', '.') }}</span>
    </div>
    <div class="flex">
        <span>(+) Entradas (Suprimentos):</span>
        <span>R$ {{ number_format($suprimentos / 100, 2, ',', '.') }}</span>
    </div>
    <div class="flex">
        <span>(-) Retiradas (Sangrias):</span>
        <span>- R$ {{ number_format($sangrias / 100, 2, ',', '.') }}</span>
    </div>

    <div class="divider"></div>
    <div class="flex bold">
        <span>SALDO ESPERADO GAVETA:</span>
        <span>R$ {{ number_format($esperado / 100, 2, ',', '.') }}</span>
    </div>
    <div class="flex bold">
        <span>VALOR INFORMADO FÍSICO:</span>
        <span>R$ {{ number_format($final / 100, 2, ',', '.') }}</span>
    </div>
    <div class="flex bold">
        <span>DIFERENÇA / QUEBRA:</span>
        <span>
            @if ($dif == 0)
                R$ 0,00 (EXATO)
            @elseif ($dif > 0)
                + R$ {{ number_format($dif / 100, 2, ',', '.') }} (SOBRA)
            @else
                - R$ {{ number_format(abs($dif) / 100, 2, ',', '.') }} (FALTA)
            @endif
        </span>
    </div>

    <div class="double-divider"></div>
    <div class="bold">FATURAMENTO TOTAL DO TURNO</div>
    <div class="divider"></div>

    <div class="flex">
        <span>Vendas em Dinheiro:</span>
        <span>R$ {{ number_format($vendasDinheiro / 100, 2, ',', '.') }}</span>
    </div>
    <div class="flex">
        <span>Vendas no PIX:</span>
        <span>R$ {{ number_format($vendasPix / 100, 2, ',', '.') }}</span>
    </div>
    <div class="flex">
        <span>Vendas em Cartão / Outros:</span>
        <span>R$ {{ number_format($vendasCartao / 100, 2, ',', '.') }}</span>
    </div>

    <div class="divider"></div>
    <div class="flex bold" style="font-size: 13px;">
        <span>TOTAL FATURADO:</span>
        <span>R$ {{ number_format(($vendasDinheiro + $vendasPix + $vendasCartao) / 100, 2, ',', '.') }}</span>
    </div>
    <div class="flex">
        <span>Total de Pedidos Atendidos:</span>
        <span>{{ $totalPedidos }}</span>
    </div>

    @if ($sessao->movimentos->count() > 0)
        <div class="divider"></div>
        <div class="bold">HISTÓRICO DE LANÇAMENTOS DO TURNO:</div>
        @foreach ($sessao->movimentos as $m)
            <div style="font-size: 10px; margin-top: 1mm;">
                • [{{ $m->created_at->format('H:i') }}] {{ strtoupper($m->tipo) }}: R$ {{ number_format($m->valor_centavos / 100, 2, ',', '.') }}
                @if ($m->observacao)
                    <br>&nbsp;&nbsp;Motivo: {{ $m->observacao }}
                @endif
            </div>
        @endforeach
    @endif

    <div class="divider" style="margin-top: 6mm;"></div>
    <div class="center" style="font-size: 10px;">
        _____________________________________<br>
        Assinatura do Operador de Caixa
    </div>

    <div class="divider" style="margin-top: 6mm;"></div>
    <div class="center" style="font-size: 10px;">
        _____________________________________<br>
        Assinatura da Gerência / Dono
    </div>

    <div class="center" style="margin-top: 4mm; font-size: 9px;">
        PedeLivre OS • Sistema de Gestão para Restaurantes
    </div>
</body>
</html>
