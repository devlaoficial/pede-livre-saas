<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-[#0a0b0e] scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="theme-color" content="{{ $loja->cor_primaria ?: '#ff5a1f' }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/manifest.json">
    <title>{{ $loja->nome }} — Cardápio Digital & Delivery</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        :root {
            --cor-loja: {{ $loja->cor_primaria ?: '#ff5a1f' }};
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #0a0b0e;
        }
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .bg-loja { background-color: var(--cor-loja) !important; }
        .text-loja { color: var(--cor-loja) !important; }
        .border-loja { border-color: var(--cor-loja) !important; }
        
        /* Proporções estritas de imagens para nunca distorcer */
        .thumb-produto {
            width: 104px;
            height: 104px;
            min-width: 104px;
            max-width: 104px;
            min-height: 104px;
            max-height: 104px;
            object-fit: cover;
        }
        .thumb-mais-pedidos {
            width: 58px;
            height: 58px;
            min-width: 58px;
            max-width: 58px;
            min-height: 58px;
            max-height: 58px;
            object-fit: cover;
        }
        .cover-banner {
            height: 180px;
        }
        @media (min-width: 640px) {
            .cover-banner {
                height: 260px;
            }
        }
        @media (min-width: 1024px) {
            .cover-banner {
                height: 300px;
            }
        }
    </style>
</head>
<body class="min-h-full bg-[#0a0b0e] text-slate-100 antialiased pb-28 lg:pb-16 relative overflow-x-hidden" x-data="cardapioApp()" x-init="initApp()">

    @php
        $rawCapa = $loja->capa_url ?: ($loja->capa ?? null);
        $capaUrl = null;
        if ($rawCapa && str_starts_with($rawCapa, 'http')) {
            $capaUrl = $rawCapa;
        } elseif ($rawCapa && file_exists(public_path('storage/' . ltrim($rawCapa, '/')))) {
            $capaUrl = '/storage/' . ltrim($rawCapa, '/');
        } else {
            $capaUrl = 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1600&q=80';
        }

        $rawLogo = $loja->logo_url ?: ($loja->logo ?? null);
        $logoUrl = null;
        if ($rawLogo && str_starts_with($rawLogo, 'http')) {
            $logoUrl = $rawLogo;
        } elseif ($rawLogo && file_exists(public_path('storage/' . ltrim($rawLogo, '/')))) {
            $logoUrl = '/storage/' . ltrim($rawLogo, '/');
        }
    @endphp

    <!-- ============================================================== -->
    <!-- 1. BANNER DE CAPA PANORÂMICO (HERO COVER BANNER)               -->
    <!-- ============================================================== -->
    <div class="relative w-full cover-banner overflow-hidden bg-[#121318]">
        @if($capaUrl)
            <img src="{{ $capaUrl }}" alt="Capa {{ $loja->nome }}" 
                 class="w-full h-full object-cover object-center filter brightness-90 transform scale-105 hover:scale-100 transition duration-1000">
        @else
            <div class="w-full h-full" style="background: radial-gradient(circle at 20% 20%, {{ $loja->cor_primaria ?: '#ff5a1f' }}44, #121318 70%);"></div>
        @endif

        <!-- Gradientes de Vinheta para Legibilidade e Integração Perfeita -->
        <div class="absolute inset-0 bg-gradient-to-t from-[#0a0b0e] via-[#0a0b0e]/50 to-black/40"></div>
        <div class="absolute inset-0 bg-radial-at-c from-transparent via-transparent to-black/60"></div>

        <!-- Barra Superior Flutuante sobre o Banner -->
        <div class="absolute top-4 inset-x-4 sm:inset-x-6 lg:inset-x-8 max-w-7xl mx-auto flex items-center justify-between z-20 pointer-events-auto">
            <!-- Badge de Status de Funcionamento -->
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full bg-black/60 backdrop-blur-md border shadow-lg"
                      :class="lojaStatus.aberto_agora ? 'text-emerald-400 border-emerald-500/30' : 'text-rose-400 border-rose-500/30'">
                    <span class="w-2 h-2 rounded-full" :class="lojaStatus.aberto_agora ? 'bg-emerald-400 animate-ping' : 'bg-rose-400'"></span>
                    <span class="w-2 h-2 rounded-full -ml-3.5" :class="lojaStatus.aberto_agora ? 'bg-emerald-400' : 'bg-rose-400'"></span>
                    <span x-text="lojaStatus.aberto_agora ? 'Aberto agora' : (lojaStatus.mensagem || 'Fechado no momento')"></span>
                </span>
                
                @if($mesa)
                    <span class="inline-flex items-center gap-1 text-xs font-black px-3 py-1.5 rounded-full bg-amber-500/90 text-slate-950 shadow-lg">
                        🪑 Mesa #{{ $mesa['numero'] }}
                    </span>
                @endif
            </div>

            <!-- Botões de Ações Rápidas (Compartilhar & Informações da Loja) -->
            <div class="flex items-center gap-2">
                <button type="button" @click="copiarLinkCardapio()" title="Compartilhar cardápio"
                    class="w-9 h-9 rounded-full bg-black/60 hover:bg-black/80 backdrop-blur-md border border-white/10 text-white flex items-center justify-center transition shadow-lg active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                </button>
                <button type="button" @click="modalInfoLoja = true" title="Ver horários e informações da loja"
                    class="px-3 py-1.5 rounded-full bg-black/60 hover:bg-black/80 backdrop-blur-md border border-white/10 text-xs font-bold text-white flex items-center gap-1.5 transition shadow-lg active:scale-95">
                    <span>ℹ️</span>
                    <span class="hidden sm:inline">Sobre a loja</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- CONTAINER PRINCIPAL DO CONTEÚDO                                -->
    <!-- ============================================================== -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-7 -mt-12 sm:-mt-20">

        <!-- ============================================================== -->
        <!-- 2. IDENTIDADE DA LOJA (LOGO FLUTUANTE + INFO + CANAL)          -->
        <!-- ============================================================== -->
        <header class="bg-[#13141a]/95 backdrop-blur-xl border border-[#232631] rounded-3xl p-4 sm:p-6 shadow-2xl space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 sm:gap-5">
                
                <!-- Logo & Dados Principais -->
                <div class="flex items-center gap-3.5 sm:gap-4">
                    <div class="relative shrink-0">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $loja->nome }}" 
                                 style="width: 72px; height: 72px; min-width: 72px;" 
                                 class="sm:w-20 sm:h-20 rounded-2xl object-cover shadow-2xl border-4 border-[#13141a] bg-[#1a1c24]">
                        @else
                            <div style="width: 72px; height: 72px; min-width: 72px; background-color: var(--cor-loja);"
                                 class="sm:w-20 sm:h-20 rounded-2xl shadow-2xl border-4 border-[#13141a] flex items-center justify-center text-white font-black text-2xl sm:text-3xl">
                                {{ substr($loja->nome, 0, 1) }}
                            </div>
                        @endif
                        <span class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-emerald-500 border-2 border-[#13141a] flex items-center justify-center text-[10px] text-white font-black" title="Loja Verificada">✓</span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                            <h1 class="text-base sm:text-2xl lg:text-3xl font-black text-white tracking-tight leading-tight">{{ $loja->nome }}</h1>
                            <span class="text-[11px] sm:text-xs font-bold px-1.5 py-0.5 rounded-md bg-amber-500/15 text-amber-300 border border-amber-500/30 flex items-center gap-1 shrink-0">
                                <span>⭐</span> {{ number_format($mediaAvaliacao ?? 5.0, 1, ',', '') }}
                            </span>
                        </div>

                        <!-- Metadados: Tempo, Entrega, Pedido Mínimo e Endereço -->
                        <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 text-[11px] sm:text-xs text-slate-300 mt-1 font-medium">
                            <span class="flex items-center gap-1 text-slate-300">
                                <span>⏱️</span>
                                <span>{{ $loja->config_json['tempo_medio_preparo_min'] ?? 35 }}–{{ ($loja->config_json['tempo_medio_preparo_min'] ?? 35) + 15 }} min</span>
                            </span>
                            <template x-if="checkout.canal === 'delivery' && !mesaAtiva">
                                <span class="flex items-center gap-1 text-emerald-400 font-bold">
                                    <span class="text-slate-600 mr-1">•</span>
                                    <span>🛵</span>
                                    <span x-text="freteCentavos === 0 ? 'Frete Grátis' : 'Taxa ' + formatarCentavos(freteCentavos)"></span>
                                </span>
                            </template>
                            <template x-if="pedidoMinimoCentavos > 0">
                                <span class="flex items-center gap-1 text-slate-400">
                                    <span class="text-slate-600 mr-1">•</span>
                                    <span>💰</span>
                                    <span x-text="'Mín. ' + formatarCentavos(pedidoMinimoCentavos)"></span>
                                </span>
                            </template>
                            @if($loja->endereco)
                                <span class="text-slate-600 hidden md:inline">•</span>
                                <span class="hidden md:inline truncate max-w-sm text-slate-400 hover:text-white cursor-pointer transition" @click="modalInfoLoja = true">
                                    📍 {{ $loja->endereco }}, {{ $loja->cidade }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Seletor Delivery / Retirada (Segmented Control) -->
                <div class="flex items-center justify-start md:justify-end shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-[#232631]">
                    <template x-if="mesaAtiva">
                        <div class="flex items-center justify-between w-full md:w-auto gap-3 bg-[#1c1e27] border border-amber-500/40 px-4 py-2.5 rounded-2xl">
                            <span class="text-xs font-black text-amber-300" x-text="'🪑 Mesa #' + mesaAtiva.numero"></span>
                            <button type="button" @click="chamarGarcom()" class="bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black px-3.5 py-1.5 rounded-xl transition shadow">
                                <span x-text="garcomChamado ? '✓ Garçom Chamado!' : '🔔 Chamar Garçom'"></span>
                            </button>
                        </div>
                    </template>
                    <template x-if="!mesaAtiva">
                        <div class="grid grid-cols-2 w-full md:w-auto bg-[#0a0b0e] p-1.5 rounded-2xl border border-[#232631] gap-1 shadow-inner">
                            <button type="button" @click="alterarCanal('delivery')"
                                class="px-6 py-2.5 rounded-xl text-xs font-black transition flex items-center justify-center gap-2"
                                :style="checkout.canal === 'delivery' ? 'background-color: var(--cor-loja); color: white;' : 'color: #94a3b8;'"
                                :class="checkout.canal === 'delivery' ? 'shadow-lg' : 'hover:text-white'">
                                <span>🛵</span>
                                <span>Delivery</span>
                            </button>
                            <button type="button" @click="alterarCanal('retirada')"
                                class="px-6 py-2.5 rounded-xl text-xs font-black transition flex items-center justify-center gap-2"
                                :style="checkout.canal === 'retirada' ? 'background-color: var(--cor-loja); color: white;' : 'color: #94a3b8;'"
                                :class="checkout.canal === 'retirada' ? 'shadow-lg' : 'hover:text-white'">
                                <span>🛍️</span>
                                <span>Retirada</span>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Aviso se Loja estiver fechada no momento -->
            <template x-if="!lojaStatus.aberto_agora">
                <div class="bg-rose-500/10 border border-rose-500/30 rounded-2xl p-3 px-4 flex items-center justify-between gap-2 text-xs text-rose-300 font-semibold">
                    <span>⏰ No momento estamos fechados para novos pedidos. Você pode navegar pelo cardápio!</span>
                    <button type="button" @click="modalInfoLoja = true" class="underline font-bold text-white shrink-0">Ver horários</button>
                </div>
            </template>

            <!-- Atalho para Último Pedido em Andamento -->
            <template x-if="ultimoPedido && ultimoPedido.tracking_url">
                <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-2xl p-2.5 px-4 flex items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2 text-emerald-300 font-bold truncate">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        <span x-text="'Pedido recente #' + ultimoPedido.codigo_publico"></span>
                    </div>
                    <a :href="ultimoPedido.tracking_url" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black px-3 py-1 rounded-xl text-[11px] shrink-0 transition">
                        Acompanhar Pedido →
                    </a>
                </div>
            </template>

            <!-- Faixa Dinâmica de Cupom Ativo (Exibida apenas quando a loja possui cupom ativo real) -->
            <template x-if="cupomDestaque">
                <div class="bg-gradient-to-r from-[#1c1815] to-[#171419] border border-[#3e271a] rounded-2xl p-2 sm:p-2.5 px-3 sm:px-4 flex items-center justify-between gap-2 shadow-md">
                    <div class="flex items-center gap-1.5 sm:gap-2 text-[11px] sm:text-xs text-orange-200/90 font-medium min-w-0">
                        <span class="text-orange-400 text-sm sm:text-base shrink-0">🎟️</span>
                        <span class="truncate">
                            Cupom <strong class="font-black text-white bg-black/40 px-1.5 py-0.5 rounded border border-orange-500/30" x-text="cupomDestaque.codigo"></strong>:
                            <span x-text="cupomDestaque.tipo === 'percentual' ? (cupomDestaque.valor + '% OFF') : (cupomDestaque.tipo === 'frete_gratis' ? 'Frete Grátis' : (formatarCentavos(cupomDestaque.valor) + ' OFF'))"></span>
                            <template x-if="cupomDestaque.pedido_minimo > 0">
                                <span class="text-slate-400" x-text="'(mín. ' + formatarCentavos(cupomDestaque.pedido_minimo) + ')'"></span>
                            </template>
                        </span>
                    </div>
                    <button type="button" @click="aplicarCupomRapido(cupomDestaque)"
                        class="px-2.5 sm:px-3.5 py-1 rounded-xl text-[11px] sm:text-xs font-black text-white transition shrink-0 hover:brightness-110 shadow-sm active:scale-95 flex items-center gap-1 whitespace-nowrap"
                        :style="'background-color: var(--cor-loja)'">
                        <span x-text="cupomAplicado?.codigo === cupomDestaque.codigo ? '✓ Ativo' : 'Ativar'"></span>
                    </button>
                </div>
            </template>
        </header>

        <!-- ============================================================== -->
        <!-- 3. DESTAQUES DA PROMOÇÃO (EXIBIDO APENAS QUANDO HÁ OFERTA REAL)-->
        <!-- ============================================================== -->
        <template x-if="!termoBusca.trim() && produtoPromocaoDestaque">
            <section class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🔥</span>
                        <h2 class="text-lg sm:text-xl font-black text-white tracking-tight">Oferta em Destaque</h2>
                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-400 border border-rose-500/30">Promoção Ativa</span>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-3xl border border-[#2d2824] bg-gradient-to-r from-[#1b1512] via-[#16141a] to-[#121319] p-4 sm:p-7 shadow-2xl">
                    <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full pointer-events-none opacity-20 filter blur-3xl"
                         style="background-color: var(--cor-loja);"></div>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-5 sm:gap-6 items-center relative z-10">
                        <div class="md:col-span-7 space-y-2.5 sm:space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-[10px] sm:text-[11px] font-black uppercase px-2.5 py-1 rounded-full text-white shadow-sm"
                                      :style="'background-color: var(--cor-loja)'">
                                    💥 OFERTA ESPECIAL
                                </span>
                                <span class="text-[10px] sm:text-[11px] font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/25 px-2 py-0.5 rounded-full"
                                      x-text="'Economize ' + formatarCentavos(produtoPromocaoDestaque.preco_centavos - produtoPromocaoDestaque.preco_promocional_centavos)">
                                </span>
                            </div>

                            <h3 class="text-lg sm:text-2xl lg:text-3xl font-black text-white leading-tight"
                                x-text="produtoPromocaoDestaque.nome"></h3>

                            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-xl"
                               x-text="produtoPromocaoDestaque.descricao || 'Aproveite nossa oferta especial preparada com ingredientes frescos e selecionados.'"></p>

                            <div class="flex flex-wrap items-center gap-4 pt-2">
                                <div>
                                    <span class="text-xs text-slate-400 line-through block"
                                          x-text="'De ' + formatarCentavos(produtoPromocaoDestaque.preco_centavos)"></span>
                                    <div class="text-2xl sm:text-3xl font-black text-white tracking-tight"
                                         x-text="formatarCentavos(produtoPromocaoDestaque.preco_promocional_centavos)">
                                    </div>
                                </div>

                                <button type="button" @click="abrirModalProduto(produtoPromocaoDestaque)"
                                    class="px-5 py-3 rounded-2xl text-xs sm:text-sm font-black text-white shadow-xl hover:brightness-110 active:scale-95 transition flex items-center gap-2"
                                    :style="'background-color: var(--cor-loja)'">
                                    <span>Aproveitar Oferta</span>
                                    <span>→</span>
                                </button>
                            </div>
                        </div>

                        <template x-if="imagemProduto(produtoPromocaoDestaque)">
                            <div class="md:col-span-5 flex justify-center md:justify-end">
                                <div class="relative w-full max-w-xs aspect-video sm:aspect-square md:aspect-video rounded-2xl overflow-hidden shadow-2xl border border-white/10 group cursor-pointer"
                                     @click="abrirModalProduto(produtoPromocaoDestaque)">
                                    <img :src="imagemProduto(produtoPromocaoDestaque)" 
                                         :alt="produtoPromocaoDestaque.nome" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </section>
        </template>

        <!-- ============================================================== -->
        <!-- 4. BARRA FIXA DE BUSCA E NAVEGAÇÃO POR CATEGORIAS              -->
        <!-- ============================================================== -->
        <div class="sticky top-0 z-30 bg-[#0a0b0e]/95 backdrop-blur-xl py-3 -mx-4 px-4 sm:mx-0 sm:px-0 flex flex-col md:flex-row md:items-center gap-3 border-b border-[#232631]/80 shadow-xl">
            <!-- Campo de Busca Refinado -->
            <div class="relative md:w-72 shrink-0">
                <input type="text" x-model="termoBusca" placeholder="Buscar no cardápio..."
                    class="w-full bg-[#13141a] border border-[#232631] text-xs text-white rounded-2xl pl-9 pr-8 py-2.5 placeholder-slate-500 focus:outline-none focus:border-slate-400 transition shadow-inner">
                <svg class="w-4 h-4 text-slate-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <button x-show="termoBusca" @click="termoBusca = ''" class="absolute right-3 top-2.5 text-slate-500 hover:text-white text-xs font-bold">✕</button>
            </div>

            <!-- Chips de Categorias com Ícones Temáticos e Contadores -->
            <nav class="flex overflow-x-auto no-scrollbar gap-2 py-0.5 flex-1">
                <template x-for="cat in categoriasFiltradas" :key="cat.id">
                    <button @click="scrollParaCategoria(cat.id)"
                        class="px-4 py-2 rounded-2xl text-xs font-bold whitespace-nowrap transition flex items-center gap-2 shrink-0 shadow-sm"
                        :style="(categoriaAtiva || categoriasFiltradas[0]?.id) === cat.id ? 'background-color: var(--cor-loja); color: white;' : ''"
                        :class="(categoriaAtiva || categoriasFiltradas[0]?.id) === cat.id ? 'shadow-lg scale-102' : 'bg-[#13141a] border border-[#232631] text-slate-300 hover:text-white hover:border-slate-600'">
                        <span x-text="obterIconeCategoria(cat.nome)"></span>
                        <span x-text="nomeCurtoCategoria(cat.nome)"></span>
                        <span class="text-[10px] font-black px-1.5 py-0.2 rounded-full"
                              :class="(categoriaAtiva || categoriasFiltradas[0]?.id) === cat.id ? 'bg-black/30 text-white' : 'bg-[#1e2029] text-slate-400'"
                              x-text="cat.produtos.length"></span>
                    </button>
                </template>
            </nav>
        </div>

        <!-- ============================================================== -->
        <!-- 5. CORPO PRINCIPAL EM 2 COLUNAS (CATÁLOGO + SACOLA FIXA)       -->
        <!-- ============================================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- COLUNA DA ESQUERDA: CATÁLOGO DE PRODUTOS -->
            <main class="lg:col-span-8 space-y-10">
                
                <!-- SEÇÃO 1: MAIS PEDIDOS / SUGESTÕES DA CASA -->
                <template x-if="!termoBusca.trim() && produtosDestaque.length >= 2">
                    <section class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-black text-white flex items-center gap-2">
                                <span>🏆</span>
                                <span>Sugestões da Casa</span>
                            </h2>
                            <span class="text-xs text-slate-400">Os favoritos do cardápio</span>
                        </div>

                        <!-- Grid 2x2 no Desktop com Numeração Estilizada 1, 2, 3, 4 -->
                        <div class="hidden sm:grid sm:grid-cols-2 gap-4">
                            <template x-for="(dest, idx) in produtosDestaque.slice(0, 4)" :key="'dest-desk-' + dest.id">
                                <div @click="abrirModalProduto(dest)"
                                     class="bg-[#13141a] border border-[#232631] hover:border-slate-500 rounded-2xl p-3.5 flex items-center gap-3.5 cursor-pointer transition group shadow-md hover:shadow-xl relative">
                                    <span class="text-2xl font-black shrink-0 w-6 text-center select-none"
                                          :style="'color: var(--cor-loja)'"
                                          x-text="idx + 1"></span>

                                    <!-- Foto Quadrada Estrita 58x58 ou Inicial -->
                                    <div class="rounded-xl overflow-hidden bg-[#1e2029] shrink-0 border border-[#232631] flex items-center justify-center"
                                         style="width: 58px; height: 58px; min-width: 58px;">
                                        <template x-if="imagemProduto(dest)">
                                            <img :src="imagemProduto(dest)" :alt="dest.nome" class="thumb-mais-pedidos group-hover:scale-110 transition duration-300">
                                        </template>
                                        <template x-if="!imagemProduto(dest)">
                                            <span class="text-xl font-black select-none" :style="'color: var(--cor-loja)'" x-text="dest.nome.trim().charAt(0).toUpperCase()"></span>
                                        </template>
                                    </div>

                                    <!-- Título e Preço -->
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-white text-xs sm:text-sm line-clamp-1 group-hover:text-amber-400 transition" x-text="dest.nome"></div>
                                        <p class="text-[11px] text-slate-400 line-clamp-1 mt-0.5" x-text="dest.descricao"></p>
                                        <div class="text-xs font-black text-white mt-1.5" x-text="formatarCentavos(dest.preco_efetivo_centavos)"></div>
                                    </div>

                                    <button type="button" 
                                        class="w-7 h-7 rounded-full text-white font-black text-xs flex items-center justify-center shrink-0 shadow-sm group-hover:scale-105 transition"
                                        :style="'background-color: var(--cor-loja)'">
                                        +
                                    </button>
                                </div>
                            </template>
                        </div>

                        <!-- Carrossel no Mobile -->
                        <div class="sm:hidden flex overflow-x-auto no-scrollbar gap-3 pb-1">
                            <template x-for="dest in produtosDestaque" :key="'dest-mob-' + dest.id">
                                <div @click="abrirModalProduto(dest)"
                                     class="w-44 shrink-0 bg-[#13141a] border border-[#232631] rounded-2xl p-3 flex flex-col justify-between cursor-pointer group shadow-md">
                                    <div class="w-full aspect-square rounded-xl overflow-hidden bg-[#1e2029] mb-2.5 flex items-center justify-center">
                                        <template x-if="imagemProduto(dest)">
                                            <img :src="imagemProduto(dest)" :alt="dest.nome" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                        </template>
                                        <template x-if="!imagemProduto(dest)">
                                            <span class="text-3xl font-black select-none" :style="'color: var(--cor-loja)'" x-text="dest.nome.trim().charAt(0).toUpperCase()"></span>
                                        </template>
                                    </div>
                                    <div>
                                        <div class="font-bold text-white text-xs line-clamp-2 leading-snug" x-text="dest.nome"></div>
                                        <div class="text-xs font-black text-white mt-2 flex items-center justify-between">
                                            <span x-text="formatarCentavos(dest.preco_efetivo_centavos)"></span>
                                            <span class="w-6 h-6 rounded-full text-white font-black text-xs flex items-center justify-center shadow"
                                                  :style="'background-color: var(--cor-loja)'">+</span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </section>
                </template>

                <!-- Estado Vazio de Busca ou Cardápio em Cadastro -->
                <template x-if="categoriasFiltradas.length === 0">
                    <div class="bg-[#13141a] border border-[#232631] rounded-3xl p-12 text-center space-y-3">
                        <div class="text-4xl" x-text="termoBusca ? '🔍' : '🍽️'"></div>
                        <h3 class="text-base font-bold text-white" x-text="termoBusca ? 'Nenhum produto encontrado' : 'Cardápio em atualização'"></h3>
                        <p class="text-xs text-slate-400 max-w-sm mx-auto" x-text="termoBusca ? 'Tente buscar por outro termo ou limpar o filtro.' : 'Os produtos desta loja estão sendo atualizados e estarão disponíveis em instantes.'"></p>
                        <button x-show="termoBusca" @click="termoBusca = ''" 
                                class="mt-2 inline-flex text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow-md"
                                :style="'background-color: var(--cor-loja)'">
                            Limpar busca e ver cardápio
                        </button>
                    </div>
                </template>

                <!-- ============================================================== -->
                <!-- SEÇÕES DE CATEGORIAS ORGANIZADAS                               -->
                <!-- ============================================================== -->
                <template x-for="categoria in categoriasFiltradas" :key="categoria.id">
                    <section :id="'categoria-' + categoria.id" class="space-y-4 scroll-mt-24">
                        <div class="border-b border-[#232631] pb-2.5 flex items-baseline justify-between gap-2">
                            <div>
                                <h2 class="text-lg sm:text-xl font-black text-white flex items-center gap-2">
                                    <span x-text="obterIconeCategoria(categoria.nome)"></span>
                                    <span x-text="categoria.nome"></span>
                                </h2>
                                <p class="text-xs text-slate-400 mt-1" 
                                   x-text="categoria.descricao || obterSubtituloCategoria(categoria.nome)"></p>
                            </div>
                            <span class="text-xs font-bold text-slate-500 shrink-0" x-text="categoria.produtos.length + (categoria.produtos.length === 1 ? ' item' : ' itens')"></span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <template x-for="produto in categoria.produtos" :key="produto.id">
                                <article @click="abrirModalProduto(produto)"
                                    class="bg-[#13141a] border border-[#232631] hover:border-slate-500/80 rounded-2xl p-4 transition-all duration-200 cursor-pointer flex justify-between gap-3.5 relative group shadow-md hover:shadow-xl">
                                    
                                    <!-- Informações do Item (Esquerda) -->
                                    <div class="flex-1 min-w-0 flex flex-col justify-between pr-2">
                                        <div>
                                            <template x-if="produto.preco_promocional_centavos && produto.preco_promocional_centavos < produto.preco_centavos">
                                                <span class="bg-emerald-500/15 text-emerald-400 border border-emerald-500/25 text-[10px] font-black px-2 py-0.5 rounded-md inline-block mb-1.5"
                                                      x-text="'-' + Math.round((1 - produto.preco_promocional_centavos / produto.preco_centavos) * 100) + '% OFF'"></span>
                                            </template>

                                            <h3 class="font-bold text-white text-sm sm:text-base leading-snug group-hover:text-amber-400 transition" x-text="produto.nome"></h3>
                                            <p class="text-xs text-slate-400 line-clamp-2 mt-1 leading-relaxed" x-text="produto.descricao"></p>
                                        </div>

                                        <div class="mt-3 flex items-baseline gap-2">
                                            <template x-if="produto.variacoes && produto.variacoes.length > 0">
                                                <span class="text-[11px] text-slate-400 font-medium">A partir de</span>
                                            </template>
                                            <span class="text-sm sm:text-base font-black text-white"
                                                  x-text="formatarCentavos(produto.variacoes?.length > 0 ? Math.min(...produto.variacoes.map(v => v.preco_centavos)) : produto.preco_efetivo_centavos)"></span>
                                            
                                            <span x-show="produto.preco_promocional_centavos && produto.preco_promocional_centavos < produto.preco_centavos"
                                                  class="text-xs text-slate-500 line-through"
                                                  x-text="formatarCentavos(produto.preco_centavos)"></span>
                                        </div>
                                    </div>

                                    <!-- Imagem com Proporção Estrita 104x104 e Botão de Ação -->
                                    <div class="relative shrink-0 flex flex-col justify-between items-end">
                                        <div class="rounded-2xl overflow-hidden bg-[#1e2029] flex items-center justify-center border border-[#232631]"
                                             style="width: 104px; height: 104px; min-width: 104px;">
                                            <template x-if="imagemProduto(produto)">
                                                <img :src="imagemProduto(produto)" :alt="produto.nome" loading="lazy" 
                                                     class="thumb-produto group-hover:scale-105 transition duration-300">
                                            </template>
                                            <template x-if="!imagemProduto(produto)">
                                                <span class="text-3xl font-black select-none"
                                                      :style="'color: var(--cor-loja)'"
                                                      x-text="produto.nome.trim().charAt(0).toUpperCase()"></span>
                                            </template>
                                        </div>

                                        <template x-if="qtdProdutoNaSacola(produto.id) > 0">
                                            <div class="absolute right-1.5 bottom-1.5 px-2 py-0.5 rounded-full text-white font-black text-xs flex items-center gap-1 shadow-lg"
                                                 :style="'background-color: var(--cor-loja)'">
                                                <span x-text="qtdProdutoNaSacola(produto.id) + 'x'"></span>
                                            </div>
                                        </template>
                                        <template x-if="qtdProdutoNaSacola(produto.id) === 0">
                                            <button type="button" 
                                                class="w-7 h-7 rounded-full text-white font-black text-sm flex items-center justify-center shadow-lg transition active:scale-90 absolute right-1.5 bottom-1.5"
                                                :style="'background-color: var(--cor-loja)'">
                                                +
                                            </button>
                                        </template>
                                    </div>

                                </article>
                            </template>
                        </div>
                    </section>
                </template>

            </main>

            <!-- ============================================================== -->
            <!-- 6. COLUNA DA DIREITA: SACOLA STICKY DESKTOP                    -->
            <!-- ============================================================== -->
            <aside class="hidden lg:block lg:col-span-4 sticky top-20">
                <div class="bg-[#13141a] border border-[#232631] rounded-3xl p-5 shadow-2xl space-y-4">
                    
                    <div class="flex items-center justify-between border-b border-[#232631] pb-3">
                        <div>
                            <h3 class="font-black text-white text-base">Sua sacola</h3>
                            <p class="text-xs text-slate-400 mt-0.5" 
                               x-text="(checkout.canal === 'delivery' ? '🛵 Delivery' : (mesaAtiva ? '🪑 Mesa #' + mesaAtiva.numero : '🛍️ Retirada')) + ' · ' + totalItensSacola + (totalItensSacola === 1 ? ' item' : ' itens')"></p>
                        </div>
                        <template x-if="carrinho.length > 0">
                            <button type="button" @click="limparSacola()" class="text-[11px] text-slate-500 hover:text-rose-400 transition font-bold">
                                Limpar
                            </button>
                        </template>
                    </div>

                    <!-- Barra Progressiva de Frete Grátis (Quando configurado pela loja) -->
                    <template x-if="checkout.canal === 'delivery' && !mesaAtiva && metaFreteGratisCentavos > 0">
                        <div class="bg-[#0a0b0e] border border-[#232631] rounded-2xl p-3.5 text-xs space-y-2">
                            <div class="flex items-center justify-between font-bold">
                                <span class="text-emerald-400 flex items-center gap-1.5">
                                    <span>🚚</span>
                                    <span x-text="faltaParaFreteGratisCentavos === 0 ? 'Parabéns! Frete grátis liberado!' : 'Faltam ' + formatarCentavos(faltaParaFreteGratisCentavos) + ' para frete grátis'"></span>
                                </span>
                                <span class="text-slate-400 font-bold" x-text="progressoFreteGratisPct + '%'"></span>
                            </div>
                            <div class="w-full h-2 bg-[#1e2029] rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500"
                                     :style="'width: ' + progressoFreteGratisPct + '%; background-color: #10b981;'"></div>
                            </div>
                        </div>
                    </template>

                    <!-- Estado Vazio da Sacola -->
                    <template x-if="carrinho.length === 0">
                        <div class="text-center py-10 space-y-2">
                            <div class="text-4xl">🛍️</div>
                            <p class="text-sm font-bold text-white">Sua sacola está vazia</p>
                            <p class="text-xs text-slate-400">Escolha seus itens favoritos no cardápio para montar seu pedido.</p>
                        </div>
                    </template>

                    <!-- Lista de Itens na Sacola -->
                    <template x-if="carrinho.length > 0">
                        <div class="space-y-4">
                            <div class="divide-y divide-[#232631] max-h-72 overflow-y-auto pr-1">
                                <template x-for="(item, idx) in carrinho" :key="idx">
                                    <div class="py-3 flex justify-between items-start gap-2">
                                        <div class="text-xs min-w-0 flex-1">
                                            <div class="font-bold text-white leading-tight" x-text="item.nome"></div>
                                            <div class="text-slate-400 text-xs mt-0.5" x-text="formatarCentavos(item.total_centavos)"></div>
                                            <template x-if="item.variacao_nome">
                                                <div class="text-slate-500 text-[11px]" x-text="'Opção: ' + item.variacao_nome"></div>
                                            </template>
                                            <template x-if="item.adicionais && item.adicionais.length > 0">
                                                <div class="text-slate-500 text-[10px] truncate" x-text="'+ ' + item.adicionais.map(a => a.nome).join(', ')"></div>
                                            </template>
                                            <template x-if="item.observacao">
                                                <div class="text-amber-400/90 text-[10px] truncate" x-text="'Obs: ' + item.observacao"></div>
                                            </template>
                                        </div>

                                        <div class="flex items-center gap-1.5 bg-[#1c1e27] border border-[#2a2d39] rounded-xl px-2 py-0.5 shrink-0">
                                            <button type="button" @click="alterarQtdCarrinho(idx, -1)"
                                                class="text-slate-400 hover:text-white font-black text-xs px-1">−</button>
                                            <span class="text-xs font-black text-white w-4 text-center" x-text="item.qtd"></span>
                                            <button type="button" @click="alterarQtdCarrinho(idx, 1)"
                                                class="text-slate-400 hover:text-white font-black text-xs px-1">+</button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Totais Financeiros & Fechamento -->
                            <div class="border-t border-[#232631] pt-3.5 space-y-2.5 text-xs">
                                <div class="flex justify-between text-slate-400">
                                    <span>Subtotal</span>
                                    <span class="font-bold text-white" x-text="formatarCentavos(subtotalCarrinhoCentavos)"></span>
                                </div>

                                <template x-if="descontoCupomCentavos > 0">
                                    <div class="flex justify-between text-emerald-400 font-bold">
                                        <span x-text="'Cupom ' + cupomAplicado.codigo"></span>
                                        <span x-text="'- ' + formatarCentavos(descontoCupomCentavos)"></span>
                                    </div>
                                </template>

                                <template x-if="checkout.canal === 'delivery' && !mesaAtiva">
                                    <div class="flex justify-between text-slate-400">
                                        <span>Entrega</span>
                                        <span class="font-bold" :class="freteCentavos === 0 ? 'text-emerald-400' : 'text-white'"
                                              x-text="freteCentavos === 0 ? 'Grátis' : formatarCentavos(freteCentavos)"></span>
                                    </div>
                                </template>

                                <div class="flex justify-between items-baseline pt-2.5 border-t border-[#232631]">
                                    <span class="text-xs font-black text-slate-300 uppercase tracking-wider">Total</span>
                                    <span class="text-2xl font-black text-white" x-text="formatarCentavos(totalFinalCentavos)"></span>
                                </div>

                                <button @click="abrirCheckout()"
                                    class="w-full text-white font-black py-3.5 rounded-2xl text-sm shadow-xl hover:brightness-110 active:scale-98 transition flex items-center justify-center gap-2 mt-2"
                                    :style="'background-color: var(--cor-loja)'">
                                    <span>Finalizar pedido</span>
                                    <span>→</span>
                                </button>

                                <p class="text-[11px] text-slate-500 text-center pt-1 font-medium flex items-center justify-center gap-1.5">
                                    <span>🔒 Pagamento seguro via PIX, cartão ou dinheiro</span>
                                </p>
                            </div>
                        </div>
                    </template>
                </div>
            </aside>

        </div>

    </div>

    <!-- ============================================================== -->
    <!-- 7. BARRA FLUTUANTE INFERIOR MOBILE                             -->
    <!-- ============================================================== -->
    <div x-cloak x-show="carrinho.length > 0" class="lg:hidden fixed bottom-4 inset-x-3 z-40 max-w-lg mx-auto pointer-events-none">
        <div @click="abrirCheckout()"
             class="rounded-2xl py-3 px-4 shadow-2xl flex items-center justify-between gap-3 pointer-events-auto cursor-pointer active:scale-98 transition backdrop-blur-md"
             :style="'background-color: var(--cor-loja)'">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="w-6 h-6 rounded-full bg-black/40 text-white font-black text-xs flex items-center justify-center shrink-0 select-none" 
                      x-text="totalItensSacola"></span>
                <span class="text-white font-extrabold text-sm whitespace-nowrap">Ver sacola</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-white font-black text-sm sm:text-base shrink-0 whitespace-nowrap tracking-tight" 
                      x-text="formatarCentavos(totalFinalCentavos)"></span>
                <span class="text-white text-xs">→</span>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 8. MODAL DE CUSTOMIZAÇÃO DO PRODUTO (VARIAÇÕES & ADICIONAIS)   -->
    <!-- ============================================================== -->
    <div x-cloak x-show="modalAberto" class="fixed inset-0 z-50 overflow-y-auto bg-black/85 backdrop-blur-md flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="modalAberto = false" class="bg-[#13141a] border border-[#232631] w-full max-w-lg rounded-t-3xl sm:rounded-3xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden">
            <!-- Capa do Produto no Modal -->
            <div class="relative h-48 sm:h-56 w-full bg-[#1e2029] shrink-0 flex items-center justify-center">
                <template x-if="produtoSelecionado && imagemProduto(produtoSelecionado)">
                    <img :src="imagemProduto(produtoSelecionado)" :alt="produtoSelecionado?.nome" class="w-full h-full object-cover">
                </template>
                <template x-if="!produtoSelecionado || !imagemProduto(produtoSelecionado)">
                    <span class="text-6xl font-black select-none opacity-40" :style="'color: var(--cor-loja)'" x-text="produtoSelecionado?.nome ? produtoSelecionado.nome.charAt(0).toUpperCase() : ''"></span>
                </template>
                <div class="absolute inset-0 bg-gradient-to-t from-[#13141a] via-[#13141a]/40 to-transparent"></div>
                <button @click="modalAberto = false" class="absolute top-3 right-3 w-9 h-9 rounded-full bg-black/70 border border-white/10 text-white hover:bg-black font-black flex items-center justify-center shadow-md">✕</button>
                <div class="absolute bottom-3 left-4 right-4 text-white">
                    <h3 class="font-black text-lg sm:text-xl leading-tight" x-text="produtoSelecionado?.nome"></h3>
                </div>
            </div>

            <div class="p-4 sm:p-5 overflow-y-auto space-y-4 flex-1">
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed" x-text="produtoSelecionado?.descricao"></p>

                <!-- Variações de Tamanho -->
                <template x-if="produtoSelecionado?.variacoes?.length > 0">
                    <div class="space-y-2 bg-[#0a0b0e] p-3.5 rounded-2xl border border-[#232631]">
                        <div class="flex justify-between items-center">
                            <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-300">Escolha a Opção</h4>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400">Obrigatório</span>
                        </div>
                        <div class="space-y-1.5 pt-1">
                            <template x-for="varItem in produtoSelecionado.variacoes" :key="varItem.id">
                                <label class="flex items-center justify-between p-3 bg-[#13141a] rounded-xl border cursor-pointer transition"
                                    :class="variacaoSelecionadaId === varItem.id ? 'border-amber-500 bg-amber-500/10' : 'border-[#232631] hover:border-slate-500'">
                                    <div class="flex items-center gap-2.5">
                                        <input type="radio" :value="varItem.id" x-model="variacaoSelecionadaId" class="text-amber-500 focus:ring-amber-500">
                                        <span class="text-xs sm:text-sm font-bold text-white" x-text="varItem.nome"></span>
                                    </div>
                                    <span class="text-xs sm:text-sm font-black text-emerald-400" x-text="formatarCentavos(varItem.preco_centavos)"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Grupos de Adicionais -->
                <template x-for="grupo in produtoSelecionado?.grupos_adicionais" :key="grupo.id">
                    <div class="space-y-2 bg-[#0a0b0e] p-3.5 rounded-2xl border"
                        :class="grupoSatisfeito(grupo) ? 'border-emerald-500/30' : (grupo.obrigatorio ? 'border-rose-500/40' : 'border-[#232631]')">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="text-xs font-extrabold uppercase tracking-wider text-white" x-text="grupo.nome"></h4>
                                <span class="text-[11px] text-slate-400" x-text="grupo.maximo === 1 ? 'Escolha 1 opção' : 'Escolha até ' + grupo.maximo + ' opções'"></span>
                            </div>
                            <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full"
                                :class="grupoSatisfeito(grupo) ? 'bg-emerald-500/15 text-emerald-400' : (grupo.obrigatorio ? 'bg-rose-500/15 text-rose-400' : 'bg-[#1e2029] text-slate-400')"
                                x-text="grupoSatisfeito(grupo) ? '✓ OK' : (grupo.obrigatorio ? 'Obrigatório' : 'Opcional')"></span>
                        </div>

                        <div class="space-y-1.5 pt-1">
                            <template x-for="opcao in grupo.opcoes" :key="opcao.id">
                                <div @click="toggleAdicional(grupo, opcao)"
                                    class="flex items-center justify-between p-2.5 bg-[#13141a] rounded-xl border cursor-pointer transition select-none"
                                    :class="adicionalSelecionado(opcao.id) ? 'border-amber-500 bg-amber-500/10' : 'border-[#232631] hover:border-slate-500'">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-4 h-4 rounded-full border flex items-center justify-center text-[10px] font-bold"
                                            :class="adicionalSelecionado(opcao.id) ? 'bg-amber-500 border-amber-500 text-slate-950 font-black' : 'border-slate-600'">
                                            <span x-show="adicionalSelecionado(opcao.id)">✓</span>
                                        </span>
                                        <span class="text-xs font-semibold text-white" x-text="opcao.nome"></span>
                                    </div>
                                    <span class="text-xs font-bold" :class="opcao.preco_centavos > 0 ? 'text-white' : 'text-emerald-400'"
                                        x-text="opcao.preco_centavos > 0 ? '+ ' + formatarCentavos(opcao.preco_centavos) : 'Grátis'"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Observação do Item -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Alguma observação neste item?</label>
                    <input type="text" x-model="observacaoItem" placeholder="Ex: Sem cebola, ponto da carne bem passado..."
                        class="w-full bg-[#0a0b0e] border border-[#232631] rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-500">
                </div>
            </div>

            <!-- Footer do Modal -->
            <div class="p-4 border-t border-[#232631] bg-[#13141a] flex items-center justify-between gap-3">
                <div class="flex items-center bg-[#0a0b0e] rounded-xl p-1 border border-[#232631]">
                    <button type="button" @click="qtdModal = Math.max(1, qtdModal - 1)"
                        class="w-8 h-8 rounded-lg bg-[#13141a] text-white font-black text-sm hover:bg-[#1e2029]">−</button>
                    <span class="w-8 text-center font-black text-sm text-white" x-text="qtdModal"></span>
                    <button type="button" @click="qtdModal++"
                        class="w-8 h-8 rounded-lg bg-[#13141a] text-white font-black text-sm hover:bg-[#1e2029]">+</button>
                </div>

                <button @click="adicionarAoCarrinho()" :disabled="!modalValido"
                    class="flex-1 text-white font-extrabold py-3.5 px-4 rounded-xl shadow-lg active:scale-95 disabled:opacity-40 transition-all text-xs sm:text-sm flex items-center justify-between"
                    :style="'background-color: var(--cor-loja)'">
                    <span x-text="mensagemBotaoModal"></span>
                    <span class="font-black" x-text="formatarCentavos(subtotalModalCentavos)"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 9. MODAL DE INFORMAÇÕES DA LOJA (HORÁRIOS & ENDEREÇO)          -->
    <!-- ============================================================== -->
    <div x-cloak x-show="modalInfoLoja" class="fixed inset-0 z-50 overflow-y-auto bg-black/85 backdrop-blur-md flex items-center justify-center p-4">
        <div @click.away="modalInfoLoja = false" class="bg-[#13141a] border border-[#232631] w-full max-w-md rounded-3xl p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-[#232631] pb-3">
                <h3 class="font-black text-lg text-white">Sobre {{ $loja->nome }}</h3>
                <button @click="modalInfoLoja = false" class="w-8 h-8 rounded-full bg-[#1e2029] text-slate-400 hover:text-white flex items-center justify-center font-bold">✕</button>
            </div>

            <div class="space-y-4 text-xs">
                <div>
                    <span class="text-slate-400 block font-bold uppercase mb-1">📍 Endereço</span>
                    <p class="text-white font-medium">{{ $loja->endereco ?: 'Consulte no WhatsApp' }}{{ $loja->cidade ? ', ' . $loja->cidade : '' }}{{ $loja->uf ? '/' . $loja->uf : '' }}</p>
                </div>

                <div>
                    <span class="text-slate-400 block font-bold uppercase mb-1">⏱️ Tempo Médio de Preparo</span>
                    <p class="text-white font-medium">{{ $loja->config_json['tempo_medio_preparo_min'] ?? 35 }} a {{ ($loja->config_json['tempo_medio_preparo_min'] ?? 35) + 15 }} minutos</p>
                </div>

                <template x-if="areasEntrega.length > 0">
                    <div>
                        <span class="text-slate-400 block font-bold uppercase mb-1.5">🛵 Taxas por Região / Bairro</span>
                        <div class="max-h-36 overflow-y-auto space-y-1 bg-[#0a0b0e] p-2.5 rounded-xl border border-[#232631]">
                            <template x-for="area in areasEntrega" :key="area.id">
                                <div class="flex justify-between text-[11px]">
                                    <span class="text-slate-300" x-text="area.bairro_nome || area.nome"></span>
                                    <span class="font-bold text-emerald-400" x-text="area.taxa_centavos === 0 ? 'Grátis' : formatarCentavos(area.taxa_centavos)"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <div>
                    <span class="text-slate-400 block font-bold uppercase mb-1">💳 Formas de Pagamento Aceitas</span>
                    <div class="flex flex-wrap gap-2 pt-1">
                        <span class="px-2.5 py-1 rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 font-bold">⚡ PIX Instantâneo</span>
                        <span class="px-2.5 py-1 rounded-lg bg-[#1e2029] text-white border border-[#232631] font-bold">💳 Cartão de Crédito/Débito</span>
                        <span class="px-2.5 py-1 rounded-lg bg-[#1e2029] text-white border border-[#232631] font-bold">💵 Dinheiro</span>
                    </div>
                </div>

                @if($loja->telefone_whatsapp)
                <div class="pt-2">
                    <a href="https://wa.me/55{{ preg_replace('/\D/', '', $loja->telefone_whatsapp) }}" target="_blank"
                       class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black py-3 rounded-2xl flex items-center justify-center gap-2 shadow-lg transition">
                        <span>💬 Conversar no WhatsApp</span>
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 10. MODAL DE CHECKOUT, ORDER BUMP, CUPOM & PIX QR CODE         -->
    <!-- ============================================================== -->
    <div x-cloak x-show="sacolaAberta" class="fixed inset-0 z-50 overflow-y-auto bg-black/85 backdrop-blur-md flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="sacolaAberta = false" class="bg-[#13141a] border border-[#232631] w-full max-w-lg rounded-t-3xl sm:rounded-3xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden">
            <div class="p-4 border-b border-[#232631] flex items-center justify-between sticky top-0 bg-[#13141a] z-10">
                <h3 class="font-black text-base text-white" x-text="pedidoCriado ? 'Pedido #' + pedidoCriado.codigo_publico + ' Confirmado!' : 'Finalizar Seu Pedido'"></h3>
                <button @click="sacolaAberta = false" class="p-1.5 rounded-full bg-[#1e2029] text-slate-400 hover:text-white">✕</button>
            </div>

            <div class="p-4 sm:p-5 overflow-y-auto space-y-4">
                <!-- Tela de PIX Gerado / Sucesso -->
                <template x-if="pedidoCriado">
                    <div class="text-center space-y-4 py-2">
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-extrabold">
                            ✓ Pedido registrado! Total: <span x-text="formatarCentavos(pedidoCriado.total_centavos)"></span>
                        </div>

                        <!-- QR Code Base64 ou QR Code Gerado via BRCode -->
                        <template x-if="pedidoCriado.pix?.qr_code_base64">
                            <div class="flex flex-col items-center space-y-2">
                                <img :src="'data:image/png;base64,' + pedidoCriado.pix.qr_code_base64" alt="QR Code PIX" class="w-52 h-52 bg-white rounded-2xl p-2 shadow-md">
                                <span class="text-xs text-slate-400">Escaneie o QR Code no app do seu banco ou copie o código PIX:</span>
                            </div>
                        </template>
                        <template x-if="!pedidoCriado.pix?.qr_code_base64 && pedidoCriado.pix?.copia_e_cola">
                            <div class="flex flex-col items-center space-y-2">
                                <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' + encodeURIComponent(pedidoCriado.pix.copia_e_cola)" alt="QR Code PIX" class="w-52 h-52 bg-white rounded-2xl p-2 shadow-md">
                                <span class="text-xs text-slate-400">Escaneie o QR Code no app do seu banco ou copie o código PIX:</span>
                            </div>
                        </template>

                        <template x-if="pedidoCriado.pix?.copia_e_cola">
                            <div class="space-y-2">
                                <textarea readonly x-text="pedidoCriado.pix.copia_e_cola" class="w-full text-[11px] font-mono bg-[#0a0b0e] text-white p-2.5 rounded-xl border border-[#232631] h-20"></textarea>
                                <button type="button" @click="navigator.clipboard.writeText(pedidoCriado.pix.copia_e_cola); copiado = true" 
                                    class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold py-3 rounded-xl text-sm shadow-md">
                                    <span x-text="copiado ? '✓ Código PIX Copiado!' : '📋 Copiar PIX Copia e Cola'"></span>
                                </button>
                            </div>
                        </template>

                        <div class="flex flex-col sm:flex-row gap-2 pt-2">
                            <template x-if="pedidoCriado.tracking_url">
                                <a :href="pedidoCriado.tracking_url" class="flex-1 bg-[#1e2029] hover:bg-[#252835] text-white font-extrabold py-3 rounded-xl text-xs text-center border border-[#232631]">
                                    📍 Acompanhar Pedido em Tempo Real →
                                </a>
                            </template>
                            @if($loja->telefone_whatsapp)
                            <a :href="'https://wa.me/55{{ preg_replace('/\D/', '', $loja->telefone_whatsapp) }}?text=' + encodeURIComponent('Olá! Acabei de fazer o pedido #' + pedidoCriado.codigo_publico + ' pelo cardápio digital.')"
                               target="_blank"
                               class="flex-1 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 border border-emerald-500/30 font-extrabold py-3 rounded-xl text-xs text-center">
                                💬 WhatsApp da Loja ↗
                            </a>
                            @endif
                        </div>
                    </div>
                </template>

                <!-- Formulário de Finalização -->
                <template x-if="!pedidoCriado">
                    <div class="space-y-4">
                        <template x-if="erroCheckout">
                            <div class="bg-rose-500/15 border border-rose-500/30 text-rose-400 text-xs font-bold p-3 rounded-xl" x-text="erroCheckout"></div>
                        </template>

                        <!-- Revisão Rápida dos Itens no Mobile -->
                        <div class="bg-[#0a0b0e] border border-[#232631] rounded-2xl p-3 space-y-2">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-300">
                                <span x-text="'Itens na sacola (' + totalItensSacola + ')'"></span>
                                <button type="button" @click="sacolaAberta = false" class="text-[11px] text-amber-400 hover:underline">+ Adicionar mais</button>
                            </div>
                            <div class="divide-y divide-[#1e2029] max-h-36 overflow-y-auto pr-1">
                                <template x-for="(item, idx) in carrinho" :key="'chk-item-' + idx">
                                    <div class="py-2 flex items-center justify-between gap-2 text-xs">
                                        <div class="min-w-0 flex-1">
                                            <span class="font-bold text-white" x-text="item.qtd + 'x ' + item.nome"></span>
                                            <span class="text-slate-400 ml-1" x-text="formatarCentavos(item.total_centavos)"></span>
                                        </div>
                                        <div class="flex items-center gap-1 bg-[#13141a] border border-[#232631] rounded-lg px-1.5 py-0.5">
                                            <button type="button" @click="alterarQtdCarrinho(idx, -1)" class="text-slate-400 hover:text-white font-black px-1">−</button>
                                            <span class="font-bold text-white text-[11px]" x-text="item.qtd"></span>
                                            <button type="button" @click="alterarQtdCarrinho(idx, 1)" class="text-slate-400 hover:text-white font-black px-1">+</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Order Bump: Sugestão Inteligente de Bebida / Acompanhamento -->
                        <template x-if="sugestoesOrderBump.length > 0">
                            <div class="bg-gradient-to-r from-[#171822] to-[#13141a] border border-amber-500/25 rounded-2xl p-3 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-300">⚡ Que tal adicionar para acompanhar?</span>
                                </div>
                                <div class="flex overflow-x-auto no-scrollbar gap-2.5 pb-1">
                                    <template x-for="sug in sugestoesOrderBump" :key="'bump-' + sug.id">
                                        <div class="shrink-0 w-44 bg-[#0a0b0e] border border-[#232631] rounded-xl p-2.5 flex items-center justify-between gap-2">
                                            <div class="min-w-0 flex-1">
                                                <div class="text-xs font-bold text-white truncate" x-text="sug.nome"></div>
                                                <div class="text-[11px] font-black text-emerald-400" x-text="'+ ' + formatarCentavos(sug.preco_efetivo_centavos)"></div>
                                            </div>
                                            <button type="button" @click="adicionarOrderBumpRapido(sug)"
                                                class="w-7 h-7 rounded-lg text-white font-black text-xs flex items-center justify-center shrink-0 shadow"
                                                :style="'background-color: var(--cor-loja)'">+</button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Dados do Cliente -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="text-[11px] font-bold uppercase text-slate-400 block mb-1">Seu Nome *</label>
                                <input type="text" x-model="checkout.nome" placeholder="Digite seu nome completo" 
                                    class="w-full bg-[#0a0b0e] border border-[#232631] text-xs text-white rounded-xl px-3.5 py-2.5 focus:border-amber-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="text-[11px] font-bold uppercase text-slate-400 block mb-1">Seu WhatsApp *</label>
                                <input type="tel" x-model="checkout.telefone" placeholder="(00) 90000-0000" 
                                    class="w-full bg-[#0a0b0e] border border-[#232631] text-xs text-white rounded-xl px-3.5 py-2.5 focus:border-amber-500 focus:outline-none">
                            </div>
                        </div>

                        <!-- Endereço se Delivery -->
                        <template x-if="checkout.canal === 'delivery' && !mesaAtiva">
                            <div class="space-y-2.5 bg-[#0a0b0e] p-3.5 rounded-2xl border border-[#232631]">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-white">📍 Endereço de Entrega</span>
                                    <span class="text-emerald-400 font-bold" x-text="freteCentavos === 0 ? 'Frete Grátis!' : 'Taxa: ' + formatarCentavos(freteCentavos)"></span>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <input type="text" x-model="checkout.cep" @blur="buscarCep()" placeholder="CEP (opcional)" class="bg-[#13141a] border border-[#232631] text-xs text-white rounded-xl px-3 py-2">
                                    <input type="text" x-model="checkout.rua" placeholder="Rua / Avenida *" class="col-span-2 bg-[#13141a] border border-[#232631] text-xs text-white rounded-xl px-3 py-2">
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <input type="text" x-model="checkout.numero" placeholder="Nº *" class="bg-[#13141a] border border-[#232631] text-xs text-white rounded-xl px-3 py-2">
                                    <input type="text" x-model="checkout.bairro" list="bairros-atendidos" @change="calcularFrete()" @blur="calcularFrete()" placeholder="Bairro *" class="col-span-2 bg-[#13141a] border border-[#232631] text-xs text-white rounded-xl px-3 py-2">
                                    <datalist id="bairros-atendidos">
                                        <template x-for="area in areasEntrega" :key="'dl-' + area.id">
                                            <option :value="area.bairro_nome || area.nome"></option>
                                        </template>
                                    </datalist>
                                </div>
                                <input type="text" x-model="checkout.complemento" placeholder="Complemento / Ponto de referência" class="w-full bg-[#13141a] border border-[#232631] text-xs text-white rounded-xl px-3 py-2">
                            </div>
                        </template>

                        <!-- Cupom de Desconto -->
                        <div class="bg-[#0a0b0e] p-3 rounded-2xl border border-[#232631] space-y-2">
                            <label class="text-[11px] font-bold uppercase text-slate-400 block">🎟️ Cupom de Desconto</label>
                            <div class="flex gap-2">
                                <input type="text" x-model="cupomInput" placeholder="Digite seu cupom"
                                    class="flex-1 bg-[#13141a] border border-[#232631] text-xs text-white uppercase font-bold rounded-xl px-3 py-2 focus:outline-none focus:border-amber-500">
                                <button type="button" @click="validarCupomApi()" :disabled="validandoCupom"
                                    class="px-4 py-2 rounded-xl bg-[#1e2029] hover:bg-[#2a2d3a] text-white text-xs font-black border border-[#2e3244] transition">
                                    <span x-text="validandoCupom ? '...' : 'Aplicar'"></span>
                                </button>
                            </div>
                            <template x-if="mensagemCupom">
                                <p class="text-[11px] font-bold" :class="cupomAplicado ? 'text-emerald-400' : 'text-rose-400'" x-text="mensagemCupom"></p>
                            </template>
                        </div>

                        <!-- Forma de Pagamento -->
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold uppercase text-slate-400 block">Forma de Pagamento</label>
                            <div class="grid grid-cols-3 gap-2 text-xs">
                                <button type="button" @click="checkout.forma_pagamento = 'pix'"
                                    class="py-2.5 px-2 rounded-xl font-bold border transition text-center"
                                    :class="checkout.forma_pagamento === 'pix' ? 'bg-emerald-500/20 border-emerald-500 text-emerald-400' : 'bg-[#0a0b0e] border-[#232631] text-slate-400'">
                                    ⚡ PIX
                                </button>
                                <button type="button" @click="checkout.forma_pagamento = 'cartao_maquina'"
                                    class="py-2.5 px-2 rounded-xl font-bold border transition text-center"
                                    :class="checkout.forma_pagamento === 'cartao_maquina' ? 'bg-amber-500/20 border-amber-500 text-amber-400' : 'bg-[#0a0b0e] border-[#232631] text-slate-400'">
                                    💳 Cartão
                                </button>
                                <button type="button" @click="checkout.forma_pagamento = 'dinheiro'"
                                    class="py-2.5 px-2 rounded-xl font-bold border transition text-center"
                                    :class="checkout.forma_pagamento === 'dinheiro' ? 'bg-amber-500/20 border-amber-500 text-amber-400' : 'bg-[#0a0b0e] border-[#232631] text-slate-400'">
                                    💵 Dinheiro
                                </button>
                            </div>

                            <template x-if="checkout.forma_pagamento === 'dinheiro'">
                                <div class="bg-[#0a0b0e] border border-[#232631] rounded-xl p-2.5 flex items-center justify-between gap-2 mt-2">
                                    <input type="number" step="0.01" x-model="checkout.troco_reais" placeholder="Precisa de troco para quanto? (Ex: 50)"
                                        class="flex-1 bg-[#13141a] border border-[#232631] rounded-lg px-2.5 py-1.5 text-xs text-white">
                                </div>
                            </template>
                        </div>

                        <!-- Observação Geral do Pedido -->
                        <div>
                            <label class="text-[11px] font-bold uppercase text-slate-400 block mb-1">Observações da Entrega / Pedido (Opcional)</label>
                            <input type="text" x-model="checkout.observacoes" placeholder="Ex: Interfone quebrado, chamar no portão, trazer sachês..."
                                class="w-full bg-[#0a0b0e] border border-[#232631] text-xs text-white rounded-xl px-3.5 py-2 focus:border-amber-500 focus:outline-none">
                        </div>

                        <!-- Resumo Final e Confirmação -->
                        <div class="bg-[#0a0b0e] border border-[#232631] rounded-2xl p-3.5 space-y-2 text-xs">
                            <div class="flex justify-between text-slate-400">
                                <span>Subtotal</span>
                                <span class="font-bold text-white" x-text="formatarCentavos(subtotalCarrinhoCentavos)"></span>
                            </div>
                            <template x-if="descontoCupomCentavos > 0">
                                <div class="flex justify-between text-emerald-400 font-bold">
                                    <span x-text="'Cupom ' + cupomAplicado.codigo"></span>
                                    <span x-text="'- ' + formatarCentavos(descontoCupomCentavos)"></span>
                                </div>
                            </template>
                            <template x-if="checkout.canal === 'delivery' && !mesaAtiva">
                                <div class="flex justify-between text-slate-400">
                                    <span>Entrega</span>
                                    <span class="font-bold text-white" x-text="freteCentavos === 0 ? 'Grátis' : formatarCentavos(freteCentavos)"></span>
                                </div>
                            </template>
                            <div class="flex justify-between items-baseline pt-2 border-t border-[#232631]">
                                <span class="font-black text-white text-sm">TOTAL A PAGAR</span>
                                <span class="font-black text-emerald-400 text-xl" x-text="formatarCentavos(totalFinalCentavos)"></span>
                            </div>
                        </div>

                        <!-- Botão Confirmar Pedido -->
                        <button @click="enviarPedido()" :disabled="enviandoPedido || !lojaStatus.aberto_agora"
                            class="w-full text-white font-black py-3.5 rounded-2xl text-sm shadow-xl hover:brightness-110 active:scale-98 disabled:opacity-50 transition flex items-center justify-center"
                            :style="'background-color: var(--cor-loja)'">
                            <span x-text="!lojaStatus.aberto_agora ? 'Loja Fechada no Momento' : (enviandoPedido ? 'Registrando seu pedido...' : 'Confirmar Pedido • ' + formatarCentavos(totalFinalCentavos))"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 11. LÓGICA REATIVA ALPINE.JS (100% DADOS REAIS DE PRODUÇÃO)    -->
    <!-- ============================================================== -->
    <script>
        function cardapioApp() {
            const mesaInicial = @json($mesa ?? null);
            const lojaSlug = @json($loja->slug);
            const cuponsIniciais = @json($cuponsAtivos ?? []);
            const areasIniciais = @json($areasEntrega ?? []);
            const taxaInicialPadrao = areasIniciais.length > 0 ? Number(areasIniciais[0].taxa_centavos || 0) : 0;

            return {
                lojaSlug: lojaSlug,
                lojaStatus: @json($lojaStatus ?? ['aberto_agora' => true]),
                mesaAtiva: mesaInicial,
                garcomChamado: false,
                termoBusca: '',
                categoriaAtiva: null,
                categorias: @json($cardapio['categorias'] ?? []),
                cuponsAtivos: cuponsIniciais,
                areasEntrega: areasIniciais,
                modalAberto: false,
                modalInfoLoja: false,
                sacolaAberta: false,
                enviandoPedido: false,
                erroCheckout: '',
                pedidoCriado: null,
                ultimoPedido: null,
                copiado: false,
                observacaoItem: '',
                qtdModal: 1,
                cupomInput: '',
                cupomAplicado: null,
                validandoCupom: false,
                mensagemCupom: '',
                freteCentavos: taxaInicialPadrao,
                checkout: {
                    canal: mesaInicial ? 'mesa' : 'delivery',
                    nome: '',
                    telefone: '',
                    cep: '',
                    rua: '',
                    numero: '',
                    complemento: '',
                    bairro: '',
                    cidade: @json($loja->cidade ?? ''),
                    uf: @json($loja->uf ?? ''),
                    forma_pagamento: 'pix',
                    troco_reais: '',
                    observacoes: ''
                },
                produtoSelecionado: null,
                variacaoSelecionadaId: null,
                adicionaisEscolhidos: [],
                carrinho: [],

                initApp() {
                    try {
                        // Carrega sacola salva e filtra apenas produtos existentes no cardápio atual
                        const idsValidos = new Set();
                        for (const c of this.categorias) {
                            for (const p of (c.produtos || [])) {
                                idsValidos.add(Number(p.id));
                            }
                        }

                        const savedCart = localStorage.getItem('pedelivre_cart_' + this.lojaSlug);
                        if (savedCart) {
                            const parsedCart = JSON.parse(savedCart) || [];
                            this.carrinho = parsedCart.filter(it => idsValidos.has(Number(it.produto_id)));
                        } else {
                            this.carrinho = [];
                        }

                        const savedCliente = localStorage.getItem('pedelivre_cliente');
                        if (savedCliente) {
                            const parsed = JSON.parse(savedCliente);
                            this.checkout = {
                                ...this.checkout,
                                ...parsed,
                                canal: this.mesaAtiva ? 'mesa' : (parsed.canal || 'delivery')
                            };
                        }

                        const savedOrder = localStorage.getItem('pedelivre_ultimo_pedido_' + this.lojaSlug);
                        if (savedOrder) {
                            this.ultimoPedido = JSON.parse(savedOrder);
                        }

                        // Aplica automaticamente cupom se vier na URL (?cupom=CODIGO)
                        const urlCupom = new URLSearchParams(window.location.search).get('cupom');
                        if (urlCupom) {
                            this.cupomInput = urlCupom.trim().toUpperCase();
                            this.validarCupomApi();
                        }
                    } catch (e) {}

                    if (this.checkout.canal === 'delivery' && !this.mesaAtiva && (this.checkout.cep || this.checkout.bairro)) {
                        this.calcularFrete();
                    }
                },

                get cupomDestaque() {
                    return (this.cuponsAtivos && this.cuponsAtivos.length > 0) ? this.cuponsAtivos[0] : null;
                },

                get produtoPromocaoDestaque() {
                    for (const cat of this.categorias) {
                        for (const p of (cat.produtos || [])) {
                            if (p.preco_promocional_centavos && p.preco_promocional_centavos > 0 && p.preco_promocional_centavos < p.preco_centavos) {
                                return p;
                            }
                        }
                    }
                    return null;
                },

                get pedidoMinimoCentavos() {
                    if (!this.areasEntrega || this.areasEntrega.length === 0) return 0;
                    const minimos = this.areasEntrega.map(a => Number(a.pedido_minimo || 0)).filter(v => v > 0);
                    return minimos.length > 0 ? Math.min(...minimos) : 0;
                },

                get metaFreteGratisCentavos() {
                    if (!this.areasEntrega || this.areasEntrega.length === 0) return 0;
                    const metas = this.areasEntrega.map(a => Number(a.frete_gratis_acima || 0)).filter(v => v > 0);
                    return metas.length > 0 ? Math.min(...metas) : 0;
                },

                get faltaParaFreteGratisCentavos() {
                    if (!this.metaFreteGratisCentavos) return 0;
                    return Math.max(0, this.metaFreteGratisCentavos - this.subtotalCarrinhoCentavos);
                },

                get progressoFreteGratisPct() {
                    if (!this.metaFreteGratisCentavos) return 0;
                    return Math.min(100, Math.round((this.subtotalCarrinhoCentavos / this.metaFreteGratisCentavos) * 100));
                },

                get sugestoesOrderBump() {
                    const idsNaSacola = new Set(this.carrinho.map(i => Number(i.produto_id)));
                    const candidatos = [];
                    for (const cat of this.categorias) {
                        const nomeCat = (cat.nome || '').toLowerCase();
                        const ehBebidaOuPorcao = nomeCat.includes('bebida') || nomeCat.includes('refri') || nomeCat.includes('suco') || nomeCat.includes('sobremesa') || nomeCat.includes('doce') || nomeCat.includes('porç') || nomeCat.includes('acompanha');
                        if (ehBebidaOuPorcao) {
                            for (const p of (cat.produtos || [])) {
                                if (!idsNaSacola.has(Number(p.id)) && (!p.grupos_adicionais || p.grupos_adicionais.length === 0)) {
                                    candidatos.push(p);
                                }
                            }
                        }
                    }
                    return candidatos.slice(0, 4);
                },

                adicionarOrderBumpRapido(produto) {
                    const varPadrao = (produto.variacoes && produto.variacoes.length > 0) ? produto.variacoes[0] : null;
                    const unitario = varPadrao ? varPadrao.preco_centavos : produto.preco_efetivo_centavos;
                    this.carrinho.push({
                        produto_id: produto.id,
                        nome: produto.nome,
                        variacao_id: varPadrao ? varPadrao.id : null,
                        variacao_nome: varPadrao ? varPadrao.nome : null,
                        qtd: 1,
                        unitario_centavos: unitario,
                        observacao: '',
                        adicionais: [],
                        total_centavos: unitario
                    });
                    this.salvarEstadoLocal();
                },

                obterIconeCategoria(nome) {
                    const n = (nome || '').toLowerCase();
                    if (n.includes('hambúrguer') || n.includes('burger') || n.includes('lanche')) return '🍔';
                    if (n.includes('pizza')) return '🍕';
                    if (n.includes('acompanha') || n.includes('porç') || n.includes('petisco')) return '🍟';
                    if (n.includes('bebida') || n.includes('refri') || n.includes('cerveja')) return '🥤';
                    if (n.includes('sobremesa') || n.includes('doce') || n.includes('açaí')) return '🍨';
                    if (n.includes('combo') || n.includes('oferta')) return '🏷️';
                    return '🍽️';
                },

                nomeCurtoCategoria(nome) {
                    return (nome || '')
                        .replace(/ Artesanais/i, '')
                        .replace(/ Especiais/i, '')
                        .replace(/ Geladas/i, '')
                        .replace(/ Promocionais/i, '')
                        .replace(/Acompanhamentos & Porções/i, 'Porções');
                },

                obterSubtituloCategoria(nome) {
                    const n = (nome || '').toLowerCase();
                    if (n.includes('hambúrguer') || n.includes('burger') || n.includes('lanche')) {
                        return 'Preparados na hora com ingredientes selecionados';
                    }
                    if (n.includes('pizza')) {
                        return 'Massa fresca e recheios generosos';
                    }
                    if (n.includes('acompanha') || n.includes('porç') || n.includes('petisco')) {
                        return 'Para acompanhar e compartilhar';
                    }
                    if (n.includes('bebida')) {
                        return 'Bebidas sempre geladas';
                    }
                    if (n.includes('sobremesa') || n.includes('doce')) {
                        return 'Para fechar a sua refeição com chave de ouro';
                    }
                    if (n.includes('combo')) {
                        return 'Combinações completas com economia';
                    }
                    return 'Preparados com ingredientes selecionados';
                },

                salvarEstadoLocal() {
                    try {
                        localStorage.setItem('pedelivre_cart_' + this.lojaSlug, JSON.stringify(this.carrinho));
                        localStorage.setItem('pedelivre_cliente', JSON.stringify({
                            nome: this.checkout.nome,
                            telefone: this.checkout.telefone,
                            cep: this.checkout.cep,
                            rua: this.checkout.rua,
                            numero: this.checkout.numero,
                            complemento: this.checkout.complemento,
                            bairro: this.checkout.bairro,
                            canal: this.checkout.canal
                        }));
                    } catch (e) {}
                },

                alterarCanal(novoCanal) {
                    this.checkout.canal = novoCanal;
                    if (novoCanal === 'delivery' && (this.checkout.cep || this.checkout.bairro)) {
                        this.calcularFrete();
                    }
                    this.salvarEstadoLocal();
                },

                limparSacola() {
                    this.carrinho = [];
                    this.salvarEstadoLocal();
                },

                copiarLinkCardapio() {
                    const url = window.location.href;
                    if (navigator.share) {
                        navigator.share({ title: @json($loja->nome), url: url }).catch(() => {});
                    } else {
                        navigator.clipboard.writeText(url);
                        alert('Link do cardápio copiado!');
                    }
                },

                imagemProduto(produto) {
                    if (!produto) return null;
                    const img = produto.imagem || produto.foto_principal_url || null;
                    if (img && (img.startsWith('http') || img.startsWith('/storage/'))) return img;
                    const nome = (produto.nome || '').toLowerCase();
                    if (nome.includes('bacon')) return 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=600&q=80';
                    if (nome.includes('smash')) return 'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?auto=format&fit=crop&w=600&q=80';
                    if (nome.includes('costela') || nome.includes('bbq')) return 'https://images.unsplash.com/photo-1553979459-d2229ba7433b?auto=format&fit=crop&w=600&q=80';
                    if (nome.includes('chicken') || nome.includes('crispy') || nome.includes('frango')) return 'https://images.unsplash.com/photo-1606755962773-d324e0a13086?auto=format&fit=crop&w=600&q=80';
                    if (nome.includes('napolitana')) return 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?auto=format&fit=crop&w=600&q=80';
                    if (nome.includes('pizza') || nome.includes('calabresa')) return 'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=600&q=80';
                    if (nome.includes('batata') || nome.includes('frita')) return 'https://images.unsplash.com/photo-1576107232684-1279f3908594?auto=format&fit=crop&w=600&q=80';
                    if (nome.includes('onion') || nome.includes('cebola')) return 'https://images.unsplash.com/photo-1639024471283-03518883512d?auto=format&fit=crop&w=600&q=80';
                    if (nome.includes('coca') || nome.includes('refrigerante') || nome.includes('refri')) return 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?auto=format&fit=crop&w=600&q=80';
                    if (nome.includes('suco') || nome.includes('laranja')) return 'https://images.unsplash.com/photo-1613478223719-2ab802602423?auto=format&fit=crop&w=600&q=80';
                    if (nome.includes('cerveja') || nome.includes('heineken')) return 'https://images.unsplash.com/photo-1608270586620-248524c67de9?auto=format&fit=crop&w=600&q=80';
                    if (nome.includes('açaí') || nome.includes('acai')) return 'https://images.unsplash.com/photo-1590301157890-4810ed352733?auto=format&fit=crop&w=600&q=80';
                    if (img && img.startsWith('/')) return img;
                    return null;
                },

                get categoriasFiltradas() {
                    const termo = this.termoBusca.trim().toLowerCase();
                    return this.categorias.map(cat => ({
                        ...cat,
                        produtos: (cat.produtos || []).filter(p => !termo || p.nome.toLowerCase().includes(termo) || (p.descricao && p.descricao.toLowerCase().includes(termo)))
                    })).filter(cat => cat.produtos.length > 0);
                },

                get produtosDestaque() {
                    const promocionais = [];
                    const outros = [];
                    for (const cat of this.categorias) {
                        for (const p of (cat.produtos || [])) {
                            if (p.preco_promocional_centavos && p.preco_promocional_centavos < p.preco_centavos) {
                                promocionais.push(p);
                            } else {
                                outros.push(p);
                            }
                        }
                    }
                    return [...promocionais, ...outros].slice(0, 4);
                },

                get unitarioModalCentavos() {
                    if (!this.produtoSelecionado) return 0;
                    let base = this.produtoSelecionado.preco_efetivo_centavos;
                    if (this.variacaoSelecionadaId) {
                        const v = this.produtoSelecionado.variacoes?.find(v => v.id === this.variacaoSelecionadaId);
                        if (v) base = v.preco_centavos;
                    }
                    const adicionaisTotal = this.adicionaisEscolhidos.reduce((acc, opt) => acc + opt.preco_centavos, 0);
                    return base + adicionaisTotal;
                },

                get subtotalModalCentavos() {
                    return this.unitarioModalCentavos * this.qtdModal;
                },

                qtdNoGrupo(grupoId) {
                    return this.adicionaisEscolhidos.filter(o => o.grupo_id === grupoId).length;
                },

                grupoSatisfeito(grupo) {
                    if (!grupo.obrigatorio) return true;
                    const min = Math.max(1, grupo.minimo || 1);
                    return this.qtdNoGrupo(grupo.id) >= min;
                },

                get modalValido() {
                    if (!this.produtoSelecionado) return false;
                    if (this.produtoSelecionado.variacoes?.length > 0 && !this.variacaoSelecionadaId) return false;
                    const grupos = this.produtoSelecionado.grupos_adicionais || [];
                    for (const g of grupos) {
                        if (!this.grupoSatisfeito(g)) return false;
                    }
                    return true;
                },

                get mensagemBotaoModal() {
                    if (!this.produtoSelecionado) return 'Adicionar';
                    if (this.produtoSelecionado.variacoes?.length > 0 && !this.variacaoSelecionadaId) {
                        return 'Escolha a Opção';
                    }
                    const grupos = this.produtoSelecionado.grupos_adicionais || [];
                    for (const g of grupos) {
                        if (!this.grupoSatisfeito(g)) {
                            return 'Escolha: ' + g.nome;
                        }
                    }
                    return 'Adicionar (' + this.qtdModal + 'x)';
                },

                get totalItensSacola() {
                    return this.carrinho.reduce((acc, item) => acc + (item.qtd || 1), 0);
                },

                qtdProdutoNaSacola(produtoId) {
                    return this.carrinho.filter(i => i.produto_id === produtoId).reduce((acc, i) => acc + (i.qtd || 1), 0);
                },

                get subtotalCarrinhoCentavos() {
                    return this.carrinho.reduce((acc, item) => acc + item.total_centavos, 0);
                },

                get descontoCupomCentavos() {
                    if (!this.cupomAplicado) return 0;
                    const min = Number(this.cupomAplicado.pedido_minimo || 0);
                    if (min > 0 && this.subtotalCarrinhoCentavos < min) return 0;
                    if (this.cupomAplicado.tipo === 'percentual') {
                        return Math.round((this.subtotalCarrinhoCentavos * Number(this.cupomAplicado.valor || 0)) / 100);
                    }
                    if (this.cupomAplicado.tipo === 'frete_gratis') {
                        return (this.checkout.canal === 'delivery' && !this.mesaAtiva) ? this.freteCentavos : 0;
                    }
                    return Math.min(this.subtotalCarrinhoCentavos, Number(this.cupomAplicado.desconto_centavos || this.cupomAplicado.valor || 0));
                },

                get totalFinalCentavos() {
                    const frete = (this.checkout.canal === 'delivery' && !this.mesaAtiva) ? this.freteCentavos : 0;
                    return Math.max(0, this.subtotalCarrinhoCentavos - this.descontoCupomCentavos + frete);
                },

                formatarCentavos(centavos) {
                    return ((centavos || 0) / 100).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                },

                abrirModalProduto(produto) {
                    this.produtoSelecionado = produto;
                    this.variacaoSelecionadaId = produto.variacoes?.length > 0 ? produto.variacoes[0].id : null;
                    this.adicionaisEscolhidos = [];
                    this.observacaoItem = '';
                    this.qtdModal = 1;
                    this.modalAberto = true;
                },

                toggleAdicional(grupo, opcao) {
                    const idx = this.adicionaisEscolhidos.findIndex(o => o.id === opcao.id);
                    if (idx >= 0) {
                        this.adicionaisEscolhidos.splice(idx, 1);
                        return;
                    }
                    if (grupo.maximo === 1) {
                        this.adicionaisEscolhidos = this.adicionaisEscolhidos.filter(o => o.grupo_id !== grupo.id);
                        this.adicionaisEscolhidos.push({ ...opcao, grupo_id: grupo.id });
                        return;
                    }
                    if (this.qtdNoGrupo(grupo.id) >= grupo.maximo) {
                        return;
                    }
                    this.adicionaisEscolhidos.push({ ...opcao, grupo_id: grupo.id });
                },

                adicionalSelecionado(opcaoId) {
                    return this.adicionaisEscolhidos.some(o => o.id === opcaoId);
                },

                adicionarAoCarrinho() {
                    if (!this.modalValido) return;
                    const varObj = this.produtoSelecionado.variacoes?.find(v => v.id === this.variacaoSelecionadaId);
                    const unitario = this.unitarioModalCentavos;
                    this.carrinho.push({
                        produto_id: this.produtoSelecionado.id,
                        nome: this.produtoSelecionado.nome,
                        variacao_id: this.variacaoSelecionadaId,
                        variacao_nome: varObj ? varObj.nome : null,
                        qtd: this.qtdModal,
                        unitario_centavos: unitario,
                        observacao: this.observacaoItem,
                        adicionais: [...this.adicionaisEscolhidos],
                        total_centavos: unitario * this.qtdModal,
                    });
                    this.salvarEstadoLocal();
                    this.modalAberto = false;
                },

                alterarQtdCarrinho(idx, delta) {
                    const item = this.carrinho[idx];
                    if (!item) return;
                    const novaQtd = (item.qtd || 1) + delta;
                    if (novaQtd <= 0) {
                        this.carrinho.splice(idx, 1);
                    } else {
                        const unit = item.unitario_centavos || Math.round(item.total_centavos / (item.qtd || 1));
                        item.qtd = novaQtd;
                        item.unitario_centavos = unit;
                        item.total_centavos = unit * novaQtd;
                    }
                    this.salvarEstadoLocal();
                },

                abrirCheckout() {
                    this.pedidoCriado = null;
                    this.erroCheckout = '';
                    this.sacolaAberta = true;
                    if (this.checkout.canal === 'delivery' && !this.mesaAtiva && (this.checkout.cep || this.checkout.bairro)) {
                        this.calcularFrete();
                    }
                },

                aplicarCupomRapido(cupomObj) {
                    if (!cupomObj) return;
                    this.cupomInput = cupomObj.codigo;
                    this.cupomAplicado = {
                        codigo: cupomObj.codigo,
                        tipo: cupomObj.tipo,
                        valor: cupomObj.valor,
                        pedido_minimo: cupomObj.pedido_minimo || 0,
                        desconto_centavos: cupomObj.tipo === 'percentual'
                            ? Math.round((this.subtotalCarrinhoCentavos * cupomObj.valor) / 100)
                            : cupomObj.valor
                    };
                    this.mensagemCupom = '✓ Cupom ' + cupomObj.codigo + ' ativado!';
                },

                validarCupomApi() {
                    if (!this.cupomInput.trim()) return;
                    this.validandoCupom = true;
                    this.mensagemCupom = '';
                    const canalFinal = this.mesaAtiva ? 'mesa' : this.checkout.canal;
                    fetch(`/api/p/${this.lojaSlug}/cupom/validar`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({
                            codigo: this.cupomInput.trim().toUpperCase(),
                            subtotal_centavos: Math.max(1000, this.subtotalCarrinhoCentavos),
                            canal: canalFinal,
                            telefone: this.checkout.telefone || null
                        })
                    })
                    .then(async r => {
                        const data = await r.json();
                        if (!r.ok || !data.valido) throw new Error(data.message || 'Cupom inválido ou expirado.');
                        const cupomCat = (this.cuponsAtivos || []).find(c => c.codigo.toUpperCase() === data.codigo.toUpperCase());
                        this.cupomAplicado = {
                            codigo: data.codigo,
                            tipo: data.tipo,
                            valor: cupomCat ? cupomCat.valor : data.desconto_centavos,
                            pedido_minimo: cupomCat ? cupomCat.pedido_minimo : 0,
                            desconto_centavos: data.desconto_centavos
                        };
                        this.mensagemCupom = '✓ ' + (data.message || 'Cupom aplicado com sucesso!');
                    })
                    .catch(e => {
                        this.cupomAplicado = null;
                        this.mensagemCupom = e.message;
                    })
                    .finally(() => {
                        this.validandoCupom = false;
                    });
                },

                buscarCep() {
                    const cepLimpo = (this.checkout.cep || '').replace(/\D/g, '');
                    if (cepLimpo.length !== 8) return;
                    fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`)
                        .then(r => r.json())
                        .then(d => {
                            if (!d.erro) {
                                if (d.logradouro) this.checkout.rua = d.logradouro;
                                if (d.bairro) this.checkout.bairro = d.bairro;
                                if (d.localidade) this.checkout.cidade = d.localidade;
                                if (d.uf) this.checkout.uf = d.uf;
                                this.calcularFrete();
                            }
                        })
                        .catch(() => {});
                },

                calcularFrete() {
                    if (!this.checkout.cep && !this.checkout.bairro) return;
                    fetch(`/api/p/${this.lojaSlug}/frete`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({
                            cep: this.checkout.cep || null,
                            bairro: this.checkout.bairro || null,
                            subtotal_centavos: this.subtotalCarrinhoCentavos || 0
                        })
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (typeof d.taxa_centavos === 'number') {
                            this.freteCentavos = d.taxa_centavos;
                        }
                    })
                    .catch(() => {});
                },

                scrollParaCategoria(id) {
                    this.categoriaAtiva = id;
                    document.getElementById('categoria-' + id)?.scrollIntoView({ behavior: 'smooth' });
                },

                chamarGarcom() {
                    if (!this.mesaAtiva) return;
                    fetch(`/api/p/${this.lojaSlug}/mesa/${this.mesaAtiva.qr_token}/chamar`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
                    }).then(() => {
                        this.garcomChamado = true;
                        setTimeout(() => { this.garcomChamado = false; }, 10000);
                    });
                },

                enviarPedido() {
                    if (!this.checkout.nome.trim() || !this.checkout.telefone.trim()) {
                        this.erroCheckout = 'Por favor, informe seu nome e WhatsApp para continuar.';
                        return;
                    }
                    const canalFinal = this.mesaAtiva ? 'mesa' : this.checkout.canal;
                    if (canalFinal === 'delivery') {
                        if (!this.checkout.rua.trim() || !this.checkout.numero.trim() || !this.checkout.bairro.trim()) {
                            this.erroCheckout = 'Por favor, preencha Rua, Número e Bairro para entrega.';
                            return;
                        }
                    }

                    this.enviandoPedido = true;
                    this.erroCheckout = '';
                    this.salvarEstadoLocal();

                    const trocoCentavos = (this.checkout.forma_pagamento === 'dinheiro' && this.checkout.troco_reais)
                        ? Math.round(parseFloat(String(this.checkout.troco_reais).replace(',', '.')) * 100)
                        : null;

                    const payload = {
                        idempotency_key: 'web_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8),
                        canal: canalFinal,
                        mesa_token: this.mesaAtiva ? this.mesaAtiva.qr_token : null,
                        cliente: {
                            nome: this.checkout.nome.trim(),
                            telefone: this.checkout.telefone.trim()
                        },
                        endereco: canalFinal === 'delivery' ? {
                            cep: this.checkout.cep.trim() || null,
                            rua: this.checkout.rua.trim(),
                            numero: this.checkout.numero.trim(),
                            complemento: this.checkout.complemento.trim() || null,
                            bairro: this.checkout.bairro.trim(),
                            cidade: this.checkout.cidade.trim() || @json($loja->cidade ?? ''),
                            uf: this.checkout.uf.trim() || @json($loja->uf ?? '')
                        } : null,
                        itens: this.carrinho.map(item => ({
                            produto_id: item.produto_id,
                            variacao_id: item.variacao_id || null,
                            qtd: item.qtd || 1,
                            observacao: item.observacao || null,
                            adicionais: (item.adicionais || []).map(a => ({ grupo_id: a.grupo_id, opcao_id: a.id, qtd: 1 }))
                        })),
                        cupom_codigo: (this.cupomAplicado && this.descontoCupomCentavos > 0) ? this.cupomAplicado.codigo : null,
                        forma_pagamento: this.checkout.forma_pagamento,
                        troco_para: trocoCentavos,
                        observacoes: this.checkout.observacoes ? this.checkout.observacoes.trim() : null
                    };

                    fetch(`/api/p/${this.lojaSlug}/pedidos`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(payload)
                    })
                    .then(async r => {
                        const data = await r.json();
                        if (!r.ok) throw new Error(data.message || 'Erro ao processar pedido.');
                        this.pedidoCriado = data;
                        this.ultimoPedido = data;
                        this.carrinho = [];
                        localStorage.removeItem('pedelivre_cart_' + this.lojaSlug);
                        localStorage.setItem('pedelivre_ultimo_pedido_' + this.lojaSlug, JSON.stringify(data));
                    })
                    .catch(e => {
                        this.erroCheckout = e.message;
                    })
                    .finally(() => {
                        this.enviandoPedido = false;
                    });
                }
            };
        }
    </script>
</body>
</html>
