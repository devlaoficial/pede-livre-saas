<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acompanhar Pedido #{{ $pedido->codigo_publico }} — {{ $loja->nome }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-full font-sans antialiased py-6 px-4 sm:px-6 lg:px-8" x-data="trackingApp()" x-init="iniciar()">
    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Topo da Loja -->
        <div class="flex items-center justify-between bg-slate-900 border border-slate-800 rounded-2xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-rose-600 flex items-center justify-center font-black text-lg text-white">
                    {{ substr($loja->nome, 0, 1) }}
                </div>
                <div>
                    <h1 class="font-bold text-white text-sm sm:text-base">{{ $loja->nome }}</h1>
                    <p class="text-xs text-slate-400">Pedido <span class="text-rose-400 font-mono font-bold">#{{ $pedido->codigo_publico }}</span> • Canal: <span class="uppercase font-semibold">{{ $pedido->canal }}</span></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($loja->telefone_whatsapp)
                <a href="https://wa.me/55{{ preg_replace('/\D/', '', $loja->telefone_whatsapp) }}?text={{ urlencode('Olá! Gostaria de falar sobre o meu pedido #' . $pedido->codigo_publico) }}"
                   target="_blank"
                   class="text-xs font-bold bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 px-3.5 py-2 rounded-xl transition">
                    💬 WhatsApp
                </a>
                @endif
                <a href="/p/{{ $loja->slug }}" class="text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 px-3.5 py-2 rounded-xl transition">
                    ← Cardápio
                </a>
            </div>
        </div>

        <!-- Status Principal em Tempo Real -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 text-center space-y-4 shadow-2xl">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-extrabold uppercase tracking-wider"
                :class="statusCorBadge()">
                <span class="w-2 h-2 rounded-full bg-current animate-ping"></span>
                <span x-text="statusTitulo()"></span>
            </div>

            <h2 class="text-2xl sm:text-3xl font-black text-white" x-text="statusDescricao()"></h2>
            <p class="text-xs text-slate-400">Atualizado automaticamente em tempo real • Previsão média: {{ $loja->config_json['tempo_medio_preparo_min'] ?? 35 }} min</p>

            <!-- Barra de Progresso em 4 Etapas -->
            <div class="grid grid-cols-4 gap-2 pt-4">
                <div class="space-y-1.5">
                    <div class="h-2 rounded-full transition-all" :class="etapaAtiva >= 1 ? 'bg-emerald-500' : 'bg-slate-800'"></div>
                    <span class="block text-[11px] font-bold" :class="etapaAtiva >= 1 ? 'text-emerald-400' : 'text-slate-500'">1. Recebido</span>
                </div>
                <div class="space-y-1.5">
                    <div class="h-2 rounded-full transition-all" :class="etapaAtiva >= 2 ? 'bg-emerald-500' : 'bg-slate-800'"></div>
                    <span class="block text-[11px] font-bold" :class="etapaAtiva >= 2 ? 'text-emerald-400' : 'text-slate-500'">2. Em Preparo</span>
                </div>
                <div class="space-y-1.5">
                    <div class="h-2 rounded-full transition-all" :class="etapaAtiva >= 3 ? 'bg-emerald-500' : 'bg-slate-800'"></div>
                    <span class="block text-[11px] font-bold" :class="etapaAtiva >= 3 ? 'text-emerald-400' : 'text-slate-500'">3. Pronto / Rota</span>
                </div>
                <div class="space-y-1.5">
                    <div class="h-2 rounded-full transition-all" :class="etapaAtiva >= 4 ? 'bg-emerald-500' : 'bg-slate-800'"></div>
                    <span class="block text-[11px] font-bold" :class="etapaAtiva >= 4 ? 'text-emerald-400' : 'text-slate-500'">4. Concluído</span>
                </div>
            </div>
        </div>

        <!-- Bloco PIX se ainda pendente -->
        @if($pedido->forma_pagamento === 'pix' && $pedido->pagamento)
        <div x-show="pagamentoStatus !== 'aprovado'" class="bg-slate-900 border border-emerald-500/30 rounded-3xl p-6 text-center space-y-4">
            <span class="text-xs font-extrabold uppercase tracking-wider text-emerald-400">Pagamento PIX Aguardando Confirmação</span>
            @if($pedido->pagamento->pix_qr_base64)
                <div class="flex justify-center">
                    <img src="data:image/png;base64,{{ $pedido->pagamento->pix_qr_base64 }}" alt="QR Code PIX" class="w-48 h-48 bg-white p-2 rounded-2xl">
                </div>
            @endif
            @if($pedido->pagamento->pix_copia_cola)
                <div class="space-y-2 max-w-md mx-auto">
                    <textarea readonly class="w-full text-xs font-mono bg-slate-950 text-slate-300 p-3 rounded-xl border border-slate-800 h-20">{{ $pedido->pagamento->pix_copia_cola }}</textarea>
                    <button @click="navigator.clipboard.writeText(@js($pedido->pagamento->pix_copia_cola)); copiado = true" class="w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black py-3 rounded-xl text-sm transition">
                        <span x-text="copiado ? '✓ Código PIX Copiado!' : '📋 Copiar Código PIX'"></span>
                    </button>
                </div>
            @endif
        </div>
        @endif

        <!-- Resumo dos Itens -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4">
            <h3 class="font-bold text-sm uppercase tracking-wider text-slate-400">Itens do Pedido</h3>
            <div class="divide-y divide-slate-800">
                @foreach($pedido->itens as $item)
                <div class="py-3 flex justify-between items-start text-sm">
                    <div>
                        <span class="font-bold text-white">{{ $item->qtd }}x {{ $item->nome_snapshot }}</span>
                        @if($item->variacao_snapshot)
                            <span class="text-xs text-slate-400">({{ $item->variacao_snapshot }})</span>
                        @endif
                        @if(is_array($item->adicionais_json) && count($item->adicionais_json) > 0)
                            <div class="text-xs text-slate-400 mt-0.5">
                                @foreach($item->adicionais_json as $ad)
                                    <span>+ {{ $ad['nome'] ?? '' }}</span>@if(!$loop->last), @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <span class="font-bold text-slate-200">R$ {{ number_format($item->total / 100, 2, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
            <div class="pt-3 border-t border-slate-800 flex justify-between items-center">
                <span class="text-sm font-bold text-slate-400">Total do Pedido</span>
                <span class="text-lg font-black text-emerald-400">R$ {{ number_format($pedido->total / 100, 2, ',', '.') }}</span>
            </div>
        </div>

        <!-- Linha do Tempo de Atualizações -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-3">
            <h3 class="font-bold text-sm uppercase tracking-wider text-slate-400">Linha do Tempo do Pedido</h3>
            <div class="space-y-2.5">
                <template x-for="(ev, idx) in eventos" :key="idx">
                    <div class="flex items-center justify-between text-xs bg-slate-950 border border-slate-800/80 p-3 rounded-xl">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <span class="font-bold text-slate-200" x-text="ev.observacao || ('Status alterado para ' + ev.status_para)"></span>
                        </div>
                        <span class="text-slate-500 font-mono text-[11px]" x-text="ev.horario || ''"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        function trackingApp() {
            return {
                status: @json($pedido->status),
                pagamentoStatus: @json($pedido->pagamento_status),
                eventos: @json($pedido->eventos->map(fn($e) => ['status_para' => $e->status_para, 'observacao' => $e->observacao, 'horario' => $e->created_at?->format('H:i')])->values()),
                copiado: false,
                token: new URLSearchParams(window.location.search).get('t') || '',

                iniciar() {
                    if (this.token) {
                        setInterval(() => this.atualizar(), 5000);
                    }
                },

                atualizar() {
                    fetch(`/api/p/{{ $loja->slug }}/pedidos/{{ $pedido->codigo_publico }}?t=${encodeURIComponent(this.token)}`)
                        .then(r => r.ok ? r.json() : null)
                        .then(d => {
                            const p = d?.pedido || d;
                            if (p && p.status) {
                                if (this.status !== p.status && navigator.vibrate) {
                                    navigator.vibrate([200, 100, 200]);
                                }
                                this.status = p.status;
                                this.pagamentoStatus = p.pagamento_status || this.pagamentoStatus;
                                if (Array.isArray(p.historico_status)) {
                                    this.eventos = p.historico_status.map(h => ({
                                        status_para: h.status,
                                        observacao: h.observacao,
                                        horario: h.data ? new Date(h.data).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) : ''
                                    }));
                                }
                            }
                        })
                        .catch(() => {});
                },

                get etapaAtiva() {
                    if (this.status === 'cancelado') return 0;
                    if (this.status === 'novo') return 1;
                    if (['aceito', 'em_preparo'].includes(this.status)) return 2;
                    if (['pronto', 'saiu_entrega'].includes(this.status)) return 3;
                    if (['entregue', 'retirado', 'servido'].includes(this.status)) return 4;
                    return 1;
                },

                statusCorBadge() {
                    if (this.status === 'cancelado') return 'bg-rose-500/20 text-rose-400 border border-rose-500/30';
                    if (['entregue', 'retirado', 'servido'].includes(this.status)) return 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30';
                    return 'bg-amber-500/20 text-amber-400 border border-amber-500/30';
                },

                statusTitulo() {
                    const map = {
                        novo: 'Aguardando Confirmação',
                        aceito: 'Pedido Aceito',
                        em_preparo: 'Na Cozinha (Em Preparo)',
                        pronto: 'Pedido Pronto!',
                        saiu_entrega: 'Saiu para Entrega 🛵',
                        entregue: 'Pedido Entregue ✓',
                        retirado: 'Pedido Retirado ✓',
                        servido: 'Servido na Mesa ✓',
                        cancelado: 'Pedido Cancelado'
                    };
                    return map[this.status] || this.status;
                },

                statusDescricao() {
                    const map = {
                        novo: 'Recebemos seu pedido! O restaurante já vai confirmar.',
                        aceito: 'O restaurante confirmou seu pedido e iniciou o preparo!',
                        em_preparo: 'A cozinha está preparando seu pedido no capricho.',
                        pronto: 'Seu pedido está prontinho e embalado!',
                        saiu_entrega: 'Nosso entregador já está a caminho do seu endereço.',
                        entregue: 'Pedido entregue! Bom apetite!',
                        retirado: 'Pedido retirado no balcão! Bom apetite!',
                        servido: 'Pedido servido na sua mesa! Bom apetite!',
                        cancelado: 'Este pedido foi cancelado.'
                    };
                    return map[this.status] || '';
                }
            };
        }
    </script>
</body>
</html>
