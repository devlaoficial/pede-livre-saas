    <div x-cloak x-show="pedidoSelecionado" class="fixed inset-0 z-50 bg-black/80 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="pedidoSelecionado = null; mostrandoMotivoCancelamento = false"
             class="bg-slate-900 border border-slate-800 w-full max-w-lg rounded-t-3xl sm:rounded-3xl p-5 space-y-4 max-h-[92vh] overflow-y-auto shadow-2xl">
            <template x-if="pedidoSelecionado">
                <div class="space-y-4 text-xs">
                    <!-- Cabeçalho do Pedido -->
                    <div class="flex justify-between items-start border-b border-slate-800 pb-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-black text-rose-400" x-text="'Pedido #' + pedidoSelecionado.codigo_publico"></span>
                                <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-200 font-bold uppercase text-[10px]" x-text="pedidoSelecionado.canal"></span>
                                <span class="px-2 py-0.5 rounded font-bold uppercase text-[10px]"
                                    :class="{
                                        'bg-rose-500/20 text-rose-400': pedidoSelecionado.status === 'novo',
                                        'bg-amber-500/20 text-amber-400': pedidoSelecionado.status === 'em_preparo' || pedidoSelecionado.status === 'aceito',
                                        'bg-emerald-500/20 text-emerald-400': pedidoSelecionado.status === 'pronto',
                                        'bg-sky-500/20 text-sky-400': pedidoSelecionado.status === 'saiu_entrega',
                                        'bg-slate-700 text-slate-300': ['entregue', 'retirado', 'servido'].includes(pedidoSelecionado.status),
                                        'bg-red-950 text-red-400 border border-red-800': pedidoSelecionado.status === 'cancelado'
                                    }"
                                    x-text="pedidoSelecionado.status"></span>
                            </div>
                            <p class="text-slate-400 mt-0.5" x-text="'Recebido há ' + pedidoSelecionado.tempo_decorrido_minutos + ' minutos'"></p>
                        </div>
                        <button @click="pedidoSelecionado = null; mostrandoMotivoCancelamento = false" class="text-slate-400 hover:text-white text-base font-bold">✕</button>
                    </div>

                    <!-- Alerta de Pedido Cancelado (se houver) -->
                    <template x-if="pedidoSelecionado.status === 'cancelado'">
                        <div class="bg-red-950/40 border border-red-500/30 rounded-2xl p-3.5 space-y-1">
                            <span class="font-extrabold text-red-400 flex items-center gap-1.5">
                                <span>🚫</span> Pedido Cancelado
                            </span>
                            <p class="text-slate-300 text-xs" x-text="'Motivo registrado: ' + (pedidoSelecionado.cancelado_motivo || 'Cancelado pelo operador')"></p>
                        </div>
                    </template>

                    <!-- Dados do Cliente -->
                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 flex items-center justify-between gap-2">
                        <div>
                            <div class="font-bold text-white text-sm" x-text="pedidoSelecionado.cliente?.nome || 'Cliente Balcão'"></div>
                            <div class="text-slate-400" x-text="(pedidoSelecionado.cliente?.telefone || '') + ' • ' + (pedidoSelecionado.cliente?.total_pedidos_historico || 1) + 'º pedido na loja'"></div>
                        </div>
                        <template x-if="pedidoSelecionado.whatsapp_notificacao_url">
                            <a :href="pedidoSelecionado.whatsapp_notificacao_url" target="_blank"
                               class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-3 py-2 rounded-xl flex items-center gap-1.5 transition">
                                <span>💬</span> WhatsApp
                            </a>
                        </template>
                    </div>

                    <!-- Endereço de Entrega + Link Google Maps -->
                    <template x-if="pedidoSelecionado.endereco && (pedidoSelecionado.endereco.rua || pedidoSelecionado.endereco.logradouro)">
                        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-300">📍 Endereço de Entrega</span>
                                <a :href="'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent((pedidoSelecionado.endereco.rua || pedidoSelecionado.endereco.logradouro) + ', ' + pedidoSelecionado.endereco.numero + ' - ' + pedidoSelecionado.endereco.bairro + ', ' + (pedidoSelecionado.endereco.cidade || ''))"
                                   target="_blank" class="text-sky-400 hover:underline font-bold">
                                    🗺️ Google Maps ↗
                                </a>
                            </div>
                            <div class="text-slate-200 font-semibold" x-text="(pedidoSelecionado.endereco.rua || pedidoSelecionado.endereco.logradouro) + ', ' + pedidoSelecionado.endereco.numero + (pedidoSelecionado.endereco.complemento ? ' (' + pedidoSelecionado.endereco.complemento + ')' : '')"></div>
                            <div class="text-slate-400" x-text="pedidoSelecionado.endereco.bairro + ' • ' + (pedidoSelecionado.endereco.cidade || '') + ' • CEP: ' + (pedidoSelecionado.endereco.cep || '')"></div>
                            <template x-if="pedidoSelecionado.endereco.referencia">
                                <div class="text-amber-300 font-bold" x-text="'Ref: ' + pedidoSelecionado.endereco.referencia"></div>
                            </template>
                        </div>
                    </template>

                    <!-- Despacho com Motoboy (se Delivery e Pronto) -->
                    <template x-if="pedidoSelecionado.canal === 'delivery' && pedidoSelecionado.status === 'pronto'">
                        <div class="bg-slate-950 border border-emerald-500/30 rounded-2xl p-3.5 space-y-2">
                            <span class="font-bold text-emerald-400 flex items-center gap-1.5">
                                <span>🛵</span> Despachar Entrega Imediatamente
                            </span>
                            <div class="flex gap-2">
                                <select x-model="pedidoSelecionado.entregador_id"
                                    class="flex-1 bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white font-bold focus:border-rose-500 focus:outline-none">
                                    <option value="">Selecione o motoboy...</option>
                                    <template x-for="ent in entregadores" :key="ent.id">
                                        <option :value="ent.id" x-text="ent.nome + (ent.telefone ? ' (' + ent.telefone + ')' : '')"></option>
                                    </template>
                                </select>
                                <button type="button" @click="despacharMotoboyRapido(pedidoSelecionado.id, pedidoSelecionado.entregador_id)"
                                    :disabled="!pedidoSelecionado.entregador_id"
                                    class="bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 text-white font-black px-4 py-2 rounded-xl transition">
                                    Despachar
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- Itens e Adicionais -->
                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 space-y-2">
                        <div class="font-bold text-slate-300">🍔 Itens do Pedido</div>
                        <template x-for="it in pedidoSelecionado.itens" :key="it.id">
                            <div class="border-b border-slate-800/80 last:border-0 pb-2 last:pb-0">
                                <div class="flex justify-between font-bold text-white">
                                    <span x-text="it.qtd + 'x ' + it.nome + (it.variacao ? ' (' + it.variacao + ')' : '')"></span>
                                    <span x-text="formatarCentavos(it.total)"></span>
                                </div>
                                <template x-if="it.adicionais && it.adicionais.length > 0">
                                    <div class="text-slate-400 pl-3 text-[11px]" x-text="it.adicionais.map(a => '+ ' + a.nome).join(', ')"></div>
                                </template>
                                <template x-if="it.observacao">
                                    <div class="text-amber-400 font-bold pl-3 text-[11px]" x-text="'⚠️ Obs: ' + it.observacao"></div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- Resumo Financeiro do Pedido -->
                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 space-y-1.5">
                        <div class="flex justify-between text-slate-400"><span>Subtotal:</span><span x-text="formatarCentavos(pedidoSelecionado.subtotal_centavos)"></span></div>
                        <div class="flex justify-between text-slate-400" x-show="pedidoSelecionado.taxa_entrega_centavos > 0"><span>Taxa de Entrega:</span><span x-text="formatarCentavos(pedidoSelecionado.taxa_entrega_centavos)"></span></div>
                        <div class="flex justify-between text-emerald-400" x-show="pedidoSelecionado.desconto_centavos > 0"><span>Desconto Cupom:</span><span x-text="'- ' + formatarCentavos(pedidoSelecionado.desconto_centavos)"></span></div>
                        <div class="flex justify-between text-emerald-400" x-show="pedidoSelecionado.desconto_cashback_centavos > 0"><span>Cashback Fidelidade:</span><span x-text="'- ' + formatarCentavos(pedidoSelecionado.desconto_cashback_centavos)"></span></div>
                        <div class="flex justify-between text-white font-black text-sm pt-1.5 border-t border-slate-800">
                            <span x-text="'Total (' + (pedidoSelecionado.forma_pagamento || '').toUpperCase() + ')'"></span>
                            <span class="text-emerald-400" x-text="formatarCentavos(pedidoSelecionado.total_centavos)"></span>
                        </div>
                        <template x-if="pedidoSelecionado.troco_para">
                            <div class="text-amber-300 font-bold pt-1" x-text="'💵 Levar troco para: ' + formatarCentavos(pedidoSelecionado.troco_para)"></div>
                        </template>
                    </div>

                    <!-- Botões de Impressão 80mm -->
                    <div class="flex gap-2">
                        <button @click="imprimirPedido(pedidoSelecionado.id, 'cozinha')" class="flex-1 bg-slate-800 hover:bg-slate-700 text-white font-bold py-2.5 rounded-xl transition flex items-center justify-center gap-1.5">
                            <span>🖨️</span> Cupom Cozinha
                        </button>
                        <button @click="imprimirPedido(pedidoSelecionado.id, 'entrega')" class="flex-1 bg-slate-800 hover:bg-slate-700 text-white font-bold py-2.5 rounded-xl transition flex items-center justify-center gap-1.5">
                            <span>🖨️</span> Cupom Caixa/Entrega
                        </button>
                    </div>

                    <!-- Seção de Cancelamento do Pedido com Motivo -->
                    <template x-if="pedidoSelecionado.status !== 'cancelado' && !['entregue', 'retirado', 'servido'].includes(pedidoSelecionado.status)">
                        <div class="pt-2 border-t border-slate-800">
                            <template x-if="!mostrandoMotivoCancelamento">
                                <button type="button" @click="mostrandoMotivoCancelamento = true"
                                    class="w-full bg-slate-950 hover:bg-rose-950/50 text-slate-400 hover:text-rose-300 border border-slate-800 hover:border-rose-800 font-bold py-2 rounded-xl transition flex items-center justify-center gap-1.5">
                                    <span>✕</span> Cancelar este Pedido
                                </button>
                            </template>

                            <template x-if="mostrandoMotivoCancelamento">
                                <div class="bg-rose-950/30 border border-rose-500/40 rounded-2xl p-3.5 space-y-2.5">
                                    <span class="font-extrabold text-rose-300 flex items-center gap-1.5">
                                        <span>⚠️</span> Motivo do Cancelamento
                                    </span>
                                    <select x-model="motivoCancelamento" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white font-bold focus:border-rose-500 focus:outline-none">
                                        <option value="Cliente solicitou o cancelamento">Cliente solicitou o cancelamento</option>
                                        <option value="Item esgotado no estoque / ingrediente em falta">Item esgotado no estoque / indisponível</option>
                                        <option value="Endereço fora da área de entrega">Endereço fora da área de entrega</option>
                                        <option value="Cliente não responde no WhatsApp ou telefone">Cliente não responde no WhatsApp</option>
                                        <option value="Tempo de espera elevado">Tempo de espera elevado</option>
                                        <option value="Outro">Outro motivo (especificar abaixo)</option>
                                    </select>
                                    <template x-if="motivoCancelamento === 'Outro'">
                                        <input type="text" x-model="motivoCancelamentoOutro" placeholder="Digite o motivo específico..."
                                            class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none">
                                    </template>
                                    <div class="flex gap-2 pt-1">
                                        <button type="button" @click="mostrandoMotivoCancelamento = false"
                                            class="w-1/3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold py-2 rounded-xl transition">
                                            Voltar
                                        </button>
                                        <button type="button" @click="confirmarCancelamentoPedidoModal()"
                                            class="w-2/3 bg-rose-600 hover:bg-rose-700 text-white font-black py-2 rounded-xl shadow-lg transition">
                                            Confirmar Cancelamento
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>