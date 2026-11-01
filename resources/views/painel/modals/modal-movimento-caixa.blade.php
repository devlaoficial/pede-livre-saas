    <!-- MODAL: LANÇAMENTO DE MOVIMENTO NO CAIXA (SANGRIA / SUPRIMENTO) -->
    <div x-cloak x-show="modalMovimentoCaixa" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-3">
        <div @click.away="modalMovimentoCaixa = false"
             class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-3xl p-5 space-y-4 shadow-2xl">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center font-black"
                          :class="formMovimentoCaixa.tipo === 'sangria' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'">
                        <span x-text="formMovimentoCaixa.tipo === 'sangria' ? '💸' : '📥'"></span>
                    </span>
                    <div>
                        <h4 class="font-black text-white text-base">Movimentação de Caixa</h4>
                        <p class="text-[11px] text-slate-400">Registre entradas ou saídas manuais de dinheiro</p>
                    </div>
                </div>
                <button @click="modalMovimentoCaixa = false" class="text-slate-400 hover:text-white font-bold text-lg">✕</button>
            </div>

            <form @submit.prevent="registrarMovimentoCaixa()" class="space-y-4 text-xs">
                <!-- Seletor de Tipo: Sangria vs Suprimento -->
                <div>
                    <label class="block font-bold text-slate-300 mb-1.5 uppercase tracking-wider text-[11px]">Tipo de Movimentação *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="formMovimentoCaixa.tipo = 'sangria'"
                            class="py-2.5 px-3 rounded-xl font-bold border transition text-center flex items-center justify-center gap-1.5"
                            :class="formMovimentoCaixa.tipo === 'sangria' ? 'bg-rose-600 border-rose-500 text-white shadow-md' : 'bg-slate-950 border-slate-800 text-slate-400'">
                            <span>💸</span> Sangria (Saída)
                        </button>
                        <button type="button" @click="formMovimentoCaixa.tipo = 'suprimento'"
                            class="py-2.5 px-3 rounded-xl font-bold border transition text-center flex items-center justify-center gap-1.5"
                            :class="formMovimentoCaixa.tipo === 'suprimento' ? 'bg-emerald-600 border-emerald-500 text-white shadow-md' : 'bg-slate-950 border-slate-800 text-slate-400'">
                            <span>📥</span> Suprimento (Entrada)
                        </button>
                    </div>
                </div>

                <!-- Campo de Valor -->
                <div>
                    <label class="block font-bold text-slate-300 mb-1.5 uppercase tracking-wider text-[11px]">Valor (R$) *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-slate-400 font-bold">R$</span>
                        <input type="text" x-model="formMovimentoCaixa.valor_reais" required placeholder="0,00"
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-10 pr-3 py-2.5 text-white font-bold text-base focus:border-rose-500 focus:outline-none">
                    </div>
                </div>

                <!-- Campo de Motivo / Observação -->
                <div>
                    <label class="block font-bold text-slate-300 mb-1.5 uppercase tracking-wider text-[11px]">Motivo / Descrição *</label>
                    <input type="text" x-model="formMovimentoCaixa.observacao" required
                        :placeholder="formMovimentoCaixa.tipo === 'sangria' ? 'Ex: Pagamento fornecedor de gelo' : 'Ex: Troco extra em notas de 5 e 2 reais'"
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none">
                </div>

                <div class="flex gap-2 pt-2 border-t border-slate-800">
                    <button type="button" @click="modalMovimentoCaixa = false"
                        class="w-1/3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold py-2.5 rounded-xl transition">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="salvandoCaixa"
                        class="w-2/3 text-white font-black py-2.5 rounded-xl shadow-lg transition flex items-center justify-center gap-1.5"
                        :class="formMovimentoCaixa.tipo === 'sangria' ? 'bg-rose-600 hover:bg-rose-500' : 'bg-emerald-600 hover:bg-emerald-500'">
                        <span x-show="!salvandoCaixa">Salvar Lançamento</span>
                        <span x-show="salvandoCaixa">Registrando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
