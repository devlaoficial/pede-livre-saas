        <div x-cloak x-show="moduloAtivo === 'financeiro'" class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-5">

            <!-- Barra de Sub-Abas Financeiras (Sem Misturar Operação de Gaveta com Configuração Técnica) -->
            <div class="bg-[#0d1322] border border-slate-800/90 p-2 rounded-2xl flex items-center justify-between gap-2 flex-wrap text-xs">
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
                    <button type="button" @click="abaFinanceiro = 'caixa'"
                        class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition flex items-center gap-2"
                        :class="abaFinanceiro === 'caixa' ? 'bg-rose-600 text-white shadow' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        <span>1. Frente de Caixa &amp; Turno Atual</span>
                        <span class="px-2 py-0.2 rounded-full text-[10px]"
                              :class="caixaStatus.aberto ? 'bg-emerald-500/20 text-emerald-300' : 'bg-black/25 text-slate-300'"
                              x-text="caixaStatus.aberto ? 'Aberto' : 'Fechado'"></span>
                    </button>

                    <button type="button" @click="abaFinanceiro = 'resumo'"
                        class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition flex items-center gap-2"
                        :class="abaFinanceiro === 'resumo' ? 'bg-rose-600 text-white shadow' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        <span>2. Indicadores de Faturamento &amp; Status PIX</span>
                    </button>
                </div>

                <button type="button" @click="mudarModulo('config'); abaConfig = 'pix'"
                        class="bg-[#111726] hover:bg-slate-800 text-sky-300 border border-slate-700 font-bold px-3.5 py-2 rounded-xl flex items-center gap-1.5 transition">
                    <span>Configurar Chave PIX / Mercado Pago →</span>
                </button>
            </div>

            <!-- ============================================================== -->
            <!-- SUB-ABA 1: CONTROLE DE FRENTE DE CAIXA & TURNOS                -->
            <!-- ============================================================== -->
            <div x-show="abaFinanceiro === 'caixa'" class="bg-[#0d1322] border border-slate-800/90 rounded-3xl p-5 sm:p-6 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
                            <h3 class="font-semibold text-base text-white">Controle de Frente de Caixa &amp; Turnos</h3>
                            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full border uppercase"
                                  :class="caixaStatus.aberto ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/40 animate-pulse' : 'bg-[#080c17] text-slate-400 border-slate-700'"
                                  x-text="caixaStatus.aberto ? '● Caixa Aberto' : '○ Caixa Fechado'"></span>
                        </div>
                        <p class="text-xs text-slate-400">
                            Gestão de fundo de troco, sangrias, suprimentos e conciliação física de gaveta com extrato térmico 80mm.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2 shrink-0">
                        <template x-if="!caixaStatus.aberto">
                            <button @click="abrirModalAberturaCaixa()"
                                class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-lg shadow-emerald-600/25 transition flex items-center gap-1.5">
                                <span><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg></span> Abrir Turno de Caixa
                            </button>
                        </template>

                        <template x-if="caixaStatus.aberto">
                            <div class="flex flex-wrap gap-2">
                                <button @click="abrirModalMovimento('sangria')"
                                    class="bg-[#080c17] hover:bg-rose-950/60 text-rose-300 border border-rose-500/30 text-xs font-bold px-3 py-2 rounded-xl transition flex items-center gap-1.5">
                                    <span>Registrar Sangria (-)</span>
                                </button>
                                <button @click="abrirModalMovimento('suprimento')"
                                    class="bg-[#080c17] hover:bg-emerald-950/60 text-emerald-300 border border-emerald-500/30 text-xs font-bold px-3 py-2 rounded-xl transition flex items-center gap-1.5">
                                    <span>Registrar Suprimento (+)</span>
                                </button>
                                <button @click="abrirModalFechamentoCaixa()"
                                    class="bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold px-3.5 py-2 rounded-xl shadow-lg transition flex items-center gap-1.5">
                                    <span><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg></span> Fechar Turno
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Estado Quando o Caixa está FECHADO -->
                <template x-if="!caixaStatus.aberto">
                    <div class="bg-[#080c17] border border-slate-800 rounded-2xl p-8 text-center space-y-3">
                        <div class="text-3xl"><svg class="w-5 h-5 shrink-0 inline-block text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg></div>
                        <h4 class="font-semibold text-white text-sm">O caixa está fechado no momento</h4>
                        <p class="text-xs text-slate-400 max-w-md mx-auto">
                            Abra um novo turno de caixa informando o valor do fundo de troco inicial para registrar movimentações em dinheiro.
                        </p>
                        <template x-if="caixaStatus.ultima_sessao">
                            <div class="pt-3 border-t border-slate-800/80 text-xs text-slate-400 space-y-1">
                                <span>Último turno encerrado em <strong class="text-white" x-text="caixaStatus.ultima_sessao.fechado_em"></strong> por <strong class="text-white" x-text="caixaStatus.ultima_sessao.operador_nome"></strong>.</span>
                                <div>Diferença apurada na contagem física: <strong :class="caixaStatus.ultima_sessao.diferenca_centavos >= 0 ? 'text-emerald-400' : 'text-rose-400'" x-text="formatarCentavos(caixaStatus.ultima_sessao.diferenca_centavos)"></strong></div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Estado Quando o Caixa está ABERTO -->
                <template x-if="caixaStatus.aberto && caixaStatus.sessao">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
                            <div class="bg-[#080c17] border border-slate-800 rounded-xl p-3 space-y-1">
                                <span class="text-slate-400 font-bold uppercase text-[10px]">Fundo Inicial</span>
                                <div class="text-sm sm:text-base font-bold text-white" x-text="formatarCentavos(caixaStatus.sessao.valor_inicial_centavos)"></div>
                            </div>
                            <div class="bg-[#080c17] border border-slate-800 rounded-xl p-3 space-y-1">
                                <span class="text-slate-400 font-bold uppercase text-[10px]">(+) Vendas Dinheiro</span>
                                <div class="text-sm sm:text-base font-bold text-emerald-400" x-text="formatarCentavos(caixaStatus.sessao.total_vendas_dinheiro_centavos)"></div>
                            </div>
                            <div class="bg-[#080c17] border border-slate-800 rounded-xl p-3 space-y-1">
                                <span class="text-slate-400 font-bold uppercase text-[10px]">(+) Suprimentos</span>
                                <div class="text-sm sm:text-base font-bold text-emerald-400" x-text="formatarCentavos(caixaStatus.sessao.total_suprimentos_centavos)"></div>
                            </div>
                            <div class="bg-[#080c17] border border-slate-800 rounded-xl p-3 space-y-1">
                                <span class="text-slate-400 font-bold uppercase text-[10px]">(-) Sangrias</span>
                                <div class="text-sm sm:text-base font-bold text-rose-400" x-text="formatarCentavos(caixaStatus.sessao.total_sangrias_centavos)"></div>
                            </div>
                            <div class="bg-emerald-950/40 border border-emerald-500/40 rounded-xl p-3 space-y-1 sm:col-span-2 lg:col-span-2">
                                <span class="text-emerald-300 font-semibold uppercase text-[10px]">Saldo Esperado na Gaveta</span>
                                <div class="text-base sm:text-xl font-bold text-emerald-400" x-text="formatarCentavos(caixaStatus.sessao.saldo_esperado_gaveta_centavos)"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 text-xs">
                            <div class="bg-[#080c17] border border-slate-800 rounded-2xl p-4 space-y-2.5">
                                <h4 class="font-semibold text-white text-xs uppercase tracking-wider">Outras Entradas do Turno</h4>
                                <div class="flex justify-between items-center text-slate-300 border-b border-slate-800/80 pb-1.5">
                                    <span>Vendas no PIX:</span>
                                    <span class="font-bold text-emerald-400" x-text="formatarCentavos(caixaStatus.sessao.total_vendas_pix_centavos)"></span>
                                </div>
                                <div class="flex justify-between items-center text-slate-300 border-b border-slate-800/80 pb-1.5">
                                    <span>Vendas em Cartão / Maquininha:</span>
                                    <span class="font-bold text-sky-400" x-text="formatarCentavos(caixaStatus.sessao.total_vendas_cartao_centavos)"></span>
                                </div>
                                <div class="flex justify-between items-center pt-1 font-semibold text-white">
                                    <span>Faturamento Total do Turno:</span>
                                    <span class="text-emerald-400 text-sm font-bold" x-text="formatarCentavos(caixaStatus.sessao.faturamento_total_centavos)"></span>
                                </div>
                            </div>

                            <div class="bg-[#080c17] border border-slate-800 rounded-2xl p-4 space-y-2.5">
                                <div class="flex justify-between items-center">
                                    <h4 class="font-semibold text-white text-xs uppercase tracking-wider">Sangrias e Suprimentos</h4>
                                    <span class="text-[10px] text-slate-500" x-text="(caixaStatus.sessao.movimentos || []).length + ' lançamentos'"></span>
                                </div>

                                <div class="max-h-36 overflow-y-auto space-y-1.5 pr-1 custom-scrollbar">
                                    <template x-if="(caixaStatus.sessao.movimentos || []).length === 0">
                                        <div class="text-center py-6 text-slate-500 text-xs">
                                            Nenhuma sangria ou suprimento lançado neste turno
                                        </div>
                                    </template>

                                    <template x-for="m in (caixaStatus.sessao.movimentos || [])" :key="m.id">
                                        <div class="flex justify-between items-center p-2 rounded-xl bg-[#0d1322] border border-slate-800">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-[10px] font-bold uppercase px-1.5 py-0.2 rounded"
                                                          :class="m.tipo === 'sangria' ? 'bg-rose-500/20 text-rose-300' : 'bg-emerald-500/20 text-emerald-300'"
                                                          x-text="m.tipo"></span>
                                                    <span class="text-slate-400 text-[10px]" x-text="m.horario"></span>
                                                </div>
                                                <div class="text-slate-300 text-[11px] truncate mt-0.5" x-text="m.observacao || 'Sem observação'"></div>
                                            </div>
                                            <span class="font-semibold text-xs shrink-0 pl-2"
                                                  :class="m.tipo === 'sangria' ? 'text-rose-400' : 'text-emerald-400'"
                                                  x-text="(m.tipo === 'sangria' ? '- ' : '+ ') + formatarCentavos(m.valor_centavos)"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- ============================================================== -->
            <!-- SUB-ABA 2: INDICADORES DE FATURAMENTO & STATUS PIX             -->
            <!-- ============================================================== -->
            <div x-show="abaFinanceiro === 'resumo'" class="space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-[#0d1322] border border-slate-800/90 rounded-2xl p-5 space-y-1">
                        <span class="text-xs font-bold uppercase text-slate-400">Faturamento Concluído</span>
                        <div class="text-2xl font-bold text-emerald-400" x-text="formatarCentavos(relatorio.faturamento_total_centavos || volumeDiaCentavos)"></div>
                    </div>
                    <div class="bg-[#0d1322] border border-slate-800/90 rounded-2xl p-5 space-y-1">
                        <span class="text-xs font-bold uppercase text-slate-400">Pedidos no Período</span>
                        <div class="text-2xl font-bold text-white" x-text="relatorio.total_pedidos_concluidos || pedidos.length"></div>
                    </div>
                    <div class="bg-[#0d1322] border border-slate-800/90 rounded-2xl p-5 space-y-1">
                        <span class="text-xs font-bold uppercase text-slate-400">Ticket Médio</span>
                        <div class="text-2xl font-bold text-rose-400" x-text="formatarCentavos(relatorio.ticket_medio_centavos || (pedidos.length ? Math.round(volumeDiaCentavos / pedidos.length) : 0))"></div>
                    </div>
                    <div class="bg-[#0d1322] border border-slate-800/90 rounded-2xl p-5 space-y-1">
                        <span class="text-xs font-bold uppercase text-slate-400">Clientes Atendidos</span>
                        <div class="text-2xl font-bold text-amber-400" x-text="((relatorio.clientes?.novos || 0) + (relatorio.clientes?.recorrentes || 0)) || pedidos.length"></div>
                    </div>
                </div>

                <div class="bg-[#0d1322] border border-slate-800/90 rounded-3xl p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-white">Status de Recebimento PIX</h3>
                            <span class="text-xs font-bold px-3 py-0.5 rounded-full border"
                                  :class="(mpStatus?.conectado || mpStatus?.chave_pix) ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border-amber-500/30'"
                                  x-text="mpStatus?.conectado
                                      ? 'API Mercado Pago Ativa (' + (mpStatus.mp_conta_nome || 'Conectado') + ')'
                                      : (mpStatus?.chave_pix ? 'PIX Ativo (' + mpStatus.chave_pix + ')' : 'Ainda Não Configurado')">
                            </span>
                        </div>
                        <p class="text-xs text-slate-400">
                            Os pagamentos PIX dos seus clientes caem 100% direto na sua conta bancária com 0% de comissão.
                        </p>
                    </div>
                    <button @click="mudarModulo('config'); abaConfig = 'pix'"
                            class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs px-5 py-3 rounded-xl transition shrink-0 shadow-lg">
                        Gerenciar Chave PIX / Mercado Pago →
                    </button>
                </div>
            </div>

        </div>
