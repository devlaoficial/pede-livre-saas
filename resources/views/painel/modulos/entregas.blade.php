<div x-cloak x-show="moduloAtivo === 'entregas'" class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Coluna Motoboys -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-bold text-base text-white">🛵 Equipe de Entregadores (Motoboys)</h2>
                            <p class="text-xs text-slate-400">Cadastre motoboys próprios ou freelancers para despacho</p>
                        </div>
                        <button @click="modalNovoEntregador = true" class="bg-rose-600 hover:bg-rose-500 text-white text-xs font-extrabold px-3.5 py-2 rounded-xl">
                            + Entregador
                        </button>
                    </div>
                    <div class="space-y-2.5">
                        <template x-for="ent in entregadores" :key="ent.id">
                            <div class="bg-slate-950 border border-slate-800 p-3.5 rounded-2xl flex items-center justify-between">
                                <div>
                                    <div class="font-bold text-sm text-white" x-text="ent.nome"></div>
                                    <div class="text-xs text-slate-400" x-text="(ent.tipo || 'proprio').toUpperCase() + ' • Taxa/corrida: ' + formatarCentavos(ent.valor_por_entrega_centavos)"></div>
                                </div>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-500/15 text-emerald-400">Ativo</span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Coluna Áreas e Taxas de Entrega -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-bold text-base text-white">📍 Taxas de Entrega (Bairro &amp; Raio KM)</h2>
                            <p class="text-xs text-slate-400">Regras de frete calculadas automaticamente no checkout</p>
                        </div>
                        <button @click="modalNovaArea = true" class="bg-rose-600 hover:bg-rose-500 text-white text-xs font-extrabold px-3.5 py-2 rounded-xl">
                            + Nova Taxa
                        </button>
                    </div>
                    <div class="space-y-2.5">
                        <template x-for="area in areasEntrega" :key="area.id">
                            <div class="bg-slate-950 border border-slate-800 p-3.5 rounded-2xl flex items-center justify-between">
                                <div>
                                    <div class="font-bold text-sm text-white"
                                         x-text="area.tipo === 'bairro' ? 'Bairro: ' + area.nome_bairro : 'Raio: ' + area.raio_km_de + 'km até ' + area.raio_km_ate + 'km'"></div>
                                    <div class="text-xs text-slate-400" x-text="'Mínimo: ' + formatarCentavos(area.pedido_minimo_centavos) + ' • Tempo: ' + area.tempo_estimado_min + ' min'"></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-black text-emerald-400" x-text="formatarCentavos(area.taxa_centavos)"></div>
                                    <button @click="removerAreaEntrega(area.id)" class="text-[11px] text-rose-400 hover:underline">Remover</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Seção de Acerto Diário de Motoboys (Etapa 3) -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-sky-500/20 text-sky-400 border border-sky-500/30 flex items-center justify-center font-black">💼</span>
                            <h3 class="font-extrabold text-base text-white">Acerto Diário de Motoboys (Fechamento da Noite)</h3>
                            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-slate-800 text-slate-300 border border-slate-700">Hoje</span>
                        </div>
                        <p class="text-xs text-slate-400">
                            Totalização de entregas concluídas, cálculo de taxas ganhas e conciliação do dinheiro recebido na rua para prestação de contas.
                        </p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button @click="carregarFechamentoMotoboys()" :disabled="carregandoFechamentoMotoboys"
                            class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold px-3 py-2 rounded-xl transition flex items-center gap-1.5">
                            <span :class="carregandoFechamentoMotoboys ? 'animate-spin' : ''">🔄</span> Atualizar Acertos
                        </button>
                    </div>
                </div>

                <!-- KPI Cards do Fechamento de Entregas -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-1">
                        <span class="text-slate-400 font-bold uppercase text-[10px]">Entregas Concluídas</span>
                        <div class="text-xl sm:text-2xl font-black text-white" x-text="totaisFechamentoMotoboys.total_entregas || 0"></div>
                    </div>
                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-1">
                        <span class="text-slate-400 font-bold uppercase text-[10px]">Total Taxas / Diárias</span>
                        <div class="text-xl sm:text-2xl font-black text-sky-400" x-text="formatarCentavos(totaisFechamentoMotoboys.total_taxas_centavos || 0)"></div>
                    </div>
                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-1">
                        <span class="text-slate-400 font-bold uppercase text-[10px]">Dinheiro Cobrado na Rua</span>
                        <div class="text-xl sm:text-2xl font-black text-emerald-400" x-text="formatarCentavos(totaisFechamentoMotoboys.total_dinheiro_recebido_centavos || 0)"></div>
                    </div>
                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-1">
                        <span class="text-slate-400 font-bold uppercase text-[10px]">Saldo Geral Líquido</span>
                        <div class="text-xl sm:text-2xl font-black"
                             :class="(totaisFechamentoMotoboys.saldo_geral_centavos || 0) >= 0 ? 'text-emerald-400' : 'text-amber-400'"
                             x-text="formatarCentavos(totaisFechamentoMotoboys.saldo_geral_centavos || 0)"></div>
                    </div>
                </div>

                <!-- Tabela / Lista de Acertos por Motoboy -->
                <div class="space-y-3">
                    <template x-if="fechamentoMotoboys.length === 0">
                        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-8 text-center space-y-2">
                            <div class="text-3xl">🛵</div>
                            <h4 class="font-extrabold text-white text-sm">Nenhum motoboy com entregas registradas hoje</h4>
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
                                                <div class="font-black text-white text-sm" x-text="item.nome"></div>
                                                <div class="text-[11px] text-slate-400" x-text="(item.tipo || 'proprio').toUpperCase() + (item.telefone ? ' • ' + item.telefone : '')"></div>
                                            </td>
                                            <td class="py-3.5 px-2 text-center">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full font-black text-xs"
                                                      :class="item.total_entregas > 0 ? 'bg-sky-500/20 text-sky-300' : 'bg-slate-800 text-slate-400'"
                                                      x-text="item.total_entregas + ' corrida(s)'"></span>
                                            </td>
                                            <td class="py-3.5 px-2 text-right text-slate-300" x-text="formatarCentavos(item.valor_por_entrega_centavos)"></td>
                                            <td class="py-3.5 px-2 text-right font-bold text-sky-400" x-text="formatarCentavos(item.total_taxas_centavos)"></td>
                                            <td class="py-3.5 px-2 text-right font-bold text-emerald-400" x-text="formatarCentavos(item.total_dinheiro_recebido_centavos)"></td>
                                            <td class="py-3.5 px-2 text-right">
                                                <div class="font-black text-sm"
                                                     :class="item.saldo_acerto_centavos > 0 ? 'text-emerald-400' : (item.saldo_acerto_centavos < 0 ? 'text-amber-400' : 'text-slate-400')"
                                                     x-text="formatarCentavos(Math.abs(item.saldo_acerto_centavos))"></div>
                                                <div class="text-[10px] font-extrabold uppercase tracking-tight"
                                                     :class="item.saldo_acerto_centavos > 0 ? 'text-emerald-500' : (item.saldo_acerto_centavos < 0 ? 'text-amber-500' : 'text-slate-500')"
                                                     x-text="item.saldo_acerto_centavos > 0 ? 'Loja Paga Motoboy' : (item.saldo_acerto_centavos < 0 ? 'Motoboy Repassa Loja' : 'Quitado / Zerado')"></div>
                                            </td>
                                            <td class="py-3.5 pl-2 text-right">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    <button @click="imprimirAcertoMotoboy(item.id)" title="Imprimir Recibo Térmico 80mm"
                                                        class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition">
                                                        🖨️
                                                    </button>
                                                    <template x-if="item.whatsapp_link">
                                                        <a :href="item.whatsapp_link" target="_blank" title="Enviar Extrato no WhatsApp"
                                                            class="p-2 rounded-xl bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 transition inline-flex items-center justify-center">
                                                            💬
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
