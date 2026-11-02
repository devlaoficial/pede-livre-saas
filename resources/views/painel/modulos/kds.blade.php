<!-- MÓDULO COZINHA (KDS) - DESIGN CLAUDE FIEL -->
<div x-cloak x-show="moduloAtivo === 'kds'" 
     x-data="{
         modoDemoKds: false,
         estacaoSelecionada: 'todas',
         itensConcluidos: {},
         demoTickets: [
             {
                 id: 101,
                 codigo_publico: '24PT',
                 canal: 'delivery',
                 cliente: 'Maria S.',
                 minutos: 2,
                 status: 'novo',
                 itens: [
                     { id: 1011, nome: 'Especial da Casa', qtd: 2, estacao: 'Chapa' },
                     { id: 1012, nome: 'Coca-Cola lata', qtd: 1, estacao: 'Bar' }
                 ]
             },
             {
                 id: 102,
                 codigo_publico: '25PT',
                 canal: 'mesa',
                 cliente: 'Mesa 4',
                 minutos: 5,
                 status: 'novo',
                 itens: [
                     { id: 1021, nome: 'Pizza Calabresa', qtd: 1, estacao: 'Forno' },
                     { id: 1022, nome: 'Suco de laranja', qtd: 2, estacao: 'Bar' }
                 ]
             },
             {
                 id: 103,
                 codigo_publico: '22PT',
                 canal: 'balcao',
                 cliente: 'João P.',
                 minutos: 12,
                 status: 'em_preparo',
                 itens: [
                     { id: 1031, nome: 'Combo Duplo', qtd: 1, estacao: 'Chapa', checked: true },
                     { id: 1032, nome: 'Batata frita', qtd: 1, estacao: 'Fritadeira', checked: false }
                 ]
             },
             {
                 id: 104,
                 codigo_publico: '21PT',
                 canal: 'delivery',
                 cliente: 'Ana L.',
                 minutos: 21,
                 atrasado: true,
                 status: 'em_preparo',
                 itens: [
                     { id: 1041, nome: 'Especial da Casa', qtd: 1, estacao: 'Chapa', checked: true },
                     { id: 1042, nome: 'Coca-Cola lata', qtd: 1, estacao: 'Bar', checked: true }
                 ]
             },
             {
                 id: 105,
                 codigo_publico: '20PT',
                 canal: 'delivery',
                 cliente: 'Carlos M.',
                 minutos: 28,
                 status: 'pronto',
                 itens: [
                     { id: 1051, nome: 'Açaí 500ml', qtd: 1, estacao: 'Bar', checked: true }
                 ]
             }
         ],

         initKds() {
             // Iniciar itens concluídos do demo
             this.demoTickets.forEach(t => {
                 t.itens.forEach(i => {
                     if (i.checked) {
                         this.itensConcluidos[i.id] = true;
                     }
                 });
             });
         },

         toggleCheckItem(itemId) {
             this.itensConcluidos[itemId] = !this.itensConcluidos[itemId];
         },

         isItemChecked(itemId) {
             return !!this.itensConcluidos[itemId];
         },

         avancarKds(ticket) {
             if (this.modoDemoKds) {
                 if (ticket.status === 'novo') ticket.status = 'em_preparo';
                 else if (ticket.status === 'em_preparo') ticket.status = 'pronto';
                 else if (ticket.status === 'pronto') {
                     this.toastMsg = 'Pedido #' + ticket.codigo_publico + ' despachado / motoboy chamado!';
                     this.demoTickets = this.demoTickets.filter(t => t.id !== ticket.id);
                 }
                 return;
             }
             // Modo Real: avança via API no backend
             const realPedido = this.pedidos.find(p => p.id === ticket.id || p.codigo_publico === ticket.codigo_publico);
             if (realPedido) {
                 this.avancarStatus(realPedido);
             }
         },

         pedidosAtivosKds() {
             if (this.modoDemoKds) {
                 return this.demoTickets;
             }

             // Gera lista a partir dos pedidos reais da loja
             const lista = (this.pedidos || [])
                 .filter(p => ['novo', 'aceito', 'em_preparo', 'pronto', 'despachado'].includes(p.status))
                 .map(p => {
                     const mins = Math.max(1, Math.round((new Date() - new Date(p.created_at)) / 60000));
                     const statusKds = (p.status === 'novo' || p.status === 'aceito') ? 'novo' : (p.status === 'em_preparo' ? 'em_preparo' : 'pronto');
                     return {
                         id: p.id,
                         codigo_publico: p.codigo_publico,
                         canal: p.canal,
                         cliente: p.cliente_nome || (p.mesa_id ? 'Mesa ' + (p.mesa?.numero || '') : 'Balcão'),
                         minutos: mins,
                         atrasado: mins > 15,
                         status: statusKds,
                         itens: (p.itens || []).map((it, idx) => {
                             const est = (it.produto && it.produto.estacao_kds) ? it.produto.estacao_kds : (it.estacao_kds || 'Chapa');
                             return {
                                 id: it.id || (p.id * 100 + idx),
                                 nome: it.nome_snapshot || it.nome,
                                 qtd: it.qtd || 1,
                                 estacao: est.charAt(0).toUpperCase() + est.slice(1).toLowerCase()
                             };
                         })
                     };
                 });

             // Se não houver pedidos reais na tela, ativa o modo demo para visualização imediata
             if (lista.length === 0) {
                 return this.demoTickets;
             }
             return lista;
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
             const todos = this.pedidosAtivosKds();
             if (todos.length === 0) return 13;
             const totalMins = todos.reduce((acc, t) => acc + (t.minutos || 0), 0);
             return Math.round(totalMins / todos.length) || 13;
         },

         totalAtrasados() {
             return this.pedidosAtivosKds().filter(t => t.minutos > 15 && t.status !== 'pronto').length;
         }
     }"
     x-init="initKds()"
     class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-6">

    <!-- SUB-BARRA SUPERIOR DO KDS: SUBTÍTULO, TOGGLE MODO DEMO & AÇÕES -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-xl lg:text-2xl font-black text-white tracking-tight">Cozinha</h2>
            <p class="text-xs text-slate-400 font-medium mt-0.5">Filtre por estação e acompanhe o tempo de cada pedido</p>
        </div>

        <div class="flex items-center gap-2">
            <!-- Botão Alternador de Demonstração / Dados Reais -->
            <button @click="modoDemoKds = !modoDemoKds" 
                class="px-3 py-1.5 rounded-xl text-xs font-bold border transition flex items-center gap-1.5"
                :class="modoDemoKds ? 'bg-amber-500/20 text-amber-300 border-amber-500/40' : 'bg-[#131927] text-slate-400 border-[#1e2638] hover:text-white'">
                <span class="w-2 h-2 rounded-full" :class="modoDemoKds ? 'bg-amber-400' : 'bg-slate-500'"></span>
                <span>Dados de exemplo</span>
            </button>

            <!-- Modo TV Tela Cheia -->
            <button @click="toggleFullscreenKds()" 
                class="bg-[#131927] hover:bg-[#1a2336] text-slate-300 hover:text-white border border-[#1e2638] px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5"
                title="Expandir para monitor ou Smart TV na cozinha">
                <span>📺</span>
                <span class="hidden sm:inline">Modo TV</span>
            </button>
        </div>
    </div>

    <!-- 3 KPI CARDS SUPERIORES (Design Fiel Imagem 2) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Card 1: Na fila -->
        <div class="bg-[#101422] border border-[#192136] rounded-2xl p-4 sm:p-5 flex flex-col justify-between shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Na fila</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl sm:text-4xl font-black text-white" x-text="totalNaFila()"></span>
            </div>
            <span class="text-xs text-slate-400 mt-1">Pedidos abertos</span>
        </div>

        <!-- Card 2: Tempo médio -->
        <div class="bg-[#101422] border border-[#192136] rounded-2xl p-4 sm:p-5 flex flex-col justify-between shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tempo médio</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl sm:text-4xl font-black text-emerald-400" x-text="tempoMedioMinutos() + ' min'"></span>
            </div>
            <span class="text-xs text-slate-400 mt-1">Meta: 15 min</span>
        </div>

        <!-- Card 3: Atrasados -->
        <div class="bg-[#101422] border border-[#192136] rounded-2xl p-4 sm:p-5 flex flex-col justify-between shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Atrasados</span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl sm:text-4xl font-black text-[#f43f5e]" x-text="totalAtrasados()"></span>
            </div>
            <span class="text-xs text-slate-400 mt-1">Passaram da meta</span>
        </div>
    </div>

    <!-- BARRA DE CHIPS DE ESTAÇÃO (Todas, Chapa, Forno, Fritadeira, Bar) -->
    <div class="bg-[#0f1422] border border-[#192136] rounded-2xl p-1.5 flex overflow-x-auto no-scrollbar gap-1.5">
        <template x-for="est in ['todas', 'chapa', 'forno', 'fritadeira', 'bar']" :key="est">
            <button @click="estacaoSelecionada = est"
                class="px-4 py-2 rounded-xl text-xs font-bold capitalize transition flex items-center gap-1.5 shrink-0"
                :class="estacaoSelecionada === est ? 'bg-[#1b2338] text-white shadow-sm' : 'text-slate-400 hover:text-slate-200'">
                <span x-text="est === 'todas' ? 'Todas' : (est.charAt(0).toUpperCase() + est.slice(1))"></span>
                <span class="text-[11px] font-bold opacity-80" x-text="contagemEstacao(est)"></span>
            </button>
        </template>
    </div>

    <!-- 3 COLUNAS KANBAN DA COZINHA (Novos, Em preparo, Prontos) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 items-start">
        
        <!-- ==================== COLUNA 1: NOVOS ==================== -->
        <div class="bg-[#0b0f19] space-y-3.5">
            <!-- Header Coluna -->
            <div class="flex items-center justify-between px-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#f43f5e]"></span>
                    <h3 class="font-extrabold text-sm text-white">Novos</h3>
                </div>
                <span class="w-5 h-5 rounded-full bg-[#161c2d] text-slate-300 font-bold text-xs flex items-center justify-center"
                      x-text="ticketsFiltrados('novo').length"></span>
            </div>

            <!-- Empty State -->
            <template x-if="ticketsFiltrados('novo').length === 0">
                <div class="bg-[#0e1220] border border-dashed border-[#1e2638] rounded-2xl py-12 px-4 text-center text-slate-500 text-xs font-medium">
                    Nenhum pedido novo na fila
                </div>
            </template>

            <!-- Cards Novos -->
            <div class="space-y-3">
                <template x-for="ticket in ticketsFiltrados('novo')" :key="'novo-' + ticket.id">
                    <div class="bg-[#121626] border border-[#1d243a] rounded-2xl p-4 space-y-3 shadow-md hover:border-slate-600 transition">
                        <!-- Topo Card -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-black text-white" x-text="'#' + ticket.codigo_publico"></span>
                                <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md bg-[#1d243a] text-slate-300"
                                      x-text="ticket.canal"></span>
                            </div>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-[#132822] text-emerald-400"
                                  x-text="ticket.minutos + ' min'"></span>
                        </div>

                        <!-- Cliente -->
                        <div class="font-bold text-sm text-white" x-text="ticket.cliente"></div>

                        <!-- Checklist de Itens -->
                        <div class="space-y-2 pt-1">
                            <template x-for="it in ticket.itens" :key="it.id">
                                <div class="flex items-center justify-between text-xs py-1 cursor-pointer group"
                                     @click="toggleCheckItem(it.id)">
                                    <div class="flex items-center gap-2 min-w-0 pr-2">
                                        <span class="font-extrabold text-slate-300" x-text="it.qtd + 'x'"></span>
                                        <span class="font-medium text-slate-200 truncate group-hover:text-white"
                                              :class="isItemChecked(it.id) ? 'line-through text-slate-500' : ''"
                                              x-text="it.nome"></span>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[#182033] text-slate-400"
                                              x-text="it.estacao"></span>
                                        <span class="w-5 h-5 rounded-full border flex items-center justify-center transition"
                                              :class="isItemChecked(it.id) ? 'bg-emerald-500 border-emerald-500 text-white font-black text-[10px]' : 'border-slate-600 hover:border-slate-400'">
                                            <span x-show="isItemChecked(it.id)">✓</span>
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Botão Iniciar Preparo -->
                        <button @click="avancarKds(ticket)"
                            class="w-full py-2.5 rounded-xl font-black text-xs bg-[#fb7185] hover:bg-[#f43f5e] text-slate-950 transition active:scale-95 shadow-md">
                            Iniciar preparo
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- ==================== COLUNA 2: EM PREPARO ==================== -->
        <div class="bg-[#0b0f19] space-y-3.5">
            <!-- Header Coluna -->
            <div class="flex items-center justify-between px-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#f59e0b]"></span>
                    <h3 class="font-extrabold text-sm text-white">Em preparo</h3>
                </div>
                <span class="w-5 h-5 rounded-full bg-[#161c2d] text-slate-300 font-bold text-xs flex items-center justify-center"
                      x-text="ticketsFiltrados('em_preparo').length"></span>
            </div>

            <!-- Empty State -->
            <template x-if="ticketsFiltrados('em_preparo').length === 0">
                <div class="bg-[#0e1220] border border-dashed border-[#1e2638] rounded-2xl py-12 px-4 text-center text-slate-500 text-xs font-medium">
                    Nada na cozinha agora
                </div>
            </template>

            <!-- Cards Em Preparo -->
            <div class="space-y-3">
                <template x-for="ticket in ticketsFiltrados('em_preparo')" :key="'prep-' + ticket.id">
                    <div class="bg-[#121626] border rounded-2xl p-4 space-y-3 shadow-md hover:border-slate-500 transition"
                         :class="ticket.atrasado ? 'border-rose-500/70 ring-1 ring-rose-500/20' : 'border-[#1d243a]'">
                        <!-- Topo Card -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-black text-white" x-text="'#' + ticket.codigo_publico"></span>
                                <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md bg-[#1d243a] text-slate-300"
                                      x-text="ticket.canal"></span>
                            </div>
                            <!-- Badge de Tempo ou Atraso -->
                            <template x-if="ticket.atrasado">
                                <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-[#331422] text-[#f43f5e] border border-rose-500/30"
                                      x-text="ticket.minutos + ' min · atrasado'"></span>
                            </template>
                            <template x-if="!ticket.atrasado">
                                <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-[#2d2212] text-[#f59e0b]"
                                      x-text="ticket.minutos + ' min'"></span>
                            </template>
                        </div>

                        <!-- Cliente -->
                        <div class="font-bold text-sm text-white" x-text="ticket.cliente"></div>

                        <!-- Checklist de Itens -->
                        <div class="space-y-2 pt-1">
                            <template x-for="it in ticket.itens" :key="it.id">
                                <div class="flex items-center justify-between text-xs py-1 cursor-pointer group"
                                     @click="toggleCheckItem(it.id)">
                                    <div class="flex items-center gap-2 min-w-0 pr-2">
                                        <span class="font-extrabold text-slate-300" x-text="it.qtd + 'x'"></span>
                                        <span class="font-medium text-slate-200 truncate group-hover:text-white"
                                              :class="isItemChecked(it.id) ? 'line-through text-slate-500' : ''"
                                              x-text="it.nome"></span>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[#182033] text-slate-400"
                                              x-text="it.estacao"></span>
                                        <span class="w-5 h-5 rounded-full border flex items-center justify-center transition"
                                              :class="isItemChecked(it.id) ? 'bg-emerald-500 border-emerald-500 text-white font-black text-[10px]' : 'border-slate-600 hover:border-slate-400'">
                                            <span x-show="isItemChecked(it.id)">✓</span>
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Botão Marcar como pronto -->
                        <button @click="avancarKds(ticket)"
                            class="w-full py-2.5 rounded-xl font-black text-xs bg-[#f59e0b] hover:bg-[#d97706] text-slate-950 transition active:scale-95 shadow-md">
                            Marcar como pronto
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- ==================== COLUNA 3: PRONTOS ==================== -->
        <div class="bg-[#0b0f19] space-y-3.5">
            <!-- Header Coluna -->
            <div class="flex items-center justify-between px-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#10b981]"></span>
                    <h3 class="font-extrabold text-sm text-white">Prontos</h3>
                </div>
                <span class="w-5 h-5 rounded-full bg-[#161c2d] text-slate-300 font-bold text-xs flex items-center justify-center"
                      x-text="ticketsFiltrados('pronto').length"></span>
            </div>

            <!-- Empty State -->
            <template x-if="ticketsFiltrados('pronto').length === 0">
                <div class="bg-[#0e1220] border border-dashed border-[#1e2638] rounded-2xl py-12 px-4 text-center text-slate-500 text-xs font-medium">
                    Nenhum pedido aguardando despacho
                </div>
            </template>

            <!-- Cards Prontos -->
            <div class="space-y-3">
                <template x-for="ticket in ticketsFiltrados('pronto')" :key="'pronto-' + ticket.id">
                    <div class="bg-[#121626] border border-[#1d243a] rounded-2xl p-4 space-y-3 shadow-md hover:border-slate-500 transition">
                        <!-- Topo Card -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-black text-white" x-text="'#' + ticket.codigo_publico"></span>
                                <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md bg-[#1d243a] text-slate-300"
                                      x-text="ticket.canal"></span>
                            </div>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-[#122b22] text-emerald-400"
                                  x-text="'Pronto'"></span>
                        </div>

                        <!-- Cliente -->
                        <div class="font-bold text-sm text-white" x-text="ticket.cliente"></div>

                        <!-- Checklist de Itens -->
                        <div class="space-y-2 pt-1">
                            <template x-for="it in ticket.itens" :key="it.id">
                                <div class="flex items-center justify-between text-xs py-1 cursor-pointer group"
                                     @click="toggleCheckItem(it.id)">
                                    <div class="flex items-center gap-2 min-w-0 pr-2">
                                        <span class="font-extrabold text-slate-300" x-text="it.qtd + 'x'"></span>
                                        <span class="font-medium text-slate-200 truncate group-hover:text-white"
                                              :class="isItemChecked(it.id) ? 'line-through text-slate-500' : ''"
                                              x-text="it.nome"></span>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[#182033] text-slate-400"
                                              x-text="it.estacao"></span>
                                        <span class="w-5 h-5 rounded-full border flex items-center justify-center transition"
                                              :class="isItemChecked(it.id) ? 'bg-emerald-500 border-emerald-500 text-white font-black text-[10px]' : 'border-slate-600 hover:border-slate-400'">
                                            <span x-show="isItemChecked(it.id)">✓</span>
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Botão Chamar motoboy / Despachar -->
                        <button @click="avancarKds(ticket)"
                            class="w-full py-2.5 rounded-xl font-black text-xs bg-[#38bdf8] hover:bg-[#0ea5e9] text-slate-950 transition active:scale-95 shadow-md">
                            <span x-text="ticket.canal === 'delivery' ? 'Chamar motoboy' : 'Entregar ao cliente'"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>

    </div>

</div>
