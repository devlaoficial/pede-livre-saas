    <div x-cloak x-show="mesaDivisao" class="fixed inset-0 z-50 bg-black/80 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="mesaDivisao = null" class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-t-3xl sm:rounded-3xl p-5 space-y-4">
            <template x-if="mesaDivisao">
                <div class="space-y-4 text-xs">
                    <div class="flex justify-between items-center">
                        <h3 class="font-black text-base text-white" x-text="'🪑 Fechamento & Divisão — Mesa #' + mesaDivisao.numero"></h3>
                        <button @click="mesaDivisao = null" class="text-slate-400 text-base">✕</button>
                    </div>

                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 space-y-2">
                        <div class="flex justify-between text-slate-400">
                            <span>Consumo dos Pedidos da Mesa:</span>
                            <span class="font-bold text-white" x-text="formatarCentavos(totalConsumoMesaCentavos)"></span>
                        </div>
                        <label class="flex items-center justify-between cursor-pointer pt-1">
                            <span class="text-slate-300 font-bold">Incluir Taxa de Serviço Garçom (10%)</span>
                            <input type="checkbox" x-model="incluirTaxaServicoMesa" class="rounded bg-slate-900 border-slate-700 text-rose-600">
                        </label>
                        <div class="flex justify-between text-sm font-black text-emerald-400 pt-2 border-t border-slate-800">
                            <span>Total Final da Mesa:</span>
                            <span x-text="formatarCentavos(totalFinalMesaCentavos)"></span>
                        </div>
                    </div>

                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-white">Dividir entre quantas pessoas?</div>
                            <div class="text-slate-400">Cálculo automático por pessoa</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="pessoasDivisaoMesa = Math.max(1, pessoasDivisaoMesa - 1)" class="w-8 h-8 rounded-xl bg-slate-800 text-white font-black">-</button>
                            <span class="text-base font-black text-white w-6 text-center" x-text="pessoasDivisaoMesa"></span>
                            <button @click="pessoasDivisaoMesa++" class="w-8 h-8 rounded-xl bg-slate-800 text-white font-black">+</button>
                        </div>
                    </div>

                    <div class="bg-emerald-500/15 border border-emerald-500/30 rounded-2xl p-4 text-center space-y-1">
                        <span class="text-[11px] font-bold uppercase text-emerald-300">Valor por Pessoa (<span x-text="pessoasDivisaoMesa"></span>x)</span>
                        <div class="text-2xl font-black text-emerald-400" x-text="formatarCentavos(Math.ceil(totalFinalMesaCentavos / Math.max(1, pessoasDivisaoMesa)))"></div>
                    </div>

                    <button @click="mesaDivisao = null; toastMsg = 'Conferência da Mesa #' + mesaDivisao.numero + ' concluída!'" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-black py-3 rounded-xl text-sm">
                        ✓ Concluir Conferência da Mesa
                    </button>
                </div>
            </template>
        </div>
    </div>
