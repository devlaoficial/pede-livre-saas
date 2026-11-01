        <div x-cloak x-show="moduloAtivo === 'crm'" class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
                    <span class="text-xs font-bold uppercase text-slate-400">Base Total de Clientes</span>
                    <div class="text-2xl font-black text-white" x-text="(relatorio.clientes_lista || []).length"></div>
                    <p class="text-[11px] text-slate-500">Cadastrados automaticamente no checkout</p>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
                    <span class="text-xs font-bold uppercase text-emerald-400">Clientes Fidelizados (2+ pedidos)</span>
                    <div class="text-2xl font-black text-emerald-400" x-text="relatorio.clientes?.recorrentes || 0"></div>
                    <p class="text-[11px] text-slate-500">Compram com recorrência e Cashback</p>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
                    <span class="text-xs font-bold uppercase text-amber-400">Sumidos há 30+ Dias (Win-Back)</span>
                    <div class="text-2xl font-black text-amber-400" x-text="relatorio.clientes?.elegiveis_winback_30d || 0"></div>
                    <p class="text-[11px] text-slate-500">Prontos p/ disparo 1-clique no WhatsApp</p>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
                    <span class="text-xs font-bold uppercase text-rose-400">Cupons Ativos na Loja</span>
                    <div class="text-2xl font-black text-rose-400" x-text="cupons.filter(c => c.ativo).length"></div>
                    <p class="text-[11px] text-slate-500">Ex: BEMVINDO10 / VOLTE10</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Ranking de Clientes & Disparo Win-Back WhatsApp -->
                <div class="lg:col-span-8 bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h2 class="font-bold text-base text-white">👥 Ranking de Clientes, Saldo Cashback &amp; Recuperação WhatsApp</h2>
                            <p class="text-xs text-slate-400">Envie cupons personalizados em 1 clique para clientes fiéis ou que não pedem há mais de 30 dias.</p>
                        </div>
                        <button @click="carregarFinanceiro()" class="bg-slate-800 hover:bg-slate-700 text-xs font-bold px-3 py-2 rounded-xl text-slate-200 self-start">
                            ↻ Atualizar CRM
                        </button>
                    </div>

                    <template x-if="!relatorio.clientes_lista || relatorio.clientes_lista.length === 0">
                        <div class="text-center py-12 text-slate-500 text-xs font-bold">
                            Nenhum cliente registrado ainda. Assim que o primeiro pedido for feito, o ranking aparecerá aqui!
                        </div>
                    </template>

                    <div class="space-y-2.5">
                        <template x-for="cli in (relatorio.clientes_lista || [])" :key="cli.id">
                            <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="space-y-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-bold text-sm text-white" x-text="cli.nome"></span>
                                        <span class="text-xs text-slate-400" x-text="cli.telefone"></span>
                                        <span x-show="cli.inativo_30d" class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                            ⚠️ Sumido há <span x-text="cli.dias_sem_pedir"></span>d
                                        </span>
                                        <span x-show="!cli.inativo_30d && cli.total_pedidos > 1" class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300">
                                            ⭐ Cliente Fiel
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
                                        class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-extrabold px-3.5 py-2.5 rounded-xl flex items-center justify-center gap-1.5 shrink-0 shadow-md">
                                        <span>💬 Enviar Cupom WhatsApp ↗</span>
                                    </a>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Gráfico Visual de Horários de Pico -->
                <div class="lg:col-span-4 bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                    <div>
                        <h3 class="font-bold text-base text-white">📊 Horários de Pico da Operação</h3>
                        <p class="text-xs text-slate-400">Volume de pedidos concluídos por faixa de horário para dimensionar cozinha e motoboys.</p>
                    </div>

                    <template x-if="Object.keys(relatorio.horarios_pico || {}).length === 0">
                        <div class="text-center py-10 text-slate-500 text-xs">
                            Conclua pedidos hoje para visualizar o mapa de calor por horário.
                        </div>
                    </template>

                    <div class="space-y-2.5">
                        <template x-for="[hora, qtd] in Object.entries(relatorio.horarios_pico || {})" :key="hora">
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs font-bold">
                                    <span class="text-slate-300" x-text="'🕒 ' + hora"></span>
                                    <span class="text-rose-400" x-text="qtd + ' pedido(s)'"></span>
                                </div>
                                <div class="w-full h-2.5 bg-slate-950 rounded-full overflow-hidden border border-slate-800">
                                    <div class="h-full bg-gradient-to-r from-rose-600 to-amber-500 rounded-full"
                                         :style="'width: ' + Math.min(100, Math.max(15, Math.round((qtd / Math.max(1, ...Object.values(relatorio.horarios_pico || {a:1}))) * 100))) + '%'"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
