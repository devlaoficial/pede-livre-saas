    <div x-cloak x-show="pedidoSelecionado" class="fixed inset-0 z-50 bg-black/80 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="pedidoSelecionado = null" class="bg-slate-900 border border-slate-800 w-full max-w-lg rounded-t-3xl sm:rounded-3xl p-5 space-y-4 max-h-[92vh] overflow-y-auto">
            <template x-if="pedidoSelecionado">
                <div class="space-y-4 text-xs">
                    <div class="flex justify-between items-start border-b border-slate-800 pb-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-black text-rose-400" x-text="'Pedido #' + pedidoSelecionado.codigo_publico"></span>
                                <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-200 font-bold uppercase text-[10px]" x-text="pedidoSelecionado.canal"></span>
                                <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold uppercase text-[10px]" x-text="pedidoSelecionado.status"></span>
                            </div>
                            <p class="text-slate-400 mt-0.5" x-text="'Recebido há ' + pedidoSelecionado.tempo_decorrido_minutos + ' minutos'"></p>
                        </div>
                        <button @click="pedidoSelecionado = null" class="text-slate-400 text-base font-bold">✕</button>
                    </div>

                    <!-- Dados do Cliente -->
                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 flex items-center justify-between gap-2">
                        <div>
                            <div class="font-bold text-white text-sm" x-text="pedidoSelecionado.cliente?.nome || 'Cliente Balcão'"></div>
                            <div class="text-slate-400" x-text="(pedidoSelecionado.cliente?.telefone || '') + ' • ' + (pedidoSelecionado.cliente?.total_pedidos_historico || 1) + 'º pedido na loja'"></div>
                        </div>
                        <template x-if="pedidoSelecionado.whatsapp_notificacao_url">
                            <a :href="pedidoSelecionado.whatsapp_notificacao_url" target="_blank" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-3 py-2 rounded-xl">
                                💬 WhatsApp
                            </a>
                        </template>
                    </div>

                    <!-- Endereço de Entrega + Link Google Maps -->
                    <template x-if="pedidoSelecionado.endereco && pedidoSelecionado.endereco.rua">
                        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-300">📍 Endereço de Entrega</span>
                                <a :href="'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(pedidoSelecionado.endereco.rua + ', ' + pedidoSelecionado.endereco.numero + ' - ' + pedidoSelecionado.endereco.bairro + ', ' + (pedidoSelecionado.endereco.cidade || ''))"
                                   target="_blank" class="text-sky-400 hover:underline font-bold">
                                    🗺️ Abrir no Google Maps ↗
                                </a>
                            </div>
                            <div class="text-slate-200 font-semibold" x-text="pedidoSelecionado.endereco.rua + ', ' + pedidoSelecionado.endereco.numero + (pedidoSelecionado.endereco.complemento ? ' (' + pedidoSelecionado.endereco.complemento + ')' : '')"></div>
                            <div class="text-slate-400" x-text="pedidoSelecionado.endereco.bairro + ' • ' + (pedidoSelecionado.endereco.cidade || '') + ' • CEP: ' + (pedidoSelecionado.endereco.cep || '')"></div>
                            <template x-if="pedidoSelecionado.endereco.referencia">
                                <div class="text-amber-300 font-bold" x-text="'Ref: ' + pedidoSelecionado.endereco.referencia"></div>
                            </template>
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
                                    <div class="text-slate-400 pl-3" x-text="it.adicionais.map(a => '+ ' + a.nome).join(', ')"></div>
                                </template>
                                <template x-if="it.observacao">
                                    <div class="text-amber-400 font-bold pl-3" x-text="'⚠️ Obs: ' + it.observacao"></div>
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

                    <div class="flex gap-2">
                        <button @click="imprimirPedido(pedidoSelecionado.id, 'cozinha')" class="flex-1 bg-slate-800 hover:bg-slate-700 text-white font-bold py-2.5 rounded-xl">
                            🖨️ Cupom Cozinha 80mm
                        </button>
                        <button @click="imprimirPedido(pedidoSelecionado.id, 'entrega')" class="flex-1 bg-slate-800 hover:bg-slate-700 text-white font-bold py-2.5 rounded-xl">
                            🖨️ Cupom Caixa/Motoboy
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
