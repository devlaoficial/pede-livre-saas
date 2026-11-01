        <div x-show="moduloAtivo === 'pedidos'" class="flex-1 flex flex-col min-h-0 overflow-hidden bg-[#0b0f19]">

            <!-- 1. 4 Cards de Métricas (Design Claude Fiel) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 p-4 lg:p-6 pb-2 shrink-0">
                <!-- Card 1: Pedidos hoje -->
                <div class="bg-[#131927] border border-[#1e2638] rounded-2xl p-4 lg:p-5 space-y-1">
                    <span class="text-xs font-bold text-slate-400">Pedidos hoje</span>
                    <div class="text-2xl lg:text-3xl font-black text-white" x-text="pedidos.length"></div>
                    <p class="text-[11px] text-slate-500 font-medium"
                       x-text="(pedidos.filter(p => p.canal === 'delivery').length || 0) + ' em delivery'"></p>
                </div>

                <!-- Card 2: Faturamento -->
                <div class="bg-[#131927] border border-[#1e2638] rounded-2xl p-4 lg:p-5 space-y-1">
                    <span class="text-xs font-bold text-slate-400">Faturamento</span>
                    <div class="text-2xl lg:text-3xl font-black text-emerald-400" x-text="formatarCentavos(volumeDiaCentavos)"></div>
                    <p class="text-[11px] text-slate-500 font-medium"
                       x-text="'Ticket médio ' + formatarCentavos(pedidos.length ? Math.round(volumeDiaCentavos / pedidos.length) : 0)"></p>
                </div>

                <!-- Card 3: Em preparo -->
                <div class="bg-[#131927] border border-[#1e2638] rounded-2xl p-4 lg:p-5 space-y-1">
                    <span class="text-xs font-bold text-slate-400">Em preparo</span>
                    <div class="text-2xl lg:text-3xl font-black text-white" x-text="colunas.em_preparo.length"></div>
                    <p class="text-[11px] text-slate-500 font-medium"
                       x-text="colunas.em_preparo.length === 0 ? 'Cozinha livre' : colunas.em_preparo.length + ' na cozinha'"></p>
                </div>

                <!-- Card 4: A receber -->
                <div class="bg-[#131927] border border-[#1e2638] rounded-2xl p-4 lg:p-5 space-y-1">
                    <span class="text-xs font-bold text-slate-400">A receber</span>
                    <div class="text-2xl lg:text-3xl font-black text-amber-400" x-text="contadoresFiltros.pendente"></div>
                    <p class="text-[11px] text-slate-500 font-medium">Pagamento pendente</p>
                </div>
            </div>

            <!-- 2. Barra de Filtros e Busca (Design Claude Fiel) -->
            <div class="px-4 lg:px-6 py-2 flex flex-col md:flex-row md:items-center justify-between gap-3 shrink-0">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Chips Canal -->
                    <div class="bg-[#131927] border border-[#1e2638] p-1 rounded-xl flex items-center gap-1 text-xs">
                        <button @click="filtroCanal = 'todos'"
                            class="px-3 py-1 rounded-lg font-bold transition flex items-center gap-1.5"
                            :class="filtroCanal === 'todos' ? 'bg-[#1e2638] text-white shadow-sm' : 'text-slate-400 hover:text-slate-200'">
                            <span>Todos</span>
                            <span class="text-[10px] font-black opacity-80" x-text="contadoresFiltros.total"></span>
                        </button>
                        <button @click="filtroCanal = 'delivery'"
                            class="px-3 py-1 rounded-lg font-bold transition flex items-center gap-1.5"
                            :class="filtroCanal === 'delivery' ? 'bg-[#1e2638] text-white shadow-sm' : 'text-slate-400 hover:text-slate-200'">
                            <span>Delivery</span>
                            <span class="text-[10px] font-black opacity-80" x-text="contadoresFiltros.delivery"></span>
                        </button>
                        <button @click="filtroCanal = 'balcao_retirada'"
                            class="px-3 py-1 rounded-lg font-bold transition flex items-center gap-1.5"
                            :class="filtroCanal === 'balcao_retirada' ? 'bg-[#1e2638] text-white shadow-sm' : 'text-slate-400 hover:text-slate-200'">
                            <span>Balcão</span>
                            <span class="text-[10px] font-black opacity-80" x-text="contadoresFiltros.balcao"></span>
                        </button>
                        <button @click="filtroCanal = 'mesa'"
                            class="px-3 py-1 rounded-lg font-bold transition flex items-center gap-1.5"
                            :class="filtroCanal === 'mesa' ? 'bg-[#1e2638] text-white shadow-sm' : 'text-slate-400 hover:text-slate-200'">
                            <span>Mesas</span>
                            <span class="text-[10px] font-black opacity-80" x-text="contadoresFiltros.mesa"></span>
                        </button>
                    </div>

                    <!-- Chips Pagamento -->
                    <div class="bg-[#131927] border border-[#1e2638] p-1 rounded-xl flex items-center gap-1 text-xs">
                        <button @click="filtroPagamento = 'todos'"
                            class="px-3 py-1 rounded-lg font-bold transition"
                            :class="filtroPagamento === 'todos' ? 'bg-[#1e2638] text-white shadow-sm' : 'text-slate-400 hover:text-slate-200'">
                            Todos
                        </button>
                        <button @click="filtroPagamento = 'pago'"
                            class="px-3 py-1 rounded-lg font-bold transition flex items-center gap-1.5"
                            :class="filtroPagamento === 'pago' ? 'bg-[#1e2638] text-white shadow-sm' : 'text-slate-400 hover:text-slate-200'">
                            <span>Pagos</span>
                            <span class="text-[10px] font-black opacity-80" x-text="contadoresFiltros.pago"></span>
                        </button>
                        <button @click="filtroPagamento = 'pendente'"
                            class="px-3 py-1 rounded-lg font-bold transition flex items-center gap-1.5"
                            :class="filtroPagamento === 'pendente' ? 'bg-[#1e2638] text-white shadow-sm' : 'text-slate-400 hover:text-slate-200'">
                            <span>A pagar</span>
                            <span class="text-[10px] font-black opacity-80" x-text="contadoresFiltros.pendente"></span>
                        </button>
                    </div>
                </div>

                <!-- Input de Busca -->
                <div class="relative w-full md:w-64">
                    <span class="absolute left-3 top-2.5 text-slate-500 text-xs">🔍</span>
                    <input type="text" x-model="buscaPedido" placeholder="Buscar pedido ou cliente"
                        class="w-full bg-[#131927] border border-[#1e2638] rounded-xl pl-8 pr-7 py-2 text-xs text-white placeholder-slate-500 focus:border-[#f43f5e] focus:outline-none transition">
                    <button x-show="buscaPedido" @click="buscaPedido = ''" class="absolute right-2.5 top-2 text-slate-500 hover:text-white text-xs">✕</button>
                </div>
            </div>

            <!-- 3. Grid Kanban em 4 Colunas (Design Claude Fiel) -->
            <div class="flex-1 min-h-0 overflow-y-auto px-4 lg:px-6 py-3 pb-24 lg:pb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-start min-h-full">

                    <!-- COLUNA 1: NOVOS -->
                    <div class="bg-[#0f1422] border border-[#192136] rounded-3xl p-4 space-y-3.5 flex flex-col">
                        <!-- Header da Coluna -->
                        <div class="flex items-center justify-between px-1">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#f43f5e]"></span>
                                <h3 class="font-extrabold text-sm text-white">Novos</h3>
                            </div>
                            <span class="w-5 h-5 rounded-full bg-[#1e2638] text-slate-300 font-bold text-xs flex items-center justify-center"
                                  x-text="colunas.novo.length"></span>
                        </div>

                        <!-- Empty State (Imagem 2) -->
                        <template x-if="colunas.novo.length === 0">
                            <div class="border border-dashed border-[#1e2638] rounded-2xl py-14 px-4 text-center text-slate-500 text-xs font-medium">
                                Nenhum pedido novo
                            </div>
                        </template>

                        <!-- Lista de Cards -->
                        <div class="space-y-3">
                            <template x-for="p in colunas.novo" :key="p.id">
                                <div @click="abrirDetalhesPedido(p)"
                                     class="bg-[#141a29] border border-[#1e2638] rounded-2xl p-4 space-y-3 cursor-pointer hover:border-slate-600 transition shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-black text-white" x-text="'#' + p.codigo_publico"></span>
                                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-[#1e2638] text-slate-300"
                                                  x-text="rotuloCanal(p)"></span>
                                        </div>
                                        <span class="text-xs text-slate-400 font-medium" x-text="tempoDecorrido(p)"></span>
                                    </div>

                                    <div>
                                        <div class="font-bold text-sm text-white" x-text="nomeClienteOuBalcao(p)"></div>
                                        <div class="text-xs text-slate-400 truncate" x-text="resumoItens(p)"></div>
                                    </div>

                                    <div class="flex items-center justify-between pt-1">
                                        <span class="text-base font-black text-white" x-text="formatarCentavos(p.total_centavos)"></span>
                                        <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full border"
                                              :class="p.pagamento_status === 'aprovado' || p.pagamento_status === 'pago' ? 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20' : 'bg-amber-500/15 text-amber-400 border-amber-500/20'"
                                              x-text="(p.pagamento_status === 'aprovado' || p.pagamento_status === 'pago') ? (p.forma_pagamento === 'pix' ? 'Pago PIX' : 'Pago') : 'A pagar'"></span>
                                    </div>

                                    <!-- Botão de Ação: Aceitar Pedido -->
                                    <button @click.stop="avancarStatus(p)"
                                        class="w-full py-2.5 rounded-xl font-black text-xs bg-[#fb7185] hover:bg-[#f43f5e] text-slate-950 transition active:scale-95 shadow-md">
                                        Aceitar pedido
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- COLUNA 2: EM PREPARO -->
                    <div class="bg-[#0f1422] border border-[#192136] rounded-3xl p-4 space-y-3.5 flex flex-col">
                        <!-- Header da Coluna -->
                        <div class="flex items-center justify-between px-1">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#f59e0b]"></span>
                                <h3 class="font-extrabold text-sm text-white">Em preparo</h3>
                            </div>
                            <span class="w-5 h-5 rounded-full bg-[#1e2638] text-slate-300 font-bold text-xs flex items-center justify-center"
                                  x-text="colunas.em_preparo.length"></span>
                        </div>

                        <!-- Empty State (Imagem 2) -->
                        <template x-if="colunas.em_preparo.length === 0">
                            <div class="border border-dashed border-[#1e2638] rounded-2xl py-14 px-4 text-center text-slate-500 text-xs font-medium">
                                Nada na cozinha agora
                            </div>
                        </template>

                        <!-- Lista de Cards -->
                        <div class="space-y-3">
                            <template x-for="p in colunas.em_preparo" :key="p.id">
                                <div @click="abrirDetalhesPedido(p)"
                                     class="bg-[#141a29] border border-[#1e2638] rounded-2xl p-4 space-y-3 cursor-pointer hover:border-slate-600 transition shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-black text-white" x-text="'#' + p.codigo_publico"></span>
                                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-[#1e2638] text-slate-300"
                                                  x-text="rotuloCanal(p)"></span>
                                        </div>
                                        <span class="text-xs text-slate-400 font-medium" x-text="tempoDecorrido(p)"></span>
                                    </div>

                                    <div>
                                        <div class="font-bold text-sm text-white" x-text="nomeClienteOuBalcao(p)"></div>
                                        <div class="text-xs text-slate-400 truncate" x-text="resumoItens(p)"></div>
                                    </div>

                                    <div class="flex items-center justify-between pt-1">
                                        <span class="text-base font-black text-white" x-text="formatarCentavos(p.total_centavos)"></span>
                                        <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full border"
                                              :class="p.pagamento_status === 'aprovado' || p.pagamento_status === 'pago' ? 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20' : 'bg-amber-500/15 text-amber-400 border-amber-500/20'"
                                              x-text="(p.pagamento_status === 'aprovado' || p.pagamento_status === 'pago') ? (p.forma_pagamento === 'pix' ? 'Pago PIX' : 'Pago') : 'A pagar'"></span>
                                    </div>

                                    <!-- Botão de Ação: Marcar como pronto -->
                                    <button @click.stop="avancarStatus(p)"
                                        class="w-full py-2.5 rounded-xl font-black text-xs bg-[#f59e0b] hover:bg-[#d97706] text-slate-950 transition active:scale-95 shadow-md">
                                        Marcar como pronto
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- COLUNA 3: PRONTO E EM ROTA -->
                    <div class="bg-[#0f1422] border border-[#192136] rounded-3xl p-4 space-y-3.5 flex flex-col">
                        <!-- Header da Coluna -->
                        <div class="flex items-center justify-between px-1">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#38bdf8]"></span>
                                <h3 class="font-extrabold text-sm text-white">Pronto e em rota</h3>
                            </div>
                            <span class="w-5 h-5 rounded-full bg-[#1e2638] text-slate-300 font-bold text-xs flex items-center justify-center"
                                  x-text="colunas.pronto_e_em_rota.length"></span>
                        </div>

                        <!-- Empty State (Imagem 2) -->
                        <template x-if="colunas.pronto_e_em_rota.length === 0">
                            <div class="border border-dashed border-[#1e2638] rounded-2xl py-14 px-4 text-center text-slate-500 text-xs font-medium">
                                Nenhum pedido para despachar
                            </div>
                        </template>

                        <!-- Lista de Cards -->
                        <div class="space-y-3">
                            <template x-for="p in colunas.pronto_e_em_rota" :key="p.id">
                                <div @click="abrirDetalhesPedido(p)"
                                     class="bg-[#141a29] border border-[#1e2638] rounded-2xl p-4 space-y-3 cursor-pointer hover:border-slate-600 transition shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-black text-white" x-text="'#' + p.codigo_publico"></span>
                                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-[#1e2638] text-slate-300"
                                                  x-text="rotuloCanal(p)"></span>
                                        </div>
                                        <span class="text-xs text-slate-400 font-medium"
                                              x-text="p.status === 'saiu_entrega' ? 'Em rota' : 'Pronto'"></span>
                                    </div>

                                    <div>
                                        <div class="font-bold text-sm text-white" x-text="nomeClienteOuBalcao(p)"></div>
                                        <div class="text-xs text-slate-400 truncate" x-text="resumoItens(p)"></div>
                                    </div>

                                    <div class="flex items-center justify-between pt-1">
                                        <span class="text-base font-black text-white" x-text="formatarCentavos(p.total_centavos)"></span>
                                        <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full border"
                                              :class="p.pagamento_status === 'aprovado' || p.pagamento_status === 'pago' ? 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20' : 'bg-amber-500/15 text-amber-400 border-amber-500/20'"
                                              x-text="(p.pagamento_status === 'aprovado' || p.pagamento_status === 'pago') ? (p.forma_pagamento === 'pix' ? 'Pago PIX' : 'Pago') : 'A pagar'"></span>
                                    </div>

                                    <!-- Botão de Ação: Chamar Motoboy ou Confirmar Entrega -->
                                    <button @click.stop="p.status === 'saiu_entrega' ? avancarStatus(p) : acaoPronto(p)"
                                        class="w-full py-2.5 rounded-xl font-black text-xs bg-[#38bdf8] hover:bg-[#0ea5e9] text-slate-950 transition active:scale-95 shadow-md"
                                        x-text="p.status === 'saiu_entrega' ? 'Confirmar entrega' : (p.canal === 'delivery' ? 'Chamar motoboy' : 'Entregar pedido')">
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- COLUNA 4: CONCLUÍDOS HOJE -->
                    <div class="bg-[#0f1422] border border-[#192136] rounded-3xl p-4 space-y-3.5 flex flex-col">
                        <!-- Header da Coluna -->
                        <div class="flex items-center justify-between px-1">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#10b981]"></span>
                                <h3 class="font-extrabold text-sm text-white">Concluídos hoje</h3>
                            </div>
                            <span class="w-5 h-5 rounded-full bg-[#1e2638] text-slate-300 font-bold text-xs flex items-center justify-center"
                                  x-text="colunas.concluidos.length"></span>
                        </div>

                        <!-- Empty State (Imagem 2) -->
                        <template x-if="colunas.concluidos.length === 0">
                            <div class="border border-dashed border-[#1e2638] rounded-2xl py-14 px-4 text-center text-slate-500 text-xs font-medium">
                                Nenhum pedido concluído hoje
                            </div>
                        </template>

                        <!-- Lista de Cards Concluídos -->
                        <div class="space-y-3">
                            <template x-for="p in colunas.concluidos" :key="p.id">
                                <div @click="abrirDetalhesPedido(p)"
                                     class="bg-[#141a29] border border-[#1e2638] rounded-2xl p-4 space-y-2 cursor-pointer hover:border-slate-600 transition shadow-sm opacity-90 hover:opacity-100">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-black text-white" x-text="'#' + p.codigo_publico"></span>
                                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-[#1e2638] text-slate-300"
                                                  x-text="rotuloCanal(p)"></span>
                                        </div>
                                        <span class="text-xs text-slate-400 font-medium">Hoje</span>
                                    </div>

                                    <div>
                                        <div class="font-bold text-sm text-white" x-text="nomeClienteOuBalcao(p)"></div>
                                        <div class="text-xs text-slate-400 truncate">Pedido concluído</div>
                                    </div>

                                    <div class="flex items-center justify-between pt-1">
                                        <span class="text-base font-black text-white" x-text="formatarCentavos(p.total_centavos)"></span>
                                        <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full border"
                                              :class="p.pagamento_status === 'aprovado' || p.pagamento_status === 'pago' ? 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20' : 'bg-amber-500/15 text-amber-400 border-amber-500/20'"
                                              x-text="(p.pagamento_status === 'aprovado' || p.pagamento_status === 'pago') ? (p.forma_pagamento === 'pix' ? 'Pago PIX' : 'Pago') : 'A pagar'"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>
            </div>
        </div>
