        <div x-cloak x-show="moduloAtivo === 'kds'" class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3 bg-slate-900 border border-slate-800 p-4 rounded-2xl">
                <div>
                    <h2 class="font-bold text-base text-white">KDS — Tela de Produção da Cozinha por Estação</h2>
                    <p class="text-xs text-slate-400">Separa automaticamente Chapa, Forno, Fritadeira e Bar com cronômetro SLA de atraso.</p>
                </div>
                <div class="flex flex-wrap items-center gap-1.5">
                    <template x-for="est in ['', 'chapa', 'forno', 'fritadeira', 'bar', 'geral']" :key="est">
                        <button @click="estacaoKds = est; carregarKds()"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold uppercase transition"
                            :class="estacaoKds === est ? 'bg-rose-600 text-white' : 'bg-slate-800 text-slate-400'"
                            x-text="est === '' ? 'Todas' : est"></button>
                    </template>
                    <button @click="toggleFullscreenKds()" class="bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/30 px-3 py-1.5 rounded-xl text-xs font-extrabold transition">
                        📺 Modo TV Tela Cheia
                    </button>
                </div>
            </div>

            <template x-if="kdsTickets.length === 0">
                <div class="text-center py-16 text-slate-500 space-y-2">
                    <div class="text-3xl">👨‍🍳</div>
                    <p class="text-sm font-bold">Nenhum item pendente na fila de produção</p>
                </div>
            </template>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <template x-for="t in kdsTickets" :key="t.item_id">
                    <div class="bg-slate-900 border-2 rounded-2xl p-4 space-y-3 shadow-lg"
                        :class="t.atrasado ? 'border-rose-500 animate-pulse' : (t.status_cozinha === 'preparando' ? 'border-amber-500/60' : 'border-emerald-500/40')">
                        <div class="flex justify-between items-center">
                            <span class="font-black text-rose-400 text-sm" x-text="'#' + t.codigo_publico"></span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded"
                                    :class="t.atrasado ? 'bg-rose-500 text-white' : 'bg-emerald-500/20 text-emerald-400'"
                                    x-text="t.atrasado ? '⚠️ ATRASADO' : 'NO PRAZO'"></span>
                                <span class="text-[11px] font-bold uppercase px-2 py-0.5 rounded bg-slate-800 text-amber-400" x-text="t.estacao_kds"></span>
                            </div>
                        </div>
                        <div class="font-extrabold text-base text-white" x-text="t.qtd + 'x ' + t.produto_nome + (t.variacao ? ' (' + t.variacao + ')' : '')"></div>
                        <template x-if="t.adicionais && t.adicionais.length > 0">
                            <div class="text-xs text-slate-300 bg-slate-950 p-2.5 rounded-xl" x-text="t.adicionais.join(', ')"></div>
                        </template>
                        <template x-if="t.observacao">
                            <div class="text-xs font-bold text-amber-300 bg-amber-500/15 border border-amber-500/30 p-2 rounded-xl" x-text="'⚠️ Obs: ' + t.observacao"></div>
                        </template>
                        <button @click="avancarItemKds(t)"
                            class="w-full font-bold py-2.5 rounded-xl text-xs transition"
                            :class="t.status_cozinha === 'fila' ? 'bg-amber-600 hover:bg-amber-500 text-white' : 'bg-emerald-600 hover:bg-emerald-500 text-white'">
                            <span x-text="t.status_cozinha === 'fila' ? '🔥 Iniciar Preparo' : '✓ Marcar Item Pronto'"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
