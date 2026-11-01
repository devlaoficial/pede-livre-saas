        <div x-show="moduloAtivo === 'pedidos'" class="flex-1 flex flex-col min-h-0 overflow-hidden">

            <!-- Checklist de Ativação Rápida "Sua Loja Pronta em 3 Passos" (Etapa 5) -->
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

            <!-- Faixa de Resumo Rápido + Auto-Impressão 80mm + Busca de Pedidos -->
            <div class="bg-slate-900/60 border-b border-slate-800 px-4 lg:px-5 py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 shrink-0">
                <div class="flex items-center gap-2.5 overflow-x-auto no-scrollbar text-xs">
                    <span class="bg-slate-900 border border-slate-800 px-3 py-1 rounded-xl whitespace-nowrap">
                        Hoje: <strong class="text-white" x-text="pedidos.length + ' pedidos'"></strong>
                    </span>
                    <span class="bg-slate-900 border border-slate-800 px-3 py-1 rounded-xl whitespace-nowrap">
                        Volume: <strong class="text-emerald-400" x-text="formatarCentavos(volumeDiaCentavos)"></strong>
                    </span>
                    <span class="bg-slate-900 border border-slate-800 px-3 py-1 rounded-xl whitespace-nowrap">
                        Na Cozinha: <strong class="text-amber-400" x-text="colunas.em_preparo.length"></strong>
                    </span>
                    <button @click="alternarAutoImpressao()"
                        class="px-3 py-1 rounded-xl whitespace-nowrap font-bold border transition"
                        :class="autoImprimir80mm ? 'bg-emerald-500/15 border-emerald-500/40 text-emerald-300' : 'bg-slate-900 border-slate-800 text-slate-400'">
                        <span x-text="autoImprimir80mm ? '🖨️ Auto-Imprimir 80mm: ON' : '🖨️ Auto-Imprimir 80mm: OFF'"></span>
                    </button>
                </div>
                <div class="relative sm:w-64">
                    <input type="text" x-model="buscaPedido" placeholder="Buscar #código ou cliente..."
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-8 pr-3 py-1.5 text-xs text-white focus:border-rose-500 focus:outline-none">
                    <span class="absolute left-2.5 top-1.5 text-slate-500 text-xs">🔍</span>
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
                        <p class="text-sm font-bold">Nenhum pedido nesta etapa</p>
                    </div>
                </template>

                <template x-for="pedido in (colunas[abaPedidoMobile] || [])" :key="pedido.id">
                    <div class="bg-slate-900 rounded-2xl p-4 border border-slate-800 space-y-3 shadow-lg">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <button @click="abrirDetalhesPedido(pedido)" class="font-black text-rose-400 text-base hover:underline" x-text="'#' + pedido.codigo_publico"></button>
                                <span class="text-[11px] font-bold uppercase px-2 py-0.5 rounded bg-slate-800 text-slate-300" x-text="pedido.canal"></span>
                                <span class="text-[11px] font-bold uppercase px-2 py-0.5 rounded"
                                    :class="pedido.pagamento_status === 'aprovado' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400'"
                                    x-text="pedido.forma_pagamento + ' • ' + pedido.pagamento_status"></span>
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
                            <button @click="imprimirPedido(pedido.id, 'cozinha')" class="px-3.5 py-3 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-bold">
                                🖨️ 80mm
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- VISÃO PC DE PEDIDOS (Quadro Kanban Widescreen de 5 Colunas) -->
            <main class="hidden lg:flex flex-1 overflow-x-auto p-5 gap-4 min-w-full">
                <!-- Coluna 1: Novos -->
                <div class="w-80 shrink-0 flex flex-col bg-slate-900/90 rounded-2xl border border-rose-500/30 shadow-xl overflow-hidden">
                    <div class="p-3.5 bg-rose-950/40 border-b border-rose-500/20 flex justify-between items-center">
                        <h2 class="font-bold text-sm text-rose-400 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                            1. Novos Pedidos
                        </h2>
                        <span class="bg-rose-500 text-white text-xs font-extrabold px-2 py-0.5 rounded-full" x-text="colunas.novo.length"></span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 space-y-3">
                        <template x-for="pedido in colunas.novo" :key="pedido.id">
                            <div class="bg-slate-800/90 rounded-xl p-3.5 border border-rose-500/40 shadow-lg space-y-2.5">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-center gap-1.5">
                                        <button @click="abrirDetalhesPedido(pedido)" class="font-black text-rose-400 text-sm hover:underline" x-text="'#' + pedido.codigo_publico"></button>
                                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-slate-700 text-slate-200" x-text="pedido.canal"></span>
                                    </div>
                                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded"
                                          :class="pedido.pagamento_status === 'aprovado' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400'"
                                          x-text="pedido.forma_pagamento + ' • ' + pedido.pagamento_status"></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="font-bold text-slate-100 text-sm truncate" x-text="pedido.cliente?.nome || 'Cliente'"></div>
                                        <div class="text-xs text-slate-400" x-text="pedido.cliente?.telefone || ''"></div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button @click="abrirDetalhesPedido(pedido)" class="text-xs bg-slate-700 hover:bg-slate-600 text-slate-200 px-2 py-1 rounded-lg font-bold" title="Ver Detalhes e Endereço">🔍</button>
                                        <template x-if="pedido.whatsapp_notificacao_url">
                                            <a :href="pedido.whatsapp_notificacao_url" target="_blank" class="text-xs bg-emerald-500/20 text-emerald-400 px-2 py-1 rounded-lg font-bold">💬</a>
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
                                    <button @click="cancelarPedido(pedido)" class="px-2.5 py-2 bg-slate-700 hover:bg-rose-900 text-slate-400 hover:text-rose-200 rounded-lg text-xs font-bold" title="Cancelar">
                                        ✕
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Coluna 2: Em Preparo -->
                <div class="w-80 shrink-0 flex flex-col bg-slate-900/90 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
                    <div class="p-3.5 bg-amber-950/20 border-b border-amber-500/20 flex justify-between items-center">
                        <h2 class="font-bold text-sm text-amber-400">2. Em Preparo</h2>
                        <span class="bg-amber-500/20 text-amber-400 text-xs font-extrabold px-2 py-0.5 rounded-full" x-text="colunas.em_preparo.length"></span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 space-y-3">
                        <template x-for="pedido in colunas.em_preparo" :key="pedido.id">
                            <div class="bg-slate-800 rounded-xl p-3.5 border border-slate-700 space-y-2.5">
                                <div class="flex justify-between items-start">
                                    <button @click="abrirDetalhesPedido(pedido)" class="font-bold text-amber-400 text-sm hover:underline" x-text="'#' + pedido.codigo_publico"></button>
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
                                    <button @click="imprimirPedido(pedido.id, 'cozinha')" class="px-2.5 py-2 bg-slate-700 text-slate-200 rounded-lg text-xs">🖨️</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Coluna 3: Prontos -->
                <div class="w-80 shrink-0 flex flex-col bg-slate-900/90 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
                    <div class="p-3.5 bg-emerald-950/20 border-b border-emerald-500/20 flex justify-between items-center">
                        <h2 class="font-bold text-sm text-emerald-400">3. Prontos p/ Despacho</h2>
                        <span class="bg-emerald-500/20 text-emerald-400 text-xs font-extrabold px-2 py-0.5 rounded-full" x-text="colunas.pronto.length"></span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 space-y-3">
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
                                <template x-if="pedido.canal === 'delivery' && entregadores.length > 0">
                                    <select @change="atribuirMotoboy(pedido.id, $event.target.value)" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-200">
                                        <option value="">🛵 Selecionar Entregador...</option>
                                        <template x-for="ent in entregadores" :key="ent.id">
                                            <option :value="ent.id" :selected="pedido.entregador_id === ent.id" x-text="ent.nome"></option>
                                        </template>
                                    </select>
                                </template>
                                <button @click="avancarPedido(pedido)" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2 rounded-lg text-xs transition">
                                    <span x-text="rotuloProximoStatus(pedido)"></span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Coluna 4: Saiu para Entrega -->
                <div class="w-80 shrink-0 flex flex-col bg-slate-900/90 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
                    <div class="p-3.5 bg-sky-950/20 border-b border-sky-500/20 flex justify-between items-center">
                        <h2 class="font-bold text-sm text-sky-400">4. Em Rota de Entrega</h2>
                        <span class="bg-sky-500/20 text-sky-400 text-xs font-extrabold px-2 py-0.5 rounded-full" x-text="colunas.saiu_entrega.length"></span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 space-y-3">
                        <template x-for="pedido in colunas.saiu_entrega" :key="pedido.id">
                            <div class="bg-slate-800 rounded-xl p-3.5 border border-slate-700 space-y-2.5">
                                <div class="flex justify-between">
                                    <button @click="abrirDetalhesPedido(pedido)" class="font-bold text-sky-400 text-sm hover:underline" x-text="'#' + pedido.codigo_publico"></button>
                                    <span class="text-xs text-slate-400">🛵 Em trânsito</span>
                                </div>
                                <div class="font-bold text-sm text-slate-200" x-text="pedido.cliente?.nome"></div>
                                <button @click="avancarPedido(pedido)" class="w-full bg-sky-600 hover:bg-sky-500 text-white font-bold py-2 rounded-lg text-xs transition">
                                    ✓ Confirmar Entrega
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Coluna 5: Concluídos Hoje -->
                <div class="w-72 shrink-0 flex flex-col bg-slate-900/50 rounded-2xl border border-slate-800/80 overflow-hidden">
                    <div class="p-3.5 border-b border-slate-800 flex justify-between items-center">
                        <h2 class="font-bold text-sm text-slate-400">5. Concluídos Hoje</h2>
                        <span class="bg-slate-800 text-slate-400 text-xs font-bold px-2 py-0.5 rounded-full" x-text="colunas.finalizado.length"></span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 space-y-2">
                        <template x-for="pedido in colunas.finalizado" :key="pedido.id">
                            <div @click="abrirDetalhesPedido(pedido)" class="bg-slate-900 hover:bg-slate-800 cursor-pointer rounded-xl p-3 border border-slate-800/80 flex justify-between items-center opacity-80">
                                <div>
                                    <span class="font-bold text-xs text-slate-300" x-text="'#' + pedido.codigo_publico"></span>
                                    <span class="text-xs text-slate-500 block" x-text="pedido.cliente?.nome"></span>
                                </div>
                                <span class="text-xs font-bold text-emerald-400" x-text="formatarCentavos(pedido.total_centavos)"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </main>
        </div>
