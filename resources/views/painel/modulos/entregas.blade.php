<div x-cloak x-show="moduloAtivo === 'entregas'" class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Coluna Motoboys -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-bold text-base text-white">Equipe de Entregadores (Motoboys)</h2>
                            <p class="text-xs text-slate-400">Cadastre motoboys próprios ou freelancers para despacho</p>
                        </div>
                        <button @click="modalNovoEntregador = true" class="bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold px-3.5 py-2 rounded-xl">
                            + Entregador
                        </button>
                    </div>
                    <div class="space-y-2.5">
                        <template x-if="entregadores.length === 0">
                            <div class="bg-slate-950 border border-slate-800 p-4 rounded-2xl text-center text-xs text-slate-500">
                                Nenhum entregador cadastrado ainda.
                            </div>
                        </template>
                        <template x-for="ent in entregadores" :key="ent.id">
                            <div class="bg-slate-950 border border-slate-800 p-3.5 rounded-2xl flex items-center justify-between">
                                <div>
                                    <div class="font-bold text-sm text-white" x-text="ent.nome"></div>
                                    <div class="text-xs text-slate-400" x-text="(ent.tipo || 'proprio').toUpperCase() + (ent.telefone ? ' • ' + ent.telefone : '') + ' • Taxa/corrida: ' + formatarCentavos(ent.valor_por_entrega_centavos)"></div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-500/15 text-emerald-400">Ativo</span>
                                    <button @click="excluirEntregador(ent.id)" title="Remover entregador" class="text-xs text-rose-400 hover:bg-rose-500/15 px-2 py-1 rounded-lg"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Coluna Áreas e Taxas de Entrega -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-bold text-base text-white">Taxas de Entrega (Bairro &amp; Raio KM)</h2>
                            <p class="text-xs text-slate-400">Regras de frete calculadas automaticamente no checkout</p>
                        </div>
                        <button @click="modalNovaArea = true" class="bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold px-3.5 py-2 rounded-xl">
                            + Nova Taxa
                        </button>
                    </div>
                    <div class="space-y-2.5">
                        <template x-if="areasEntrega.length === 0">
                            <div class="bg-slate-950 border border-slate-800 p-4 rounded-2xl text-center text-xs text-slate-500">
                                Nenhuma taxa de entrega cadastrada ainda.
                            </div>
                        </template>
                        <template x-for="area in areasEntrega" :key="area.id">
                            <div class="bg-slate-950 border border-slate-800 p-3.5 rounded-2xl flex items-center justify-between">
                                <div>
                                    <div class="font-bold text-sm text-white"
                                         x-text="area.tipo === 'bairro' ? 'Bairro: ' + (area.nome_bairro || area.bairro_nome || area.nome) : 'Raio: ' + (area.raio_km_de || 0) + 'km até ' + (area.raio_km_ate || area.raio_ate_km || 0) + 'km'"></div>
                                    <div class="text-xs text-slate-400" x-text="'Mínimo: ' + formatarCentavos(area.pedido_minimo_centavos || area.pedido_minimo || 0) + (area.frete_gratis_acima ? ' • Grátis acima de ' + formatarCentavos(area.frete_gratis_acima) : '') + ' • Tempo: ' + (area.tempo_estimado_min || 40) + ' min'"></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-emerald-400" x-text="formatarCentavos(area.taxa_centavos)"></div>
                                    <button @click="removerAreaEntrega(area.id)" class="text-[11px] text-rose-400 hover:underline">Remover</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Seção de Acerto Diário de Motoboys -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-sky-500/20 text-sky-400 border border-sky-500/30 flex items-center justify-center font-bold"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg></span>
                            <h3 class="font-semibold text-base text-white">Acerto Diário de Motoboys (Fechamento da Noite)</h3>
                            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-slate-800 text-slate-300 border border-slate-700">Hoje</span>
                        </div>
                        <p class="text-xs text-slate-400">
                            Totalização de entregas concluídas, cálculo de taxas ganhas e conciliação do dinheiro recebido na rua para prestação de contas.
                        </p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button @click="carregarFechamentoMotoboys()" :disabled="carregandoFechamentoMotoboys"
                            class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold px-3 py-2 rounded-xl transition flex items-center gap-1.5">
                            <span :class="carregandoFechamentoMotoboys ? 'animate-spin' : ''"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg></span> Atualizar Acertos
                        </button>
                    </div>
                </div>

                <!-- KPI Cards do Fechamento de Entregas -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-1">
                        <span class="text-slate-400 font-bold uppercase text-[10px]">Entregas Concluídas</span>
                        <div class="text-xl sm:text-2xl font-bold text-white" x-text="totaisFechamentoMotoboys.total_entregas || 0"></div>
                    </div>
                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-1">
                        <span class="text-slate-400 font-bold uppercase text-[10px]">Total Taxas / Diárias</span>
                        <div class="text-xl sm:text-2xl font-bold text-sky-400" x-text="formatarCentavos(totaisFechamentoMotoboys.total_taxas_centavos || 0)"></div>
                    </div>
                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-1">
                        <span class="text-slate-400 font-bold uppercase text-[10px]">Dinheiro Cobrado na Rua</span>
                        <div class="text-xl sm:text-2xl font-bold text-emerald-400" x-text="formatarCentavos(totaisFechamentoMotoboys.total_dinheiro_recebido_centavos || 0)"></div>
                    </div>
                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-1">
                        <span class="text-slate-400 font-bold uppercase text-[10px]">Saldo Geral Líquido</span>
                        <div class="text-xl sm:text-2xl font-bold"
                             :class="(totaisFechamentoMotoboys.saldo_geral_centavos || 0) >= 0 ? 'text-emerald-400' : 'text-amber-400'"
                             x-text="formatarCentavos(totaisFechamentoMotoboys.saldo_geral_centavos || 0)"></div>
                    </div>
                </div>

                <!-- Tabela / Lista de Acertos por Motoboy -->
                <div class="space-y-3">
                    <template x-if="fechamentoMotoboys.length === 0">
                        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-8 text-center space-y-2">
                            <div class="text-3xl"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg></div>
                            <h4 class="font-semibold text-white text-sm">Nenhum motoboy com entregas registradas hoje</h4>
                            <p class="text-xs text-slate-400 max-w-md mx-auto">
                                Assim que pedidos com entrega forem despachados e concluídos no Kanban com o motoboy vinculado, o acerto financeiro será consolidado aqui automaticamente.
                            </p>
                        </div>
                    </template>

                    <template x-if="fechamentoMotoboys.length > 0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                                        <th class="pb-3 font-bold">Entregador</th>
                                        <th class="pb-3 font-bold text-center">Corridas</th>
                                        <th class="pb-3 font-bold text-right">Taxa Unid.</th>
                                        <th class="pb-3 font-bold text-right">Total Ganho</th>
                                        <th class="pb-3 font-bold text-right">Dinheiro na Rua</th>
                                        <th class="pb-3 font-bold text-right">Saldo do Acerto</th>
                                        <th class="pb-3 font-bold text-right">Ações Rápidas</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/60 font-medium">
                                    <template x-for="item in fechamentoMotoboys" :key="item.id">
                                        <tr class="hover:bg-slate-800/40 transition">
                                            <td class="py-3.5 pr-2">
                                                <div class="font-bold text-white text-sm" x-text="item.nome"></div>
                                                <div class="text-[11px] text-slate-400" x-text="(item.tipo || 'proprio').toUpperCase() + (item.telefone ? ' • ' + item.telefone : '')"></div>
                                            </td>
                                            <td class="py-3.5 px-2 text-center">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full font-bold text-xs"
                                                      :class="item.total_entregas > 0 ? 'bg-sky-500/20 text-sky-300' : 'bg-slate-800 text-slate-400'"
                                                      x-text="item.total_entregas + ' corrida(s)'"></span>
                                            </td>
                                            <td class="py-3.5 px-2 text-right text-slate-300" x-text="formatarCentavos(item.valor_por_entrega_centavos)"></td>
                                            <td class="py-3.5 px-2 text-right font-bold text-sky-400" x-text="formatarCentavos(item.total_taxas_centavos)"></td>
                                            <td class="py-3.5 px-2 text-right font-bold text-emerald-400" x-text="formatarCentavos(item.total_dinheiro_recebido_centavos)"></td>
                                            <td class="py-3.5 px-2 text-right">
                                                <div class="font-bold text-sm"
                                                     :class="item.saldo_acerto_centavos > 0 ? 'text-emerald-400' : (item.saldo_acerto_centavos < 0 ? 'text-amber-400' : 'text-slate-400')"
                                                     x-text="formatarCentavos(Math.abs(item.saldo_acerto_centavos))"></div>
                                                <div class="text-[10px] font-semibold uppercase tracking-tight"
                                                     :class="item.saldo_acerto_centavos > 0 ? 'text-emerald-500' : (item.saldo_acerto_centavos < 0 ? 'text-amber-500' : 'text-slate-500')"
                                                     x-text="item.saldo_acerto_centavos > 0 ? 'Loja Paga Motoboy' : (item.saldo_acerto_centavos < 0 ? 'Motoboy Repassa Loja' : 'Quitado / Zerado')"></div>
                                            </td>
                                            <td class="py-3.5 pl-2 text-right">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    <button @click="imprimirAcertoMotoboy(item.id)" title="Imprimir Recibo Térmico 80mm"
                                                        class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg></button>
                                                    <template x-if="item.whatsapp_link">
                                                        <a :href="item.whatsapp_link" target="_blank" title="Enviar Extrato no WhatsApp"
                                                            class="p-2 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 transition inline-flex items-center justify-center">
                                                            </a>
                                                    </template>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>
                </div>
            </div>
        </div>
