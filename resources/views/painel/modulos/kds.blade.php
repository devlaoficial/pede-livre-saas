<!-- MÓDULO COZINHA (KDS) - 100% PRODUÇÃO -->
<div x-cloak x-show="moduloAtivo === 'kds'" 
     x-data="{
         estacaoSelecionada: 'todas',
         abaKdsMobile: 'novo',
         itensConcluidos: {},

         initKds() {
             // Sincroniza estado inicial de itens prontos da API
             (this.pedidos || []).forEach(p => {
                 (p.itens || []).forEach(it => {
                     if (it.id && it.status_kds === 'pronto') {
                         this.itensConcluidos[it.id] = true;
                     }
                 });
             });
         },

         async toggleCheckItem(itemId) {
             const novoEstado = !this.itensConcluidos[itemId];
             this.itensConcluidos[itemId] = novoEstado;
             if (itemId && Number.isInteger(Number(itemId)) && Number(itemId) < 100000000) {
                 try {
                     await this.apiFetch(`/api/kds/itens/${itemId}/status`, {
                         method: 'PATCH',
                         body: JSON.stringify({ status_kds: novoEstado ? 'pronto' : 'em_preparo' })
                     });
                 } catch (e) {}
             }
         },

         isItemChecked(itemId) {
             return !!this.itensConcluidos[itemId];
         },

         avancarKds(ticket) {
             const realPedido = (this.pedidos || []).find(p => p.id === ticket.id || p.codigo_publico === ticket.codigo_publico);
             if (realPedido) {
                 this.avancarStatus(realPedido);
             }
         },

         pedidosAtivosKds() {
             const metaMin = Number(this.loja?.config_json?.tempo_medio_preparo_min || 15);
             return (this.pedidos || [])
                 .filter(p => ['novo', 'aceito', 'em_preparo', 'pronto', 'saiu_entrega'].includes(p.status))
                 .map(p => {
                     const mins = Math.max(1, Math.round((new Date() - new Date(p.created_at)) / 60000));
                     const statusKds = (p.status === 'novo' || p.status === 'aceito')
                         ? 'novo'
                         : (p.status === 'em_preparo' ? 'em_preparo' : 'pronto');
                     return {
                         id: p.id,
                         codigo_publico: p.codigo_publico,
                         canal: p.canal,
                         cliente: p.cliente?.nome || p.cliente_nome || (p.mesa ? 'Mesa ' + (p.mesa.numero || '') : 'Balcão'),
                         minutos: mins,
                         atrasado: mins > metaMin && statusKds !== 'pronto',
                         status: statusKds,
                         observacoes: p.observacoes || '',
                         itens: (p.itens || []).map((it, idx) => {
                             const est = (it.produto && it.produto.estacao_kds)
                                 ? it.produto.estacao_kds
                                 : (it.estacao_kds || 'chapa');
                             if (it.id && it.status_kds === 'pronto' && this.itensConcluidos[it.id] === undefined) {
                                 this.itensConcluidos[it.id] = true;
                             }
                             return {
                                 id: it.id || (p.id * 100000 + idx),
                                 nome: it.nome_snapshot || it.nome,
                                 variacao: it.variacao_snapshot || it.variacao || null,
                                 adicionais: Array.isArray(it.adicionais) ? it.adicionais : (Array.isArray(it.adicionais_json) ? it.adicionais_json : []),
                                 obs: it.obs || null,
                                 qtd: it.qtd || 1,
                                 estacao: est.charAt(0).toUpperCase() + est.slice(1).toLowerCase()
                             };
                         })
                     };
                 });
         },

         ticketsFiltrados(status) {
             const todos = this.pedidosAtivosKds().filter(t => t.status === status);
             if (this.estacaoSelecionada === 'todas') {
                 return todos;
             }
             return todos.filter(t => t.itens.some(i => i.estacao.toLowerCase() === this.estacaoSelecionada.toLowerCase()));
         },

         contagemEstacao(estacao) {
             const todos = this.pedidosAtivosKds();
             if (estacao === 'todas') return todos.length;
             return todos.filter(t => t.itens.some(i => i.estacao.toLowerCase() === estacao.toLowerCase())).length;
         },

         totalNaFila() {
             return this.pedidosAtivosKds().filter(t => t.status !== 'pronto').length;
         },

         tempoMedioMinutos() {
             const todos = this.pedidosAtivosKds().filter(t => t.status !== 'pronto');
             if (todos.length === 0) return 0;
             const totalMins = todos.reduce((acc, t) => acc + (t.minutos || 0), 0);
             return Math.round(totalMins / todos.length) || 0;
         },

         totalAtrasados() {
             return this.pedidosAtivosKds().filter(t => t.atrasado && t.status !== 'pronto').length;
         }
     }"
     x-init="initKds()"
     class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-6">

    <!-- SUB-BARRA SUPERIOR DO KDS -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-xl lg:text-2xl font-bold text-white tracking-tight">Cozinha (KDS em Tempo Real)</h2>
            <p class="text-xs text-slate-400 font-medium mt-0.5">Filtre por estação de preparo e acompanhe o SLA de cada pedido ao vivo</p>
        </div>

        <div class="flex items-center gap-2">
            <button @click="carregarDadosIniciais()" 
                class="bg-[#131927] hover:bg-[#1a2336] text-slate-300 hover:text-white border border-[#1e2638] px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <span><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg></span>
                <span>Atualizar</span>
            </button>

            <!-- Modo TV Tela Cheia -->
            <button @click="toggleFullscreenKds()" 
                class="bg-[#131927] hover:bg-[#1a2336] text-slate-300 hover:text-white border border-[#1e2638] px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5"
                title="Expandir para monitor ou Smart TV na cozinha">
                <span><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg></span>
                <span class="hidden sm:inline">Modo TV</span>
            </button>
        </div>
    </div>

    <!-- RESUMO COMPACTO MOBILE (< sm) -->
    <div class="sm:hidden flex items-center justify-between gap-2 bg-[#101422] border border-[#192136] rounded-xl px-3 py-2 text-[11px]">
        <div class="flex items-center gap-3">
            <span class="text-slate-300 font-semibold">Fila: <strong class="text-white" x-text="totalNaFila()"></strong></span>
            <span class="text-slate-600">•</span>
            <span class="text-slate-300 font-semibold">Médio: <strong class="text-emerald-400" x-text="tempoMedioMinutos() + ' min'"></strong></span>
        </div>
        <span class="px-2 py-0.5 rounded-md font-bold"
              :class="totalAtrasados() > 0 ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-slate-800 text-slate-400'"
              x-text="totalAtrasados() + ' atrasados'"></span>
    </div>

    <!-- 3 KPI CARDS SUPERIORES (Tablet/Desktop >= sm) -->
    <div class="hidden sm:grid sm:grid-cols-3 gap-4">
        <!-- Card 1: Na fila -->
        <div class="bg-[#101422] border border-[#192136] rounded-2xl p-4 sm:p-5 flex flex-col justify-between shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Na fila</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl sm:text-4xl font-bold text-white" x-text="totalNaFila()"></span>
            </div>
            <span class="text-xs text-slate-400 mt-1">Pedidos em preparo / aguardando</span>
        </div>

        <!-- Card 2: Tempo médio -->
        <div class="bg-[#101422] border border-[#192136] rounded-2xl p-4 sm:p-5 flex flex-col justify-between shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tempo médio na fila</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl sm:text-4xl font-bold text-emerald-400" x-text="tempoMedioMinutos() + ' min'"></span>
            </div>
            <span class="text-xs text-slate-400 mt-1" x-text="'Meta da loja: ' + (loja?.config_json?.tempo_medio_preparo_min || 15) + ' min'"></span>
        </div>

        <!-- Card 3: Atrasados -->
        <div class="bg-[#101422] border border-[#192136] rounded-2xl p-4 sm:p-5 flex flex-col justify-between shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Atrasados</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl sm:text-4xl font-bold text-[#f43f5e]" x-text="totalAtrasados()"></span>
            </div>
            <span class="text-xs text-slate-400 mt-1">Acima do tempo médio</span>
        </div>
    </div>

    <!-- BARRA DE CHIPS DE ESTAÇÃO (Todas, Chapa, Forno, Fritadeira, Bar, Sobremesa) -->
    <div class="bg-[#0f1422] border border-[#192136] rounded-2xl p-1.5 flex overflow-x-auto no-scrollbar gap-1.5">
        <template x-for="est in ['todas', 'chapa', 'forno', 'fritadeira', 'bar', 'sobremesa']" :key="est">
            <button @click="estacaoSelecionada = est"
                class="px-4 py-2 rounded-xl text-xs font-bold capitalize transition flex items-center gap-1.5 shrink-0"
                :class="estacaoSelecionada === est ? 'bg-[#1b2338] text-white shadow-sm' : 'text-slate-400 hover:text-slate-200'">
                <span x-text="est === 'todas' ? 'Todas' : (est.charAt(0).toUpperCase() + est.slice(1))"></span>
                <span class="text-[11px] font-bold opacity-80" x-text="contagemEstacao(est)"></span>
            </button>
        </template>
    </div>

    <!-- SELETOR DE ETAPA KDS NO CELULAR (< md) -->
    <div class="md:hidden grid grid-cols-3 gap-1.5 bg-[#0f1422] p-1 rounded-xl border border-[#192136]">
        <button type="button" @click="abaKdsMobile = 'novo'"
            class="py-2 px-2 rounded-lg text-[11px] font-bold flex items-center justify-center gap-1.5 transition"
            :class="abaKdsMobile === 'novo' ? 'bg-[#f43f5e] text-white shadow' : 'text-slate-400'">
            <span>Novos</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/25" x-text="ticketsFiltrados('novo').length"></span>
        </button>
        <button type="button" @click="abaKdsMobile = 'em_preparo'"
            class="py-2 px-2 rounded-lg text-[11px] font-bold flex items-center justify-center gap-1.5 transition"
            :class="abaKdsMobile === 'em_preparo' ? 'bg-[#f59e0b] text-slate-950 shadow' : 'text-slate-400'">
            <span>Preparo</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/20" x-text="ticketsFiltrados('em_preparo').length"></span>
        </button>
        <button type="button" @click="abaKdsMobile = 'pronto'"
            class="py-2 px-2 rounded-lg text-[11px] font-bold flex items-center justify-center gap-1.5 transition"
            :class="abaKdsMobile === 'pronto' ? 'bg-[#10b981] text-slate-950 shadow' : 'text-slate-400'">
            <span>Prontos</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/20" x-text="ticketsFiltrados('pronto').length"></span>
        </button>
    </div>

    <!-- 3 COLUNAS KANBAN DA COZINHA (Novos, Em preparo, Prontos) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 items-start">
        
        <!-- ==================== COLUNA 1: NOVOS ==================== -->
        <div :class="abaKdsMobile === 'novo' ? 'block' : 'hidden md:block'" class="bg-[#0b0f19] space-y-3.5">
            <div class="flex items-center justify-between px-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#f43f5e]"></span>
                    <h3 class="font-semibold text-sm text-white">Novos / Recebidos</h3>
                </div>
                <span class="w-5 h-5 rounded-full bg-[#161c2d] text-slate-300 font-bold text-xs flex items-center justify-center"
                      x-text="ticketsFiltrados('novo').length"></span>
            </div>

            <template x-if="ticketsFiltrados('novo').length === 0">
                <div class="bg-[#0e1220] border border-dashed border-[#1e2638] rounded-2xl py-12 px-4 text-center text-slate-500 text-xs font-medium">
                    Nenhum pedido novo na fila
                </div>
            </template>

            <div class="space-y-3">
                <template x-for="ticket in ticketsFiltrados('novo')" :key="'novo-' + ticket.id">
                    <div class="bg-[#121626] border border-[#1d243a] rounded-2xl p-4 space-y-3 shadow-md hover:border-slate-600 transition">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-white" x-text="'#' + ticket.codigo_publico"></span>
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-[#1d243a] text-slate-300"
                                      x-text="ticket.canal"></span>
                            </div>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-[#132822] text-emerald-400"
                                  x-text="ticket.minutos + ' min'"></span>
                        </div>

                        <div class="font-bold text-sm text-white" x-text="ticket.cliente"></div>

                        <div class="space-y-2 pt-1">
                            <template x-for="it in ticket.itens" :key="it.id">
                                <div class="text-xs py-1 cursor-pointer group" @click="toggleCheckItem(it.id)">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 min-w-0 pr-2">
                                            <span class="font-semibold text-slate-300" x-text="it.qtd + 'x'"></span>
                                            <span class="font-medium text-slate-200 truncate group-hover:text-white"
                                                  :class="isItemChecked(it.id) ? 'line-through text-slate-500' : ''"
                                                  x-text="it.nome"></span>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[#182033] text-slate-400"
                                                  x-text="it.estacao"></span>
                                            <span class="w-5 h-5 rounded-full border flex items-center justify-center transition"
                                                  :class="isItemChecked(it.id) ? 'bg-emerald-500 border-emerald-500 text-white font-bold text-[10px]' : 'border-slate-600 hover:border-slate-400'">
                                                <span x-show="isItemChecked(it.id)"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>
                                            </span>
                                        </div>
                                    </div>
                                    <template x-if="it.obs">
                                        <div class="text-[11px] text-amber-400 pl-6 mt-0.5" x-text="'' + it.obs"></div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <button @click="avancarKds(ticket)"
                            class="w-full py-2.5 rounded-xl font-bold text-xs bg-[#fb7185] hover:bg-[#f43f5e] text-slate-950 transition active:scale-95 shadow-md">
                            Iniciar preparo
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- ==================== COLUNA 2: EM PREPARO ==================== -->
        <div :class="abaKdsMobile === 'em_preparo' ? 'block' : 'hidden md:block'" class="bg-[#0b0f19] space-y-3.5">
            <div class="flex items-center justify-between px-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#f59e0b]"></span>
                    <h3 class="font-semibold text-sm text-white">Em preparo</h3>
                </div>
                <span class="w-5 h-5 rounded-full bg-[#161c2d] text-slate-300 font-bold text-xs flex items-center justify-center"
                      x-text="ticketsFiltrados('em_preparo').length"></span>
            </div>

            <template x-if="ticketsFiltrados('em_preparo').length === 0">
                <div class="bg-[#0e1220] border border-dashed border-[#1e2638] rounded-2xl py-12 px-4 text-center text-slate-500 text-xs font-medium">
                    Nada na cozinha agora
                </div>
            </template>

            <div class="space-y-3">
                <template x-for="ticket in ticketsFiltrados('em_preparo')" :key="'prep-' + ticket.id">
                    <div class="bg-[#121626] border rounded-2xl p-4 space-y-3 shadow-md hover:border-slate-500 transition"
                         :class="ticket.atrasado ? 'border-rose-500/70 ring-1 ring-rose-500/20' : 'border-[#1d243a]'">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-white" x-text="'#' + ticket.codigo_publico"></span>
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-[#1d243a] text-slate-300"
                                      x-text="ticket.canal"></span>
                            </div>
                            <template x-if="ticket.atrasado">
                                <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-[#331422] text-[#f43f5e] border border-rose-500/30"
                                      x-text="ticket.minutos + ' min · atrasado'"></span>
                            </template>
                            <template x-if="!ticket.atrasado">
                                <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-[#2d2212] text-[#f59e0b]"
                                      x-text="ticket.minutos + ' min'"></span>
                            </template>
                        </div>

                        <div class="font-bold text-sm text-white" x-text="ticket.cliente"></div>

                        <div class="space-y-2 pt-1">
                            <template x-for="it in ticket.itens" :key="it.id">
                                <div class="text-xs py-1 cursor-pointer group" @click="toggleCheckItem(it.id)">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 min-w-0 pr-2">
                                            <span class="font-semibold text-slate-300" x-text="it.qtd + 'x'"></span>
                                            <span class="font-medium text-slate-200 truncate group-hover:text-white"
                                                  :class="isItemChecked(it.id) ? 'line-through text-slate-500' : ''"
                                                  x-text="it.nome"></span>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[#182033] text-slate-400"
                                                  x-text="it.estacao"></span>
                                            <span class="w-5 h-5 rounded-full border flex items-center justify-center transition"
                                                  :class="isItemChecked(it.id) ? 'bg-emerald-500 border-emerald-500 text-white font-bold text-[10px]' : 'border-slate-600 hover:border-slate-400'">
                                                <span x-show="isItemChecked(it.id)"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>
                                            </span>
                                        </div>
                                    </div>
                                    <template x-if="it.obs">
                                        <div class="text-[11px] text-amber-400 pl-6 mt-0.5" x-text="'' + it.obs"></div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <button @click="avancarKds(ticket)"
                            class="w-full py-2.5 rounded-xl font-bold text-xs bg-[#f59e0b] hover:bg-[#d97706] text-slate-950 transition active:scale-95 shadow-md">
                            Marcar como pronto
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- ==================== COLUNA 3: PRONTOS ==================== -->
        <div :class="abaKdsMobile === 'pronto' ? 'block' : 'hidden md:block'" class="bg-[#0b0f19] space-y-3.5">
            <div class="flex items-center justify-between px-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#10b981]"></span>
                    <h3 class="font-semibold text-sm text-white">Prontos para Expedição</h3>
                </div>
                <span class="w-5 h-5 rounded-full bg-[#161c2d] text-slate-300 font-bold text-xs flex items-center justify-center"
                      x-text="ticketsFiltrados('pronto').length"></span>
            </div>

            <template x-if="ticketsFiltrados('pronto').length === 0">
                <div class="bg-[#0e1220] border border-dashed border-[#1e2638] rounded-2xl py-12 px-4 text-center text-slate-500 text-xs font-medium">
                    Nenhum pedido aguardando despacho
                </div>
            </template>

            <div class="space-y-3">
                <template x-for="ticket in ticketsFiltrados('pronto')" :key="'pronto-' + ticket.id">
                    <div class="bg-[#121626] border border-[#1d243a] rounded-2xl p-4 space-y-3 shadow-md hover:border-slate-500 transition">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-white" x-text="'#' + ticket.codigo_publico"></span>
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-[#1d243a] text-slate-300"
                                      x-text="ticket.canal"></span>
                            </div>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-[#122b22] text-emerald-400">Pronto</span>
                        </div>

                        <div class="font-bold text-sm text-white" x-text="ticket.cliente"></div>

                        <div class="space-y-2 pt-1">
                            <template x-for="it in ticket.itens" :key="it.id">
                                <div class="flex items-center justify-between text-xs py-1 cursor-pointer group"
                                     @click="toggleCheckItem(it.id)">
                                    <div class="flex items-center gap-2 min-w-0 pr-2">
                                        <span class="font-semibold text-slate-300" x-text="it.qtd + 'x'"></span>
                                        <span class="font-medium text-slate-200 truncate group-hover:text-white"
                                              :class="isItemChecked(it.id) ? 'line-through text-slate-500' : ''"
                                              x-text="it.nome"></span>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[#182033] text-slate-400"
                                              x-text="it.estacao"></span>
                                        <span class="w-5 h-5 rounded-full border flex items-center justify-center transition"
                                              :class="isItemChecked(it.id) ? 'bg-emerald-500 border-emerald-500 text-white font-bold text-[10px]' : 'border-slate-600 hover:border-slate-400'">
                                            <span x-show="isItemChecked(it.id)"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <button @click="avancarKds(ticket)"
                            class="w-full py-2.5 rounded-xl font-bold text-xs bg-[#38bdf8] hover:bg-[#0ea5e9] text-slate-950 transition active:scale-95 shadow-md">
                            <span x-text="ticket.canal === 'delivery' ? 'Despachar p/ Entrega' : 'Entregar ao Cliente'"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>

    </div>

</div>
