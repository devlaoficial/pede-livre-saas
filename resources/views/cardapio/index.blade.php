<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-50 scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="theme-color" content="{{ $loja->cor_primaria ?: '#E11D48' }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/manifest.json">
    <title>{{ $loja->nome }} — Cardápio Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --cor-loja: {{ $loja->cor_primaria ?: '#E11D48' }};
        }
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .bg-loja { background-color: var(--cor-loja) !important; }
        .text-loja { color: var(--cor-loja) !important; }
        .border-loja { border-color: var(--cor-loja) !important; }
    </style>
</head>
<body class="min-h-full font-sans antialiased text-slate-800 pb-28 lg:pb-16" x-data="cardapioApp()" x-init="initApp()">

    <!-- Header da Loja com Card Flutuante (Zero Colisão de Texto no Mobile & Desktop) -->
    <header class="relative bg-slate-50">
        <!-- Capa Fotográfica Dinâmica da Loja -->
        <div class="h-36 sm:h-48 lg:h-56 w-full bg-cover bg-center relative"
             style="background-image: url('{{ ($loja->capa && str_starts_with($loja->capa, 'http')) ? $loja->capa : 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1400&q=80' }}')">
            <div class="w-full h-full bg-gradient-to-t from-slate-950/85 via-slate-950/35 to-transparent"></div>
        </div>

        <!-- Card Flutuante de Identidade da Loja -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-3">
            <div class="bg-white rounded-3xl shadow-md border border-slate-200/80 p-4 sm:p-6 -mt-14 sm:-mt-16 relative z-10">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <!-- Logo + Nome + Status -->
                    <div class="flex items-start sm:items-center gap-3.5 sm:gap-4">
                        @if($loja->logo && str_starts_with($loja->logo, 'http'))
                            <img src="{{ $loja->logo }}" alt="{{ $loja->nome }}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover shadow-md border-2 border-white shrink-0">
                        @else
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-loja shadow-md border-2 border-white flex items-center justify-center text-white font-black text-2xl sm:text-3xl shrink-0">
                                {{ substr($loja->nome, 0, 1) }}
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h1 class="text-lg sm:text-2xl lg:text-3xl font-black text-slate-900 leading-tight break-words">{{ $loja->nome }}</h1>
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-extrabold px-2.5 py-0.5 rounded-full border shrink-0"
                                      :class="lojaStatus.aberto_agora ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="lojaStatus.aberto_agora ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'"></span>
                                    <span x-text="lojaStatus.aberto_agora ? 'Aberto Agora' : 'Aberto para Pedidos'"></span>
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-600 mt-1.5">
                                <span class="font-semibold text-slate-700">⏱️ {{ $loja->config_json['tempo_medio_preparo_min'] ?? 35 }}–45 min</span>
                                <span class="text-slate-300">•</span>
                                <span class="font-semibold text-emerald-700">⚡ Aceita PIX, Cartão e Dinheiro</span>
                                @if($loja->endereco)
                                    <span class="hidden sm:inline text-slate-300">•</span>
                                    <span class="text-slate-500 truncate max-w-xs sm:max-w-md">📍 {{ $loja->endereco }}, {{ $loja->cidade }}/{{ $loja->uf }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Seletor de Canal (Delivery / Retirada) ou Banner de Mesa QR -->
                    <div class="flex items-center justify-between sm:justify-end gap-2 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100 shrink-0">
                        <template x-if="mesaAtiva">
                            <div class="flex items-center justify-between w-full sm:w-auto gap-3 bg-amber-50 border border-amber-300 px-3.5 py-2 rounded-2xl">
                                <span class="text-xs font-black text-amber-900" x-text="'🪑 Mesa #' + mesaAtiva.numero"></span>
                                <button type="button" @click="chamarGarcom()" class="bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold px-3 py-1.5 rounded-xl shadow-xs transition">
                                    <span x-text="garcomChamado ? '✓ Garçom Avisado!' : '🔔 Chamar Garçom'"></span>
                                </button>
                            </div>
                        </template>
                        <template x-if="!mesaAtiva">
                            <div class="grid grid-cols-2 sm:inline-flex w-full sm:w-auto bg-slate-100 p-1 rounded-2xl border border-slate-200/80">
                                <button type="button" @click="alterarCanal('delivery')"
                                    :class="checkout.canal === 'delivery' ? 'bg-white text-slate-900 shadow-xs font-extrabold' : 'text-slate-600 font-semibold hover:text-slate-900'"
                                    class="px-4 py-2 rounded-xl text-xs transition flex items-center justify-center gap-1.5">
                                    <span>🛵</span>
                                    <span>Delivery</span>
                                </button>
                                <button type="button" @click="alterarCanal('retirada')"
                                    :class="checkout.canal === 'retirada' ? 'bg-white text-slate-900 shadow-xs font-extrabold' : 'text-slate-600 font-semibold hover:text-slate-900'"
                                    class="px-4 py-2 rounded-xl text-xs transition flex items-center justify-center gap-1.5">
                                    <span>🛍️</span>
                                    <span>Retirada</span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Faixa de Cupom Promocional & Pedido Ativo -->
                <div class="mt-3.5 pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <button type="button" @click="aplicarCupomRapido('BEMVINDO10')"
                        class="flex items-center justify-between sm:justify-start gap-2 bg-rose-50/80 hover:bg-rose-100/80 border border-rose-200/80 text-rose-700 px-3 py-1.5 rounded-xl text-xs transition group text-left">
                        <span class="flex items-center gap-1.5 font-semibold">
                            <span>🏷️</span>
                            <span>Cupom <strong class="font-black underline">BEMVINDO10</strong>: 10% OFF em pedidos acima de R$ 30</span>
                        </span>
                        <span class="text-[11px] font-extrabold bg-rose-600 text-white px-2 py-0.5 rounded-lg shrink-0 group-hover:bg-rose-700"
                              x-text="cupomAplicado?.codigo === 'BEMVINDO10' ? '✓ Ativado' : 'Ativar'"></span>
                    </button>

                    <template x-if="ultimoPedido && ultimoPedido.tracking_url">
                        <a :href="ultimoPedido.tracking_url"
                           class="inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold px-3.5 py-1.5 rounded-xl shadow-xs transition shrink-0">
                            <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
                            <span x-text="'Acompanhar Pedido #' + ultimoPedido.codigo_publico + ' →'"></span>
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </header>

    <!-- Barra Sticky de Busca & Categorias -->
    <div class="sticky top-0 z-30 bg-white/95 backdrop-blur-md shadow-xs border-b border-slate-200/70">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex flex-col md:flex-row md:items-center gap-2.5">
            <div class="relative md:w-72 lg:w-80 shrink-0">
                <input type="text" x-model="termoBusca" placeholder="Buscar prato, bebida ou ingrediente..."
                    class="w-full bg-slate-100 text-sm rounded-xl pl-9 pr-8 py-2 border border-transparent focus:bg-white focus:border-rose-500 focus:outline-none transition">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <button x-cloak x-show="termoBusca.length > 0" @click="termoBusca = ''" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs font-bold p-0.5">✕</button>
            </div>

            <nav class="flex overflow-x-auto no-scrollbar gap-2 py-0.5 flex-1">
                <template x-for="cat in categoriasFiltradas" :key="cat.id">
                    <button @click="scrollParaCategoria(cat.id)"
                        class="px-3.5 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-all shrink-0 flex items-center gap-1.5"
                        :class="(categoriaAtiva || categoriasFiltradas[0]?.id) === cat.id ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                        <span x-text="cat.nome"></span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full"
                              :class="(categoriaAtiva || categoriasFiltradas[0]?.id) === cat.id ? 'bg-rose-700 text-rose-100' : 'bg-slate-200/80 text-slate-500'"
                              x-text="cat.produtos.length"></span>
                    </button>
                </template>
            </nav>
        </div>
    </div>

    <!-- Container Principal Auto-Adaptativo: 1 Coluna no Mobile | 12 Colunas (Cardápio 8 + Sacola Fixa 4) no PC -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Coluna de Produtos (8 colunas no PC, 100% no Mobile) -->
            <main class="lg:col-span-8 space-y-8">
                <!-- Carrossel Horizontal: 🔥 Destaques & Mais Pedidos (Alta Conversão) -->
                <template x-if="!termoBusca.trim() && produtosDestaque.length > 0">
                    <section class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h2 class="text-base sm:text-lg font-black text-slate-900 flex items-center gap-2">
                                <span>🔥</span>
                                <span>Destaques &amp; Mais Pedidos</span>
                            </h2>
                            <span class="text-[11px] font-bold text-rose-600 bg-rose-50 px-2.5 py-0.5 rounded-full">Campeões de Venda</span>
                        </div>
                        <div class="flex overflow-x-auto no-scrollbar gap-3.5 pb-1">
                            <template x-for="dest in produtosDestaque" :key="'dest-' + dest.id">
                                <div @click="abrirModalProduto(dest)"
                                     class="w-44 sm:w-52 shrink-0 bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md hover:border-rose-400 transition cursor-pointer overflow-hidden flex flex-col justify-between group">
                                    <div class="relative h-32 w-full bg-slate-100 overflow-hidden">
                                        <img :src="imagemProduto(dest)" :alt="dest.nome" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        <template x-if="dest.preco_promocional_centavos && dest.preco_promocional_centavos < dest.preco_centavos">
                                            <span class="absolute top-2 left-2 bg-emerald-600 text-white text-[10px] font-black px-2 py-0.5 rounded-lg shadow-xs"
                                                  x-text="'-' + Math.round((1 - dest.preco_promocional_centavos / dest.preco_centavos) * 100) + '% OFF'"></span>
                                        </template>
                                    </div>
                                    <div class="p-3 flex-1 flex flex-col justify-between">
                                        <h3 class="font-bold text-xs sm:text-sm text-slate-900 line-clamp-2 leading-snug group-hover:text-rose-600" x-text="dest.nome"></h3>
                                        <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between">
                                            <div>
                                                <span class="text-xs sm:text-sm font-black text-emerald-700" x-text="formatarCentavos(dest.preco_efetivo_centavos)"></span>
                                                <span x-show="dest.preco_promocional_centavos && dest.preco_promocional_centavos < dest.preco_centavos"
                                                      class="block text-[10px] text-slate-400 line-through" x-text="formatarCentavos(dest.preco_centavos)"></span>
                                            </div>
                                            <span class="w-7 h-7 rounded-xl bg-rose-50 text-rose-600 group-hover:bg-rose-600 group-hover:text-white font-black text-sm flex items-center justify-center transition">+</span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </section>
                </template>

                <template x-if="categoriasFiltradas.length === 0">
                    <div class="bg-white rounded-3xl border border-slate-200/80 p-10 text-center space-y-2">
                        <div class="text-3xl">🔍</div>
                        <h3 class="text-base font-bold text-slate-800">Nenhum item encontrado</h3>
                        <p class="text-xs text-slate-500">Tente buscar por outro termo ou limpe o filtro de busca.</p>
                        <button @click="termoBusca = ''" class="mt-2 inline-flex bg-rose-600 text-white text-xs font-bold px-4 py-2 rounded-xl">Ver Todo o Cardápio</button>
                    </div>
                </template>

                <template x-for="categoria in categoriasFiltradas" :key="categoria.id">
                    <section :id="'categoria-' + categoria.id" class="space-y-3.5 scroll-mt-28">
                        <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-2.5">
                            <div class="min-w-0">
                                <h2 class="text-lg sm:text-xl font-black text-slate-900 border-l-4 border-rose-600 pl-3 leading-snug" x-text="categoria.nome"></h2>
                                <p x-show="categoria.descricao" class="text-xs text-slate-500 pl-4 mt-0.5 leading-relaxed" x-text="categoria.descricao"></p>
                            </div>
                            <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full shrink-0 whitespace-nowrap"
                                  x-text="categoria.produtos.length === 1 ? '1 item' : categoria.produtos.length + ' itens'"></span>
                        </div>

                        <!-- Grade de Produtos: 1 coluna no celular, 2 colunas em telas médias/PC -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                            <template x-for="produto in categoria.produtos" :key="produto.id">
                                <article @click="abrirModalProduto(produto)"
                                    class="bg-white rounded-2xl p-4 shadow-xs border transition-all cursor-pointer flex justify-between gap-3.5 hover:border-rose-500/60 hover:shadow-md active:scale-[0.99] group relative"
                                    :class="qtdProdutoNaSacola(produto.id) > 0 ? 'border-rose-400 ring-1 ring-rose-500/20' : 'border-slate-200/80'">

                                    <div class="flex-1 min-w-0 flex flex-col justify-between">
                                        <div>
                                            <div class="flex flex-wrap items-center gap-1.5 mb-1">
                                                <template x-if="qtdProdutoNaSacola(produto.id) > 0">
                                                    <span class="bg-rose-600 text-white text-[10px] font-black px-2 py-0.5 rounded-md"
                                                          x-text="qtdProdutoNaSacola(produto.id) + 'x na sacola'"></span>
                                                </template>
                                                <template x-if="produto.preco_promocional_centavos && produto.preco_promocional_centavos < produto.preco_centavos">
                                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-black px-2 py-0.5 rounded-md"
                                                          x-text="'-' + Math.round((1 - produto.preco_promocional_centavos / produto.preco_centavos) * 100) + '% OFF'"></span>
                                                </template>
                                            </div>
                                            <h3 class="font-bold text-slate-900 text-sm sm:text-base group-hover:text-rose-600 transition-colors leading-snug" x-text="produto.nome"></h3>
                                            <p class="text-xs text-slate-500 line-clamp-2 mt-1 leading-relaxed" x-text="produto.descricao"></p>
                                        </div>

                                        <div class="mt-3 pt-2 flex items-center justify-between gap-2">
                                            <div class="flex flex-wrap items-baseline gap-1.5">
                                                <template x-if="produto.variacoes && produto.variacoes.length > 0">
                                                    <span class="text-[11px] text-slate-400 font-semibold">A partir de</span>
                                                </template>
                                                <span class="text-sm sm:text-base font-black text-emerald-700"
                                                    x-text="formatarCentavos(produto.variacoes?.length > 0 ? Math.min(...produto.variacoes.map(v => v.preco_centavos)) : produto.preco_efetivo_centavos)"></span>
                                                <span x-show="produto.preco_promocional_centavos && produto.preco_promocional_centavos < produto.preco_centavos"
                                                    class="text-xs text-slate-400 line-through"
                                                    x-text="formatarCentavos(produto.preco_centavos)"></span>
                                            </div>
                                            <span class="text-xs font-extrabold text-rose-600 bg-rose-50 px-3 py-1.5 rounded-xl group-hover:bg-rose-600 group-hover:text-white transition shrink-0">
                                                + Adicionar
                                            </span>
                                        </div>
                                    </div>

                                    <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden shrink-0 bg-slate-100 border border-slate-100 flex items-center justify-center relative">
                                        <img :src="imagemProduto(produto)" :alt="produto.nome" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    </div>
                                </article>
                            </template>
                        </div>
                    </section>
                </template>
            </main>

            <!-- Coluna Lateral da Sacola Fixa no PC (Desktop Sticky Cart Sidebar) -->
            <aside class="hidden lg:block lg:col-span-4 sticky top-20">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-lg overflow-hidden">
                    <div class="bg-slate-900 text-white p-4 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <span class="text-lg">🛍️</span>
                            <div>
                                <h3 class="font-bold text-sm">Sua Sacola</h3>
                                <p class="text-[11px] text-slate-400" x-text="mesaAtiva ? 'Consumo na Mesa #' + mesaAtiva.numero : (checkout.canal === 'delivery' ? 'Entrega Delivery' : 'Retirada no Balcão')"></p>
                            </div>
                        </div>
                        <span class="bg-rose-600 text-white text-xs font-black px-2.5 py-1 rounded-full"
                              x-text="totalItensSacola === 1 ? '1 item' : totalItensSacola + ' itens'"></span>
                    </div>

                    <div class="p-4">
                        <!-- Barra Progressiva de Frete Grátis na Sacola Desktop -->
                        <template x-if="checkout.canal === 'delivery' && !mesaAtiva">
                            <div class="mb-3 p-3 rounded-2xl border text-xs space-y-1.5"
                                 :class="faltaParaFreteGratisCentavos === 0 ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-slate-50 border-slate-200 text-slate-700'">
                                <div class="flex items-center justify-between font-bold">
                                    <span x-text="faltaParaFreteGratisCentavos === 0 ? '🎉 Você ganhou Frete Grátis!' : '🚚 Faltam ' + formatarCentavos(faltaParaFreteGratisCentavos) + ' p/ Frete Grátis'"></span>
                                    <span class="text-[11px]" x-text="progressoFreteGratisPct + '%'"></span>
                                </div>
                                <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                                    <div class="h-full transition-all duration-300 rounded-full"
                                         :class="faltaParaFreteGratisCentavos === 0 ? 'bg-emerald-500' : 'bg-rose-600'"
                                         :style="'width: ' + progressoFreteGratisPct + '%'"></div>
                                </div>
                            </div>
                        </template>

                        <template x-if="carrinho.length === 0">
                            <div class="text-center py-10 space-y-2">
                                <div class="text-3xl">🍔</div>
                                <p class="text-sm font-bold text-slate-700">Sua sacola está vazia</p>
                                <p class="text-xs text-slate-400">Clique em qualquer item ao lado para montar seu pedido.</p>
                            </div>
                        </template>

                        <template x-if="carrinho.length > 0">
                            <div class="space-y-4">
                                <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto pr-1">
                                    <template x-for="(item, idx) in carrinho" :key="idx">
                                        <div class="py-3 flex justify-between items-start gap-2">
                                            <div class="text-xs min-w-0 flex-1">
                                                <div class="font-bold text-slate-900" x-text="item.nome"></div>
                                                <template x-if="item.variacao_nome">
                                                    <div class="text-slate-500 text-[11px]" x-text="'Tamanho: ' + item.variacao_nome"></div>
                                                </template>
                                                <template x-if="item.adicionais && item.adicionais.length > 0">
                                                    <div class="text-slate-400 text-[11px]" x-text="item.adicionais.map(a => '+ ' + a.nome).join(', ')"></div>
                                                </template>
                                                <template x-if="item.observacao">
                                                    <div class="text-amber-700 text-[11px] italic" x-text="'Obs: ' + item.observacao"></div>
                                                </template>
                                                <div class="text-xs font-extrabold text-emerald-700 mt-1" x-text="formatarCentavos(item.total_centavos)"></div>
                                            </div>
                                            <!-- Controle de Quantidade na Sacola -->
                                            <div class="flex items-center gap-1.5 bg-slate-100 rounded-xl p-1 border border-slate-200/70 shrink-0">
                                                <button type="button" @click="alterarQtdCarrinho(idx, -1)"
                                                    class="w-6 h-6 rounded-lg bg-white text-slate-700 hover:text-rose-600 font-black text-xs shadow-2xs flex items-center justify-center">
                                                    <span x-text="item.qtd === 1 ? '🗑️' : '−'"></span>
                                                </button>
                                                <span class="text-xs font-black text-slate-900 w-5 text-center" x-text="item.qtd"></span>
                                                <button type="button" @click="alterarQtdCarrinho(idx, 1)"
                                                    class="w-6 h-6 rounded-lg bg-white text-slate-700 hover:text-emerald-600 font-black text-xs shadow-2xs flex items-center justify-center">+</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div class="border-t border-slate-200 pt-3 space-y-2">
                                    <div class="flex justify-between items-center text-xs text-slate-500">
                                        <span>Subtotal</span>
                                        <span class="font-bold text-slate-800" x-text="formatarCentavos(subtotalCarrinhoCentavos)"></span>
                                    </div>
                                    <template x-if="descontoCupomCentavos > 0">
                                        <div class="flex justify-between items-center text-xs text-emerald-600 font-bold">
                                            <span x-text="'Cupom (' + cupomAplicado.codigo + ')'"></span>
                                            <span x-text="'- ' + formatarCentavos(descontoCupomCentavos)"></span>
                                        </div>
                                    </template>
                                    <template x-if="checkout.canal === 'delivery' && !mesaAtiva">
                                        <div class="flex justify-between items-center text-xs text-slate-500">
                                            <span>Taxa de Entrega</span>
                                            <span class="font-bold" :class="freteCentavos === 0 ? 'text-emerald-600' : 'text-slate-800'"
                                                  x-text="freteCentavos === 0 ? 'Grátis' : formatarCentavos(freteCentavos)"></span>
                                        </div>
                                    </template>
                                    <div class="flex justify-between items-center pt-1 border-t border-slate-100">
                                        <span class="text-xs font-black text-slate-700 uppercase">Total</span>
                                        <span class="text-lg font-black text-slate-900" x-text="formatarCentavos(totalFinalCentavos)"></span>
                                    </div>
                                    <button @click="abrirCheckout()"
                                        class="w-full bg-rose-600 hover:bg-rose-700 text-white font-extrabold py-3.5 rounded-2xl text-sm shadow-lg shadow-rose-600/25 transition">
                                        Finalizar Pedido →
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </aside>

        </div>
    </div>

    <!-- Modal de Customização do Produto (Responsivo Mobile Bottom-Sheet / PC Dialog) -->
    <div x-cloak x-show="modalAberto" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="modalAberto = false" class="bg-white w-full max-w-lg rounded-t-3xl sm:rounded-3xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden">
            <!-- Imagem de Capa do Produto no Modal -->
            <div class="relative h-44 sm:h-52 w-full bg-slate-100 shrink-0">
                <img :src="produtoSelecionado ? imagemProduto(produtoSelecionado) : ''" :alt="produtoSelecionado?.nome" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent"></div>
                <button @click="modalAberto = false" class="absolute top-3 right-3 w-9 h-9 rounded-full bg-white/90 text-slate-800 hover:bg-white font-black flex items-center justify-center shadow-md">✕</button>
                <div class="absolute bottom-3 left-4 right-4 text-white">
                    <h3 class="font-black text-lg sm:text-xl leading-tight" x-text="produtoSelecionado?.nome"></h3>
                </div>
            </div>

            <div class="p-4 overflow-y-auto space-y-5 flex-1">
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed" x-text="produtoSelecionado?.descricao"></p>

                <!-- Variações de Tamanho -->
                <template x-if="produtoSelecionado?.variacoes?.length > 0">
                    <div class="space-y-2 bg-slate-50 p-3.5 rounded-2xl border border-slate-200">
                        <div class="flex justify-between items-center">
                            <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-800">Escolha o Tamanho</h4>
                            <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700">✓ Obrigatório</span>
                        </div>
                        <div class="space-y-1.5 pt-1">
                            <template x-for="varItem in produtoSelecionado.variacoes" :key="varItem.id">
                                <label class="flex items-center justify-between p-3 bg-white rounded-xl border cursor-pointer transition"
                                    :class="variacaoSelecionadaId === varItem.id ? 'border-rose-600 bg-rose-50/40 ring-1 ring-rose-500' : 'border-slate-200 hover:border-slate-300'">
                                    <div class="flex items-center gap-2.5">
                                        <input type="radio" :value="varItem.id" x-model="variacaoSelecionadaId" class="text-rose-600 focus:ring-rose-500">
                                        <span class="text-xs sm:text-sm font-bold text-slate-800" x-text="varItem.nome"></span>
                                    </div>
                                    <span class="text-xs sm:text-sm font-black text-slate-900" x-text="formatarCentavos(varItem.preco_centavos)"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Grupos de Adicionais & Sabores Meio a Meio -->
                <template x-for="grupo in produtoSelecionado?.grupos_adicionais" :key="grupo.id">
                    <div class="space-y-2 bg-slate-50 p-3.5 rounded-2xl border"
                        :class="grupoSatisfeito(grupo) ? 'border-emerald-200' : (grupo.obrigatorio ? 'border-rose-300' : 'border-slate-200')">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-800" x-text="grupo.nome"></h4>
                                <span class="text-[11px] text-slate-500"
                                    x-text="grupo.maximo === 1 ? 'Escolha 1 opção' : 'Escolha até ' + grupo.maximo + ' opções'"></span>
                            </div>
                            <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full"
                                :class="grupoSatisfeito(grupo) ? 'bg-emerald-100 text-emerald-700' : (grupo.obrigatorio ? 'bg-rose-100 text-rose-700' : 'bg-slate-200 text-slate-600')"
                                x-text="grupoSatisfeito(grupo) ? '✓ OK' : (grupo.obrigatorio ? 'Obrigatório (' + qtdNoGrupo(grupo.id) + '/' + Math.max(1, grupo.minimo) + ')' : 'Opcional')"></span>
                        </div>

                        <div class="space-y-1.5 pt-1">
                            <template x-for="opcao in grupo.opcoes" :key="opcao.id">
                                <div @click="toggleAdicional(grupo, opcao)"
                                    class="flex items-center justify-between p-2.5 bg-white rounded-xl border cursor-pointer transition select-none"
                                    :class="adicionalSelecionado(opcao.id) ? 'border-rose-600 bg-rose-50/40 ring-1 ring-rose-500' : 'border-slate-200/80 hover:border-slate-300'">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-4 h-4 rounded-full border flex items-center justify-center text-[10px] font-bold"
                                            :class="adicionalSelecionado(opcao.id) ? 'bg-rose-600 border-rose-600 text-white' : 'border-slate-300'">
                                            <span x-show="adicionalSelecionado(opcao.id)">✓</span>
                                        </span>
                                        <span class="text-xs font-semibold text-slate-800" x-text="opcao.nome"></span>
                                    </div>
                                    <span class="text-xs font-bold" :class="opcao.preco_centavos > 0 ? 'text-slate-900' : 'text-emerald-600'"
                                        x-text="opcao.preco_centavos > 0 ? '+ ' + formatarCentavos(opcao.preco_centavos) : 'Grátis'"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Observação do Item -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Alguma observação neste item?</label>
                    <input type="text" x-model="observacaoItem" placeholder="Ex: Sem cebola, maionese à parte, bem passado..."
                        class="w-full bg-slate-100 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500">
                </div>
            </div>

            <!-- Modal Footer com Controle de Quantidade -->
            <div class="p-4 border-t border-slate-100 bg-white sticky bottom-0 flex items-center justify-between gap-3">
                <div class="flex items-center bg-slate-100 rounded-xl p-1 border border-slate-200">
                    <button type="button" @click="qtdModal = Math.max(1, qtdModal - 1)"
                        class="w-9 h-9 rounded-lg bg-white text-slate-800 font-black text-base shadow-2xs hover:bg-slate-50">−</button>
                    <span class="w-8 text-center font-black text-sm text-slate-900" x-text="qtdModal"></span>
                    <button type="button" @click="qtdModal++"
                        class="w-9 h-9 rounded-lg bg-white text-slate-800 font-black text-base shadow-2xs hover:bg-slate-50">+</button>
                </div>

                <button @click="adicionarAoCarrinho()" :disabled="!modalValido"
                    class="flex-1 bg-rose-600 text-white font-extrabold py-3.5 px-4 rounded-xl shadow-md shadow-rose-600/20 hover:bg-rose-700 active:scale-95 disabled:opacity-40 transition-all text-xs sm:text-sm flex items-center justify-between">
                    <span x-text="mensagemBotaoModal"></span>
                    <span class="font-black" x-text="formatarCentavos(subtotalModalCentavos)"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Barra Inferior da Sacola no Mobile (Oculta no Desktop pois o Desktop já tem a Sacola Lateral Fixa) -->
    <div x-cloak x-show="carrinho.length > 0" class="lg:hidden fixed bottom-0 inset-x-0 z-40 p-3 max-w-xl mx-auto pointer-events-none">
        <div @click="abrirCheckout()" class="bg-slate-900 text-white rounded-2xl p-3.5 shadow-2xl flex items-center justify-between gap-4 pointer-events-auto border border-slate-800 cursor-pointer active:scale-[0.99] transition">
            <div class="flex items-center gap-3">
                <span class="bg-rose-600 text-white text-xs font-black w-7 h-7 rounded-xl flex items-center justify-center" x-text="totalItensSacola"></span>
                <div>
                    <span class="text-[11px] text-slate-400 block font-medium">Total com Entrega / Descontos</span>
                    <span class="text-base font-black" x-text="formatarCentavos(totalFinalCentavos)"></span>
                </div>
            </div>
            <button type="button" class="bg-rose-600 hover:bg-rose-700 text-white text-xs sm:text-sm font-extrabold py-2.5 px-4 rounded-xl shadow-md transition-all">
                Ver Sacola →
            </button>
        </div>
    </div>

    <!-- Modal de Checkout & PIX QR Code (Mobile & Desktop) -->
    <div x-cloak x-show="sacolaAberta" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="sacolaAberta = false" class="bg-white w-full max-w-lg rounded-t-3xl sm:rounded-3xl max-h-[92vh] flex flex-col shadow-2xl">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white rounded-t-3xl z-10">
                <h3 class="font-black text-base text-slate-900" x-text="pedidoCriado ? 'Pedido #' + pedidoCriado.codigo_publico + ' Confirmado!' : 'Finalizar Seu Pedido'"></h3>
                <button @click="sacolaAberta = false" class="p-1.5 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200">✕</button>
            </div>

            <div class="p-4 overflow-y-auto space-y-4">
                <!-- Tela de PIX Gerado / Sucesso -->
                <template x-if="pedidoCriado">
                    <div class="text-center space-y-4 py-2">
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-extrabold">
                            ✓ Pedido registrado! Total: <span x-text="formatarCentavos(pedidoCriado.total_centavos)"></span>
                        </div>
                        <template x-if="pedidoCriado.pix?.qr_code_base64">
                            <div class="flex flex-col items-center space-y-2">
                                <img :src="'data:image/png;base64,' + pedidoCriado.pix.qr_code_base64" alt="QR Code PIX" class="w-52 h-52 border rounded-2xl p-2 shadow-sm">
                                <span class="text-xs text-slate-500">Escaneie o QR Code no app do seu banco ou copie o código PIX abaixo:</span>
                            </div>
                        </template>
                        <template x-if="pedidoCriado.pix?.copia_e_cola">
                            <div class="space-y-2">
                                <textarea readonly x-text="pedidoCriado.pix.copia_e_cola" class="w-full text-[11px] font-mono bg-slate-100 p-2.5 rounded-xl border border-slate-200 h-20"></textarea>
                                <button type="button" @click="navigator.clipboard.writeText(pedidoCriado.pix.copia_e_cola); copiado = true" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold py-3 rounded-xl text-sm shadow-md">
                                    <span x-text="copiado ? '✓ Código PIX Copiado!' : '📋 Copiar PIX Copia e Cola'"></span>
                                </button>
                            </div>
                        </template>
                        <div class="flex flex-col sm:flex-row gap-2 pt-2">
                            <template x-if="pedidoCriado.tracking_url">
                                <a :href="pedidoCriado.tracking_url" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-extrabold py-3 rounded-xl text-xs">
                                    📍 Acompanhar Status em Tempo Real →
                                </a>
                            </template>
                            @if($loja->telefone_whatsapp)
                            <a :href="'https://wa.me/55{{ preg_replace('/\D/', '', $loja->telefone_whatsapp) }}?text=' + encodeURIComponent('Olá! Acabei de fazer o pedido #' + pedidoCriado.codigo_publico + ' pelo cardápio digital.')"
                               target="_blank"
                               class="flex-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-extrabold py-3 rounded-xl text-xs">
                                💬 Falar no WhatsApp da Loja ↗
                            </a>
                            @endif
                        </div>
                    </div>
                </template>

                <!-- Formulário de Checkout -->
                <template x-if="!pedidoCriado">
                    <div class="space-y-4">
                        <template x-if="erroCheckout">
                            <div class="bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold p-3 rounded-xl" x-text="erroCheckout"></div>
                        </template>

                        <!-- Barra Progressiva de Frete Grátis no Checkout -->
                        <template x-if="checkout.canal === 'delivery' && !mesaAtiva">
                            <div class="p-3 rounded-2xl border text-xs space-y-1.5"
                                 :class="faltaParaFreteGratisCentavos === 0 ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-slate-50 border-slate-200 text-slate-700'">
                                <div class="flex items-center justify-between font-bold">
                                    <span x-text="faltaParaFreteGratisCentavos === 0 ? '🎉 Parabéns! Você ganhou Frete Grátis!' : '🚚 Adicione mais ' + formatarCentavos(faltaParaFreteGratisCentavos) + ' para ganhar Frete Grátis'"></span>
                                    <span class="text-[11px]" x-text="progressoFreteGratisPct + '%'"></span>
                                </div>
                                <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                    <div class="h-full transition-all duration-300 rounded-full"
                                         :class="faltaParaFreteGratisCentavos === 0 ? 'bg-emerald-500' : 'bg-rose-600'"
                                         :style="'width: ' + progressoFreteGratisPct + '%'"></div>
                                </div>
                            </div>
                        </template>

                        <!-- Lista de Itens com Controle +/- -->
                        <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200/70 space-y-2 max-h-44 overflow-y-auto">
                            <template x-for="(item, idx) in carrinho" :key="idx">
                                <div class="flex justify-between items-center text-xs bg-white p-2.5 rounded-xl border border-slate-100 gap-2">
                                    <div class="min-w-0 flex-1">
                                        <span class="font-bold text-slate-900 block truncate" x-text="item.nome"></span>
                                        <template x-if="item.variacao_nome">
                                            <span class="text-slate-500 block text-[11px]" x-text="'Tamanho: ' + item.variacao_nome"></span>
                                        </template>
                                        <template x-if="item.adicionais?.length > 0">
                                            <span class="text-slate-500 block text-[11px]" x-text="item.adicionais.map(a => '+ ' + a.nome).join(', ')"></span>
                                        </template>
                                        <span class="font-extrabold text-emerald-700 text-xs" x-text="formatarCentavos(item.total_centavos)"></span>
                                    </div>
                                    <div class="flex items-center gap-1.5 bg-slate-100 rounded-xl p-1 shrink-0">
                                        <button type="button" @click="alterarQtdCarrinho(idx, -1)" class="w-6 h-6 rounded-lg bg-white text-slate-700 font-black text-xs shadow-2xs">−</button>
                                        <span class="w-5 text-center font-black text-slate-900" x-text="item.qtd"></span>
                                        <button type="button" @click="alterarQtdCarrinho(idx, 1)" class="w-6 h-6 rounded-lg bg-white text-slate-700 font-black text-xs shadow-2xs">+</button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Order Bump: Sugestão Inteligente "Peça Também com 1 Toque" -->
                        <template x-if="sugestoesOrderBump.length > 0">
                            <div class="bg-amber-50/70 border border-amber-200/80 rounded-2xl p-3 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-900">⚡ Que tal adicionar para acompanhar?</span>
                                    <span class="text-[10px] font-bold text-amber-700">1 toque</span>
                                </div>
                                <div class="flex overflow-x-auto no-scrollbar gap-2">
                                    <template x-for="sug in sugestoesOrderBump" :key="'bump-' + sug.id">
                                        <div class="bg-white border border-amber-200/80 rounded-xl p-2 flex items-center gap-2.5 shrink-0 w-56 shadow-2xs">
                                            <img :src="imagemProduto(sug)" class="w-10 h-10 rounded-lg object-cover shrink-0">
                                            <div class="min-w-0 flex-1">
                                                <div class="text-xs font-bold text-slate-900 truncate" x-text="sug.nome"></div>
                                                <div class="text-[11px] font-black text-emerald-700" x-text="'+ ' + formatarCentavos(sug.preco_efetivo_centavos)"></div>
                                            </div>
                                            <button type="button" @click="adicionarRapidoOrderBump(sug)"
                                                    class="bg-rose-600 hover:bg-rose-700 text-white font-black text-xs px-2.5 py-1.5 rounded-lg shrink-0 transition">
                                                + Add
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Canal de Atendimento -->
                        <template x-if="!mesaAtiva">
                            <div class="grid grid-cols-2 gap-2 bg-slate-100 p-1 rounded-2xl border border-slate-200">
                                <button type="button" @click="alterarCanal('delivery')" :class="checkout.canal === 'delivery' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-700'" class="py-2.5 rounded-xl text-xs font-extrabold transition">🛵 Entrega (Delivery)</button>
                                <button type="button" @click="alterarCanal('retirada')" :class="checkout.canal === 'retirada' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-700'" class="py-2.5 rounded-xl text-xs font-extrabold transition">🛍️ Retirada no Balcão</button>
                            </div>
                        </template>

                        <!-- Dados do Cliente -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="text-[11px] font-bold uppercase text-slate-500 block mb-1">Seu Nome *</label>
                                <input type="text" x-model="checkout.nome" placeholder="Ex: João Silva" class="w-full bg-slate-100 text-sm rounded-xl px-3.5 py-2.5 border border-transparent focus:bg-white focus:border-rose-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="text-[11px] font-bold uppercase text-slate-500 block mb-1">Seu WhatsApp *</label>
                                <input type="tel" x-model="checkout.telefone" placeholder="(31) 99999-9999" class="w-full bg-slate-100 text-sm rounded-xl px-3.5 py-2.5 border border-transparent focus:bg-white focus:border-rose-500 focus:outline-none">
                            </div>
                        </div>

                        <!-- Endereço de Entrega (com busca automática ViaCEP e cálculo de frete) -->
                        <template x-if="checkout.canal === 'delivery' && !mesaAtiva">
                            <div class="space-y-2.5 bg-slate-50 p-3.5 rounded-2xl border border-slate-200/80">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-extrabold uppercase text-slate-600">📍 Endereço de Entrega</span>
                                    <span class="text-[11px] font-bold text-emerald-700" x-text="freteCentavos === 0 ? 'Frete Grátis!' : 'Taxa: ' + formatarCentavos(freteCentavos)"></span>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <input type="text" x-model="checkout.cep" @blur="buscarCep()" placeholder="CEP" class="bg-white border border-slate-200 text-sm rounded-xl px-3 py-2">
                                    <input type="text" x-model="checkout.rua" placeholder="Rua / Avenida *" class="col-span-2 bg-white border border-slate-200 text-sm rounded-xl px-3 py-2">
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <input type="text" x-model="checkout.numero" placeholder="Nº *" class="bg-white border border-slate-200 text-sm rounded-xl px-3 py-2">
                                    <input type="text" x-model="checkout.bairro" @change="calcularFrete()" placeholder="Bairro *" class="col-span-2 bg-white border border-slate-200 text-sm rounded-xl px-3 py-2">
                                </div>
                                <input type="text" x-model="checkout.complemento" placeholder="Complemento / Ponto de referência (Opcional)" class="w-full bg-white border border-slate-200 text-xs rounded-xl px-3 py-2">
                            </div>
                        </template>

                        <!-- Cupom de Desconto -->
                        <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200/80 space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-extrabold uppercase text-slate-600">🏷️ Cupom de Desconto</label>
                                <button type="button" @click="cupomInput = 'BEMVINDO10'; validarCupomApi()" class="text-[11px] font-bold text-rose-600 hover:underline">Usar BEMVINDO10 (10% OFF)</button>
                            </div>
                            <div class="flex gap-2">
                                <input type="text" x-model="cupomInput" placeholder="Digite seu cupom..." class="flex-1 bg-white border border-slate-200 text-xs font-bold uppercase rounded-xl px-3 py-2">
                                <button type="button" @click="validarCupomApi()" :disabled="validandoCupom" class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-extrabold px-4 py-2 rounded-xl shrink-0">
                                    <span x-text="validandoCupom ? '...' : 'Aplicar'"></span>
                                </button>
                            </div>
                            <template x-if="mensagemCupom">
                                <p class="text-[11px] font-bold" :class="cupomAplicado ? 'text-emerald-600' : 'text-rose-600'" x-text="mensagemCupom"></p>
                            </template>
                        </div>

                        <!-- Forma de Pagamento + Troco -->
                        <div class="space-y-2">
                            <label class="text-[11px] font-extrabold uppercase text-slate-500 block">Forma de Pagamento</label>
                            <select x-model="checkout.forma_pagamento" class="w-full bg-slate-100 text-sm rounded-xl px-3.5 py-2.5 font-bold text-slate-800 border border-slate-200">
                                <option value="pix">⚡ PIX Instantâneo (QR Code Mercado Pago)</option>
                                <option value="cartao_maquina">💳 Cartão na Entrega / Presencial</option>
                                <option value="dinheiro">💵 Dinheiro</option>
                            </select>

                            <template x-if="checkout.forma_pagamento === 'dinheiro'">
                                <div class="pt-1">
                                    <label class="text-[11px] font-bold text-slate-600 block mb-1">Precisa de troco para quanto? (Deixe em branco se não precisar)</label>
                                    <input type="number" step="1" x-model="checkout.troco_reais" placeholder="Ex: 100" class="w-full bg-slate-100 text-sm rounded-xl px-3.5 py-2 border border-slate-200">
                                </div>
                            </template>
                        </div>

                        <!-- Resumo Financeiro do Pedido -->
                        <div class="bg-slate-900 text-white p-4 rounded-2xl space-y-1.5 text-xs">
                            <div class="flex justify-between text-slate-300">
                                <span>Subtotal dos Itens</span>
                                <span x-text="formatarCentavos(subtotalCarrinhoCentavos)"></span>
                            </div>
                            <template x-if="checkout.canal === 'delivery' && !mesaAtiva">
                                <div class="flex justify-between text-slate-300">
                                    <span>Taxa de Entrega</span>
                                    <span x-text="freteCentavos === 0 ? 'Grátis' : formatarCentavos(freteCentavos)"></span>
                                </div>
                            </template>
                            <template x-if="descontoCupomCentavos > 0">
                                <div class="flex justify-between text-emerald-400 font-bold">
                                    <span x-text="'Desconto Cupom (' + cupomAplicado.codigo + ')'"></span>
                                    <span x-text="'- ' + formatarCentavos(descontoCupomCentavos)"></span>
                                </div>
                            </template>
                            <div class="flex justify-between items-center pt-2 border-t border-slate-800 text-sm font-black">
                                <span>Total a Pagar</span>
                                <span class="text-base text-emerald-400" x-text="formatarCentavos(totalFinalCentavos)"></span>
                            </div>
                        </div>

                        <button type="button" @click="enviarPedido()" :disabled="enviandoPedido || carrinho.length === 0"
                            class="w-full bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-white font-black py-3.5 rounded-2xl text-sm shadow-lg shadow-rose-600/25 transition">
                            <span x-text="enviandoPedido ? 'Gerando seu pedido...' : 'Confirmar Pedido • ' + formatarCentavos(totalFinalCentavos)"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

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
                        if (savedCart) this.carrinho = JSON.parse(savedCart) || [];

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

                    try {
                        const urlParams = new URLSearchParams(window.location.search);
                        const cupomParam = urlParams.get('cupom');
                        if (cupomParam) {
                            this.cupomInput = cupomParam.trim().toUpperCase();
                            this.$nextTick(() => {
                                this.validarCupomApi();
                            });
                        }
                    } catch (e) {}
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
                    if (nome.includes('smash')) return 'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?auto=format&fit=crop&w=600&q=80';
                    if (nome.includes('costela')) return 'https://images.unsplash.com/photo-1553979459-d2229ba7433b?auto=format&fit=crop&w=600&q=80';
                    if (nome.includes('chicken') || nome.includes('frango')) return 'https://images.unsplash.com/photo-1606755962773-d324e0a13086?auto=format&fit=crop&w=600&q=80';
                    if (nome.includes('napolitana')) return 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?auto=format&fit=crop&w=600&q=80';
                    if (nome.includes('pizza')) return 'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=600&q=80';
                    if (nome.includes('batata') || nome.includes('fritas')) return 'https://images.unsplash.com/photo-1576107232684-1279f3908594?auto=format&fit=crop&w=600&q=80';
                    if (nome.includes('onion') || nome.includes('cebola')) return 'https://images.unsplash.com/photo-1639024471283-03518883512d?auto=format&fit=crop&w=600&q=80';
                    if (nome.includes('coca')) return 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?auto=format&fit=crop&w=600&q=80';
                    if (nome.includes('suco') || nome.includes('laranja')) return 'https://images.unsplash.com/photo-1613478223719-2ab802602423?auto=format&fit=crop&w=600&q=80';
                    if (nome.includes('heineken') || nome.includes('cerveja')) return 'https://images.unsplash.com/photo-1608270586620-248524c67de9?auto=format&fit=crop&w=600&q=80';
                    if (nome.includes('combo')) return 'https://images.unsplash.com/photo-1594212699903-ec8a3eca50f5?auto=format&fit=crop&w=600&q=80';
                    return 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=600&q=80';
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
                    const promos = todos.filter(p => p.preco_promocional_centavos && p.preco_promocional_centavos < p.preco_centavos);
                    return (promos.length >= 3 ? promos : todos).slice(0, 6);
                },

                get sugestoesOrderBump() {
                    const idsNaSacola = new Set(this.carrinho.map(i => i.produto_id));
                    const candidatos = [];
                    for (const cat of this.categorias) {
                        const nomeCat = (cat.nome || '').toLowerCase();
                        const isAcompOuBebida = nomeCat.includes('bebida') || nomeCat.includes('acompanhamento') || nomeCat.includes('porç') || nomeCat.includes('sobremesa');
                        for (const p of (cat.produtos || [])) {
                            if (!idsNaSacola.has(p.id) && (!p.variacoes || p.variacoes.length === 0) && (isAcompOuBebida || p.preco_efetivo_centavos <= 2500)) {
                                candidatos.push(p);
                            }
                        }
                    }
                    return candidatos.slice(0, 4);
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

                adicionarRapidoOrderBump(prod) {
                    const existente = this.carrinho.find(i => i.produto_id === prod.id && !i.variacao_id && (!i.adicionais || i.adicionais.length === 0));
                    if (existente) {
                        existente.qtd = (existente.qtd || 1) + 1;
                        existente.total_centavos = existente.unitario_centavos * existente.qtd;
                    } else {
                        this.carrinho.push({
                            produto_id: prod.id,
                            nome: prod.nome,
                            variacao_id: null,
                            variacao_nome: null,
                            qtd: 1,
                            unitario_centavos: prod.preco_efetivo_centavos,
                            observacao: '',
                            adicionais: [],
                            total_centavos: prod.preco_efetivo_centavos,
                        });
                    }
                    this.salvarEstadoLocal();
                    if (this.checkout.canal === 'delivery' && !this.mesaAtiva) {
                        this.calcularFrete();
                    }
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
                        return 'Escolha o Tamanho';
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
                    if (this.subtotalCarrinhoCentavos < 3000 && this.cupomAplicado.codigo === 'BEMVINDO10') return 0;
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
                    this.mensagemCupom = '✓ Cupom ' + codigo + ' (10% OFF) ativado para pedidos acima de R$ 30!';
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
                            subtotal_centavos: Math.max(3000, this.subtotalCarrinhoCentavos),
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
