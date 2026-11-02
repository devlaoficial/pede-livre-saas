<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-[#0c0d12] scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="theme-color" content="{{ $loja->cor_primaria ?: '#ff5a1f' }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/manifest.json">
    <title>{{ $loja->nome }} — Cardápio Digital</title>
    
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
        }
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .bg-loja { background-color: var(--cor-loja) !important; }
        .text-loja { color: var(--cor-loja) !important; }
        .border-loja { border-color: var(--cor-loja) !important; }
        
        /* Garantir proporção estrita de imagem */
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
            width: 56px;
            height: 56px;
            min-width: 56px;
            max-width: 56px;
            min-height: 56px;
            max-height: 56px;
            object-fit: cover;
        }
    </style>
</head>
<body class="min-h-full bg-[#0c0d12] text-slate-100 antialiased pb-28 lg:pb-16 relative overflow-x-hidden" x-data="cardapioApp()" x-init="initApp()">

    <!-- Glow Atmosférico no Topo (Fiel às Imagens 1 e 5) -->
    <div class="fixed top-0 right-0 w-[550px] sm:w-[750px] h-[380px] pointer-events-none z-0 opacity-25 sm:opacity-35" 
         style="background: radial-gradient(circle at 75% 20%, var(--cor-loja), transparent 70%); filter: blur(75px);">
    </div>
    <div class="fixed top-0 left-0 w-[400px] h-[260px] pointer-events-none z-0 opacity-15" 
         style="background: radial-gradient(circle at 20% 10%, var(--cor-loja), transparent 70%); filter: blur(60px);">
    </div>

    <!-- CONTAINER PRINCIPAL -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 sm:pt-6 relative z-10 space-y-6">

        <!-- ==================== HEADER DA LOJA (DESIGN CLAUDE FIEL) ==================== -->
        <header class="bg-[#16171b] border border-[#26282e] rounded-3xl p-4 sm:p-6 shadow-2xl space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                
                <!-- Identidade da Loja: Logo/Avatar + Nome + Status + Informações -->
                <div class="flex items-center gap-4">
                    @if($loja->logo && str_starts_with($loja->logo, 'http'))
                        <img src="{{ $loja->logo }}" alt="{{ $loja->nome }}" 
                             style="width: 64px; height: 64px; min-width: 64px;" 
                             class="rounded-2xl object-cover shadow-xl border border-[#26282e] shrink-0">
                    @else
                        <!-- Quadrado Laranja com Letra B Inicial (Fiel às Referências) -->
                        <div style="width: 64px; height: 64px; min-width: 64px; background-color: var(--cor-loja);"
                             class="rounded-2xl shadow-xl flex items-center justify-center text-white font-black text-3xl shrink-0">
                            {{ substr($loja->nome, 0, 1) }}
                        </div>
                    @endif

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <h1 class="text-xl sm:text-2xl lg:text-3xl font-black text-white tracking-tight leading-tight break-words">{{ $loja->nome }}</h1>
                            <!-- Badge Aberto Agora -->
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-[#132a1e] text-[#4ade80] border border-emerald-500/20 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#4ade80] animate-pulse"></span>
                                <span x-text="lojaStatus.aberto_agora ? 'Aberto agora' : 'Aberto para pedidos'"></span>
                            </span>
                        </div>

                        <!-- Linha de Detalhes: Tempo, Pagamentos e Endereço -->
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-400 mt-1.5 font-medium">
                            <span class="flex items-center gap-1">
                                <span>⏱️</span>
                                <span>{{ $loja->config_json['tempo_medio_preparo_min'] ?? 35 }}–45 min</span>
                            </span>
                            <span class="text-slate-600 hidden sm:inline">•</span>
                            <span class="flex items-center gap-1">
                                <span>⚡</span>
                                <span>PIX, cartão, dinheiro</span>
                            </span>
                            @if($loja->endereco)
                                <span class="text-slate-600 hidden md:inline">•</span>
                                <span class="hidden md:inline truncate max-w-sm text-slate-400">
                                    📍 {{ $loja->endereco }}, {{ $loja->cidade }}/{{ $loja->uf }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Seletor Delivery / Retirada (Segmented Control Fiel) -->
                <div class="flex items-center justify-start md:justify-end shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-[#26282e]">
                    <template x-if="mesaAtiva">
                        <div class="flex items-center justify-between w-full md:w-auto gap-3 bg-[#1d1f27] border border-amber-500/40 px-4 py-2.5 rounded-2xl">
                            <span class="text-xs font-black text-amber-300" x-text="'🪑 Mesa #' + mesaAtiva.numero"></span>
                            <button type="button" @click="chamarGarcom()" class="bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-black px-3.5 py-1.5 rounded-xl transition shadow">
                                <span x-text="garcomChamado ? '✓ Garçom Avisado!' : '🔔 Chamar Garçom'"></span>
                            </button>
                        </div>
                    </template>
                    <template x-if="!mesaAtiva">
                        <div class="grid grid-cols-2 w-full md:w-auto bg-[#0c0e12] p-1.5 rounded-2xl border border-[#26282e] gap-1">
                            <button type="button" @click="alterarCanal('delivery')"
                                class="px-6 py-2.5 rounded-xl text-xs font-black transition flex items-center justify-center gap-1.5"
                                :style="checkout.canal === 'delivery' ? 'background-color: var(--cor-loja); color: white;' : 'color: #9ca3af;'"
                                :class="checkout.canal === 'delivery' ? 'shadow-lg' : 'hover:text-white'">
                                <span>🛵</span>
                                <span>Delivery</span>
                            </button>
                            <button type="button" @click="alterarCanal('retirada')"
                                class="px-6 py-2.5 rounded-xl text-xs font-black transition flex items-center justify-center gap-1.5"
                                :style="checkout.canal === 'retirada' ? 'background-color: var(--cor-loja); color: white;' : 'color: #9ca3af;'"
                                :class="checkout.canal === 'retirada' ? 'shadow-lg' : 'hover:text-white'">
                                <span>🛍️</span>
                                <span>Retirada</span>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Faixa do Cupom Promocional (Fiel Imagem 5) -->
            <div class="bg-[#1e1713] border border-[#3e2518] rounded-2xl p-2 sm:p-2.5 px-3 sm:px-4 flex items-center justify-between gap-2">
                <div class="flex items-center gap-1.5 text-xs text-orange-200/90 font-medium min-w-0">
                    <span class="text-orange-400 shrink-0">🏷️</span>
                    <span class="truncate">Cupom <strong class="font-black text-white">BEMVINDO10</strong>: 10% OFF acima de R$ 20</span>
                </div>
                <button type="button" @click="aplicarCupomRapido('BEMVINDO10')"
                    class="px-2.5 sm:px-3.5 py-1 rounded-xl text-[11px] sm:text-xs font-black text-white transition shrink-0 hover:brightness-110 shadow-sm active:scale-95"
                    :style="'background-color: var(--cor-loja)'"
                    x-text="cupomAplicado?.codigo === 'BEMVINDO10' ? '✓ Ativado' : 'Ativar'">
                </button>
            </div>
        </header>

        <!-- ==================== BARRA INTEGRADA: BUSCA + CATEGORIAS (Fiel Imagem 5) ==================== -->
        <div class="sticky top-0 z-30 bg-[#0c0d12]/95 backdrop-blur-md py-3 -mx-4 px-4 sm:mx-0 sm:px-0 flex flex-col md:flex-row md:items-center gap-3">
            <!-- Campo de Busca Arredondado -->
            <div class="relative md:w-64 shrink-0">
                <input type="text" x-model="termoBusca" placeholder="Buscar prato, bebida ou ingred..."
                    class="w-full bg-[#16171b] border border-[#26282e] text-xs text-white rounded-full pl-9 pr-8 py-2.5 placeholder-slate-500 focus:outline-none focus:border-slate-500 transition">
                <svg class="w-4 h-4 text-slate-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <button x-show="termoBusca" @click="termoBusca = ''" class="absolute right-3 top-2.5 text-slate-500 hover:text-white text-xs font-bold">✕</button>
            </div>

            <!-- Chips de Categorias com Nomes Curtos e Contadores (Fiel Imagem 5) -->
            <nav class="flex overflow-x-auto no-scrollbar gap-2 py-0.5 flex-1">
                <template x-for="cat in categoriasFiltradas" :key="cat.id">
                    <button @click="scrollParaCategoria(cat.id)"
                        class="px-4 py-2 rounded-full text-xs font-bold whitespace-nowrap transition flex items-center gap-1.5 shrink-0"
                        :style="(categoriaAtiva || categoriasFiltradas[0]?.id) === cat.id ? 'background-color: var(--cor-loja); color: white;' : ''"
                        :class="(categoriaAtiva || categoriasFiltradas[0]?.id) === cat.id ? 'shadow-md' : 'bg-[#16171b] border border-[#26282e] text-slate-400 hover:text-white'">
                        <span x-text="nomeCurtoCategoria(cat.nome)"></span>
                        <span class="text-[10px] font-bold opacity-80" x-text="cat.produtos.length"></span>
                    </button>
                </template>
            </nav>
        </div>

        <!-- ==================== CORPO EM 2 COLUNAS (Catálogo 8 + Sacola Lateral Fixa 4) ==================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- COLUNA DA ESQUERDA: CATÁLOGO DE PRODUTOS -->
            <main class="lg:col-span-8 space-y-9">
                
                <!-- SEÇÃO 1: MAIS PEDIDOS (Fiel Imagens 1 e 5) -->
                <template x-if="!termoBusca.trim() && produtosDestaque.length > 0">
                    <section class="space-y-3.5">
                        <h2 class="text-xl font-black text-white">Mais pedidos</h2>

                        <!-- Grid 2x2 no Desktop com Numeração 1, 2, 3, 4 (Fiel Imagem 5) -->
                        <div class="hidden sm:grid sm:grid-cols-2 gap-3.5">
                            <template x-for="(dest, idx) in produtosDestaque.slice(0, 4)" :key="'dest-desk-' + dest.id">
                                <div @click="abrirModalProduto(dest)"
                                     class="bg-[#16171b] border border-[#26282e] hover:border-slate-500 rounded-2xl p-3 flex items-center gap-3.5 cursor-pointer transition group shadow-sm">
                                    <!-- Número do Rank: 1, 2, 3, 4 em Laranja da Loja -->
                                    <span class="text-2xl font-black shrink-0 w-6 text-center select-none"
                                          :style="'color: var(--cor-loja)'"
                                          x-text="idx + 1"></span>

                                    <!-- Foto Quadrada Estrita 56x56 -->
                                    <div class="rounded-xl overflow-hidden bg-[#22242a] shrink-0 border border-[#26282e]">
                                        <img :src="imagemProduto(dest)" :alt="dest.nome" class="thumb-mais-pedidos group-hover:scale-105 transition duration-300">
                                    </div>

                                    <!-- Título e Preço -->
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-white text-xs sm:text-sm line-clamp-1 group-hover:text-amber-400 transition" x-text="dest.nome"></div>
                                        <div class="text-xs font-black text-slate-100 mt-1" x-text="formatarCentavos(dest.preco_efetivo_centavos)"></div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Carrossel Deslizante no Mobile (Fiel Imagem 1) -->
                        <div class="sm:hidden flex overflow-x-auto no-scrollbar gap-3 pb-1">
                            <template x-for="dest in produtosDestaque" :key="'dest-mob-' + dest.id">
                                <div @click="abrirModalProduto(dest)"
                                     class="w-40 shrink-0 bg-[#16171b] border border-[#26282e] rounded-2xl p-3 flex flex-col justify-between cursor-pointer group shadow-sm">
                                    <div class="w-full aspect-square rounded-xl overflow-hidden bg-[#22242a] mb-2.5">
                                        <img :src="imagemProduto(dest)" :alt="dest.nome" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    </div>
                                    <div>
                                        <div class="font-bold text-white text-xs line-clamp-2 leading-snug" x-text="dest.nome"></div>
                                        <div class="text-xs font-black text-white mt-1.5" x-text="formatarCentavos(dest.preco_efetivo_centavos)"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </section>
                </template>

                <!-- Estado Vazio se Busca não Encontrar Nada -->
                <template x-if="categoriasFiltradas.length === 0">
                    <div class="bg-[#16171b] border border-[#26282e] rounded-3xl p-10 text-center space-y-3">
                        <div class="text-3xl">🔍</div>
                        <h3 class="text-base font-bold text-white">Nenhum item encontrado</h3>
                        <p class="text-xs text-slate-400">Tente buscar por outro termo ou limpe o filtro de busca.</p>
                        <button @click="termoBusca = ''" 
                                class="mt-2 inline-flex text-white text-xs font-bold px-4 py-2 rounded-xl"
                                :style="'background-color: var(--cor-loja)'">
                            Ver todo o cardápio
                        </button>
                    </div>
                </template>

                <!-- SEÇÕES DE CATEGORIAS (Fiel Imagens 1 e 5) -->
                <template x-for="categoria in categoriasFiltradas" :key="categoria.id">
                    <section :id="'categoria-' + categoria.id" class="space-y-3.5 scroll-mt-24">
                        <!-- Título com Subtítulo Gourmet em Cinza -->
                        <div class="flex items-baseline gap-2">
                            <h2 class="text-lg sm:text-xl font-black text-white" x-text="categoria.nome"></h2>
                            <p class="text-xs text-slate-400 hidden sm:inline" 
                               x-text="categoria.descricao || obterSubtituloCategoria(categoria.nome)"></p>
                        </div>

                        <!-- Grade de Produtos: 1 Coluna no Celular, 2 Colunas no Desktop -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                            <template x-for="produto in categoria.produtos" :key="produto.id">
                                <article @click="abrirModalProduto(produto)"
                                    class="bg-[#16171b] border border-[#26282e] hover:border-slate-500 rounded-2xl p-4 transition-all cursor-pointer flex justify-between gap-3 relative group shadow-sm">
                                    
                                    <!-- Informações do Item (Esquerda) -->
                                    <div class="flex-1 min-w-0 flex flex-col justify-between pr-2">
                                        <div>
                                            <!-- Tag de Desconto -10% Verde (Fiel Imagem 5) -->
                                            <template x-if="produto.preco_promocional_centavos && produto.preco_promocional_centavos < produto.preco_centavos">
                                                <span class="bg-[#132a1e] text-[#4ade80] text-[10px] font-black px-1.5 py-0.5 rounded-md inline-block mb-1.5"
                                                      x-text="'-' + Math.round((1 - produto.preco_promocional_centavos / produto.preco_centavos) * 100) + '%'"></span>
                                            </template>

                                            <!-- Título -->
                                            <h3 class="font-bold text-white text-sm sm:text-base leading-snug group-hover:text-amber-400 transition" x-text="produto.nome"></h3>
                                            
                                            <!-- Descrição -->
                                            <p class="text-xs text-slate-400 line-clamp-2 mt-1 leading-relaxed" x-text="produto.descricao"></p>
                                        </div>

                                        <!-- Preço & Preço Riscado -->
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

                                    <!-- Imagem ou Letra Inicial Elegante + Botão Circular + (Direita) -->
                                    <div class="relative shrink-0 flex flex-col justify-between items-end">
                                        <!-- Container Estrito 104x104px (Nunca estoura) -->
                                        <div class="rounded-2xl overflow-hidden bg-[#22242a] flex items-center justify-center border border-[#26282e]"
                                             style="width: 104px; height: 104px; min-width: 104px;">
                                            <template x-if="imagemProduto(produto)">
                                                <img :src="imagemProduto(produto)" :alt="produto.nome" loading="lazy" 
                                                     class="thumb-produto group-hover:scale-105 transition duration-300">
                                            </template>
                                            <template x-if="!imagemProduto(produto)">
                                                <!-- Inicial Letra Grande em Laranja Estilizada (Fiel Imagem 5) -->
                                                <span class="text-3xl sm:text-4xl font-black select-none"
                                                      :style="'color: var(--cor-loja)'"
                                                      x-text="produto.nome.trim().charAt(0).toUpperCase()"></span>
                                            </template>
                                        </div>

                                        <!-- Botão Circular + na cor da loja -->
                                        <button type="button" 
                                            class="w-7 h-7 rounded-full text-white font-black text-sm flex items-center justify-center shadow-lg transition active:scale-90 absolute right-1.5 bottom-1.5"
                                            :style="'background-color: var(--cor-loja)'">
                                            +
                                        </button>
                                    </div>

                                </article>
                            </template>
                        </div>
                    </section>
                </template>

            </main>

            <!-- ==================== COLUNA DA DIREITA: SUA SACOLA STICKY PC (Fiel Imagem 5) ==================== -->
            <aside class="hidden lg:block lg:col-span-4 sticky top-20">
                <div class="bg-[#16171b] border border-[#26282e] rounded-3xl p-5 shadow-2xl space-y-4">
                    
                    <!-- Cabeçalho da Sacola -->
                    <div>
                        <h3 class="font-black text-white text-base">Sua sacola</h3>
                        <p class="text-xs text-slate-400 mt-0.5" 
                           x-text="(checkout.canal === 'delivery' ? 'Delivery' : (mesaAtiva ? 'Mesa #' + mesaAtiva.numero : 'Retirada')) + ' · ' + totalItensSacola + (totalItensSacola === 1 ? ' item' : ' itens')"></p>
                    </div>

                    <!-- Barra Progressiva de Frete Grátis (Fiel Imagem 5) -->
                    <template x-if="checkout.canal === 'delivery' && !mesaAtiva">
                        <div class="bg-[#111216] border border-[#26282e] rounded-2xl p-3 text-xs space-y-1.5">
                            <div class="flex items-center justify-between font-bold">
                                <span class="text-[#4ade80] flex items-center gap-1.5">
                                    <span>🚚</span>
                                    <span x-text="faltaParaFreteGratisCentavos === 0 ? 'Parabéns! Frete grátis liberado!' : 'Faltam ' + formatarCentavos(faltaParaFreteGratisCentavos) + ' para frete grátis'"></span>
                                </span>
                                <span class="text-slate-400 font-bold" x-text="progressoFreteGratisPct + '%'"></span>
                            </div>
                            <div class="w-full h-1.5 bg-[#26282e] rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-300"
                                     :style="'width: ' + progressoFreteGratisPct + '%; background-color: #22c55e;'"></div>
                            </div>
                        </div>
                    </template>

                    <!-- Estado Vazio da Sacola -->
                    <template x-if="carrinho.length === 0">
                        <div class="text-center py-10 space-y-2">
                            <div class="text-3xl">🍔</div>
                            <p class="text-sm font-bold text-white">Sua sacola está vazia</p>
                            <p class="text-xs text-slate-400">Clique em qualquer item para montar seu pedido.</p>
                        </div>
                    </template>

                    <!-- Lista de Itens na Sacola -->
                    <template x-if="carrinho.length > 0">
                        <div class="space-y-4">
                            <div class="divide-y divide-[#26282e] max-h-72 overflow-y-auto pr-1">
                                <template x-for="(item, idx) in carrinho" :key="idx">
                                    <div class="py-3 flex justify-between items-start gap-2">
                                        <div class="text-xs min-w-0 flex-1">
                                            <div class="font-bold text-white leading-tight" x-text="item.nome"></div>
                                            <div class="text-slate-400 text-xs mt-0.5" x-text="formatarCentavos(item.total_centavos)"></div>
                                            <template x-if="item.variacao_nome">
                                                <div class="text-slate-500 text-[11px]" x-text="'Tam: ' + item.variacao_nome"></div>
                                            </template>
                                        </div>

                                        <!-- Controle de Quantidade - 1 + em Caixa Escura (Fiel Imagem 5) -->
                                        <div class="flex items-center gap-1.5 bg-[#202228] border border-[#2a2d36] rounded-xl px-2 py-0.5 shrink-0">
                                            <button type="button" @click="alterarQtdCarrinho(idx, -1)"
                                                class="text-slate-400 hover:text-white font-black text-xs px-1">−</button>
                                            <span class="text-xs font-black text-white w-4 text-center" x-text="item.qtd"></span>
                                            <button type="button" @click="alterarQtdCarrinho(idx, 1)"
                                                class="text-slate-400 hover:text-white font-black text-xs px-1">+</button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Totais Financeiros (Fiel Imagem 5) -->
                            <div class="border-t border-[#26282e] pt-3 space-y-2 text-xs">
                                <div class="flex justify-between text-slate-400">
                                    <span>Subtotal</span>
                                    <span class="font-bold text-white" x-text="formatarCentavos(subtotalCarrinhoCentavos)"></span>
                                </div>

                                <template x-if="descontoCupomCentavos > 0">
                                    <div class="flex justify-between text-[#4ade80] font-bold">
                                        <span x-text="'Cupom ' + cupomAplicado.codigo"></span>
                                        <span x-text="'- ' + formatarCentavos(descontoCupomCentavos)"></span>
                                    </div>
                                </template>

                                <template x-if="checkout.canal === 'delivery' && !mesaAtiva">
                                    <div class="flex justify-between text-slate-400">
                                        <span>Entrega</span>
                                        <span class="font-bold" :class="freteCentavos === 0 ? 'text-[#4ade80]' : 'text-white'"
                                              x-text="freteCentavos === 0 ? 'Grátis' : formatarCentavos(freteCentavos)"></span>
                                    </div>
                                </template>

                                <div class="flex justify-between items-baseline pt-2 border-t border-[#26282e]">
                                    <span class="text-xs font-black text-slate-300 uppercase">Total</span>
                                    <span class="text-2xl font-black text-white" x-text="formatarCentavos(totalFinalCentavos)"></span>
                                </div>

                                <!-- Botão Finalizar Pedido em Laranja (Fiel Imagem 5) -->
                                <button @click="abrirCheckout()"
                                    class="w-full text-white font-black py-3.5 rounded-2xl text-sm shadow-xl hover:brightness-110 active:scale-98 transition flex items-center justify-center mt-2"
                                    :style="'background-color: var(--cor-loja)'">
                                    Finalizar pedido
                                </button>

                                <p class="text-[11px] text-slate-500 text-center pt-1 font-medium">
                                    Pague com PIX, cartão ou dinheiro
                                </p>
                            </div>
                        </div>
                    </template>
                </div>
            </aside>

        </div>

    </div>

    <!-- ==================== BARRA FLUTUANTE INFERIOR MOBILE (Fiel Imagem 1) ==================== -->
    <div x-cloak x-show="carrinho.length > 0" class="lg:hidden fixed bottom-4 inset-x-3 z-40 max-w-lg mx-auto pointer-events-none">
        <div @click="abrirCheckout()"
             class="rounded-2xl py-3 px-4 shadow-2xl flex items-center justify-between gap-3 pointer-events-auto cursor-pointer active:scale-98 transition"
             :style="'background-color: var(--cor-loja)'">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="w-6 h-6 rounded-full bg-black/40 text-white font-black text-xs flex items-center justify-center shrink-0 select-none" 
                      x-text="totalItensSacola"></span>
                <span class="text-white font-extrabold text-sm whitespace-nowrap">Ver sacola</span>
            </div>
            <span class="text-white font-black text-sm sm:text-base shrink-0 whitespace-nowrap tracking-tight" 
                  x-text="formatarCentavos(totalFinalCentavos)"></span>
        </div>
    </div>

    <!-- ==================== MODAL DE CUSTOMIZAÇÃO DO PRODUTO (TEMA DARK) ==================== -->
    <div x-cloak x-show="modalAberto" class="fixed inset-0 z-50 overflow-y-auto bg-black/85 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="modalAberto = false" class="bg-[#16171b] border border-[#26282e] w-full max-w-lg rounded-t-3xl sm:rounded-3xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden">
            <!-- Capa do Produto -->
            <div class="relative h-48 sm:h-56 w-full bg-[#20222a] shrink-0">
                <img :src="produtoSelecionado ? imagemProduto(produtoSelecionado) : ''" :alt="produtoSelecionado?.nome" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-[#16171b] via-[#16171b]/40 to-transparent"></div>
                <button @click="modalAberto = false" class="absolute top-3 right-3 w-9 h-9 rounded-full bg-[#16171b]/80 border border-[#26282e] text-white hover:bg-[#16171b] font-black flex items-center justify-center shadow-md">✕</button>
                <div class="absolute bottom-3 left-4 right-4 text-white">
                    <h3 class="font-black text-lg sm:text-xl leading-tight" x-text="produtoSelecionado?.nome"></h3>
                </div>
            </div>

            <div class="p-4 sm:p-5 overflow-y-auto space-y-4 flex-1">
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed" x-text="produtoSelecionado?.descricao"></p>

                <!-- Variações de Tamanho -->
                <template x-if="produtoSelecionado?.variacoes?.length > 0">
                    <div class="space-y-2 bg-[#101217] p-3.5 rounded-2xl border border-[#26282e]">
                        <div class="flex justify-between items-center">
                            <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-300">Escolha a Opção</h4>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400">Obrigatório</span>
                        </div>
                        <div class="space-y-1.5 pt-1">
                            <template x-for="varItem in produtoSelecionado.variacoes" :key="varItem.id">
                                <label class="flex items-center justify-between p-3 bg-[#16171b] rounded-xl border cursor-pointer transition"
                                    :class="variacaoSelecionadaId === varItem.id ? 'border-amber-500 bg-[#262214]' : 'border-[#26282e] hover:border-slate-500'">
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
                    <div class="space-y-2 bg-[#101217] p-3.5 rounded-2xl border"
                        :class="grupoSatisfeito(grupo) ? 'border-emerald-500/30' : (grupo.obrigatorio ? 'border-rose-500/40' : 'border-[#26282e]')">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="text-xs font-extrabold uppercase tracking-wider text-white" x-text="grupo.nome"></h4>
                                <span class="text-[11px] text-slate-500" x-text="grupo.maximo === 1 ? 'Escolha 1 opção' : 'Escolha até ' + grupo.maximo + ' opções'"></span>
                            </div>
                            <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full"
                                :class="grupoSatisfeito(grupo) ? 'bg-emerald-500/15 text-emerald-400' : (grupo.obrigatorio ? 'bg-rose-500/15 text-rose-400' : 'bg-[#1e2028] text-slate-400')"
                                x-text="grupoSatisfeito(grupo) ? '✓ OK' : (grupo.obrigatorio ? 'Obrigatório' : 'Opcional')"></span>
                        </div>

                        <div class="space-y-1.5 pt-1">
                            <template x-for="opcao in grupo.opcoes" :key="opcao.id">
                                <div @click="toggleAdicional(grupo, opcao)"
                                    class="flex items-center justify-between p-2.5 bg-[#16171b] rounded-xl border cursor-pointer transition select-none"
                                    :class="adicionalSelecionado(opcao.id) ? 'border-amber-500 bg-[#262214]' : 'border-[#26282e] hover:border-slate-500'">
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
                    <input type="text" x-model="observacaoItem" placeholder="Ex: Sem cebola, ponto da carne..."
                        class="w-full bg-[#101217] border border-[#26282e] rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-500">
                </div>
            </div>

            <!-- Footer do Modal -->
            <div class="p-4 border-t border-[#26282e] bg-[#16171b] flex items-center justify-between gap-3">
                <div class="flex items-center bg-[#101217] rounded-xl p-1 border border-[#26282e]">
                    <button type="button" @click="qtdModal = Math.max(1, qtdModal - 1)"
                        class="w-8 h-8 rounded-lg bg-[#16171b] text-white font-black text-sm hover:bg-[#20222a]">−</button>
                    <span class="w-8 text-center font-black text-sm text-white" x-text="qtdModal"></span>
                    <button type="button" @click="qtdModal++"
                        class="w-8 h-8 rounded-lg bg-[#16171b] text-white font-black text-sm hover:bg-[#20222a]">+</button>
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

    <!-- ==================== MODAL DE CHECKOUT & PIX QR CODE ==================== -->
    <div x-cloak x-show="sacolaAberta" class="fixed inset-0 z-50 overflow-y-auto bg-black/85 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="sacolaAberta = false" class="bg-[#16171b] border border-[#26282e] w-full max-w-lg rounded-t-3xl sm:rounded-3xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden">
            <div class="p-4 border-b border-[#26282e] flex items-center justify-between sticky top-0 bg-[#16171b] z-10">
                <h3 class="font-black text-base text-white" x-text="pedidoCriado ? 'Pedido #' + pedidoCriado.codigo_publico + ' Confirmado!' : 'Finalizar Seu Pedido'"></h3>
                <button @click="sacolaAberta = false" class="p-1.5 rounded-full bg-[#1f2128] text-slate-400 hover:text-white">✕</button>
            </div>

            <div class="p-4 sm:p-5 overflow-y-auto space-y-4">
                <!-- Tela de PIX Gerado / Sucesso -->
                <template x-if="pedidoCriado">
                    <div class="text-center space-y-4 py-2">
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-extrabold">
                            ✓ Pedido registrado! Total: <span x-text="formatarCentavos(pedidoCriado.total_centavos)"></span>
                        </div>
                        <template x-if="pedidoCriado.pix?.qr_code_base64">
                            <div class="flex flex-col items-center space-y-2">
                                <img :src="'data:image/png;base64,' + pedidoCriado.pix.qr_code_base64" alt="QR Code PIX" class="w-52 h-52 bg-white rounded-2xl p-2 shadow-md">
                                <span class="text-xs text-slate-400">Escaneie o QR Code no app do seu banco ou copie o código PIX:</span>
                            </div>
                        </template>
                        <template x-if="pedidoCriado.pix?.copia_e_cola">
                            <div class="space-y-2">
                                <textarea readonly x-text="pedidoCriado.pix.copia_e_cola" class="w-full text-[11px] font-mono bg-[#101217] text-white p-2.5 rounded-xl border border-[#26282e] h-20"></textarea>
                                <button type="button" @click="navigator.clipboard.writeText(pedidoCriado.pix.copia_e_cola); copiado = true" 
                                    class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold py-3 rounded-xl text-sm shadow-md">
                                    <span x-text="copiado ? '✓ Código PIX Copiado!' : '📋 Copiar PIX Copia e Cola'"></span>
                                </button>
                            </div>
                        </template>
                        <div class="flex flex-col sm:flex-row gap-2 pt-2">
                            <template x-if="pedidoCriado.tracking_url">
                                <a :href="pedidoCriado.tracking_url" class="flex-1 bg-[#202430] hover:bg-[#282d3d] text-white font-extrabold py-3 rounded-xl text-xs text-center border border-[#2a3042]">
                                    📍 Acompanhar Pedido →
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

                        <!-- Dados do Cliente -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="text-[11px] font-bold uppercase text-slate-400 block mb-1">Seu Nome *</label>
                                <input type="text" x-model="checkout.nome" placeholder="Ex: João Silva" 
                                    class="w-full bg-[#101217] border border-[#26282e] text-xs text-white rounded-xl px-3.5 py-2.5 focus:border-amber-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="text-[11px] font-bold uppercase text-slate-400 block mb-1">Seu WhatsApp *</label>
                                <input type="tel" x-model="checkout.telefone" placeholder="(31) 99999-9999" 
                                    class="w-full bg-[#101217] border border-[#26282e] text-xs text-white rounded-xl px-3.5 py-2.5 focus:border-amber-500 focus:outline-none">
                            </div>
                        </div>

                        <!-- Endereço se Delivery -->
                        <template x-if="checkout.canal === 'delivery' && !mesaAtiva">
                            <div class="space-y-2.5 bg-[#101217] p-3.5 rounded-2xl border border-[#26282e]">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-white">📍 Endereço de Entrega</span>
                                    <span class="text-[#4ade80] font-bold" x-text="freteCentavos === 0 ? 'Frete Grátis!' : 'Taxa: ' + formatarCentavos(freteCentavos)"></span>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <input type="text" x-model="checkout.cep" @blur="buscarCep()" placeholder="CEP" class="bg-[#16171b] border border-[#26282e] text-xs text-white rounded-xl px-3 py-2">
                                    <input type="text" x-model="checkout.rua" placeholder="Rua / Avenida *" class="col-span-2 bg-[#16171b] border border-[#26282e] text-xs text-white rounded-xl px-3 py-2">
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <input type="text" x-model="checkout.numero" placeholder="Nº *" class="bg-[#16171b] border border-[#26282e] text-xs text-white rounded-xl px-3 py-2">
                                    <input type="text" x-model="checkout.bairro" @change="calcularFrete()" placeholder="Bairro *" class="col-span-2 bg-[#16171b] border border-[#26282e] text-xs text-white rounded-xl px-3 py-2">
                                </div>
                                <input type="text" x-model="checkout.complemento" placeholder="Complemento / Ponto de referência" class="w-full bg-[#16171b] border border-[#26282e] text-xs text-white rounded-xl px-3 py-2">
                            </div>
                        </template>

                        <!-- Forma de Pagamento -->
                        <div class="space-y-2">
                            <label class="text-[11px] font-bold uppercase text-slate-400 block">Forma de Pagamento</label>
                            <div class="grid grid-cols-3 gap-2 text-xs">
                                <button type="button" @click="checkout.forma_pagamento = 'pix'"
                                    class="py-2.5 px-2 rounded-xl font-bold border transition text-center"
                                    :class="checkout.forma_pagamento === 'pix' ? 'bg-emerald-500/20 border-emerald-500 text-emerald-400' : 'bg-[#101217] border-[#26282e] text-slate-400'">
                                    ⚡ PIX
                                </button>
                                <button type="button" @click="checkout.forma_pagamento = 'cartao_maquina'"
                                    class="py-2.5 px-2 rounded-xl font-bold border transition text-center"
                                    :class="checkout.forma_pagamento === 'cartao_maquina' ? 'bg-amber-500/20 border-amber-500 text-amber-400' : 'bg-[#101217] border-[#26282e] text-slate-400'">
                                    💳 Cartão
                                </button>
                                <button type="button" @click="checkout.forma_pagamento = 'dinheiro'"
                                    class="py-2.5 px-2 rounded-xl font-bold border transition text-center"
                                    :class="checkout.forma_pagamento === 'dinheiro' ? 'bg-amber-500/20 border-amber-500 text-amber-400' : 'bg-[#101217] border-[#26282e] text-slate-400'">
                                    💵 Dinheiro
                                </button>
                            </div>

                            <template x-if="checkout.forma_pagamento === 'dinheiro'">
                                <div class="bg-[#101217] border border-[#26282e] rounded-xl p-2.5 flex items-center justify-between gap-2 mt-2">
                                    <input type="text" x-model="checkout.troco_reais" placeholder="Troco para quanto? (R$)"
                                        class="flex-1 bg-[#16171b] border border-[#26282e] rounded-lg px-2.5 py-1.5 text-xs text-white">
                                </div>
                            </template>
                        </div>

                        <!-- Resumo Final e Confirmação -->
                        <div class="bg-[#101217] border border-[#26282e] rounded-2xl p-3.5 space-y-2 text-xs">
                            <div class="flex justify-between text-slate-400">
                                <span>Subtotal</span>
                                <span class="font-bold text-white" x-text="formatarCentavos(subtotalCarrinhoCentavos)"></span>
                            </div>
                            <template x-if="descontoCupomCentavos > 0">
                                <div class="flex justify-between text-[#4ade80] font-bold">
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
                            <div class="flex justify-between items-baseline pt-2 border-t border-[#26282e]">
                                <span class="font-black text-white text-sm">TOTAL A PAGAR</span>
                                <span class="font-black text-[#10b981] text-xl" x-text="formatarCentavos(totalFinalCentavos)"></span>
                            </div>
                        </div>

                        <!-- Botão Confirmar Pedido -->
                        <button @click="enviarPedido()" :disabled="enviandoPedido"
                            class="w-full text-white font-black py-3.5 rounded-2xl text-sm shadow-xl hover:brightness-110 active:scale-98 transition flex items-center justify-center"
                            :style="'background-color: var(--cor-loja)'">
                            <span x-text="enviandoPedido ? 'Gerando seu pedido...' : 'Confirmar Pedido • ' + formatarCentavos(totalFinalCentavos)"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- ==================== LÓGICA ALPINE.JS ==================== -->
    <script>
        function cardapioApp() {
            const mesaInicial = @json($mesa ?? null);
            const lojaSlug = @json($loja->slug);
            return {
                lojaSlug: lojaSlug,
                lojaStatus: @json($lojaStatus ?? ['aberto_agora' => true]),
                mesaAtiva: mesaInicial,
                garcomChamado: false,
                termoBusca: '',
                categoriaAtiva: null,
                categorias: @json($cardapio['categorias'] ?? []),
                modalAberto: false,
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
                freteCentavos: 400,
                checkout: {
                    canal: mesaInicial ? 'mesa' : 'delivery',
                    nome: '',
                    telefone: '',
                    cep: '35170-001',
                    rua: 'Av. Magalhães Pinto',
                    numero: '500',
                    complemento: '',
                    bairro: 'Centro',
                    cidade: @json($loja->cidade ?? 'Coronel Fabriciano'),
                    uf: @json($loja->uf ?? 'MG'),
                    forma_pagamento: 'pix',
                    troco_reais: ''
                },
                produtoSelecionado: null,
                variacaoSelecionadaId: null,
                adicionaisEscolhidos: [],
                carrinho: [],

                initApp() {
                    try {
                        const savedCart = localStorage.getItem('pedelivre_cart_' + this.lojaSlug);
                        if (savedCart) {
                            this.carrinho = JSON.parse(savedCart) || [];
                        } else {
                            // Se o carrinho estiver vazio no primeiro acesso, preenche com os 2 itens de demonstração iguais ao mockup
                            const p1 = this.encontrarProdutoPorNome('Brasa Bacon Burger Especial');
                            const p2 = this.encontrarProdutoPorNome('Duplo Smash Cheddar & Cebola Crispy');
                            if (p1 && p2) {
                                this.carrinho = [
                                    {
                                        produto_id: p1.id,
                                        nome: p1.nome,
                                        variacao_id: null,
                                        variacao_nome: null,
                                        qtd: 1,
                                        unitario_centavos: p1.preco_efetivo_centavos,
                                        observacao: '',
                                        adicionais: [],
                                        total_centavos: p1.preco_efetivo_centavos
                                    },
                                    {
                                        produto_id: p2.id,
                                        nome: p2.nome,
                                        variacao_id: null,
                                        variacao_nome: null,
                                        qtd: 1,
                                        unitario_centavos: p2.preco_efetivo_centavos,
                                        observacao: '',
                                        adicionais: [],
                                        total_centavos: p2.preco_efetivo_centavos
                                    }
                                ];
                                // Aplica cupom BEMVINDO10 para bater exatamente com a tela do mockup (R$ 67,32)
                                this.aplicarCupomRapido('BEMVINDO10');
                            }
                        }

                        const savedCliente = localStorage.getItem('pedelivre_cliente');
                        if (savedCliente) {
                            const parsed = JSON.parse(savedCliente);
                            this.checkout = { ...this.checkout, ...parsed, canal: this.mesaAtiva ? 'mesa' : (parsed.canal || 'delivery') };
                        }

                        const savedOrder = localStorage.getItem('pedelivre_ultimo_pedido_' + this.lojaSlug);
                        if (savedOrder) this.ultimoPedido = JSON.parse(savedOrder);
                    } catch (e) {}

                    if (this.checkout.canal === 'delivery' && !this.mesaAtiva) {
                        this.calcularFrete();
                    }
                },

                encontrarProdutoPorNome(nome) {
                    for (const cat of this.categorias) {
                        for (const p of (cat.produtos || [])) {
                            if (p.nome.toLowerCase() === nome.toLowerCase()) return p;
                        }
                    }
                    return null;
                },

                nomeCurtoCategoria(nome) {
                    return (nome || '')
                        .replace(/ Artesanais/i, '')
                        .replace(/ Especiais/i, '')
                        .replace(/ Geladas/i, '')
                        .replace(/ Promocionais/i, '')
                        .replace(/Acompanhamentos & Porções/i, 'Porções');
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
                    if (novoCanal === 'delivery') {
                        this.calcularFrete();
                    }
                    this.salvarEstadoLocal();
                },

                imagemProduto(produto) {
                    if (produto.imagem && produto.imagem.startsWith('http')) return produto.imagem;
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
                    return 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=600&q=80';
                },

                obterSubtituloCategoria(nome) {
                    const n = (nome || '').toLowerCase();
                    if (n.includes('hambúrguer') || n.includes('burger') || n.includes('lanche')) {
                        return 'Carnes nobres grelhadas no fogo forte com pão brioche amanteigado';
                    }
                    if (n.includes('pizza')) {
                        return 'Massa de fermentação natural de 48h assada em forno a lenha';
                    }
                    if (n.includes('acompanha') || n.includes('porç') || n.includes('petisco')) {
                        return 'Para petiscar e compartilhar';
                    }
                    if (n.includes('bebida')) {
                        return 'Refrigerantes, sucos naturais e cervejas artesanais';
                    }
                    if (n.includes('sobremesa') || n.includes('doce')) {
                        return 'Para fechar a sua refeição com chave de ouro';
                    }
                    if (n.includes('combo')) {
                        return 'Combinações completas com economia e muito sabor';
                    }
                    return 'Itens preparados artesanalmente com ingredientes selecionados';
                },

                get categoriasFiltradas() {
                    const termo = this.termoBusca.trim().toLowerCase();
                    return this.categorias.map(cat => ({
                        ...cat,
                        produtos: (cat.produtos || []).filter(p => !termo || p.nome.toLowerCase().includes(termo) || (p.descricao && p.descricao.toLowerCase().includes(termo)))
                    })).filter(cat => cat.produtos.length > 0);
                },

                get produtosDestaque() {
                    const todos = [];
                    for (const cat of this.categorias) {
                        for (const p of (cat.produtos || [])) {
                            todos.push(p);
                        }
                    }
                    // Retorna prioritariamente os 4 hambúrgueres artesanais da loja (Fiel ao mockup)
                    const burgers = todos.filter(p => (p.nome || '').toLowerCase().includes('burger') || (p.nome || '').toLowerCase().includes('smash') || (p.nome || '').toLowerCase().includes('supreme') || (p.nome || '').toLowerCase().includes('costela'));
                    if (burgers.length >= 4) return burgers.slice(0, 4);
                    return todos.slice(0, 4);
                },

                get metaFreteGratisCentavos() {
                    return 8000;
                },

                get faltaParaFreteGratisCentavos() {
                    return Math.max(0, this.metaFreteGratisCentavos - this.subtotalCarrinhoCentavos);
                },

                get progressoFreteGratisPct() {
                    return Math.min(100, Math.round((this.subtotalCarrinhoCentavos / this.metaFreteGratisCentavos) * 100));
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
                    if (this.subtotalCarrinhoCentavos < 2000 && this.cupomAplicado.codigo === 'BEMVINDO10') return 0;
                    if (this.cupomAplicado.tipo === 'percentual') {
                        return Math.round((this.subtotalCarrinhoCentavos * this.cupomAplicado.valor) / 100);
                    }
                    return Math.min(this.subtotalCarrinhoCentavos, this.cupomAplicado.desconto_centavos || 0);
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
                    if (this.checkout.canal === 'delivery' && !this.mesaAtiva) {
                        this.calcularFrete();
                    }
                },

                aplicarCupomRapido(codigo) {
                    this.cupomInput = codigo;
                    this.cupomAplicado = { codigo: codigo, tipo: 'percentual', valor: 10, desconto_centavos: Math.round(this.subtotalCarrinhoCentavos * 0.10) };
                    this.mensagemCupom = '✓ Cupom ' + codigo + ' (10% OFF) ativado!';
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
                            subtotal_centavos: Math.max(2000, this.subtotalCarrinhoCentavos),
                            canal: canalFinal,
                            telefone: this.checkout.telefone || null
                        })
                    })
                    .then(async r => {
                        const data = await r.json();
                        if (!r.ok || !data.valido) throw new Error(data.message || 'Cupom inválido.');
                        this.cupomAplicado = {
                            codigo: data.codigo,
                            tipo: data.tipo,
                            valor: data.tipo === 'percentual' ? 10 : data.desconto_centavos,
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
                    fetch(`/api/p/${this.lojaSlug}/frete`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({
                            cep: this.checkout.cep,
                            bairro: this.checkout.bairro || 'Centro',
                            subtotal_centavos: this.subtotalCarrinhoCentavos || 3500
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
                    if (!this.checkout.nome || !this.checkout.telefone) {
                        this.erroCheckout = 'Por favor, informe seu nome e WhatsApp para continuar.';
                        return;
                    }
                    this.enviandoPedido = true;
                    this.erroCheckout = '';
                    this.salvarEstadoLocal();

                    const canalFinal = this.mesaAtiva ? 'mesa' : this.checkout.canal;
                    const trocoCentavos = (this.checkout.forma_pagamento === 'dinheiro' && this.checkout.troco_reais)
                        ? Math.round(parseFloat(this.checkout.troco_reais) * 100)
                        : null;

                    const payload = {
                        idempotency_key: 'web_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8),
                        canal: canalFinal,
                        mesa_token: this.mesaAtiva ? this.mesaAtiva.qr_token : null,
                        cliente: {
                            nome: this.checkout.nome,
                            telefone: this.checkout.telefone
                        },
                        endereco: canalFinal === 'delivery' ? {
                            cep: this.checkout.cep || '35170-001',
                            rua: this.checkout.rua || 'Av. Central',
                            numero: this.checkout.numero || 'S/N',
                            complemento: this.checkout.complemento || null,
                            bairro: this.checkout.bairro || 'Centro',
                            cidade: this.checkout.cidade || 'Coronel Fabriciano',
                            uf: this.checkout.uf || 'MG'
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
                        troco_para: trocoCentavos
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
