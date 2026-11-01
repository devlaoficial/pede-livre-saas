        <div x-show="moduloAtivo === 'pedidos'" class="flex-1 flex flex-col min-h-0 overflow-hidden">

            <!-- Checklist de Ativação Rápida "Sua Loja Pronta em 3 Passos" -->
            <template x-if="!onboardingOculto">
                <div class="bg-gradient-to-r from-slate-900 via-slate-900 to-rose-950/40 border-b border-slate-800 px-4 lg:px-5 py-2.5 flex flex-wrap items-center justify-between gap-3 shrink-0">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="font-black text-white flex items-center gap-1.5">🚀 Guia de Ativação:</span>
                        <button @click="mudarModulo('config')" class="px-2.5 py-1 rounded-lg font-bold flex items-center gap-1 border transition"
                            :class="loja?.nome && loja?.telefone_whatsapp ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-300' : 'bg-slate-800 border-slate-700 text-slate-300'">
                            <span x-text="loja?.nome && loja?.telefone_whatsapp ? '✅' : '1.'"></span> Marca &amp; WhatsApp
                        </button>
                        <button @click="mudarModulo('catalogo')" class="px-2.5 py-1 rounded-lg font-bold flex items-center gap-1 border transition"
                            :class="produtos.length > 0 ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-300' : 'bg-slate-800 border-slate-700 text-slate-300'">
                            <span x-text="produtos.length > 0 ? '✅' : '2.'"></span> Cardápio Ativo
                        </button>
                        <button @click="mudarModulo('financeiro')" class="px-2.5 py-1 rounded-lg font-bold flex items-center gap-1 border transition"
                            :class="mpStatus?.conectado ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-300' : 'bg-slate-800 border-slate-700 text-slate-300'">
                            <span x-text="mpStatus?.conectado ? '✅' : '3.'"></span> PIX 0% Taxa
                        </button>
                        <button @click="copiarLinkCardapio()" class="px-2.5 py-1 rounded-lg font-bold bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 border border-rose-500/30 transition">
                            📲 Copiar Link p/ Instagram
                        </button>
                    </div>
                    <button @click="ocultarOnboarding()" class="text-[11px] text-slate-400 hover:text-white font-bold">Ocultar ✕</button>
                </div>
            </template>

            <!-- Barra Superior: Métricas, Filtros Avançados (Canais / Pagamento) e Ações Rápidas -->
            <div class="bg-slate-900/80 border-b border-slate-800 px-4 lg:px-5 py-2.5 space-y-2 shrink-0">
                <!-- Linha 1: Métricas de Hoje + Auto-impressão + Botão PDV -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar text-xs">
                        <span class="bg-slate-950 border border-slate-800 px-3 py-1 rounded-xl whitespace-nowrap text-slate-300">
                            Hoje: <strong class="text-white" x-text="pedidos.length + ' pedidos'"></strong>
                        </span>
                        <span class="bg-slate-950 border border-slate-800 px-3 py-1 rounded-xl whitespace-nowrap text-slate-300">
                            Faturamento: <strong class="text-emerald-400" x-text="formatarCentavos(volumeDiaCentavos)"></strong>
                        </span>
                        <span class="bg-slate-950 border border-slate-800 px-3 py-1 rounded-xl whitespace-nowrap text-slate-300">
                            Na Cozinha: <strong class="text-amber-400" x-text="colunas.em_preparo.length"></strong>
                        </span>
                        <button @click="alternarAutoImpressao()"
                            class="px-3 py-1 rounded-xl whitespace-nowrap font-bold border transition text-xs flex items-center gap-1.5"
                            :class="autoImprimir80mm ? 'bg-emerald-500/15 border-emerald-500/40 text-emerald-300' : 'bg-slate-950 border-slate-800 text-slate-400 hover:text-slate-200'">
                            <span class="w-1.5 h-1.5 rounded-full" :class="autoImprimir80mm ? 'bg-emerald-400 animate-pulse' : 'bg-slate-600'"></span>
                            <span x-text="autoImprimir80mm ? '🖨️ Auto-Imprimir: ON' : '🖨️ Auto-Imprimir: OFF'"></span>
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <button @click="abrirModalPdv()"
                            class="bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-white font-extrabold text-xs px-3.5 py-1.5 rounded-xl shadow-lg shadow-rose-600/25 flex items-center gap-1.5 active:scale-95 transition">
                            <span>⚡ + Pedido Balcão (PDV)</span>
                            <span class="text-[10px] bg-black/30 px-1.5 py-0.5 rounded font-mono hidden sm:inline">F2</span>
                        </button>
                        <div class="relative w-48 sm:w-60">
                            <input type="text" x-model="buscaPedido" placeholder="Buscar #código ou cliente..."
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-8 pr-7 py-1.5 text-xs text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none transition">
                            <span class="absolute left-2.5 top-1.5 text-slate-500 text-xs">🔍</span>
                            <button x-show="buscaPedido" @click="buscaPedido = ''" class="absolute right-2 top-1.5 text-slate-500 hover:text-white text-xs">✕</button>
                        </div>
                    </div>
                </div>

                <!-- Linha 2: Filtros por Canal de Venda e Status de Pagamento -->
                <div class="flex flex-wrap items-center justify-between gap-2 pt-1 border-t border-slate-800/60 text-xs">
                    <!-- Filtros por Canal -->
                    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider shrink-0 mr-1">Canal:</span>
                        <button @click="filtroCanal = 'todos'"
                            class="px-2.5 py-1 rounded-lg font-bold text-xs transition flex items-center gap-1.5"
                            :class="filtroCanal === 'todos' ? 'bg-slate-700 text-white shadow-sm' : 'bg-slate-950 text-slate-400 hover:text-slate-200 border border-slate-800'">
                            <span>Todos</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="filtroCanal === 'todos' ? 'bg-black/30' : 'bg-slate-800'" x-text="contadoresFiltros.total"></span>
                        </button>
                        <button @click="filtroCanal = 'delivery'"
                            class="px-2.5 py-1 rounded-lg font-bold text-xs transition flex items-center gap-1.5"
                            :class="filtroCanal === 'delivery' ? 'bg-rose-600 text-white shadow-sm' : 'bg-slate-950 text-slate-400 hover:text-slate-200 border border-slate-800'">
                            <span>🛵 Delivery</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="filtroCanal === 'delivery' ? 'bg-black/30' : 'bg-slate-800'" x-text="contadoresFiltros.delivery"></span>
                        </button>
                        <button @click="filtroCanal = 'balcao_retirada'"
                            class="px-2.5 py-1 rounded-lg font-bold text-xs transition flex items-center gap-1.5"
                            :class="filtroCanal === 'balcao_retirada' ? 'bg-amber-600 text-white shadow-sm' : 'bg-slate-950 text-slate-400 hover:text-slate-200 border border-slate-800'">
                            <span>🛍️ Balcão / Retirada</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="filtroCanal === 'balcao_retirada' ? 'bg-black/30' : 'bg-slate-800'" x-text="contadoresFiltros.balcao"></span>
                        </button>
                        <button @click="filtroCanal = 'mesa'"
                            class="px-2.5 py-1 rounded-lg font-bold text-xs transition flex items-center gap-1.5"
                            :class="filtroCanal === 'mesa' ? 'bg-sky-600 text-white shadow-sm' : 'bg-slate-950 text-slate-400 hover:text-slate-200 border border-slate-800'">
                            <span>🪑 Mesas</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="filtroCanal === 'mesa' ? 'bg-black/30' : 'bg-slate-800'" x-text="contadoresFiltros.mesa"></span>
                        </button>
                    </div>

                    <!-- Filtros por Pagamento -->
                    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider shrink-0 mr-1">Pagamento:</span>
                        <button @click="filtroPagamento = 'todos'"
                            class="px-2.5 py-1 rounded-lg font-bold text-xs transition"
                            :class="filtroPagamento === 'todos' ? 'bg-slate-700 text-white' : 'bg-slate-950 text-slate-400 hover:text-slate-200 border border-slate-800'">
                            Todos
                        </button>
                        <button @click="filtroPagamento = 'pago'"
                            class="px-2.5 py-1 rounded-lg font-bold text-xs transition flex items-center gap-1"
                            :class="filtroPagamento === 'pago' ? 'bg-emerald-600 text-white' : 'bg-slate-950 text-emerald-400/80 hover:text-emerald-300 border border-slate-800'">
                            <span>✅ Pagos</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-black/20" x-text="contadoresFiltros.pago"></span>
                        </button>
                        <button @click="filtroPagamento = 'pendente'"
                            class="px-2.5 py-1 rounded-lg font-bold text-xs transition flex items-center gap-1"
                            :class="filtroPagamento === 'pendente' ? 'bg-amber-600 text-white' : 'bg-slate-950 text-amber-400/80 hover:text-amber-300 border border-slate-800'">
                            <span>⏳ A Pagar</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-black/20" x-text="contadoresFiltros.pendente"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Seletor de Abas Mobile (Exibido apenas em telas < lg) -->
            <div class="lg:hidden bg-slate-900 border-b border-slate-800 px-3 py-2 flex overflow-x-auto no-scrollbar gap-1.5 shrink-0">
                <button @click="abaPedidoMobile = 'novo'"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap flex items-center gap-1.5 transition"
                    :class="abaPedidoMobile === 'novo' ? 'bg-rose-600 text-white' : 'bg-slate-800 text-slate-400'">
                    <span>Novos</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/30" x-text="colunas.novo.length"></span>
                </button>
                <button @click="abaPedidoMobile = 'em_preparo'"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap flex items-center gap-1.5 transition"
                    :class="abaPedidoMobile === 'em_preparo' ? 'bg-amber-600 text-white' : 'bg-slate-800 text-slate-400'">
                    <span>Preparo</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/30" x-text="colunas.em_preparo.length"></span>
                </button>
                <button @click="abaPedidoMobile = 'pronto'"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap flex items-center gap-1.5 transition"
                    :class="abaPedidoMobile === 'pronto' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-400'">
                    <span>Prontos</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/30" x-text="colunas.pronto.length"></span>
                </button>
                <button @click="abaPedidoMobile = 'saiu_entrega'"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap flex items-center gap-1.5 transition"
                    :class="abaPedidoMobile === 'saiu_entrega' ? 'bg-sky-600 text-white' : 'bg-slate-800 text-slate-400'">
                    <span>Em Rota</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/30" x-text="colunas.saiu_entrega.length"></span>
                </button>
                <button @click="abaPedidoMobile = 'finalizado'"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap flex items-center gap-1.5 transition"
                    :class="abaPedidoMobile === 'finalizado' ? 'bg-slate-700 text-white' : 'bg-slate-800 text-slate-400'">
                    <span>Concluídos</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/30" x-text="colunas.finalizado.length"></span>
                </button>
            </div>

            <!-- VISÃO MOBILE DE PEDIDOS -->
            <div class="lg:hidden flex-1 overflow-y-auto p-3 pb-24 space-y-3">
                <template x-if="(colunas[abaPedidoMobile] || []).length === 0">
                    <div class="text-center py-16 text-slate-500 space-y-2">
                        <div class="text-3xl">📭</div>
                        <p class="text-sm font-bold">Nenhum pedido correspondente encontrado</p>
                        <p class="text-xs text-slate-600">Altere os filtros acima para visualizar outros pedidos.</p>
                    </div>
                </template>

                <template x-for="pedido in (colunas[abaPedidoMobile] || [])" :key="pedido.id">
                    <div class="bg-slate-900 rounded-2xl p-4 border border-slate-800 space-y-3 shadow-lg">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <button @click="abrirDetalhesPedido(pedido)" class="font-black text-rose-400 text-base hover:underline" x-text="'#' + pedido.codigo_publico"></button>
                                <span class="text-[11px] font-bold uppercase px-2 py-0.5 rounded bg-slate-800 text-slate-300" x-text="pedido.canal"></span>
                                <span class="text-[11px] font-bold uppercase px-2 py-0.5 rounded"
                                    :class="pedido.pagamento_status === 'aprovado' || pedido.pagamento_status === 'pago' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400'"
                                    x-text="(pedido.forma_pagamento || '').toUpperCase() + ' • ' + (pedido.pagamento_status === 'aprovado' || pedido.pagamento_status === 'pago' ? 'PAGO' : 'PENDENTE')"></span>
                            </div>
                            <span class="text-xs text-slate-400" x-text="pedido.tempo_decorrido_minutos + ' min'"></span>
                        </div>

                        <div class="flex justify-between items-center">
                            <div>
                                <div class="font-bold text-white text-sm" x-text="pedido.cliente?.nome || 'Cliente Balcão'"></div>
                                <div class="text-xs text-slate-400" x-text="pedido.cliente?.telefone || ''"></div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button @click="abrirDetalhesPedido(pedido)" class="bg-slate-800 text-slate-200 border border-slate-700 text-xs font-bold px-2.5 py-1.5 rounded-xl">
                                    🔍 Detalhes
                                </button>
                                <template x-if="pedido.whatsapp_notificacao_url">
                                    <a :href="pedido.whatsapp_notificacao_url" target="_blank" class="bg-emerald-600/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold px-3 py-1.5 rounded-xl">
                                        💬 WhatsApp
                                    </a>
                                </template>
                            </div>
                        </div>

                        <template x-if="pedido.observacao">
                            <div class="bg-amber-500/15 border border-amber-500/30 text-amber-300 text-xs font-bold px-3 py-2 rounded-xl" x-text="'⚠️ Obs: ' + pedido.observacao"></div>
                        </template>

                        <div class="bg-slate-950/70 rounded-xl p-3 space-y-1.5 text-xs">
                            <template x-for="item in pedido.itens" :key="item.id">
                                <div>
                                    <div class="flex justify-between font-semibold text-slate-200">
                                        <span x-text="item.qtd + 'x ' + item.nome + (item.variacao ? ' (' + item.variacao + ')' : '')"></span>
                                        <span x-text="formatarCentavos(item.total)"></span>
                                    </div>
                                    <template x-if="item.adicionais && item.adicionais.length > 0">
                                        <div class="text-[11px] text-slate-400 pl-3" x-text="item.adicionais.map(a => '+ ' + a.nome).join(', ')"></div>
                                    </template>
                                    <template x-if="item.observacao">
                                        <div class="text-[11px] text-amber-400 font-bold pl-3" x-text="'Obs: ' + item.observacao"></div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- Despacho Rápido de Motoboy no Mobile se status 'pronto' e canal 'delivery' -->
                        <template x-if="pedido.status === 'pronto' && pedido.canal === 'delivery'">
                            <div class="bg-slate-950 p-2.5 rounded-xl border border-slate-800 space-y-1">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase">🛵 Despachar com Motoboy:</label>
                                <select @change="despacharMotoboyRapido(pedido.id, $event.target.value)" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-white">
                                    <option value="">Selecione o entregador...</option>
                                    <template x-for="ent in entregadores" :key="ent.id">
                                        <option :value="ent.id" :selected="pedido.entregador_id === ent.id" x-text="ent.nome + (ent.telefone ? ' (' + ent.telefone + ')' : '')"></option>
                                    </template>
                                </select>
                            </div>
                        </template>

                        <div class="flex justify-between items-center pt-1">
                            <span class="text-xs text-slate-400 font-bold">Total do Pedido</span>
                            <span class="text-base font-black text-emerald-400" x-text="formatarCentavos(pedido.total_centavos)"></span>
                        </div>

                        <div class="flex gap-2 pt-1">
                            <template x-if="proximoStatus(pedido)">
                                <button @click="avancarPedido(pedido)" class="flex-1 bg-rose-600 hover:bg-rose-500 text-white font-extrabold py-3 rounded-xl text-xs shadow-md active:scale-95 transition">
                                    <span x-text="rotuloProximoStatus(pedido)"></span>
                                </button>
                            </template>
                            <button @click="imprimirPedido(pedido.id, 'cozinha')" class="px-3.5 py-3 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-bold" title="Imprimir Cupom 80mm">
                                🖨️ 80mm
                            </button>
                            <button @click="abrirModalCancelar(pedido)" class="px-3 py-3 bg-slate-800 hover:bg-rose-950 text-slate-400 hover:text-rose-300 rounded-xl text-xs font-bold" title="Cancelar Pedido">
                                ✕
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- VISÃO PC DE PEDIDOS (Quadro Kanban Widescreen de 5 Colunas) -->
            <main class="hidden lg:flex flex-1 overflow-x-auto p-5 gap-4 min-w-full">
                <!-- Coluna 1: Novos Pedidos -->
                <div class="w-80 shrink-0 flex flex-col bg-slate-900/90 rounded-2xl border border-rose-500/30 shadow-xl overflow-hidden">
                    <div class="p-3.5 bg-rose-950/40 border-b border-rose-500/20 flex justify-between items-center">
                        <h2 class="font-bold text-sm text-rose-400 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                            1. Novos Pedidos
                        </h2>
                        <span class="bg-rose-500 text-white text-xs font-extrabold px-2 py-0.5 rounded-full" x-text="colunas.novo.length"></span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 space-y-3">
                        <template x-if="colunas.novo.length === 0">
                            <div class="text-center py-12 text-slate-500 text-xs">
                                Nenhum novo pedido aguardando aceite
                            </div>
                        </template>
                        <template x-for="pedido in colunas.novo" :key="pedido.id">
                            <div class="bg-slate-800/90 rounded-xl p-3.5 border border-rose-500/40 shadow-lg space-y-2.5">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-center gap-1.5">
                                        <button @click="abrirDetalhesPedido(pedido)" class="font-black text-rose-400 text-sm hover:underline" x-text="'#' + pedido.codigo_publico"></button>
                                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-slate-700 text-slate-200" x-text="pedido.canal"></span>
                                    </div>
                                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded"
                                          :class="pedido.pagamento_status === 'aprovado' || pedido.pagamento_status === 'pago' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400'"
                                          x-text="(pedido.forma_pagamento || '').toUpperCase() + ' • ' + (pedido.pagamento_status === 'aprovado' || pedido.pagamento_status === 'pago' ? 'PAGO' : 'A PAGAR')"></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="font-bold text-slate-100 text-sm truncate" x-text="pedido.cliente?.nome || 'Cliente'"></div>
                                        <div class="text-xs text-slate-400" x-text="pedido.cliente?.telefone || ''"></div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button @click="abrirDetalhesPedido(pedido)" class="text-xs bg-slate-700 hover:bg-slate-600 text-slate-200 px-2 py-1 rounded-lg font-bold" title="Ver Detalhes e Endereço">🔍</button>
                                        <template x-if="pedido.whatsapp_notificacao_url">
                                            <a :href="pedido.whatsapp_notificacao_url" target="_blank" class="text-xs bg-emerald-500/20 text-emerald-400 px-2 py-1 rounded-lg font-bold" title="Conversar no WhatsApp">💬</a>
                                        </template>
                                    </div>
                                </div>
                                <template x-if="pedido.observacao">
                                    <div class="bg-amber-500/15 border border-amber-500/30 text-amber-300 text-[11px] font-bold px-2.5 py-1.5 rounded-lg" x-text="'⚠️ Obs: ' + pedido.observacao"></div>
                                </template>
                                <div class="text-xs text-slate-300 space-y-1 bg-slate-900/60 p-2.5 rounded-lg cursor-pointer" @click="abrirDetalhesPedido(pedido)">
                                    <template x-for="item in pedido.itens" :key="item.id">
                                        <div>
                                            <div class="flex justify-between">
                                                <span class="font-semibold" x-text="item.qtd + 'x ' + item.nome + (item.variacao ? ' (' + item.variacao + ')' : '')"></span>
                                                <span class="font-bold" x-text="formatarCentavos(item.total)"></span>
                                            </div>
                                            <template x-if="item.adicionais?.length > 0">
                                                <div class="text-[10px] text-slate-400 pl-2" x-text="item.adicionais.map(a => '+ ' + a.nome).join(', ')"></div>
                                            </template>
                                            <template x-if="item.observacao">
                                                <div class="text-[10px] text-amber-400 font-bold pl-2" x-text="'Obs: ' + item.observacao"></div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex justify-between items-center pt-1 border-t border-slate-700/50">
                                    <span class="text-xs font-bold text-slate-400" x-text="pedido.tempo_decorrido_minutos + ' min atrás'"></span>
                                    <span class="text-sm font-black text-emerald-400" x-text="formatarCentavos(pedido.total_centavos)"></span>
                                </div>
                                <div class="flex gap-1.5">
                                    <button @click="avancarPedido(pedido)" class="flex-1 bg-rose-600 hover:bg-rose-700 text-white font-bold py-2 rounded-lg text-xs shadow-md transition">
                                        ✓ Aceitar
                                    </button>
                                    <button @click="imprimirPedido(pedido.id, 'cozinha')" class="px-2.5 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg text-xs font-bold" title="Imprimir 80mm">
                                        🖨️
                                    </button>
                                    <button @click="abrirModalCancelar(pedido)" class="px-2.5 py-2 bg-slate-700 hover:bg-rose-900 text-slate-400 hover:text-rose-200 rounded-lg text-xs font-bold" title="Cancelar Pedido">
                                        ✕
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Coluna 2: Em Preparo (Cozinha / Montagem) -->
                <div class="w-80 shrink-0 flex flex-col bg-slate-900/90 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
                    <div class="p-3.5 bg-amber-950/20 border-b border-amber-500/20 flex justify-between items-center">
                        <h2 class="font-bold text-sm text-amber-400 flex items-center gap-1.5">
                            <span>🍳</span> 2. Em Preparo
                        </h2>
                        <span class="bg-amber-500/20 text-amber-400 text-xs font-extrabold px-2 py-0.5 rounded-full" x-text="colunas.em_preparo.length"></span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 space-y-3">
                        <template x-if="colunas.em_preparo.length === 0">
                            <div class="text-center py-12 text-slate-500 text-xs">
                                Nenhum pedido em preparo
                            </div>
                        </template>
                        <template x-for="pedido in colunas.em_preparo" :key="pedido.id">
                            <div class="bg-slate-800 rounded-xl p-3.5 border border-slate-700 space-y-2.5">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-center gap-1.5">
                                        <button @click="abrirDetalhesPedido(pedido)" class="font-bold text-amber-400 text-sm hover:underline" x-text="'#' + pedido.codigo_publico"></button>
                                        <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-slate-700 text-slate-300" x-text="pedido.canal"></span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs text-slate-400" x-text="pedido.tempo_decorrido_minutos + ' min'"></span>
                                        <button @click="abrirDetalhesPedido(pedido)" class="text-xs bg-slate-700 px-1.5 py-0.5 rounded">🔍</button>
                                    </div>
                                </div>
                                <div class="font-bold text-sm text-slate-200" x-text="pedido.cliente?.nome"></div>
                                <div class="text-xs text-slate-300 space-y-1 bg-slate-900/50 p-2 rounded-lg cursor-pointer" @click="abrirDetalhesPedido(pedido)">
                                    <template x-for="item in pedido.itens" :key="item.id">
                                        <div>
                                            <span x-text="item.qtd + 'x ' + item.nome + (item.variacao ? ' (' + item.variacao + ')' : '')"></span>
                                            <template x-if="item.observacao">
                                                <div class="text-[10px] text-amber-400 font-bold" x-text="'⚠️ ' + item.observacao"></div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex gap-2">
                                    <button @click="avancarPedido(pedido)" class="flex-1 bg-amber-600 hover:bg-amber-500 text-white font-bold py-2 rounded-lg text-xs transition">
                                        <span x-text="rotuloProximoStatus(pedido)"></span>
                                    </button>
                                    <button @click="imprimirPedido(pedido.id, 'cozinha')" class="px-2.5 py-2 bg-slate-700 text-slate-200 rounded-lg text-xs" title="Imprimir Cupom Cozinha">🖨️</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Coluna 3: Prontos p/ Despacho / Retirada -->
                <div class="w-80 shrink-0 flex flex-col bg-slate-900/90 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
                    <div class="p-3.5 bg-emerald-950/20 border-b border-emerald-500/20 flex justify-between items-center">
                        <h2 class="font-bold text-sm text-emerald-400 flex items-center gap-1.5">
                            <span>📦</span> 3. Prontos p/ Despacho
                        </h2>
                        <span class="bg-emerald-500/20 text-emerald-400 text-xs font-extrabold px-2 py-0.5 rounded-full" x-text="colunas.pronto.length"></span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 space-y-3">
                        <template x-if="colunas.pronto.length === 0">
                            <div class="text-center py-12 text-slate-500 text-xs">
                                Nenhum pedido pronto aguardando despacho
                            </div>
                        </template>
                        <template x-for="pedido in colunas.pronto" :key="pedido.id">
                            <div class="bg-slate-800 rounded-xl p-3.5 border border-slate-700 space-y-2.5">
                                <div class="flex justify-between items-start">
                                    <button @click="abrirDetalhesPedido(pedido)" class="font-bold text-emerald-400 text-sm hover:underline" x-text="'#' + pedido.codigo_publico"></button>
                                    <div class="flex items-center gap-1">
                                        <span class="text-[11px] uppercase px-2 py-0.5 rounded bg-slate-700 text-slate-300" x-text="pedido.canal"></span>
                                        <button @click="abrirDetalhesPedido(pedido)" class="text-xs bg-slate-700 px-1.5 py-0.5 rounded">🔍</button>
                                    </div>
                                </div>
                                <div class="font-bold text-sm text-slate-200" x-text="pedido.cliente?.nome"></div>

                                <!-- Seleção rápida de motoboy com despacho automático -->
                                <template x-if="pedido.canal === 'delivery'">
                                    <div class="bg-slate-900/90 p-2.5 rounded-xl border border-slate-700/80 space-y-1">
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">🛵 Despachar com Motoboy:</label>
                                        <select @change="despacharMotoboyRapido(pedido.id, $event.target.value)"
                                            class="w-full bg-slate-950 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-200 focus:border-rose-500 focus:outline-none">
                                            <option value="">Selecione o entregador...</option>
                                            <template x-for="ent in entregadores" :key="ent.id">
                                                <option :value="ent.id" :selected="pedido.entregador_id === ent.id" x-text="ent.nome + (ent.telefone ? ' (' + ent.telefone + ')' : '')"></option>
                                            </template>
                                        </select>
                                    </div>
                                </template>

                                <button @click="avancarPedido(pedido)" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2 rounded-lg text-xs transition">
                                    <span x-text="rotuloProximoStatus(pedido)"></span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Coluna 4: Saiu para Entrega (Em Rota) -->
                <div class="w-80 shrink-0 flex flex-col bg-slate-900/90 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
                    <div class="p-3.5 bg-sky-950/20 border-b border-sky-500/20 flex justify-between items-center">
                        <h2 class="font-bold text-sm text-sky-400 flex items-center gap-1.5">
                            <span>🛵</span> 4. Em Rota de Entrega
                        </h2>
                        <span class="bg-sky-500/20 text-sky-400 text-xs font-extrabold px-2 py-0.5 rounded-full" x-text="colunas.saiu_entrega.length"></span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 space-y-3">
                        <template x-if="colunas.saiu_entrega.length === 0">
                            <div class="text-center py-12 text-slate-500 text-xs">
                                Nenhum pedido em trânsito no momento
                            </div>
                        </template>
                        <template x-for="pedido in colunas.saiu_entrega" :key="pedido.id">
                            <div class="bg-slate-800 rounded-xl p-3.5 border border-slate-700 space-y-2.5">
                                <div class="flex justify-between items-start">
                                    <button @click="abrirDetalhesPedido(pedido)" class="font-bold text-sky-400 text-sm hover:underline" x-text="'#' + pedido.codigo_publico"></button>
                                    <span class="text-xs text-slate-400">🛵 Em trânsito</span>
                                </div>
                                <div class="font-bold text-sm text-slate-200" x-text="pedido.cliente?.nome"></div>
                                <template x-if="pedido.entregador?.nome">
                                    <div class="text-xs text-sky-300 font-semibold bg-sky-950/40 px-2 py-1 rounded-lg border border-sky-500/20">
                                        Entregador: <span x-text="pedido.entregador.nome"></span>
                                    </div>
                                </template>
                                <button @click="avancarPedido(pedido)" class="w-full bg-sky-600 hover:bg-sky-500 text-white font-bold py-2 rounded-lg text-xs transition">
                                    ✓ Confirmar Entrega Realizada
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Coluna 5: Concluídos Hoje -->
                <div class="w-72 shrink-0 flex flex-col bg-slate-900/50 rounded-2xl border border-slate-800/80 overflow-hidden">
                    <div class="p-3.5 border-b border-slate-800 flex justify-between items-center">
                        <h2 class="font-bold text-sm text-slate-400 flex items-center gap-1.5">
                            <span>✅</span> 5. Concluídos Hoje
                        </h2>
                        <span class="bg-slate-800 text-slate-400 text-xs font-bold px-2 py-0.5 rounded-full" x-text="colunas.finalizado.length"></span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 space-y-2">
                        <template x-if="colunas.finalizado.length === 0">
                            <div class="text-center py-12 text-slate-500 text-xs">
                                Nenhum pedido concluído ainda hoje
                            </div>
                        </template>
                        <template x-for="pedido in colunas.finalizado" :key="pedido.id">
                            <div @click="abrirDetalhesPedido(pedido)" class="bg-slate-900 hover:bg-slate-800 cursor-pointer rounded-xl p-3 border border-slate-800/80 flex justify-between items-center opacity-85 transition">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-xs text-slate-300" x-text="'#' + pedido.codigo_publico"></span>
                                        <span class="text-[10px] uppercase font-bold text-slate-500" x-text="pedido.canal"></span>
                                    </div>
                                    <span class="text-xs text-slate-400 block truncate max-w-[130px]" x-text="pedido.cliente?.nome"></span>
                                </div>
                                <span class="text-xs font-bold text-emerald-400" x-text="formatarCentavos(pedido.total_centavos)"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </main>
        </div>