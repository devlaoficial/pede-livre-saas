    <!-- MODAL: ABERTURA DE TURNO DE CAIXA -->
    <div x-cloak x-show="modalAbrirCaixa" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-3">
        <div @click.away="modalAbrirCaixa = false"
             class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-3xl p-5 space-y-4 shadow-2xl">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
                    <div>
                        <h4 class="font-bold text-white text-base">Abrir Turno de Caixa</h4>
                        <p class="text-[11px] text-slate-400">Informe o fundo de troco inicial da gaveta</p>
                    </div>
                </div>
                <button @click="modalAbrirCaixa = false" class="text-slate-400 hover:text-white font-bold text-lg"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>

            <form @submit.prevent="abrirTurnoCaixa()" class="space-y-4 text-xs">
                <div>
                    <label class="block font-bold text-slate-300 mb-1.5 uppercase tracking-wider text-[11px]">
                        Fundo de Troco Inicial (R$) *
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-slate-400 font-bold">R$</span>
                        <input type="text" x-model="formAbrirCaixa.valor_inicial_reais" required placeholder="100,00"
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-10 pr-3 py-2.5 text-white font-bold text-base focus:border-emerald-500 focus:outline-none">
                    </div>
                </div>

                <!-- Atalhos de valores comuns -->
                <div class="flex gap-2">
                    <button type="button" @click="formAbrirCaixa.valor_inicial_reais = '50,00'"
                        class="flex-1 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 rounded-lg text-slate-300 font-bold">
                        R$ 50,00
                    </button>
                    <button type="button" @click="formAbrirCaixa.valor_inicial_reais = '100,00'"
                        class="flex-1 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 rounded-lg text-slate-300 font-bold">
                        R$ 100,00
                    </button>
                    <button type="button" @click="formAbrirCaixa.valor_inicial_reais = '200,00'"
                        class="flex-1 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 rounded-lg text-slate-300 font-bold">
                        R$ 200,00
                    </button>
                </div>

                <div class="flex gap-2 pt-2 border-t border-slate-800">
                    <button type="button" @click="modalAbrirCaixa = false"
                        class="w-1/3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold py-2.5 rounded-xl transition">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="salvandoCaixa"
                        class="w-2/3 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-bold py-2.5 rounded-xl shadow-lg shadow-emerald-600/25 transition flex items-center justify-center gap-1.5">
                        <span x-show="!salvandoCaixa">Confirmar Abertura</span>
                        <span x-show="salvandoCaixa">Abrindo Caixa...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
