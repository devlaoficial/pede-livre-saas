        <div x-cloak x-show="moduloAtivo === 'catalogo'" class="flex-1 overflow-y-auto p-3.5 sm:p-5 lg:p-6 pb-24 space-y-4 sm:space-y-5">
            
            <!-- Cabeçalho Principal + Link Direto do Cardápio + Ações Rápidas -->
            <div class="bg-[#0f1322] border border-[#1d253b] p-4 sm:p-5 rounded-2xl sm:rounded-3xl shadow-lg space-y-4">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3.5">
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-bold text-base sm:text-lg text-white tracking-tight">Cardápio Digital, Adicionais &amp; Cupons</h2>
                            <a :href="'/p/' + (loja?.slug || '')" target="_blank"
                               class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-300 border border-emerald-500/30 transition">
                                <span>Ver Cardápio Online</span>
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                        </div>
                        <p class="text-xs text-slate-400">Gerencie categorias, produtos com foto WebP, grupos de complementos e promoções em tempo real.</p>
                    </div>

                    <!-- Botões de Ação Principal (2x2 no Celular, Linha no Desktop) -->
                    <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2">
                        <button @click="abrirModalProduto()"
                            class="col-span-2 sm:col-span-1 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-lg shadow-rose-600/25 flex items-center justify-center gap-1.5 transition active:scale-95">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                            <span>Novo Produto</span>
                        </button>
                        <button @click="abrirModalCategoria()"
                            class="bg-[#161c2e] hover:bg-[#1e263d] text-slate-200 text-xs font-bold px-3.5 py-2.5 rounded-xl border border-[#26314d] flex items-center justify-center gap-1.5 transition">
                            <svg class="w-3.5 h-3.5 shrink-0 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                            <span>+ Categoria</span>
                        </button>
                        <button @click="abrirModalGrupoAdicional()"
                            class="bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 text-xs font-bold px-3.5 py-2.5 rounded-xl border border-amber-500/30 flex items-center justify-center gap-1.5 transition">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            <span>+ Adicionais</span>
                        </button>
                        <button @click="modalNovoCupom = true"
                            class="col-span-2 sm:col-span-1 bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-300 text-xs font-bold px-3.5 py-2.5 rounded-xl border border-emerald-500/30 flex items-center justify-center gap-1.5 transition">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                            <span>+ Cupom</span>
                        </button>
                    </div>
                </div>

                <!-- 4 Indicadores Rápidos do Cardápio (2x2 no Mobile, 4 no Desktop) -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 pt-1">
                    <div @click="abaCatalogo = 'produtos'; filtroStatusCatalogo = 'todos'"
                         class="bg-[#0a0d17] hover:bg-[#111626] border border-[#1b2236] rounded-2xl p-3 cursor-pointer transition flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total no Cardápio</span>
                            <span class="text-lg sm:text-xl font-bold text-white" x-text="produtos.length"></span>
                        </div>
                        <span class="text-[11px] font-bold px-2 py-1 rounded-lg bg-slate-800 text-slate-300" x-text="categorias.length + ' cat.'"></span>
                    </div>

                    <div @click="abaCatalogo = 'produtos'; filtroStatusCatalogo = 'ativos'"
                         class="bg-[#0a0d17] hover:bg-[#111626] border border-[#1b2236] rounded-2xl p-3 cursor-pointer transition flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 block">Ativos na Vitrine</span>
                            <span class="text-lg sm:text-xl font-bold text-emerald-400" x-text="produtos.filter(p => !p.esgotado).length"></span>
                        </div>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                    </div>

                    <div @click="abaCatalogo = 'produtos'; filtroStatusCatalogo = 'promocao'"
                         class="bg-[#0a0d17] hover:bg-[#111626] border border-[#1b2236] rounded-2xl p-3 cursor-pointer transition flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400 block">Em Promoção</span>
                            <span class="text-lg sm:text-xl font-bold text-amber-400" x-text="produtos.filter(p => p.preco_promocional_centavos && p.preco_promocional_centavos < p.preco_centavos).length"></span>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-500/15 text-amber-300 border border-amber-500/30">Oferta</span>
                    </div>

                    <div @click="abaCatalogo = 'produtos'; filtroStatusCatalogo = 'esgotados'"
                         class="bg-[#0a0d17] hover:bg-[#111626] border border-[#1b2236] rounded-2xl p-3 cursor-pointer transition flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-400 block">Pausados / Esgotados</span>
                            <span class="text-lg sm:text-xl font-bold text-rose-400" x-text="produtos.filter(p => p.esgotado).length"></span>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-rose-500/15 text-rose-300 border border-rose-500/30">Estoque</span>
                    </div>
                </div>

                <!-- Sub-Abas de Organização (Produtos & Categorias | Grupos de Adicionais | Cupons) -->
                <div class="flex overflow-x-auto no-scrollbar gap-2 pt-2 border-t border-[#1b2236]">
                    <button type="button" @click="abaCatalogo = 'produtos'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition flex items-center gap-1.5"
                        :class="abaCatalogo === 'produtos' ? 'bg-rose-600 text-white shadow' : 'bg-[#0a0d17] text-slate-400 hover:text-white border border-[#1b2236]'">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                        <span>Produtos &amp; Categorias</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/25" x-text="produtos.length"></span>
                    </button>

                    <button type="button" @click="abaCatalogo = 'adicionais'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition flex items-center gap-1.5"
                        :class="abaCatalogo === 'adicionais' ? 'bg-amber-500 text-slate-950 shadow' : 'bg-[#0a0d17] text-slate-400 hover:text-white border border-[#1b2236]'">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <span>Complementos &amp; Adicionais</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/20" x-text="gruposAdicionais.length"></span>
                    </button>

                    <button type="button" @click="abaCatalogo = 'cupons'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition flex items-center gap-1.5"
                        :class="abaCatalogo === 'cupons' ? 'bg-emerald-500 text-slate-950 shadow' : 'bg-[#0a0d17] text-slate-400 hover:text-white border border-[#1b2236]'">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        <span>Cupons de Desconto</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/20" x-text="cupons.length"></span>
                    </button>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- ABA 1: PRODUTOS & CATEGORIAS                                              -->
            <!-- ========================================================================= -->
            <div x-show="abaCatalogo === 'produtos'" class="space-y-4">
                
                <!-- Barra de Busca de Produto + Filtros de Status -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-[#0f1322] border border-[#1d253b] p-3 rounded-2xl">
                    <div class="relative flex-1">
                        <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input type="text" x-model="buscaCatalogo" placeholder="Buscar produto por nome ou ingrediente..."
                            class="w-full bg-[#090c15] border border-[#1b2236] rounded-xl pl-10 pr-8 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500">
                        <button x-show="buscaCatalogo" @click="buscaCatalogo = ''" class="absolute right-3 top-2 text-slate-500 hover:text-white">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="flex overflow-x-auto no-scrollbar items-center gap-1.5 shrink-0">
                        <button type="button" @click="filtroStatusCatalogo = 'todos'"
                            class="px-3 py-1.5 rounded-xl text-[11px] font-bold transition whitespace-nowrap"
                            :class="filtroStatusCatalogo === 'todos' ? 'bg-slate-700 text-white' : 'bg-[#090c15] text-slate-400 border border-[#1b2236]'">
                            Todos
                        </button>
                        <button type="button" @click="filtroStatusCatalogo = 'ativos'"
                            class="px-3 py-1.5 rounded-xl text-[11px] font-bold transition whitespace-nowrap"
                            :class="filtroStatusCatalogo === 'ativos' ? 'bg-emerald-600 text-white' : 'bg-[#090c15] text-emerald-400 border border-[#1b2236]'">
                            Disponíveis
                        </button>
                        <button type="button" @click="filtroStatusCatalogo = 'promocao'"
                            class="px-3 py-1.5 rounded-xl text-[11px] font-bold transition whitespace-nowrap"
                            :class="filtroStatusCatalogo === 'promocao' ? 'bg-amber-500 text-slate-950' : 'bg-[#090c15] text-amber-400 border border-[#1b2236]'">
                            Em Promoção
                        </button>
                        <button type="button" @click="filtroStatusCatalogo = 'esgotados'"
                            class="px-3 py-1.5 rounded-xl text-[11px] font-bold transition whitespace-nowrap"
                            :class="filtroStatusCatalogo === 'esgotados' ? 'bg-rose-600 text-white' : 'bg-[#090c15] text-rose-400 border border-[#1b2236]'">
                            Esgotados
                        </button>
                    </div>
                </div>

                <!-- Barra de Categorias com Opção de Filtrar, Editar e Excluir -->
                <div class="flex overflow-x-auto no-scrollbar items-center gap-2 pb-1">
                    <button @click="filtroCategoriaId = ''"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition shrink-0 flex items-center gap-1.5"
                        :class="filtroCategoriaId === '' ? 'bg-rose-600 text-white shadow' : 'bg-[#0f1322] text-slate-300 border border-[#1d253b] hover:border-slate-600'">
                        <span>Todas as Categorias</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/25" x-text="produtos.length"></span>
                    </button>

                    <template x-for="cat in categorias" :key="cat.id">
                        <div class="inline-flex items-center rounded-xl overflow-hidden border shrink-0 transition"
                             :class="Number(filtroCategoriaId) === Number(cat.id) ? 'bg-rose-600 border-rose-500 text-white shadow' : 'bg-[#0f1322] border-[#1d253b] text-slate-300 hover:border-slate-600'">
                            <button @click="filtroCategoriaId = cat.id"
                                class="pl-3.5 pr-2 py-2 text-xs font-bold whitespace-nowrap flex items-center gap-1.5">
                                <span x-text="cat.nome"></span>
                                <span class="text-[10px] px-1.5 py-0.2 rounded-full"
                                      :class="Number(filtroCategoriaId) === Number(cat.id) ? 'bg-black/25 text-white' : 'bg-slate-800 text-slate-400'"
                                      x-text="contagemProdutosCategoria(cat.id)"></span>
                            </button>
                            <button @click.stop="abrirModalCategoria(cat)" title="Editar nome/descrição da categoria"
                                class="px-1.5 py-2 opacity-75 hover:opacity-100 transition">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            <button @click.stop="excluirCategoria(cat)" title="Excluir categoria"
                                class="pr-2.5 pl-1 py-2 opacity-75 hover:opacity-100 transition">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </template>

                    <button @click="abrirModalCategoria()"
                        class="px-3 py-2 rounded-xl text-xs font-bold whitespace-nowrap bg-[#0f1322] hover:bg-[#171d32] text-sky-400 border border-dashed border-sky-500/40 shrink-0">
                        + Nova Categoria
                    </button>
                </div>

                <!-- Estado Vazio de Produtos -->
                <template x-if="produtosFiltrados.length === 0">
                    <div class="bg-[#0f1322] border border-dashed border-[#1d253b] rounded-3xl p-10 text-center space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-800 mx-auto flex items-center justify-center text-slate-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-sm font-bold text-white">Nenhum produto encontrado neste filtro</h3>
                            <p class="text-xs text-slate-400">Cadastre um novo item ou limpe os filtros de busca e categoria acima.</p>
                        </div>
                        <div class="flex items-center justify-center gap-2 pt-1">
                            <button x-show="buscaCatalogo || filtroCategoriaId || filtroStatusCatalogo !== 'todos'"
                                @click="buscaCatalogo = ''; filtroCategoriaId = ''; filtroStatusCatalogo = 'todos'"
                                class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-800 text-slate-200 hover:bg-slate-700">
                                Limpar Filtros
                            </button>
                            <button @click="abrirModalProduto()"
                                class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white shadow">
                                + Cadastrar Produto
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Grid de Produtos Premium (1 col no Celular, 2 no Tablet, 3 no Desktop) -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3.5 sm:gap-4">
                    <template x-for="prod in produtosFiltrados" :key="prod.id">
                        <div class="bg-[#0f1322] hover:bg-[#13182a] border rounded-2xl p-4 flex flex-col justify-between gap-3.5 transition shadow-md"
                            :class="prod.esgotado ? 'border-rose-500/40 opacity-80' : 'border-[#1d253b] hover:border-slate-600'">
                            
                            <div class="flex items-start justify-between gap-3.5">
                                <div class="min-w-0 flex-1">
                                    <!-- Badges de Categoria, Status, Promoção e KDS -->
                                    <div class="flex flex-wrap items-center gap-1.5 mb-1.5">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#171e33] text-sky-300 border border-[#242f4d]"
                                              x-text="nomeCategoriaPorId(prod.categoria_id)"></span>
                                        <span x-show="prod.esgotado" class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                            Esgotado
                                        </span>
                                        <template x-if="prod.preco_promocional_centavos && prod.preco_promocional_centavos < prod.preco_centavos">
                                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-emerald-500/20 text-emerald-400 border border-emerald-500/30"
                                                  x-text="'-' + Math.round((1 - prod.preco_promocional_centavos / prod.preco_centavos) * 100) + '% OFF'"></span>
                                        </template>
                                        <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded-md bg-slate-900 text-slate-400 border border-slate-800"
                                              x-text="'KDS: ' + (prod.estacao_kds || 'geral')"></span>
                                    </div>

                                    <h3 class="font-bold text-sm sm:text-base text-white leading-snug line-clamp-1" x-text="prod.nome"></h3>
                                    <p class="text-xs text-slate-400 line-clamp-2 mt-1 leading-relaxed" x-text="prod.descricao || 'Sem descrição cadastrada'"></p>

                                    <!-- Variações e Grupos de Adicionais vinculados -->
                                    <div class="flex flex-wrap gap-1.5 mt-2.5">
                                        <template x-if="prod.variacoes && prod.variacoes.length > 0">
                                            <template x-for="v in prod.variacoes" :key="v.id || v.nome">
                                                <span class="text-[10px] bg-[#090c15] border border-[#1d253b] text-slate-300 px-2 py-0.5 rounded-lg font-medium"
                                                      x-text="v.nome + ': ' + formatarCentavos(v.preco_centavos)"></span>
                                            </template>
                                        </template>
                                        <template x-if="prod.grupos_adicionais && prod.grupos_adicionais.length > 0">
                                            <span class="text-[10px] bg-amber-500/10 border border-amber-500/25 text-amber-300 px-2 py-0.5 rounded-lg font-semibold"
                                                  x-text="prod.grupos_adicionais.length + (prod.grupos_adicionais.length === 1 ? ' grupo de adicional' : ' grupos de adicionais')"></span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Miniatura Quadrada 72x72 ou Inicial -->
                                <div class="w-[72px] h-[72px] rounded-2xl overflow-hidden bg-[#161c2e] border border-[#242e49] shrink-0 flex items-center justify-center">
                                    <template x-if="prod.imagem">
                                        <img :src="prod.imagem.startsWith('http') || prod.imagem.startsWith('/') ? prod.imagem : '/storage/' + prod.imagem"
                                             :alt="prod.nome"
                                             class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!prod.imagem">
                                        <span class="text-2xl font-bold text-rose-400 select-none"
                                              x-text="(prod.nome || 'P').trim().charAt(0).toUpperCase()"></span>
                                    </template>
                                </div>
                            </div>

                            <!-- Rodapé do Card: Preço + Ações Rápidas -->
                            <div class="flex items-center justify-between gap-2 pt-3 border-t border-[#1b2236]">
                                <div>
                                    <template x-if="prod.variacoes && prod.variacoes.length > 0">
                                        <span class="text-[10px] text-slate-400 block">A partir de</span>
                                    </template>
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-sm sm:text-base font-bold text-emerald-400"
                                              x-text="formatarCentavos(prod.variacoes?.length > 0 ? Math.min(...prod.variacoes.map(v => v.preco_centavos)) : prod.preco_efetivo_centavos)"></span>
                                        <span x-show="prod.preco_promocional_centavos && prod.preco_promocional_centavos < prod.preco_centavos"
                                              class="text-xs text-slate-500 line-through"
                                              x-text="formatarCentavos(prod.preco_centavos)"></span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <button @click="abrirModalProduto(prod)"
                                        class="px-3 py-1.5 rounded-xl text-xs font-bold bg-[#171e33] hover:bg-[#222c49] text-white border border-[#26314d] transition">
                                        Editar
                                    </button>
                                    <button @click="duplicarProduto(prod)" title="Duplicar produto"
                                        class="p-1.5 rounded-xl text-xs font-bold bg-[#171e33] hover:bg-[#222c49] text-slate-300 hover:text-white border border-[#26314d] transition">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    </button>
                                    <button @click="alternarEstoqueProduto(prod)"
                                        class="px-2.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1"
                                        :class="prod.esgotado ? 'bg-emerald-600 hover:bg-emerald-500 text-white shadow' : 'bg-[#171e33] hover:bg-rose-600/20 text-slate-300 hover:text-rose-300 border border-[#26314d]'">
                                        <span x-text="prod.esgotado ? 'Ativar' : 'Pausar'"></span>
                                    </button>
                                    <button @click="excluirProduto(prod)" title="Excluir produto"
                                        class="p-1.5 rounded-xl text-xs font-bold bg-[#171e33] hover:bg-rose-600/20 text-rose-400 border border-[#26314d] transition">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- ABA 2: GRUPOS DE ADICIONAIS & COMPLEMENTOS                                -->
            <!-- ========================================================================= -->
            <div x-show="abaCatalogo === 'adicionais'" class="bg-[#0f1322] border border-[#1d253b] rounded-3xl p-4 sm:p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#1b2236] pb-3">
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-white">Grupos de Adicionais &amp; Complementos</h3>
                        <p class="text-xs text-slate-400">Crie grupos como "Ponto da Carne", "Escolha a Borda" ou "Turbine seu Lanche" e vincule aos produtos.</p>
                    </div>
                    <button @click="abrirModalGrupoAdicional()"
                        class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs px-4 py-2.5 rounded-xl shadow transition shrink-0">
                        + Novo Grupo de Adicionais
                    </button>
                </div>

                <template x-if="!gruposAdicionais || gruposAdicionais.length === 0">
                    <div class="text-center py-10 space-y-2 text-xs text-slate-400">
                        <p class="font-bold text-white text-sm">Nenhum grupo de adicionais cadastrado</p>
                        <p>Clique em "+ Novo Grupo de Adicionais" para criar complementos pagos ou gratuitos.</p>
                    </div>
                </template>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    <template x-for="grp in gruposAdicionais" :key="grp.id">
                        <div class="bg-[#090c15] border border-[#1d253b] rounded-2xl p-4 flex flex-col justify-between gap-3 text-xs">
                            <div class="space-y-2">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <div class="font-bold text-sm text-white" x-text="grp.nome"></div>
                                        <div class="text-[11px] text-slate-400 mt-0.5"
                                             x-text="(grp.obrigatorio ? 'Obrigatório' : 'Opcional') + ' • Mín: ' + (grp.minimo ?? grp.min_escolhas ?? 0) + ' • Máx: ' + (grp.maximo ?? grp.max_escolhas ?? 1)"></div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold"
                                          :class="grp.obrigatorio ? 'bg-rose-500/15 text-rose-300 border border-rose-500/30' : 'bg-slate-800 text-slate-300'"
                                          x-text="grp.obrigatorio ? 'Obrigatório' : 'Opcional'"></span>
                                </div>

                                <div class="flex flex-wrap gap-1.5 pt-1">
                                    <template x-for="op in (grp.opcoes || [])" :key="op.id || op.nome">
                                        <span class="px-2 py-0.5 rounded-lg bg-[#121728] border border-[#1f2942] text-[11px] text-slate-300"
                                              x-text="op.nome + (op.preco_centavos > 0 ? ' (+' + formatarCentavos(op.preco_centavos) + ')' : ' (Grátis)')"></span>
                                    </template>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#182034]">
                                <button @click="abrirModalGrupoAdicional(grp)"
                                    class="px-3 py-1.5 rounded-xl bg-[#171e33] hover:bg-[#222c49] text-white text-xs font-bold border border-[#26314d]">
                                    Editar Opções
                                </button>
                                <button @click="excluirGrupoAdicional(grp.id)"
                                    class="p-1.5 rounded-xl text-rose-400 hover:bg-rose-500/15 border border-[#26314d]">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- ABA 3: CUPONS DE DESCONTO DA LOJA                                         -->
            <!-- ========================================================================= -->
            <div x-show="abaCatalogo === 'cupons'" class="bg-[#0f1322] border border-[#1d253b] rounded-3xl p-4 sm:p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#1b2236] pb-3">
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-white">Cupons de Desconto Ativos</h3>
                        <p class="text-xs text-slate-400">O primeiro cupom ativo aparece automaticamente em destaque no topo do cardápio digital.</p>
                    </div>
                    <button @click="modalNovoCupom = true"
                        class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs px-4 py-2.5 rounded-xl shadow transition shrink-0">
                        + Criar Novo Cupom
                    </button>
                </div>

                <template x-if="!cupons || cupons.length === 0">
                    <div class="text-center py-10 space-y-2 text-xs text-slate-400">
                        <p class="font-bold text-white text-sm">Nenhum cupom cadastrado</p>
                        <p>Crie cupons como BEMVINDO10 ou FRETEGRATIS para aumentar a conversão do cardápio.</p>
                    </div>
                </template>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <template x-for="cp in cupons" :key="cp.id">
                        <div class="bg-[#090c15] border rounded-2xl p-3.5 flex flex-col justify-between gap-3"
                            :class="cp.ativo ? 'border-emerald-500/30' : 'border-[#1d253b] opacity-60'">
                            <div>
                                <div class="flex items-center justify-between gap-1.5">
                                    <span class="font-bold text-sm text-emerald-400 tracking-wider" x-text="cp.codigo"></span>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-emerald-500/15 text-emerald-300 border border-emerald-500/30"
                                        x-text="cp.tipo === 'percentual' ? (cp.valor + '% OFF') : (cp.tipo === 'frete_gratis' ? 'Frete Grátis' : formatarCentavos(cp.valor) + ' OFF')"></span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-1.5"
                                     x-text="'Pedido mín: ' + formatarCentavos(cp.minimo_centavos || 0) + ' • Usos: ' + (cp.usos || 0)"></div>
                            </div>
                            <div class="flex items-center justify-between gap-2 pt-2 border-t border-[#182034]">
                                <button @click="alternarCupom(cp.id)" class="flex-1 text-xs font-bold py-1.5 rounded-xl transition"
                                    :class="cp.ativo ? 'bg-slate-800 hover:bg-slate-700 text-slate-200' : 'bg-emerald-600 text-white'"
                                    x-text="cp.ativo ? 'Pausar Cupom' : 'Ativar Cupom'"></button>
                                <button @click="excluirCupom(cp.id)" class="text-rose-400 hover:bg-rose-500/15 p-1.5 rounded-xl border border-[#26314d]">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>
