<!-- MODAL PRINCIPAL: PDV BALCÃO & LANÇAMENTO DE PEDIDO (LAYOUT POS PROFISSIONAL — ZERO DISTORÇÃO) -->
<div x-cloak x-show="modalPdv"
     @keydown.escape.window="if (pdvItemCustomizando) { fecharCustomizacaoPdv() } else if (modalPdv) { fecharModalPdv() }"
     class="fixed inset-0 z-50 bg-black/85 backdrop-blur-sm flex items-center justify-center p-0 sm:p-3 lg:p-5">
    <div @click.away="if (!pdvItemCustomizando) fecharModalPdv()"
         class="bg-[#0b0e17] border-0 sm:border border-[#1b2234] w-full max-w-6xl rounded-none sm:rounded-3xl shadow-2xl flex flex-col h-full sm:h-[90vh] max-h-full sm:max-h-[860px] overflow-hidden">

        <!-- ==================== HEADER DO PDV ==================== -->
        <div class="px-4 sm:px-6 py-3 bg-[#080b12] border-b border-[#1b2234] flex justify-between items-center shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#f43f5e] to-[#be123c] flex items-center justify-center text-white shadow-lg shadow-rose-600/25">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-sm sm:text-base text-white tracking-tight">Frente de Caixa (PDV)</h3>
                        <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-500/15 text-rose-400 border border-rose-500/25">
                            Lançamento Rápido
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400">Selecione os produtos à esquerda e finalize a comanda à direita</p>
                </div>
            </div>

            <!-- Seletor Rápido de Canal no Topo (Desktop >= sm) + Fechar -->
            <div class="flex items-center gap-2">
                <!-- Toggle Visualização Grade vs Lista Compacta -->
                <div class="hidden md:flex items-center bg-[#121624] border border-[#1c2336] p-0.5 rounded-xl">
                    <button type="button" @click="modoVisualizacaoPdv = 'grade'"
                        title="Visualização em cartões com foto"
                        class="px-2.5 py-1 rounded-lg text-[11px] font-bold flex items-center gap-1 transition"
                        :class="modoVisualizacaoPdv === 'grade' ? 'bg-[#1e2638] text-white shadow-sm' : 'text-slate-400 hover:text-white'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        <span>Fotos</span>
                    </button>
                    <button type="button" @click="modoVisualizacaoPdv = 'lista'"
                        title="Visualização em lista rápida de caixa"
                        class="px-2.5 py-1 rounded-lg text-[11px] font-bold flex items-center gap-1 transition"
                        :class="modoVisualizacaoPdv === 'lista' ? 'bg-[#1e2638] text-white shadow-sm' : 'text-slate-400 hover:text-white'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        <span>Lista</span>
                    </button>
                </div>

                <span class="text-[11px] font-medium text-slate-400 bg-[#121624] border border-[#1c2336] px-2.5 py-1.5 rounded-xl hidden sm:inline">
                    Esc fecha
                </span>
                <button @click="fecharModalPdv()"
                        class="text-slate-400 hover:text-white bg-[#121624] hover:bg-rose-500/20 hover:border-rose-500/40 border border-[#1c2336] w-9 h-9 rounded-xl flex items-center justify-center transition">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>

        <!-- Abas Mobile (< lg): 1. Catálogo vs 2. Comanda & Pagamento -->
        <div class="lg:hidden grid grid-cols-2 gap-1.5 p-2 bg-[#090c14] border-b border-[#1b2234] shrink-0">
            <button type="button" @click="abaPdvMobile = 'produtos'"
                class="py-2 px-3 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition"
                :class="abaPdvMobile === 'produtos' ? 'bg-[#f43f5e] text-white shadow' : 'bg-[#121624] text-slate-400 border border-[#1c2336]'">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                <span>1. Catálogo</span>
            </button>
            <button type="button" @click="abaPdvMobile = 'sacola'"
                class="py-2 px-3 rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 transition"
                :class="abaPdvMobile === 'sacola' ? 'bg-[#10b981] text-slate-950 shadow' : 'bg-[#121624] text-slate-300 border border-[#1c2336]'">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                <span>2. Comanda</span>
                <span x-show="pdv.itens.length > 0" class="px-1.5 py-0.5 rounded-full text-[10px] font-extrabold bg-black/25 text-white" x-text="pdv.itens.reduce((a, b) => a + b.qtd, 0)"></span>
            </button>
        </div>

        <!-- ==================== CORPO EM 2 COLUNAS (ALTURA FIXA COM SCROLL INDEPENDENTE) ==================== -->
        <div class="flex-1 min-h-0 flex flex-col lg:flex-row divide-y lg:divide-y-0 lg:divide-x divide-[#1b2234] text-xs overflow-hidden">

            <!-- ==================== COLUNA DA ESQUERDA: CATÁLOGO DE PRODUTOS ==================== -->
            <div :class="abaPdvMobile === 'produtos' ? 'flex' : 'hidden lg:flex'"
                 class="w-full lg:flex-1 min-w-0 h-full flex-col bg-[#0b0e17] overflow-hidden">

                <!-- Topo Fixo do Catálogo: Busca + Categorias -->
                <div class="p-3.5 sm:px-5 sm:pt-4 sm:pb-3 space-y-3 border-b border-[#151b2b] shrink-0">
                    <!-- Barra de Busca -->
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" x-model="buscaPdvProduto" placeholder="Buscar produto por nome ou descrição..."
                            class="w-full bg-[#121624] border border-[#1c2336] rounded-xl pl-10 pr-28 py-2 text-xs text-white placeholder-slate-500 focus:border-[#f43f5e] focus:outline-none transition">
                        <div class="absolute right-2 top-1.5 flex items-center gap-1">
                            <button x-show="buscaPdvProduto" @click="buscaPdvProduto = ''" type="button"
                                    class="text-slate-400 hover:text-white px-1.5 py-0.5 text-[11px]">Limpar</button>
                            <span class="text-[10px] font-bold text-slate-400 bg-[#192033] px-2 py-1 rounded-lg"
                                  x-text="produtosPdvFiltrados.length + (produtosPdvFiltrados.length === 1 ? ' item' : ' itens')"></span>
                        </div>
                    </div>

                    <!-- Pílulas de Categorias -->
                    <div class="flex overflow-x-auto no-scrollbar gap-1.5">
                        <button type="button" @click="filtroPdvCategoria = ''"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition shrink-0"
                            :class="filtroPdvCategoria === '' ? 'bg-[#f43f5e] text-white shadow-sm' : 'bg-[#121624] text-slate-400 border border-[#1c2336] hover:text-white'">
                            Todas
                        </button>
                        <template x-for="cat in categorias" :key="cat.id">
                            <button type="button" @click="filtroPdvCategoria = cat.id"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition shrink-0"
                                :class="Number(filtroPdvCategoria) === Number(cat.id) ? 'bg-[#f43f5e] text-white shadow-sm' : 'bg-[#121624] text-slate-400 border border-[#1c2336] hover:text-white'"
                                x-text="cat.nome">
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Área Rolável de Produtos (SEM ESTICAR CARDS!) -->
                <div class="flex-1 overflow-y-auto custom-scrollbar p-3.5 sm:p-5">

                    <!-- Estado Vazio se nenhum produto encontrado -->
                    <template x-if="produtosPdvFiltrados.length === 0">
                        <div class="h-48 flex flex-col items-center justify-center text-center text-slate-500 space-y-2">
                            <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            <p class="text-xs font-semibold text-slate-400">Nenhum produto encontrado para este filtro.</p>
                        </div>
                    </template>

                    <!-- MODO 1: GRADE PROPORCIONAL (3 Colunas no Desktop, Altura Natural Fixa) -->
                    <div x-show="modoVisualizacaoPdv === 'grade' && produtosPdvFiltrados.length > 0"
                         class="grid grid-cols-2 sm:grid-cols-3 gap-3 content-start items-start">
                        <template x-for="p in produtosPdvFiltrados" :key="p.id">
                            <div @click="clicarProdutoPdv(p)"
                                 class="bg-[#121624] hover:bg-[#161c2e] border rounded-2xl p-2.5 cursor-pointer transition flex flex-col group relative shadow-sm select-none"
                                 :class="qtdProdutoNaSacolaPdv(p.id) > 0 ? 'border-[#f43f5e]/70 ring-1 ring-[#f43f5e]/30' : 'border-[#1c2336] hover:border-slate-700'">

                                <!-- Badge de Quantidade se já adicionado -->
                                <template x-if="qtdProdutoNaSacolaPdv(p.id) > 0">
                                    <span class="absolute top-4 right-4 bg-[#f43f5e] text-white text-[11px] font-extrabold px-2 py-0.5 rounded-lg shadow-lg z-10"
                                          x-text="qtdProdutoNaSacolaPdv(p.id) + 'x'"></span>
                                </template>

                                <!-- Miniatura 16:10 Compacta (Não deforma o card) -->
                                <div class="w-full h-28 sm:h-32 rounded-xl overflow-hidden flex items-center justify-center relative mb-2.5 shrink-0"
                                     :class="p.imagem ? 'bg-[#182033]' : ((p.nome.toUpperCase().charCodeAt(0) % 2 === 0) ? 'bg-[#261622]' : 'bg-[#292212]')">
                                    <template x-if="p.imagem">
                                        <img :src="p.imagem.startsWith('http') || p.imagem.startsWith('/') ? p.imagem : '/storage/' + p.imagem"
                                             :alt="p.nome"
                                             class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    </template>
                                    <template x-if="!p.imagem">
                                        <span class="text-3xl font-bold select-none tracking-tighter"
                                              :class="(p.nome.toUpperCase().charCodeAt(0) % 2 === 0) ? 'text-[#f43f5e]' : 'text-[#fbbf24]'"
                                              x-text="p.nome.trim().charAt(0).toUpperCase()"></span>
                                    </template>

                                    <!-- Tag se possui variações/opções -->
                                    <template x-if="(p.variacoes && p.variacoes.length > 0) || (p.grupos_adicionais && p.grupos_adicionais.length > 0)">
                                        <span class="absolute bottom-1.5 left-1.5 bg-black/75 backdrop-blur-sm text-slate-200 text-[9px] font-bold px-1.5 py-0.5 rounded-md border border-white/10">
                                            Opções
                                        </span>
                                    </template>
                                </div>

                                <!-- Nome + Descrição curta (altura controlada) -->
                                <div class="min-h-[34px] mb-2">
                                    <div class="font-bold text-white text-xs line-clamp-2 leading-snug group-hover:text-rose-300 transition"
                                         x-text="p.nome"></div>
                                </div>

                                <!-- Rodapé do Card: Preço + Botão Rápido -->
                                <div class="flex items-center justify-between pt-2 border-t border-[#1c2336]/80 mt-auto">
                                    <span class="font-bold text-[#10b981] text-xs sm:text-sm font-mono"
                                          x-text="formatarCentavos(p.preco_efetivo_centavos || p.preco_centavos)"></span>

                                    <!-- Se já está na sacola e é item simples, mostra - / + direto no card -->
                                    <template x-if="qtdProdutoNaSacolaPdv(p.id) > 0 && (!p.variacoes || p.variacoes.length === 0) && (!p.grupos_adicionais || p.grupos_adicionais.length === 0)">
                                        <div @click.stop class="flex items-center bg-[#0b0e17] border border-[#f43f5e]/50 rounded-xl px-1 py-0.5 gap-1.5">
                                            <button type="button" @click="decrementarProdutoSimplesPdv(p)"
                                                    class="w-5 h-5 rounded-lg bg-[#141926] hover:bg-rose-500/20 text-slate-300 hover:text-white font-bold text-xs flex items-center justify-center">−</button>
                                            <span class="font-bold text-white text-[11px] font-mono px-0.5" x-text="qtdProdutoNaSacolaPdv(p.id)"></span>
                                            <button type="button" @click="clicarProdutoPdv(p)"
                                                    class="w-5 h-5 rounded-lg bg-[#f43f5e] hover:bg-[#e11d48] text-white font-bold text-xs flex items-center justify-center">+</button>
                                        </div>
                                    </template>

                                    <template x-if="qtdProdutoNaSacolaPdv(p.id) === 0 || (p.variacoes && p.variacoes.length > 0) || (p.grupos_adicionais && p.grupos_adicionais.length > 0)">
                                        <span class="w-7 h-7 rounded-xl bg-[#f43f5e] group-hover:bg-[#e11d48] text-white font-bold text-sm flex items-center justify-center shadow-md shadow-rose-600/20 transition active:scale-90">
                                            +
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- MODO 2: LISTA COMPACTA DE CAIXA (Para Lojas com Muitos Produtos) -->
                    <div x-show="modoVisualizacaoPdv === 'lista' && produtosPdvFiltrados.length > 0"
                         class="space-y-1.5">
                        <template x-for="p in produtosPdvFiltrados" :key="'list-' + p.id">
                            <div @click="clicarProdutoPdv(p)"
                                 class="bg-[#121624] hover:bg-[#161c2e] border rounded-xl p-2.5 cursor-pointer transition flex items-center justify-between gap-3 select-none"
                                 :class="qtdProdutoNaSacolaPdv(p.id) > 0 ? 'border-[#f43f5e]/60' : 'border-[#1c2336]'">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-lg overflow-hidden shrink-0 flex items-center justify-center bg-[#182033]">
                                        <template x-if="p.imagem">
                                            <img :src="p.imagem.startsWith('http') || p.imagem.startsWith('/') ? p.imagem : '/storage/' + p.imagem" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!p.imagem">
                                            <span class="text-sm font-bold text-rose-400" x-text="p.nome.trim().charAt(0).toUpperCase()"></span>
                                        </template>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-white text-xs truncate" x-text="p.nome"></div>
                                        <div class="text-[11px] text-slate-400 truncate" x-text="p.descricao || 'Toque para adicionar à comanda'"></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2.5 shrink-0">
                                    <span class="font-bold text-[#10b981] text-xs font-mono"
                                          x-text="formatarCentavos(p.preco_efetivo_centavos || p.preco_centavos)"></span>
                                    <template x-if="qtdProdutoNaSacolaPdv(p.id) > 0">
                                        <span class="bg-[#f43f5e] text-white text-[10px] font-bold px-2 py-0.5 rounded-md"
                                              x-text="qtdProdutoNaSacolaPdv(p.id) + 'x'"></span>
                                    </template>
                                    <span class="w-7 h-7 rounded-xl bg-[#192033] hover:bg-[#f43f5e] text-white font-bold text-sm flex items-center justify-center transition">+</span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Barra Flutuante Mobile para Avançar para a Comanda -->
                <div x-show="pdv.itens.length > 0" class="lg:hidden p-3 border-t border-[#1b2234] bg-[#080b12] shrink-0">
                    <button type="button" @click="abaPdvMobile = 'sacola'"
                        class="w-full bg-[#10b981] hover:bg-emerald-400 text-slate-950 font-bold py-3 px-4 rounded-2xl text-xs shadow-lg flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <span class="bg-slate-950/20 px-2 py-0.5 rounded-lg font-extrabold" x-text="pdv.itens.reduce((a, b) => a + b.qtd, 0) + ' it.'"></span>
                            <span>Revisar comanda e finalizar</span>
                        </span>
                        <span class="font-extrabold text-sm" x-text="formatarCentavos(calcularTotalPdvCentavos()) + ' →'"></span>
                    </button>
                </div>
            </div>

            <!-- ==================== COLUNA DA DIREITA: COMANDA ESTRUTURADA (TOPO + ITENS + RODAPÉ FIXO) ==================== -->
            <div :class="abaPdvMobile === 'sacola' ? 'flex' : 'hidden lg:flex'"
                 class="w-full lg:w-[430px] xl:w-[460px] shrink-0 h-full flex-col bg-[#080b12] overflow-hidden">

                <!-- 1. TOPO DA COMANDA: CANAL + CLIENTE / MESA / ENDEREÇO -->
                <div class="p-3.5 sm:px-5 sm:py-3.5 border-b border-[#181f30] space-y-2.5 shrink-0">
                    <!-- Seletor de Canal em 4 Pílulas -->
                    <div class="grid grid-cols-4 gap-1.5 bg-[#0e131f] p-1 rounded-xl border border-[#1a2234]">
                        <button type="button" @click="pdv.canal = 'balcao'"
                            class="py-1.5 px-1 rounded-lg font-bold text-[11px] transition flex items-center justify-center gap-1"
                            :class="pdv.canal === 'balcao' ? 'bg-[#f43f5e] text-white shadow' : 'text-slate-400 hover:text-white'">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            <span>Balcão</span>
                        </button>
                        <button type="button" @click="pdv.canal = 'retirada'"
                            class="py-1.5 px-1 rounded-lg font-bold text-[11px] transition flex items-center justify-center gap-1"
                            :class="pdv.canal === 'retirada' ? 'bg-[#f43f5e] text-white shadow' : 'text-slate-400 hover:text-white'">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            <span>Retirada</span>
                        </button>
                        <button type="button" @click="pdv.canal = 'delivery'"
                            class="py-1.5 px-1 rounded-lg font-bold text-[11px] transition flex items-center justify-center gap-1"
                            :class="pdv.canal === 'delivery' ? 'bg-[#f43f5e] text-white shadow' : 'text-slate-400 hover:text-white'">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                            <span>Delivery</span>
                        </button>
                        <button type="button" @click="pdv.canal = 'mesa'"
                            class="py-1.5 px-1 rounded-lg font-bold text-[11px] transition flex items-center justify-center gap-1"
                            :class="pdv.canal === 'mesa' ? 'bg-[#f43f5e] text-white shadow' : 'text-slate-400 hover:text-white'">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            <span>Mesa</span>
                        </button>
                    </div>

                    <!-- Identificação Rápida do Cliente -->
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" x-model="pdv.cliente_nome" placeholder="Cliente (opcional)"
                            class="w-full bg-[#121624] border border-[#1c2336] rounded-xl px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:border-[#f43f5e] focus:outline-none">
                        <input type="text" x-model="pdv.cliente_telefone" placeholder="WhatsApp com DDD"
                            class="w-full bg-[#121624] border border-[#1c2336] rounded-xl px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:border-[#f43f5e] focus:outline-none">
                    </div>

                    <!-- Bloco Condicional: Endereço + Bairro com Taxa Automática (Apenas quando Delivery) -->
                    <template x-if="pdv.canal === 'delivery'">
                        <div class="bg-[#111624] border border-[#1e273d] rounded-xl p-2.5 space-y-2">
                            <div class="grid grid-cols-12 gap-1.5">
                                <input type="text" x-model="pdv.endereco.rua" placeholder="Rua / Avenida *"
                                    class="col-span-7 bg-[#0b0e17] border border-[#1c2336] rounded-lg px-2.5 py-1.5 text-white placeholder-slate-500 text-xs focus:border-[#f43f5e] focus:outline-none">
                                <input type="text" x-model="pdv.endereco.numero" placeholder="Nº *"
                                    class="col-span-2 bg-[#0b0e17] border border-[#1c2336] rounded-lg px-2 py-1.5 text-white placeholder-slate-500 text-xs focus:border-[#f43f5e] focus:outline-none">
                                <input type="text" x-model="pdv.endereco.complemento" placeholder="Apto/Ref"
                                    class="col-span-3 bg-[#0b0e17] border border-[#1c2336] rounded-lg px-2 py-1.5 text-white placeholder-slate-500 text-xs focus:border-[#f43f5e] focus:outline-none">
                            </div>
                            <div class="grid grid-cols-12 gap-1.5 items-center">
                                <!-- Se a loja tem bairros cadastrados, permite escolher ou digitar -->
                                <div class="col-span-7">
                                    <template x-if="areasEntrega && areasEntrega.filter(a => a.nome_bairro).length > 0">
                                        <select @change="selecionarBairroPdv($event.target.value)"
                                                class="w-full bg-[#0b0e17] border border-[#1c2336] rounded-lg px-2.5 py-1.5 text-white text-xs focus:border-[#f43f5e] focus:outline-none">
                                            <option value="">Selecionar bairro cadastrado...</option>
                                            <template x-for="area in areasEntrega.filter(a => a.nome_bairro)" :key="area.id">
                                                <option :value="area.id" x-text="area.nome_bairro + ' (' + formatarCentavos(area.taxa_centavos) + ')'"></option>
                                            </template>
                                        </select>
                                    </template>
                                    <template x-if="!areasEntrega || areasEntrega.filter(a => a.nome_bairro).length === 0">
                                        <input type="text" x-model="pdv.endereco.bairro" placeholder="Bairro *"
                                            class="w-full bg-[#0b0e17] border border-[#1c2336] rounded-lg px-2.5 py-1.5 text-white placeholder-slate-500 text-xs focus:border-[#f43f5e] focus:outline-none">
                                    </template>
                                </div>
                                <div class="col-span-5 flex items-center bg-[#0b0e17] border border-[#1c2336] rounded-lg px-2.5 py-1.5">
                                    <span class="text-[10px] text-slate-400 font-bold mr-1">Frete R$</span>
                                    <input type="text" x-model="pdv.taxa_entrega_reais" placeholder="0,00"
                                        class="w-full bg-transparent text-right text-emerald-400 font-bold text-xs focus:outline-none font-mono">
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Bloco Condicional: Seleção de Mesa (Apenas quando Mesa) -->
                    <template x-if="pdv.canal === 'mesa'">
                        <div class="bg-[#111624] border border-[#1e273d] rounded-xl p-2.5 flex items-center justify-between gap-2">
                            <span class="text-[11px] font-bold text-slate-300 shrink-0">Mesa do Salão:</span>
                            <select x-model="pdv.mesa_id" class="flex-1 bg-[#0b0e17] border border-[#1c2336] rounded-lg px-2.5 py-1.5 text-white font-bold text-xs focus:border-[#f43f5e] focus:outline-none">
                                <option value="">Selecione a mesa...</option>
                                <template x-for="m in mesas" :key="m.id">
                                    <option :value="m.id" x-text="'Mesa #' + m.numero + ' (' + m.status + ')'"></option>
                                </template>
                            </select>
                        </div>
                    </template>
                </div>

                <!-- 2. CENTRO ROLÁVEL: LISTA DE ITENS DA COMANDA -->
                <div class="flex-1 min-h-0 flex flex-col overflow-hidden px-3.5 sm:px-5 py-2.5">
                    <div class="flex items-center justify-between pb-2 shrink-0">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                              x-text="'ITENS DA COMANDA (' + pdv.itens.reduce((a, b) => a + b.qtd, 0) + ')'"></span>
                        <button x-show="pdv.itens.length > 0" @click="pdv.itens = []"
                                class="text-[11px] text-rose-400 hover:text-rose-300 font-bold">
                            Limpar tudo
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto custom-scrollbar space-y-1.5 pr-1">
                        <template x-if="pdv.itens.length === 0">
                            <div class="h-full min-h-[110px] flex flex-col items-center justify-center text-center text-slate-500 bg-[#0e131f]/70 border border-dashed border-[#1c2336] rounded-2xl p-4">
                                <svg class="w-6 h-6 text-slate-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                <p class="text-xs font-semibold text-slate-400">Comanda vazia</p>
                                <p class="text-[11px] text-slate-500">Clique nos produtos ao lado para adicionar</p>
                            </div>
                        </template>

                        <template x-for="(it, idx) in pdv.itens" :key="idx">
                            <div class="bg-[#121624] border border-[#1c2336] rounded-xl px-3 py-2 flex items-center justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-rose-400 text-xs font-mono" x-text="it.qtd + 'x'"></span>
                                        <span class="font-bold text-white text-xs truncate" x-text="it.nome"></span>
                                    </div>
                                    <template x-if="it.variacao_nome">
                                        <div class="text-[10px] text-amber-300 font-medium truncate" x-text="'Opção: ' + it.variacao_nome"></div>
                                    </template>
                                    <template x-if="it.adicionais && it.adicionais.length > 0">
                                        <div class="text-[10px] text-slate-400 truncate" x-text="'+ ' + it.adicionais.map(a => a.nome).join(', ')"></div>
                                    </template>
                                    <template x-if="it.observacao">
                                        <div class="text-[10px] text-sky-400 italic truncate" x-text="'Obs: ' + it.observacao"></div>
                                    </template>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="font-bold text-emerald-400 text-xs font-mono" x-text="formatarCentavos(it.total_centavos)"></span>

                                    <div class="flex items-center bg-[#0b0e17] border border-[#1c2336] rounded-lg px-1 py-0.5 gap-1">
                                        <button type="button" @click="alterarQtdItemPdv(idx, -1)" class="w-5 h-5 text-slate-400 hover:text-white font-bold text-xs flex items-center justify-center">−</button>
                                        <span class="font-bold text-white text-[11px] font-mono px-0.5" x-text="it.qtd"></span>
                                        <button type="button" @click="alterarQtdItemPdv(idx, 1)" class="w-5 h-5 text-slate-400 hover:text-white font-bold text-xs flex items-center justify-center">+</button>
                                    </div>

                                    <button type="button" @click="removerItemPdv(idx)" class="text-slate-500 hover:text-rose-400 p-1 rounded-lg transition" title="Remover">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 3. RODAPÉ FIXO DA COMANDA: PAGAMENTO, AJUSTES & LANÇAR PEDIDO -->
                <div class="p-3.5 sm:px-5 sm:py-3.5 bg-[#0b0e17] border-t border-[#181f30] space-y-2.5 shrink-0">

                    <!-- Forma de Pagamento -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">PAGAMENTO</span>
                            <!-- Toggle compacto para Desconto / Obs / Destino -->
                            <button type="button" @click="mostrarOpcoesExtrasPdv = !mostrarOpcoesExtrasPdv"
                                    class="text-[11px] font-bold text-slate-400 hover:text-white flex items-center gap-1">
                                <span x-text="mostrarOpcoesExtrasPdv ? 'Ocultar ajustes' : '+ Desconto / Obs'"></span>
                                <span x-show="centavos(pdv.desconto_reais || '0') > 0 || pdv.observacao" class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                            </button>
                        </div>

                        <div class="grid grid-cols-4 gap-1.5">
                            <button type="button" @click="pdv.forma_pagamento = 'dinheiro'"
                                class="py-2 px-1 rounded-xl font-bold text-xs transition text-center"
                                :class="pdv.forma_pagamento === 'dinheiro' ? 'bg-[#1c2336] border border-[#f43f5e] text-white shadow-sm' : 'bg-[#121624] text-slate-400 border border-[#1c2336] hover:text-white'">
                                Dinheiro
                            </button>
                            <button type="button" @click="pdv.forma_pagamento = 'pix'"
                                class="py-2 px-1 rounded-xl font-bold text-xs transition text-center"
                                :class="pdv.forma_pagamento === 'pix' ? 'bg-[#1c2336] border border-[#f43f5e] text-white shadow-sm' : 'bg-[#121624] text-slate-400 border border-[#1c2336] hover:text-white'">
                                PIX
                            </button>
                            <button type="button" @click="pdv.forma_pagamento = 'cartao_maquina'"
                                class="py-2 px-1 rounded-xl font-bold text-xs transition text-center"
                                :class="pdv.forma_pagamento === 'cartao_maquina' ? 'bg-[#1c2336] border border-[#f43f5e] text-white shadow-sm' : 'bg-[#121624] text-slate-400 border border-[#1c2336] hover:text-white'">
                                Cartão
                            </button>
                            <button type="button" @click="pdv.forma_pagamento = 'voucher'"
                                class="py-2 px-1 rounded-xl font-bold text-xs transition text-center"
                                :class="pdv.forma_pagamento === 'voucher' ? 'bg-[#1c2336] border border-[#f43f5e] text-white shadow-sm' : 'bg-[#121624] text-slate-400 border border-[#1c2336] hover:text-white'">
                                Vale
                            </button>
                        </div>

                        <!-- Troco Inteligente com Cédulas Rápidas (Apenas se Dinheiro) -->
                        <template x-if="pdv.forma_pagamento === 'dinheiro'">
                            <div class="bg-[#121624] border border-[#1c2336] rounded-xl p-2 space-y-1.5">
                                <div class="flex items-center justify-between gap-2">
                                    <input type="text" x-model="pdv.troco_para_reais" placeholder="Troco p/ quanto? (ex: 50,00)"
                                        class="flex-1 bg-[#0b0e17] border border-[#1c2336] rounded-lg px-2.5 py-1.5 text-white font-bold placeholder-slate-500 text-xs focus:border-[#f43f5e] focus:outline-none">
                                    <div class="text-right shrink-0 px-1">
                                        <span class="block text-[9px] font-bold uppercase text-slate-400">TROCO</span>
                                        <span class="font-bold text-xs text-emerald-400 font-mono" x-text="formatarCentavos(calcularTrocoPdvCentavos())"></span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="definirTrocoRapidoPdv(0)" class="flex-1 bg-[#0b0e17] hover:bg-[#192033] text-slate-300 border border-[#1c2336] rounded-lg py-1 text-[10px] font-bold">Exato</button>
                                    <button type="button" @click="definirTrocoRapidoPdv(20)" class="flex-1 bg-[#0b0e17] hover:bg-[#192033] text-slate-300 border border-[#1c2336] rounded-lg py-1 text-[10px] font-bold">R$ 20</button>
                                    <button type="button" @click="definirTrocoRapidoPdv(50)" class="flex-1 bg-[#0b0e17] hover:bg-[#192033] text-slate-300 border border-[#1c2336] rounded-lg py-1 text-[10px] font-bold">R$ 50</button>
                                    <button type="button" @click="definirTrocoRapidoPdv(100)" class="flex-1 bg-[#0b0e17] hover:bg-[#192033] text-slate-300 border border-[#1c2336] rounded-lg py-1 text-[10px] font-bold">R$ 100</button>
                                    <button type="button" @click="definirTrocoRapidoPdv(200)" class="flex-1 bg-[#0b0e17] hover:bg-[#192033] text-slate-300 border border-[#1c2336] rounded-lg py-1 text-[10px] font-bold">R$ 200</button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Painel Recolhível: Desconto, Observação e Destino (Zero Poluição) -->
                    <div x-show="mostrarOpcoesExtrasPdv" class="bg-[#121624] border border-[#1c2336] rounded-xl p-2.5 space-y-2">
                        <div class="grid grid-cols-12 gap-2 items-center">
                            <div class="col-span-5 flex items-center bg-[#0b0e17] border border-[#1c2336] rounded-lg px-2.5 py-1.5">
                                <span class="text-[10px] text-slate-400 font-bold mr-1">Desc. R$</span>
                                <input type="text" x-model="pdv.desconto_reais" placeholder="0,00"
                                    class="w-full bg-transparent text-right text-emerald-400 font-bold text-xs focus:outline-none font-mono">
                            </div>
                            <div class="col-span-7">
                                <input type="text" x-model="pdv.observacao" placeholder="Obs. geral do pedido..."
                                    class="w-full bg-[#0b0e17] border border-[#1c2336] rounded-lg px-2.5 py-1.5 text-white placeholder-slate-500 text-xs focus:border-[#f43f5e] focus:outline-none">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-1.5">
                            <button type="button" @click="pdv.status_inicial = 'aceito'"
                                class="py-1.5 px-2 rounded-lg font-bold border text-[11px] transition text-center"
                                :class="pdv.status_inicial === 'aceito' ? 'bg-[#291f11] border-[#f59e0b] text-[#f59e0b]' : 'bg-[#0b0e17] border-[#1c2336] text-slate-400'">
                                Direto para a cozinha
                            </button>
                            <button type="button" @click="pdv.status_inicial = 'novo'"
                                class="py-1.5 px-2 rounded-lg font-bold border text-[11px] transition text-center"
                                :class="pdv.status_inicial === 'novo' ? 'bg-[#2a131e] border-[#f43f5e] text-[#f43f5e]' : 'bg-[#0b0e17] border-[#1c2336] text-slate-400'">
                                Aguardar aceite
                            </button>
                        </div>
                    </div>

                    <!-- Resumo de Valores Compacto + Botão Lançar Pedido -->
                    <div class="bg-[#121624] border border-[#1c2336] rounded-xl px-3.5 py-2.5 flex items-center justify-between">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2 text-[11px] text-slate-400">
                                <span>Sub: <strong class="text-slate-200 font-mono" x-text="formatarCentavos(calcularSubtotalPdvCentavos())"></strong></span>
                                <template x-if="pdv.canal === 'delivery' && centavos(pdv.taxa_entrega_reais || '0') > 0">
                                    <span>• Frete: <strong class="text-emerald-400 font-mono" x-text="formatarCentavos(centavos(pdv.taxa_entrega_reais))"></strong></span>
                                </template>
                                <template x-if="centavos(pdv.desconto_reais || '0') > 0">
                                    <span>• Desc: <strong class="text-rose-400 font-mono" x-text="'-' + formatarCentavos(centavos(pdv.desconto_reais))"></strong></span>
                                </template>
                            </div>
                            <div class="text-[10px] text-amber-400 font-bold"
                                 x-text="pdv.status_inicial === 'aceito' ? '⚡ Entra direto em preparo na cozinha' : '🔔 Entra na fila aguardando aceite'"></div>
                        </div>
                        <div class="text-right">
                            <span class="block text-[10px] uppercase font-bold text-slate-400">Total</span>
                            <span class="font-bold text-[#10b981] text-xl font-mono" x-text="formatarCentavos(calcularTotalPdvCentavos())"></span>
                        </div>
                    </div>

                    <button @click="enviarPedidoPdv()" :disabled="pdv.itens.length === 0 || salvandoPdv"
                        class="w-full bg-gradient-to-r from-[#e11d48] to-[#f43f5e] hover:from-[#be123c] hover:to-[#e11d48] disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold py-3.5 rounded-2xl text-sm shadow-xl shadow-rose-600/25 active:scale-[0.99] transition flex items-center justify-center gap-2">
                        <span x-show="!salvandoPdv" class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            <span>Lançar pedido · </span>
                            <span class="font-mono" x-text="formatarCentavos(calcularTotalPdvCentavos())"></span>
                        </span>
                        <span x-show="salvandoPdv" class="flex items-center gap-2">
                            <span class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span>Gravando pedido...</span>
                        </span>
                    </button>
                </div>

            </div>

        </div>

    </div>

    <!-- SUB-MODAL: PERSONALIZAÇÃO DE PRODUTO (VARIAÇÕES & ADICIONAIS) -->
    <template x-if="pdvItemCustomizando">
        <div class="fixed inset-0 z-60 bg-black/85 backdrop-blur-sm flex items-center justify-center p-3">
            <div @click.away="fecharCustomizacaoPdv()"
                 class="bg-[#0b0e17] border border-[#1b2234] w-full max-w-lg rounded-3xl p-5 space-y-4 max-h-[90vh] overflow-y-auto shadow-2xl">

                <div class="flex justify-between items-start border-b border-[#1b2234] pb-3">
                    <div>
                        <h4 class="font-bold text-white text-base" x-text="pdvItemCustomizando.nome"></h4>
                        <p class="text-xs text-slate-400" x-text="pdvItemCustomizando.descricao || 'Personalize os adicionais e opções'"></p>
                    </div>
                    <button @click="fecharCustomizacaoPdv()" class="text-slate-400 hover:text-white font-bold text-lg p-1">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Variações de Tamanho -->
                <template x-if="pdvItemCustomizando.variacoes && pdvItemCustomizando.variacoes.length > 0">
                    <div class="space-y-2">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">Escolha a Opção / Tamanho *</label>
                        <div class="space-y-1.5">
                            <template x-for="v in pdvItemCustomizando.variacoes" :key="v.id">
                                <label class="flex justify-between items-center p-3 rounded-xl border cursor-pointer transition"
                                       :class="pdvVariacaoSelecionadaId === v.id ? 'bg-rose-500/10 border-[#f43f5e] text-white' : 'bg-[#121624] border-[#1c2336] text-slate-300 hover:border-slate-600'">
                                    <div class="flex items-center gap-2.5">
                                        <input type="radio" :value="v.id" x-model="pdvVariacaoSelecionadaId" class="text-rose-600 focus:ring-rose-500">
                                        <span class="font-bold text-xs" x-text="v.nome"></span>
                                    </div>
                                    <span class="font-semibold text-[#10b981] text-xs font-mono" x-text="formatarCentavos(v.preco_centavos)"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Grupos de Adicionais -->
                <template x-if="pdvItemCustomizando.grupos_adicionais && pdvItemCustomizando.grupos_adicionais.length > 0">
                    <div class="space-y-3">
                        <template x-for="g in pdvItemCustomizando.grupos_adicionais" :key="g.id">
                            <div class="bg-[#121624] border border-[#1c2336] rounded-2xl p-3.5 space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-white text-xs" x-text="g.nome"></span>
                                    <span class="text-[10px] text-slate-400" x-text="'Máx: ' + (g.maximo || 1)"></span>
                                </div>
                                <div class="space-y-1.5">
                                    <template x-for="op in g.opcoes" :key="op.id">
                                        <label class="flex justify-between items-center p-2 rounded-xl bg-[#0b0e17] hover:bg-[#151a2a] border border-[#1c2336] cursor-pointer">
                                            <div class="flex items-center gap-2">
                                                <input type="checkbox" :checked="isAdicionalSelecionadoPdv(op.id)" @change="alternarAdicionalPdv(op, g)"
                                                    class="rounded text-rose-600 focus:ring-rose-500">
                                                <span class="text-xs text-slate-200" x-text="op.nome"></span>
                                            </div>
                                            <span class="text-xs font-bold text-[#10b981] font-mono" x-text="op.preco_centavos > 0 ? '+ ' + formatarCentavos(op.preco_centavos) : 'Grátis'"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Quantidade e Observações do Item -->
                <div class="space-y-2 pt-2 border-t border-[#1b2234]">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-300">Quantidade do Item:</span>
                        <div class="flex items-center gap-2 bg-[#121624] px-3 py-1 rounded-xl border border-[#1c2336]">
                            <button type="button" @click="pdvQtdItem = Math.max(1, pdvQtdItem - 1)" class="font-bold text-slate-400 hover:text-white px-1">−</button>
                            <span class="font-bold text-white text-sm px-2 font-mono" x-text="pdvQtdItem"></span>
                            <button type="button" @click="pdvQtdItem++" class="font-bold text-slate-400 hover:text-white px-1">+</button>
                        </div>
                    </div>
                    <input type="text" x-model="pdvObservacaoItem" placeholder="Observação deste item (ex: Sem cebola)..."
                        class="w-full bg-[#121624] border border-[#1c2336] rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:border-[#f43f5e] focus:outline-none">
                </div>

                <!-- Botão Confirmar Adição do Item -->
                <div class="flex gap-2 pt-2">
                    <button type="button" @click="fecharCustomizacaoPdv()"
                        class="w-1/3 bg-[#121624] hover:bg-[#1a2034] text-slate-300 border border-[#1c2336] font-bold py-2.5 rounded-xl text-xs transition">
                        Cancelar
                    </button>
                    <button type="button" @click="confirmarItemCustomizadoPdv()"
                        class="w-2/3 bg-[#e11d48] hover:bg-[#be123c] text-white font-semibold py-2.5 rounded-xl text-xs shadow-lg shadow-rose-600/30 transition">
                        Inserir no Pedido
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
