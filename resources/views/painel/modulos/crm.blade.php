        <div x-cloak x-show="moduloAtivo === 'crm'" class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-6 bg-[#0b0f19]">
            <!-- 4 Cards de Métricas do CRM -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-[#131927] border border-[#1e2638] rounded-2xl p-5 space-y-1">
                    <span class="text-xs font-bold uppercase text-slate-400">Base Total de Clientes</span>
                    <div class="text-2xl font-bold text-white" x-text="(relatorio.clientes_lista || []).length"></div>
                    <p class="text-[11px] text-slate-500">Cadastrados automaticamente no checkout</p>
                </div>
                <div class="bg-[#131927] border border-[#1e2638] rounded-2xl p-5 space-y-1">
                    <span class="text-xs font-bold uppercase text-emerald-400">Clientes Fidelizados (2+ pedidos)</span>
                    <div class="text-2xl font-bold text-emerald-400" x-text="relatorio.clientes?.recorrentes || 0"></div>
                    <p class="text-[11px] text-slate-500">Compram com recorrência e acumulam Cashback</p>
                </div>
                <div class="bg-[#131927] border border-[#1e2638] rounded-2xl p-5 space-y-1">
                    <span class="text-xs font-bold uppercase text-amber-400">Sumidos há 30+ Dias (Win-Back)</span>
                    <div class="text-2xl font-bold text-amber-400" x-text="relatorio.clientes?.elegiveis_winback_30d || 0"></div>
                    <p class="text-[11px] text-slate-500">Prontos para recuperação 1-clique no WhatsApp</p>
                </div>
                <div class="bg-[#131927] border border-[#1e2638] rounded-2xl p-5 space-y-1">
                    <span class="text-xs font-bold uppercase text-[#f43f5e]">Cupons Ativos na Loja</span>
                    <div class="text-2xl font-bold text-[#f43f5e]" x-text="cupons.filter(c => c.ativo).length"></div>
                    <p class="text-[11px] text-slate-500">Ex: VOLTE10 / BEMVINDO10</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Ranking de Clientes & Disparo Win-Back WhatsApp -->
                <div class="lg:col-span-8 bg-[#131927] border border-[#1e2638] rounded-3xl p-5 sm:p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#1e2638] pb-3">
                        <div>
                            <h2 class="font-bold text-base text-white">Ranking de Clientes, Cashback &amp; Recuperação WhatsApp</h2>
                            <p class="text-xs text-slate-400">Envie cupons personalizados em 1 clique para clientes fiéis ou sumidos há 30+ dias.</p>
                        </div>
                        <button @click="carregarFinanceiro()" class="bg-[#1e2638] hover:bg-slate-700 text-xs font-bold px-3 py-2 rounded-xl text-slate-200 self-start transition">
                            ↻ Atualizar CRM
                        </button>
                    </div>

                    <template x-if="!relatorio.clientes_lista || relatorio.clientes_lista.length === 0">
                        <div class="text-center py-12 text-slate-500 text-xs font-bold">
                            Nenhum cliente registrado ainda. Assim que o primeiro pedido for feito, o ranking aparecerá aqui!
                        </div>
                    </template>

                    <div class="space-y-2.5 max-h-[600px] overflow-y-auto pr-1">
                        <template x-for="cli in (relatorio.clientes_lista || [])" :key="cli.id">
                            <div class="bg-[#0f1422] border border-[#192136] rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-slate-700 transition">
                                <div class="space-y-1.5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-bold text-sm text-white" x-text="cli.nome"></span>
                                        <span class="text-xs text-slate-400" x-text="cli.telefone"></span>
                                        <span x-show="cli.inativo_30d" class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                            Sumido há <span x-text="cli.dias_sem_pedir"></span>d
                                        </span>
                                        <span x-show="!cli.inativo_30d && cli.total_pedidos > 1" class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                            Cliente Fiel
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400">
                                        <span>Pedidos: <strong class="text-white" x-text="cli.total_pedidos"></strong></span>
                                        <span>Total gasto: <strong class="text-emerald-400" x-text="formatarCentavos(cli.total_gasto_centavos)"></strong></span>
                                        <span>Saldo Cashback: <strong class="text-amber-400" x-text="formatarCentavos(cli.saldo_cashback_centavos)"></strong></span>
                                        <span x-show="cli.ultimo_pedido_em" x-text="'Último: ' + cli.ultimo_pedido_em"></span>
                                    </div>
                                </div>
                                <template x-if="cli.whatsapp_winback_url">
                                    <a :href="cli.whatsapp_winback_url" target="_blank"
                                        class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold px-3.5 py-2.5 rounded-xl flex items-center justify-center gap-1.5 shrink-0 shadow-md transition active:scale-95">
                                        <span>Enviar Cupom WhatsApp ↗</span>
                                    </a>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Coluna Direita: Horários de Pico & Produtos Campeões -->
                <div class="lg:col-span-4 space-y-6">
                    <!-- 1. Horários de Pico -->
                    <div class="bg-[#131927] border border-[#1e2638] rounded-3xl p-5 space-y-4">
                        <div>
                            <h3 class="font-bold text-base text-white">Horários de Pico da Operação</h3>
                            <p class="text-xs text-slate-400">Volume de pedidos concluídos por horário para dimensionar cozinha e entregadores.</p>
                        </div>

                        <template x-if="Object.keys(relatorio.horarios_pico || {}).length === 0">
                            <div class="text-center py-8 text-slate-500 text-xs">
                                Conclua pedidos hoje para visualizar o mapa de calor por horário.
                            </div>
                        </template>

                        <div class="space-y-2.5">
                            <template x-for="[hora, qtd] in Object.entries(relatorio.horarios_pico || {})" :key="hora">
                                <div class="space-y-1">
                                    <div class="flex justify-between text-xs font-bold">
                                        <span class="text-slate-300" x-text="'' + hora"></span>
                                        <span class="text-[#f43f5e]" x-text="qtd + ' pedido(s)'"></span>
                                    </div>
                                    <div class="w-full h-2.5 bg-[#0f1422] rounded-full overflow-hidden border border-[#1e2638]">
                                        <div class="h-full bg-gradient-to-r from-[#f43f5e] to-amber-500 rounded-full"
                                             :style="'width: ' + Math.min(100, Math.max(15, Math.round((qtd / Math.max(1, ...Object.values(relatorio.horarios_pico || {a:1}))) * 100))) + '%'"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- 2. Produtos Campeões de Venda (Etapa 4) -->
                    <div class="bg-[#131927] border border-[#1e2638] rounded-3xl p-5 space-y-4">
                        <div>
                            <h3 class="font-bold text-base text-white">Produtos Campeões de Venda</h3>
                            <p class="text-xs text-slate-400">Os itens mais pedidos e lucrativos do seu cardápio.</p>
                        </div>

                        <template x-if="!relatorio.produtos_campeoes || relatorio.produtos_campeoes.length === 0">
                            <div class="text-center py-8 text-slate-500 text-xs">
                                Nenhum produto com vendas finalizadas ainda.
                            </div>
                        </template>

                        <div class="space-y-2">
                            <template x-for="(prod, idx) in (relatorio.produtos_campeoes || [])" :key="prod.nome">
                                <div class="bg-[#0f1422] border border-[#192136] rounded-xl p-3 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="w-6 h-6 rounded-lg bg-[#1e2638] font-bold text-white flex items-center justify-center text-[11px] shrink-0"
                                              x-text="'#' + (idx + 1)"></span>
                                        <span class="font-bold text-white truncate" x-text="prod.nome"></span>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="font-bold text-emerald-400" x-text="formatarCentavos(prod.total_centavos)"></div>
                                        <div class="text-[10px] text-slate-400" x-text="prod.qtd + ' vendidos'"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
