        <div x-cloak x-show="moduloAtivo === 'catalogo'" class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-900 border border-slate-800 p-4 rounded-2xl">
                <div>
                    <h2 class="font-bold text-base text-white">Gestão Completa de Cardápio, Adicionais, Promoções &amp; Cupons</h2>
                    <p class="text-xs text-slate-400">Cadastre categorias, produtos com fotos WebP, grupos de adicionais/complementos e cupons de desconto.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button @click="modalNovoCupom = true" class="bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 text-xs font-bold px-3.5 py-2.5 rounded-xl border border-emerald-500/30">
                        + Novo Cupom
                    </button>
                    <button @click="abrirModalGrupoAdicional()" class="bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 text-xs font-bold px-3.5 py-2.5 rounded-xl border border-amber-500/30">
                        + Grupo de Adicionais
                    </button>
                    <button @click="modalNovaCategoria = true" class="bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold px-3.5 py-2.5 rounded-xl border border-slate-700">
                        + Nova Categoria
                    </button>
                    <button @click="abrirModalProduto()" class="bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-md">
                        + Novo Produto
                    </button>
                </div>
            </div>

            <!-- Faixa de Cupons de Desconto Ativos -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-white">Cupons de Desconto da Loja</span>
                        <span class="text-[11px] bg-emerald-500/15 text-emerald-400 font-bold px-2 py-0.5 rounded-full" x-text="cupons.length + ' cadastrados'"></span>
                    </div>
                    <button @click="modalNovoCupom = true" class="text-xs font-bold text-rose-400 hover:underline">+ Criar Cupom</button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
                    <template x-for="cp in cupons" :key="cp.id">
                        <div class="bg-slate-950 border rounded-xl p-3 flex items-center justify-between gap-2"
                            :class="cp.ativo ? 'border-emerald-500/30' : 'border-slate-800 opacity-60'">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-xs text-emerald-400 tracking-wider" x-text="cp.codigo"></span>
                                    <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-slate-800 text-slate-300"
                                        x-text="cp.tipo === 'percentual' ? (cp.valor + '% OFF') : formatarCentavos(cp.valor) + ' OFF'"></span>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5" x-text="'Mín: ' + formatarCentavos(cp.minimo_centavos || 0) + ' • Usos: ' + (cp.usos || 0)"></div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button @click="alternarCupom(cp.id)" class="text-[10px] font-bold px-2 py-1 rounded-lg"
                                    :class="cp.ativo ? 'bg-slate-800 text-slate-300' : 'bg-emerald-600 text-white'"
                                    x-text="cp.ativo ? 'Pausar' : 'Ativar'"></button>
                                <button @click="excluirCupom(cp.id)" class="text-xs text-rose-400 hover:bg-rose-500/15 px-1.5 py-1 rounded-lg"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Grupos de Adicionais / Complementos -->
            <template x-if="gruposAdicionais && gruposAdicionais.length > 0">
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-white">Grupos de Adicionais &amp; Complementos</span>
                            <span class="text-[11px] bg-amber-500/15 text-amber-300 font-bold px-2 py-0.5 rounded-full" x-text="gruposAdicionais.length + ' grupos'"></span>
                        </div>
                        <button @click="abrirModalGrupoAdicional()" class="text-xs font-bold text-amber-400 hover:underline">+ Novo Grupo</button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                        <template x-for="grp in gruposAdicionais" :key="grp.id">
                            <div class="bg-slate-950 border border-slate-800 rounded-xl p-3 flex items-center justify-between gap-2 text-xs">
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-white truncate" x-text="grp.nome"></div>
                                    <div class="text-[10px] text-slate-400" x-text="(grp.obrigatorio ? 'Obrigatório' : 'Opcional') + ' • Máx: ' + grp.max_escolhas + ' • ' + (grp.opcoes?.length || 0) + ' opções'"></div>
                                </div>
                                <div class="flex items-center gap-1 shrink-0">
                                    <button @click="abrirModalGrupoAdicional(grp)" class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                                    <button @click="excluirGrupoAdicional(grp.id)" class="px-2 py-1 rounded-lg text-rose-400 hover:bg-rose-500/15 text-[11px] font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Filtro por Categoria com Opção de Excluir Categoria -->
            <div class="flex overflow-x-auto no-scrollbar gap-2">
                <button @click="filtroCategoriaId = ''"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition"
                    :class="filtroCategoriaId === '' ? 'bg-rose-600 text-white' : 'bg-slate-900 text-slate-400 border border-slate-800'">
                    Todas as Categorias (<span x-text="produtos.length"></span>)
                </button>
                <template x-for="cat in categorias" :key="cat.id">
                    <div class="inline-flex items-center rounded-xl overflow-hidden border"
                         :class="filtroCategoriaId === cat.id ? 'bg-rose-600 border-rose-500 text-white' : 'bg-slate-900 border-slate-800 text-slate-400'">
                        <button @click="filtroCategoriaId = cat.id"
                            class="px-3 py-1.5 text-xs font-bold whitespace-nowrap transition"
                            x-text="cat.nome"></button>
                        <button @click.stop="excluirCategoria(cat)" title="Excluir categoria"
                            class="pr-2.5 pl-1 py-1.5 text-[10px] opacity-70 hover:opacity-100"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                    </div>
                </template>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <template x-for="prod in produtosFiltrados" :key="prod.id">
                    <div class="bg-slate-900 border rounded-2xl p-4 flex flex-col justify-between gap-3"
                        :class="prod.esgotado ? 'border-rose-500/40 opacity-75' : 'border-slate-800'">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-1.5 mb-1">
                                    <span x-show="prod.esgotado" class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-rose-500/20 text-rose-400">Esgotado</span>
                                    <span x-show="prod.preco_promocional_centavos && prod.preco_promocional_centavos < prod.preco_centavos" class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400">Promoção</span>
                                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-slate-800 text-slate-400" x-text="'KDS: ' + (prod.estacao_kds || 'geral')"></span>
                                </div>
                                <h3 class="font-bold text-sm text-white leading-snug" x-text="prod.nome"></h3>
                                <p class="text-xs text-slate-400 line-clamp-2 mt-1" x-text="prod.descricao"></p>
                                <template x-if="prod.variacoes && prod.variacoes.length > 0">
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        <template x-for="v in prod.variacoes" :key="v.id || v.nome">
                                            <span class="text-[10px] bg-slate-950 border border-slate-800 text-slate-300 px-2 py-0.5 rounded-lg" x-text="v.nome + ': ' + formatarCentavos(v.preco_centavos)"></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                            <template x-if="prod.imagem">
                                <img :src="prod.imagem.startsWith('http') || prod.imagem.startsWith('/') ? prod.imagem : '/storage/' + prod.imagem" class="w-16 h-16 rounded-xl object-cover shrink-0 border border-slate-800">
                            </template>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-slate-800/80">
                            <div>
                                <span class="text-sm font-bold text-emerald-400" x-text="formatarCentavos(prod.preco_efetivo_centavos)"></span>
                                <span x-show="prod.preco_promocional_centavos" class="text-xs text-slate-500 line-through ml-1" x-text="formatarCentavos(prod.preco_centavos)"></span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button @click="abrirModalProduto(prod)" class="px-2.5 py-1.5 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-200">
                                    Editar
                                </button>
                                <button @click="alternarEstoqueProduto(prod)"
                                    class="px-2.5 py-1.5 rounded-xl text-xs font-bold transition"
                                    :class="prod.esgotado ? 'bg-emerald-600 text-white' : 'bg-slate-800 hover:bg-rose-600/20 text-slate-300 hover:text-rose-300 border border-slate-700'">
                                    <span x-text="prod.esgotado ? 'Ativar' : '⏸Pausar'"></span>
                                </button>
                                <button @click="excluirProduto(prod)" title="Excluir produto"
                                    class="px-2 py-1.5 rounded-xl text-xs font-bold bg-slate-800 hover:bg-rose-600/20 text-rose-400 border border-slate-700"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
