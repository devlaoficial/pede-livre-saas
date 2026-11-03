    <div x-cloak x-show="modalNovoCupom" class="fixed inset-0 z-50 bg-black/75 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="modalNovoCupom = false" class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-t-3xl sm:rounded-3xl p-5 space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="font-bold text-base text-white">Criar Cupom Promocional</h3>
                <button @click="modalNovoCupom = false" class="text-slate-400"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <form @submit.prevent="salvarNovoCupom()" class="space-y-3 text-xs">
                <div>
                    <label class="font-bold text-slate-400 block mb-1">Código do Cupom *</label>
                    <input type="text" x-model="novoCupom.codigo" required placeholder="Ex: SEXTOU15 ou VOLTE10" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-emerald-400 font-bold uppercase">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Tipo de Desconto</label>
                        <select x-model="novoCupom.tipo" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                            <option value="percentual">Porcentagem (%)</option>
                            <option value="fixo_centavos">Valor Fixo (R$)</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-slate-400 block mb-1" x-text="novoCupom.tipo === 'percentual' ? 'Desconto (%)' : 'Desconto (R$)'"></label>
                        <input type="text" x-model="novoCupom.valor_input" required placeholder="10" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-bold">
                    </div>
                </div>
                <div>
                    <label class="font-bold text-slate-400 block mb-1">Pedido Mínimo p/ Usar (R$)</label>
                    <input type="text" x-model="novoCupom.minimo_reais" placeholder="30,00" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                </div>
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-3 rounded-xl text-sm">
                    Ativar Cupom Agora
                </button>
            </form>
        </div>
    </div>
