    <div x-cloak x-show="modalPdv" class="fixed inset-0 z-50 bg-black/75 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="modalPdv = false" class="bg-slate-900 border border-slate-800 w-full max-w-lg rounded-t-3xl sm:rounded-3xl p-5 space-y-4 max-h-[92vh] overflow-y-auto">
            <div class="flex justify-between items-center">
                <h3 class="font-black text-base text-white">⚡ Lançar Pedido Rápido (PDV Balcão / Telefone)</h3>
                <button @click="modalPdv = false" class="text-slate-400 font-bold">✕</button>
            </div>
            <div class="space-y-3 text-xs">
                <div class="grid grid-cols-2 gap-2.5">
                    <input type="text" x-model="pdv.nome" placeholder="Nome do Cliente *" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-white">
                    <input type="text" x-model="pdv.telefone" placeholder="WhatsApp *" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-white">
                </div>
                <div class="grid grid-cols-2 gap-2.5">
                    <select x-model="pdv.canal" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-white font-bold">
                        <option value="balcao">🛍️ Balcão / Retirada</option>
                        <option value="delivery">🛵 Delivery</option>
                    </select>
                    <select x-model="pdv.forma_pagamento" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-white font-bold">
                        <option value="cartao_maquina">💳 Cartão Presencial</option>
                        <option value="dinheiro">💵 Dinheiro</option>
                        <option value="pix">⚡ PIX QR Code</option>
                    </select>
                </div>

                <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3 max-h-48 overflow-y-auto space-y-1.5">
                    <div class="text-[11px] font-bold uppercase text-slate-400 mb-1">Toque nos produtos para adicionar:</div>
                    <template x-for="p in produtos" :key="p.id">
                        <div @click="adicionarItemPdv(p)" class="flex justify-between items-center p-2 rounded-xl bg-slate-900 hover:bg-slate-800 cursor-pointer transition">
                            <span class="font-semibold text-white" x-text="p.nome"></span>
                            <span class="font-bold text-emerald-400" x-text="'+ ' + formatarCentavos(p.preco_efetivo_centavos)"></span>
                        </div>
                    </template>
                </div>

                <template x-if="pdv.itens.length > 0">
                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3 space-y-1.5">
                        <template x-for="(it, i) in pdv.itens" :key="i">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-200 font-bold" x-text="it.qtd + 'x ' + it.nome"></span>
                                <button @click="pdv.itens.splice(i, 1)" class="text-rose-400 font-bold">✕</button>
                            </div>
                        </template>
                    </div>
                </template>

                <button @click="enviarPedidoPdv()" :disabled="pdv.itens.length === 0" class="w-full bg-rose-600 hover:bg-rose-500 disabled:opacity-40 text-white font-black py-3.5 rounded-xl text-sm">
                    Confirmar Pedido no Kanban
                </button>
            </div>
        </div>
    </div>
