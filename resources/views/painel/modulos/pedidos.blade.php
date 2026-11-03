        <div x-show="moduloAtivo === 'pedidos'" class="flex-1 flex flex-col min-h-0 overflow-hidden bg-transparent">

            <!-- ========================================================================= -->
            <!-- TOPO MOBILE (< md): FAIXA COMPACTA + ABAS DE ETAPA + BUSCA SOB DEMANDA    -->
            <!-- Garante 80% da altura da tela livre para ver e operar os pedidos no celular -->
            <!-- ========================================================================= -->
            <div class="md:hidden px-3.5 pt-2.5 pb-1.5 space-y-2 shrink-0 border-b border-slate-800/70 bg-[#080c17]/60">
                <!-- Linha 1: Resumo do Dia em 1 Linha + Botão de Busca/Filtros -->
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-3 min-w-0 text-xs">
                        <div class="flex items-center gap-1.5">
                            <span class="text-slate-400 font-medium">Hoje:</span>
                            <strong class="text-white font-mono" x-text="pedidos.length + ' ped'"></strong>
                        </div>
                        <span class="text-slate-700">•</span>
                        <div class="flex items-center gap-1">
                            <strong class="text-emerald-400 font-mono" x-text="formatarCentavos(volumeDiaCentavos)"></strong>
                        </div>
                        <template x-if="contadoresFiltros.pendente > 0">
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-300 border border-amber-500/25"
                                  x-text="contadoresFiltros.pendente + ' a receber'"></span>
                        </template>
                    </div>

                    <button type="button" @click="mostrarFiltrosMobile = !mostrarFiltrosMobile"
                        class="px-2.5 py-1.5 rounded-xl border text-[11px] font-semibold flex items-center gap-1.5 transition shrink-0"
                        :class="mostrarFiltrosMobile || filtroCanal !== 'todos' || filtroPagamento !== 'todos' || buscaPedido
                            ? 'bg-rose-500/15 border-rose-500/40 text-rose-300'
                            : 'bg-[#111726] border-slate-800 text-slate-300'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <span>Filtrar</span>
                    </button>
                </div>

                <!-- Painel Expansível de Busca e Filtros no Celular (só ocupa espaço quando aberto) -->
                <div x-show="mostrarFiltrosMobile" x-cloak class="space-y-2 pt-1 pb-1">
                    <div class="relative">
                        <svg class="w-3.5 h-3.5 text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input type="text" x-model="buscaPedido" placeholder="Buscar por #código, nome ou telefone..."
                            class="w-full bg-[#0d1322] border border-slate-800 rounded-xl pl-8 pr-7 py-2 text-xs text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none">
                        <button x-show="buscaPedido" @click="buscaPedido = ''" class="absolute right-2.5 top-2 text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar text-[11px] font-bold">
                        <button @click="filtroCanal = 'todos'" class="px-2.5 py-1 rounded-lg border shrink-0" :class="filtroCanal === 'todos' ? 'bg-rose-500 border-rose-500 text-white' : 'bg-[#0d1322] border-slate-800 text-slate-400'">Todos Canais</button>
                        <button @click="filtroCanal = 'delivery'" class="px-2.5 py-1 rounded-lg border shrink-0" :class="filtroCanal === 'delivery' ? 'bg-rose-500 border-rose-500 text-white' : 'bg-[#0d1322] border-slate-800 text-slate-400'">Delivery (<span x-text="contadoresFiltros.delivery"></span>)</button>
                        <button @click="filtroCanal = 'balcao_retirada'" class="px-2.5 py-1 rounded-lg border shrink-0" :class="filtroCanal === 'balcao_retirada' ? 'bg-rose-500 border-rose-500 text-white' : 'bg-[#0d1322] border-slate-800 text-slate-400'">Balcão (<span x-text="contadoresFiltros.balcao"></span>)</button>
                        <button @click="filtroCanal = 'mesa'" class="px-2.5 py-1 rounded-lg border shrink-0" :class="filtroCanal === 'mesa' ? 'bg-rose-500 border-rose-500 text-white' : 'bg-[#0d1322] border-slate-800 text-slate-400'">Mesas (<span x-text="contadoresFiltros.mesa"></span>)</button>
                        <button @click="filtroPagamento = filtroPagamento === 'pendente' ? 'todos' : 'pendente'" class="px-2.5 py-1 rounded-lg border shrink-0" :class="filtroPagamento === 'pendente' ? 'bg-amber-500 text-slate-950 border-amber-500' : 'bg-[#0d1322] border-slate-800 text-amber-300'">A pagar (<span x-text="contadoresFiltros.pendente"></span>)</button>
                    </div>
                </div>

                <!-- Abas das 4 Etapas do Kanban no Celular -->
                <div class="bg-[#0d1322] border border-slate-800/90 p-1 rounded-2xl grid grid-cols-4 gap-1 text-[11px] font-bold">
                    <button @click="abaPedidoMobile = 'novo'"
                        class="py-2 rounded-xl flex items-center justify-center gap-1 transition"
                        :class="abaPedidoMobile === 'novo' ? 'bg-rose-500 text-white shadow' : 'text-slate-400'">
                        <span>Novos</span>
                        <span class="font-mono text-[10px] px-1.5 py-0.2 rounded-full"
                              :class="abaPedidoMobile === 'novo' ? 'bg-black/25 text-white' : 'bg-slate-800 text-slate-300'"
                              x-text="colunas.novo.length"></span>
                    </button>
                    <button @click="abaPedidoMobile = 'em_preparo'"
                        class="py-2 rounded-xl flex items-center justify-center gap-1 transition"
                        :class="abaPedidoMobile === 'em_preparo' ? 'bg-amber-500 text-slate-950 shadow' : 'text-slate-400'">
                        <span>Cozinha</span>
                        <span class="font-mono text-[10px] px-1.5 py-0.2 rounded-full"
                              :class="abaPedidoMobile === 'em_preparo' ? 'bg-black/20 text-slate-950' : 'bg-slate-800 text-slate-300'"
                              x-text="colunas.em_preparo.length"></span>
                    </button>
                    <button @click="abaPedidoMobile = 'pronto_e_em_rota'"
                        class="py-2 rounded-xl flex items-center justify-center gap-1 transition"
                        :class="abaPedidoMobile === 'pronto_e_em_rota' ? 'bg-sky-400 text-slate-950 shadow' : 'text-slate-400'">
                        <span>Rota</span>
                        <span class="font-mono text-[10px] px-1.5 py-0.2 rounded-full"
                              :class="abaPedidoMobile === 'pronto_e_em_rota' ? 'bg-black/20 text-slate-950' : 'bg-slate-800 text-slate-300'"
                              x-text="colunas.pronto_e_em_rota.length"></span>
                    </button>
                    <button @click="abaPedidoMobile = 'concluidos'"
                        class="py-2 rounded-xl flex items-center justify-center gap-1 transition"
                        :class="abaPedidoMobile === 'concluidos' ? 'bg-emerald-500 text-slate-950 shadow' : 'text-slate-400'">
                        <span>Feitos</span>
                        <span class="font-mono text-[10px] px-1.5 py-0.2 rounded-full"
                              :class="abaPedidoMobile === 'concluidos' ? 'bg-black/20 text-slate-950' : 'bg-slate-800 text-slate-300'"
                              x-text="colunas.concluidos.length"></span>
                    </button>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- TOPO DESKTOP/TABLET (md:grid): 4 KPI CARDS EXECUTIVOS + BARRA DE FILTROS  -->
            <!-- ========================================================================= -->
            <div class="hidden md:grid md:grid-cols-4 gap-3.5 px-6 pt-5 pb-2 shrink-0">
                <div class="bg-[#0d1322]/90 border border-slate-800/90 rounded-2xl p-4 flex items-start justify-between gap-2 shadow-sm">
                    <div class="space-y-1 min-w-0">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Pedidos Hoje</span>
                        <div class="text-2xl lg:text-3xl font-bold font-mono text-white tracking-tight" x-text="pedidos.length"></div>
                        <p class="text-[11px] text-slate-400 font-medium truncate"
                           x-text="contadoresFiltros.delivery + ' delivery • ' + contadoresFiltros.balcao + ' balcão • ' + contadoresFiltros.mesa + ' mesa'"></p>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-rose-500/15 border border-rose-500/25 text-rose-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                </div>

                <div class="bg-[#0d1322]/90 border border-slate-800/90 rounded-2xl p-4 flex items-start justify-between gap-2 shadow-sm">
                    <div class="space-y-1 min-w-0">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Faturamento Hoje</span>
                        <div class="text-2xl lg:text-3xl font-bold font-mono text-emerald-400 tracking-tight truncate" x-text="formatarCentavos(volumeDiaCentavos)"></div>
                        <p class="text-[11px] text-slate-400 font-medium truncate"
                           x-text="'Ticket médio ' + formatarCentavos(pedidos.length ? Math.round(volumeDiaCentavos / pedidos.length) : 0)"></p>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/15 border border-emerald-500/25 text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>

                <div class="bg-[#0d1322]/90 border border-slate-800/90 rounded-2xl p-4 flex items-start justify-between gap-2 shadow-sm">
                    <div class="space-y-1 min-w-0">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Em Preparo</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl lg:text-3xl font-bold font-mono text-amber-400 tracking-tight" x-text="colunas.em_preparo.length"></span>
                            <span x-show="colunas.novo.length > 0" class="text-[11px] font-bold text-rose-400" x-text="'+' + colunas.novo.length + ' novo(s)'"></span>
                        </div>
                        <p class="text-[11px] text-slate-400 font-medium truncate"
                           x-text="colunas.em_preparo.length === 0 ? 'Cozinha livre agora' : ('SLA médio: ' + (loja?.config_json?.tempo_medio_preparo_min || 35) + ' min')"></p>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-amber-500/15 border border-amber-500/25 text-amber-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path></svg>
                    </div>
                </div>

                <div class="bg-[#0d1322]/90 border border-slate-800/90 rounded-2xl p-4 flex items-start justify-between gap-2 shadow-sm">
                    <div class="space-y-1 min-w-0">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">A Receber / Rua</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl lg:text-3xl font-bold font-mono text-sky-400 tracking-tight" x-text="contadoresFiltros.pendente"></span>
                            <span class="text-[11px] text-slate-400 font-semibold" x-text="colunas.pronto_e_em_rota.length + ' em rota'"></span>
                        </div>
                        <p class="text-[11px] text-slate-400 font-medium truncate"
                           x-text="contadoresFiltros.pago + ' pedido(s) já quitados'"></p>
                    </div>
                    <div class="w-9 h-9 rounded-xl bg-sky-500/15 border border-sky-500/25 text-sky-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Barra de Filtros Desktop (>= md) -->
            <div class="hidden md:flex px-6 py-2 items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
                    <div class="bg-[#0d1322] border border-slate-800/90 p-1 rounded-xl flex items-center gap-1 text-xs shrink-0">
                        <button @click="filtroCanal = 'todos'"
                            class="px-2.5 py-1 rounded-lg font-bold transition flex items-center gap-1.5"
                            :class="filtroCanal === 'todos' ? 'bg-rose-500 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200'">
                            <span>Todos</span>
                            <span class="text-[10px] font-mono font-bold opacity-85" x-text="contadoresFiltros.total"></span>
                        </button>
                        <button @click="filtroCanal = 'delivery'"
                            class="px-2.5 py-1 rounded-lg font-bold transition flex items-center gap-1.5"
                            :class="filtroCanal === 'delivery' ? 'bg-rose-500 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200'">
                            <span>Delivery</span>
                            <span class="text-[10px] font-mono font-bold opacity-85" x-text="contadoresFiltros.delivery"></span>
                        </button>
                        <button @click="filtroCanal = 'balcao_retirada'"
                            class="px-2.5 py-1 rounded-lg font-bold transition flex items-center gap-1.5"
                            :class="filtroCanal === 'balcao_retirada' ? 'bg-rose-500 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200'">
                            <span>Balcão</span>
                            <span class="text-[10px] font-mono font-bold opacity-85" x-text="contadoresFiltros.balcao"></span>
                        </button>
                        <button @click="filtroCanal = 'mesa'"
                            class="px-2.5 py-1 rounded-lg font-bold transition flex items-center gap-1.5"
                            :class="filtroCanal === 'mesa' ? 'bg-rose-500 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200'">
                            <span>Mesas</span>
                            <span class="text-[10px] font-mono font-bold opacity-85" x-text="contadoresFiltros.mesa"></span>
                        </button>
                    </div>

                    <div class="bg-[#0d1322] border border-slate-800/90 p-1 rounded-xl flex items-center gap-1 text-xs shrink-0">
                        <button @click="filtroPagamento = 'todos'"
                            class="px-2.5 py-1 rounded-lg font-bold transition"
                            :class="filtroPagamento === 'todos' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200'">
                            Todos
                        </button>
                        <button @click="filtroPagamento = 'pago'"
                            class="px-2.5 py-1 rounded-lg font-bold transition flex items-center gap-1"
                            :class="filtroPagamento === 'pago' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'text-slate-400 hover:text-slate-200'">
                            <span>Pagos</span>
                            <span class="text-[10px] font-mono font-bold opacity-80" x-text="contadoresFiltros.pago"></span>
                        </button>
                        <button @click="filtroPagamento = 'pendente'"
                            class="px-2.5 py-1 rounded-lg font-bold transition flex items-center gap-1"
                            :class="filtroPagamento === 'pendente' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'text-slate-400 hover:text-slate-200'">
                            <span>A pagar</span>
                            <span class="text-[10px] font-mono font-bold opacity-80" x-text="contadoresFiltros.pendente"></span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <div class="relative w-64">
                        <svg class="w-3.5 h-3.5 text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input type="text" x-model="buscaPedido" placeholder="Buscar #código, cliente ou telefone..."
                            class="w-full bg-[#0d1322] border border-slate-800/90 rounded-xl pl-8 pr-7 py-2 text-xs text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none transition">
                        <button x-show="buscaPedido" @click="buscaPedido = ''" class="absolute right-2.5 top-2 text-slate-500 hover:text-white text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <button @click="alternarAutoImpressao()"
                        title="Ativar impressão automática na impressora térmica 80mm ao aceitar pedidos"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl border text-xs font-bold transition shrink-0"
                        :class="autoImprimir80mm ? 'bg-sky-500/15 border-sky-500/40 text-sky-300' : 'bg-[#0d1322] border-slate-800/90 text-slate-400 hover:text-white'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        <span x-text="autoImprimir80mm ? 'Auto 80mm ON' : 'Auto 80mm'"></span>
                    </button>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- ÁREA DE PEDIDOS: LISTA LIMPA EM TELA CHEIA NO MOBILE & 4 COLUNAS DESKTOP  -->
            <!-- ========================================================================= -->
            <div class="flex-1 min-h-0 overflow-y-auto px-3.5 lg:px-6 py-2.5 pb-28 lg:pb-6 custom-scrollbar">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-start min-h-full">

                    <!-- ==================== COLUNA 1: NOVOS ==================== -->
                    <div :class="abaPedidoMobile === 'novo' ? 'flex' : 'hidden md:flex'"
                         class="md:bg-[#0b101d]/95 md:border md:border-slate-800/80 md:rounded-3xl md:p-3.5 space-y-3 flex-col md:shadow-lg">
                        <div class="hidden md:flex items-center justify-between px-1.5 py-0.5">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-sm shadow-rose-500"></span>
                                <h3 class="font-semibold text-xs uppercase tracking-wider text-white">Novos Pedidos</h3>
                            </div>
                            <span class="min-w-[24px] h-6 px-2 rounded-full bg-rose-500/15 border border-rose-500/30 text-rose-300 font-mono font-bold text-xs flex items-center justify-center"
                                  x-text="colunas.novo.length"></span>
                        </div>

                        <template x-if="colunas.novo.length === 0">
                            <div class="border border-dashed border-slate-800/90 bg-[#080c17]/60 rounded-2xl py-12 px-4 text-center space-y-2">
                                <div class="w-9 h-9 rounded-xl bg-slate-900 border border-slate-800 text-slate-500 flex items-center justify-center mx-auto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                </div>
                                <div class="text-slate-300 text-xs font-bold">Nenhum pedido aguardando</div>
                                <div class="text-[11px] text-slate-500">Novos pedidos tocam alerta sonoro automático</div>
                            </div>
                        </template>

                        <div class="space-y-3">
                            <template x-for="p in colunas.novo" :key="p.id">
                                <div @click="abrirDetalhesPedido(p)"
                                     class="bg-[#111829] border border-rose-500/40 rounded-2xl p-3.5 space-y-3 cursor-pointer hover:border-rose-500 transition shadow-md pulse-rose-glow">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-sm font-mono font-bold text-white bg-rose-500/20 border border-rose-500/30 px-2 py-0.5 rounded-lg" x-text="'#' + p.codigo_publico"></span>
                                            <span class="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-lg bg-slate-800 text-slate-200 border border-slate-700/80"
                                                  x-text="rotuloCanal(p)"></span>
                                        </div>
                                        <span class="text-[11px] font-mono font-bold text-rose-300 bg-rose-950/60 px-2 py-0.5 rounded-lg border border-rose-500/20 inline-flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span x-text="tempoDecorrido(p)"></span>
                                        </span>
                                    </div>

                                    <div class="space-y-1">
                                        <div class="font-semibold text-sm text-white truncate" x-text="nomeClienteOuBalcao(p)"></div>
                                        <template x-if="p.endereco && p.endereco.bairro">
                                            <div class="text-[11px] text-slate-400 truncate flex items-center gap-1">
                                                <svg class="w-3 h-3 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                <span class="truncate" x-text="p.endereco.bairro + (p.endereco.rua ? ' • ' + p.endereco.rua + ', ' + (p.endereco.numero || '') : '')"></span>
                                            </div>
                                        </template>
                                        <div class="text-xs text-slate-300 font-medium line-clamp-2 bg-[#0b101d] border border-slate-800/80 rounded-xl px-2.5 py-1.5" x-text="resumoItens(p)"></div>
                                    </div>

                                    <div class="flex items-center justify-between pt-0.5">
                                        <span class="text-base font-mono font-bold text-white" x-text="formatarCentavos(p.total_centavos)"></span>
                                        <span class="text-[10px] font-semibold uppercase px-2.5 py-0.5 rounded-full border"
                                              :class="p.pagamento_status === 'aprovado' || p.pagamento_status === 'pago' ? 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30' : 'bg-amber-500/15 text-amber-300 border-amber-500/30'"
                                              x-text="(p.pagamento_status === 'aprovado' || p.pagamento_status === 'pago') ? (p.forma_pagamento === 'pix' ? 'Pago PIX' : 'Pago') : (p.forma_pagamento || 'A pagar').toUpperCase()"></span>
                                    </div>

                                    <div class="flex items-center gap-2 pt-0.5">
                                        <button @click.stop="avancarStatus(p)"
                                            class="flex-1 py-2.5 rounded-xl font-bold text-xs bg-gradient-to-r from-[#f43f5e] to-[#e11d48] hover:from-[#fb7185] hover:to-[#f43f5e] text-white transition active:scale-95 shadow-lg shadow-rose-600/25 flex items-center justify-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                            <span>Aceitar e Preparar</span>
                                        </button>
                                        <button @click.stop="imprimirPedido(p.id, 'cozinha')"
                                            title="Imprimir comanda 80mm"
                                            class="px-3 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- ==================== COLUNA 2: EM PREPARO ==================== -->
                    <div :class="abaPedidoMobile === 'em_preparo' ? 'flex' : 'hidden md:flex'"
                         class="md:bg-[#0b101d]/95 md:border md:border-slate-800/80 md:rounded-3xl md:p-3.5 space-y-3 flex-col md:shadow-lg">
                        <div class="hidden md:flex items-center justify-between px-1.5 py-0.5">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-sm shadow-amber-400"></span>
                                <h3 class="font-semibold text-xs uppercase tracking-wider text-white">Na Cozinha</h3>
                            </div>
                            <span class="min-w-[24px] h-6 px-2 rounded-full bg-amber-500/15 border border-amber-500/30 text-amber-300 font-mono font-bold text-xs flex items-center justify-center"
                                  x-text="colunas.em_preparo.length"></span>
                        </div>

                        <template x-if="colunas.em_preparo.length === 0">
                            <div class="border border-dashed border-slate-800/90 bg-[#080c17]/60 rounded-2xl py-12 px-4 text-center space-y-2">
                                <div class="w-9 h-9 rounded-xl bg-slate-900 border border-slate-800 text-slate-500 flex items-center justify-center mx-auto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path></svg>
                                </div>
                                <div class="text-slate-300 text-xs font-bold">Nada em preparo agora</div>
                                <div class="text-[11px] text-slate-500">Pedidos aceitos entram automaticamente no KDS</div>
                            </div>
                        </template>

                        <div class="space-y-3">
                            <template x-for="p in colunas.em_preparo" :key="p.id">
                                <div @click="abrirDetalhesPedido(p)"
                                     class="bg-[#111829] border border-slate-800/90 hover:border-amber-500/40 rounded-2xl p-3.5 space-y-3 cursor-pointer transition shadow-sm">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-sm font-mono font-bold text-white bg-slate-800 px-2 py-0.5 rounded-lg border border-slate-700" x-text="'#' + p.codigo_publico"></span>
                                            <span class="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-lg bg-slate-800/90 text-slate-300"
                                                  x-text="rotuloCanal(p)"></span>
                                        </div>
                                        <span class="text-[11px] font-mono font-bold text-amber-300 bg-amber-500/10 px-2 py-0.5 rounded-lg border border-amber-500/20 inline-flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span x-text="tempoDecorrido(p)"></span>
                                        </span>
                                    </div>

                                    <div class="space-y-1">
                                        <div class="font-semibold text-sm text-white truncate" x-text="nomeClienteOuBalcao(p)"></div>
                                        <div class="text-xs text-slate-300 font-medium line-clamp-2 bg-[#0b101d] border border-slate-800/80 rounded-xl px-2.5 py-1.5" x-text="resumoItens(p)"></div>
                                    </div>

                                    <div class="flex items-center justify-between pt-0.5">
                                        <span class="text-base font-mono font-bold text-white" x-text="formatarCentavos(p.total_centavos)"></span>
                                        <span class="text-[10px] font-semibold uppercase px-2.5 py-0.5 rounded-full border"
                                              :class="p.pagamento_status === 'aprovado' || p.pagamento_status === 'pago' ? 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30' : 'bg-amber-500/15 text-amber-300 border-amber-500/30'"
                                              x-text="(p.pagamento_status === 'aprovado' || p.pagamento_status === 'pago') ? (p.forma_pagamento === 'pix' ? 'Pago PIX' : 'Pago') : 'A pagar'"></span>
                                    </div>

                                    <div class="flex items-center gap-2 pt-0.5">
                                        <button @click.stop="avancarStatus(p)"
                                            class="flex-1 py-2.5 rounded-xl font-bold text-xs bg-amber-500 hover:bg-amber-400 text-slate-950 transition active:scale-95 shadow-md flex items-center justify-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                            <span>Marcar como Pronto</span>
                                        </button>
                                        <button @click.stop="imprimirPedido(p.id, 'cozinha')"
                                            title="Reimprimir comanda 80mm"
                                            class="px-3 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- ==================== COLUNA 3: PRONTO E EM ROTA ==================== -->
                    <div :class="abaPedidoMobile === 'pronto_e_em_rota' ? 'flex' : 'hidden md:flex'"
                         class="md:bg-[#0b101d]/95 md:border md:border-slate-800/80 md:rounded-3xl md:p-3.5 space-y-3 flex-col md:shadow-lg">
                        <div class="hidden md:flex items-center justify-between px-1.5 py-0.5">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-sky-400 shadow-sm shadow-sky-400"></span>
                                <h3 class="font-semibold text-xs uppercase tracking-wider text-white">Pronto &amp; Em Rota</h3>
                            </div>
                            <span class="min-w-[24px] h-6 px-2 rounded-full bg-sky-500/15 border border-sky-500/30 text-sky-300 font-mono font-bold text-xs flex items-center justify-center"
                                  x-text="colunas.pronto_e_em_rota.length"></span>
                        </div>

                        <template x-if="colunas.pronto_e_em_rota.length === 0">
                            <div class="border border-dashed border-slate-800/90 bg-[#080c17]/60 rounded-2xl py-12 px-4 text-center space-y-2">
                                <div class="w-9 h-9 rounded-xl bg-slate-900 border border-slate-800 text-slate-500 flex items-center justify-center mx-auto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                                </div>
                                <div class="text-slate-300 text-xs font-bold">Nenhuma entrega na fila</div>
                                <div class="text-[11px] text-slate-500">Se o cliente avisar "Já recebi" no WhatsApp, o pedido conclui sozinho</div>
                            </div>
                        </template>

                        <div class="space-y-3">
                            <template x-for="p in colunas.pronto_e_em_rota" :key="p.id">
                                <div @click="abrirDetalhesPedido(p)"
                                     class="bg-[#111829] border border-slate-800/90 hover:border-sky-500/40 rounded-2xl p-3.5 space-y-3 cursor-pointer transition shadow-sm">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-sm font-mono font-bold text-white bg-slate-800 px-2 py-0.5 rounded-lg border border-slate-700" x-text="'#' + p.codigo_publico"></span>
                                            <span class="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-lg bg-slate-800/90 text-slate-300"
                                                  x-text="rotuloCanal(p)"></span>
                                        </div>
                                        <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded-lg border"
                                              :class="p.status === 'saiu_entrega' ? 'bg-sky-500/15 text-sky-300 border-sky-500/30' : 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30'"
                                              x-text="p.status === 'saiu_entrega' ? 'Em rota' : 'Pronto'"></span>
                                    </div>

                                    <div class="space-y-1">
                                        <div class="font-semibold text-sm text-white truncate" x-text="nomeClienteOuBalcao(p)"></div>
                                        <template x-if="p.endereco && p.endereco.bairro">
                                            <div class="text-[11px] text-slate-400 truncate flex items-center gap-1">
                                                <svg class="w-3 h-3 text-sky-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                <span class="truncate" x-text="p.endereco.bairro + (p.endereco.rua ? ' • ' + p.endereco.rua + ', ' + (p.endereco.numero || '') : '')"></span>
                                            </div>
                                        </template>
                                        <div class="text-xs text-slate-300 font-medium line-clamp-2 bg-[#0b101d] border border-slate-800/80 rounded-xl px-2.5 py-1.5" x-text="resumoItens(p)"></div>
                                    </div>

                                    <div class="flex items-center justify-between pt-0.5">
                                        <span class="text-base font-mono font-bold text-white" x-text="formatarCentavos(p.total_centavos)"></span>
                                        <span class="text-[10px] font-semibold uppercase px-2.5 py-0.5 rounded-full border"
                                              :class="p.pagamento_status === 'aprovado' || p.pagamento_status === 'pago' ? 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30' : 'bg-amber-500/15 text-amber-300 border-amber-500/30'"
                                              x-text="(p.pagamento_status === 'aprovado' || p.pagamento_status === 'pago') ? (p.forma_pagamento === 'pix' ? 'Pago PIX' : 'Pago') : 'Cobrar na entrega'"></span>
                                    </div>

                                    <div class="flex items-center gap-2 pt-0.5">
                                        <button @click.stop="p.status === 'saiu_entrega' ? avancarStatus(p) : acaoPronto(p)"
                                            class="flex-1 py-2.5 rounded-xl font-bold text-xs bg-sky-400 hover:bg-sky-300 text-slate-950 transition active:scale-95 shadow-md"
                                            x-text="p.status === 'saiu_entrega' ? 'Confirmar Entrega' : (p.canal === 'delivery' ? 'Despachar Motoboy' : 'Entregar Pedido')">
                                        </button>
                                        <button @click.stop="imprimirPedido(p.id, 'entrega')"
                                            title="Imprimir cupom de entrega 80mm"
                                            class="px-3 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- ==================== COLUNA 4: CONCLUÍDOS HOJE ==================== -->
                    <div :class="abaPedidoMobile === 'concluidos' ? 'flex' : 'hidden md:flex'"
                         class="md:bg-[#0b101d]/95 md:border md:border-slate-800/80 md:rounded-3xl md:p-3.5 space-y-3 flex-col md:shadow-lg">
                        <div class="hidden md:flex items-center justify-between px-1.5 py-0.5">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 shadow-sm shadow-emerald-400"></span>
                                <h3 class="font-semibold text-xs uppercase tracking-wider text-white">Concluídos Hoje</h3>
                            </div>
                            <span class="min-w-[24px] h-6 px-2 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 font-mono font-bold text-xs flex items-center justify-center"
                                  x-text="colunas.concluidos.length"></span>
                        </div>

                        <template x-if="colunas.concluidos.length === 0">
                            <div class="border border-dashed border-slate-800/90 bg-[#080c17]/60 rounded-2xl py-12 px-4 text-center space-y-2">
                                <div class="w-9 h-9 rounded-xl bg-slate-900 border border-slate-800 text-slate-500 flex items-center justify-center mx-auto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <div class="text-slate-300 text-xs font-bold">Nenhum pedido finalizado ainda</div>
                                <div class="text-[11px] text-slate-500">Pedidos entregues pelo painel ou WhatsApp aparecem aqui</div>
                            </div>
                        </template>

                        <div class="space-y-2.5">
                            <template x-for="p in colunas.concluidos" :key="p.id">
                                <div @click="abrirDetalhesPedido(p)"
                                     class="bg-[#111829]/80 border border-slate-800/80 rounded-2xl p-3.5 space-y-2 cursor-pointer hover:border-slate-700 hover:bg-[#111829] transition">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs font-mono font-bold text-slate-200" x-text="'#' + p.codigo_publico"></span>
                                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-slate-800 text-slate-400"
                                                  x-text="rotuloCanal(p)"></span>
                                        </div>
                                        <span class="text-[10px] font-bold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-md border border-emerald-500/20">Concluído</span>
                                    </div>

                                    <div>
                                        <div class="font-bold text-xs text-white truncate" x-text="nomeClienteOuBalcao(p)"></div>
                                        <div class="text-[11px] text-slate-400 truncate" x-text="resumoItens(p)"></div>
                                    </div>

                                    <div class="flex items-center justify-between pt-1 border-t border-slate-800/60">
                                        <span class="text-sm font-mono font-bold text-emerald-400" x-text="formatarCentavos(p.total_centavos)"></span>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase" x-text="p.forma_pagamento || 'Pago'"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>
            </div>
        </div>
