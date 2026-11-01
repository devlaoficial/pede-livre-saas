        <div x-cloak x-show="moduloAtivo === 'catalogo'" class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-900 border border-slate-800 p-4 rounded-2xl">
                <div>
                    <h2 class="font-bold text-base text-white">Gestão Completa de Cardápio, Promoções &amp; Cupons</h2>
                    <p class="text-xs text-slate-400">Edite fotos WebP, preços promocionais ("De/Por"), variações de tamanho e cupons de desconto.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button @click="modalNovoCupom = true" class="bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 text-xs font-bold px-3.5 py-2.5 rounded-xl border border-emerald-500/30">
                        🎟️ + Novo Cupom
                    </button>
                    <button @click="modalNovaCategoria = true" class="bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold px-3.5 py-2.5 rounded-xl border border-slate-700">
                        + Nova Categoria
                    </button>
                    <button @click="abrirModalProduto()" class="bg-rose-600 hover:bg-rose-500 text-white text-xs font-extrabold px-4 py-2.5 rounded-xl shadow-md">
                        + Novo Produto
                    </button>
                </div>
            </div>

            <!-- Faixa de Cupons de Desconto Ativos (Etapa 2) -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-black text-white">🎟️ Cupons de Desconto da Loja</span>
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
                                    <span class="font-black text-xs text-emerald-400 tracking-wider" x-text="cp.codigo"></span>
                                    <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-slate-800 text-slate-300"
                                        x-text="cp.tipo === 'percentual' ? (cp.valor + '% OFF') : formatarCentavos(cp.valor) + ' OFF'"></span>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5" x-text="'Mín: ' + formatarCentavos(cp.minimo_centavos || 0) + ' • Usos: ' + (cp.usos || 0)"></div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button @click="alternarCupom(cp.id)" class="text-[10px] font-bold px-2 py-1 rounded-lg"
                                    :class="cp.ativo ? 'bg-slate-800 text-slate-300' : 'bg-emerald-600 text-white'"
                                    x-text="cp.ativo ? 'Pausar' : 'Ativar'"></button>
                                <button @click="excluirCupom(cp.id)" class="text-xs text-rose-400 hover:bg-rose-500/15 px-1.5 py-1 rounded-lg">✕</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Filtro por Categoria -->
            <div class="flex overflow-x-auto no-scrollbar gap-2">
                <button @click="filtroCategoriaId = ''"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition"
                    :class="filtroCategoriaId === '' ? 'bg-rose-600 text-white' : 'bg-slate-900 text-slate-400 border border-slate-800'">
                    Todas as Categorias (<span x-text="produtos.length"></span>)
                </button>
                <template x-for="cat in categorias" :key="cat.id">
                    <button @click="filtroCategoriaId = cat.id"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition"
                        :class="filtroCategoriaId === cat.id ? 'bg-rose-600 text-white' : 'bg-slate-900 text-slate-400 border border-slate-800'"
                        x-text="cat.nome"></button>
                </template>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <template x-for="prod in produtosFiltrados" :key="prod.id">
                    <div class="bg-slate-900 border rounded-2xl p-4 flex flex-col justify-between gap-3"
                        :class="prod.esgotado ? 'border-rose-500/40 opacity-75' : 'border-slate-800'">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-1.5 mb-1">
                                    <span x-show="prod.esgotado" class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-rose-500/20 text-rose-400">Esgotado</span>
                                    <span x-show="prod.preco_promocional_centavos && prod.preco_promocional_centavos < prod.preco_centavos" class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400">🔥 Promoção</span>
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
                            <template x-if="prod.imagem && (prod.imagem.startsWith('http') || prod.imagem.startsWith('/'))">
                                <img :src="prod.imagem" class="w-16 h-16 rounded-xl object-cover shrink-0 border border-slate-800">
                            </template>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-slate-800/80">
                            <div>
                                <span class="text-sm font-black text-emerald-400" x-text="formatarCentavos(prod.preco_efetivo_centavos)"></span>
                                <span x-show="prod.preco_promocional_centavos" class="text-xs text-slate-500 line-through ml-1" x-text="formatarCentavos(prod.preco_centavos)"></span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button @click="abrirModalProduto(prod)" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-200">
                                    ✏️ Editar
                                </button>
                                <button @click="alternarEstoqueProduto(prod)"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
                                    :class="prod.esgotado ? 'bg-emerald-600 text-white' : 'bg-slate-800 hover:bg-rose-600/20 text-slate-300 hover:text-rose-300 border border-slate-700'">
                                    <span x-text="prod.esgotado ? '✓ Ativar' : '⏸️ Pausar'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
