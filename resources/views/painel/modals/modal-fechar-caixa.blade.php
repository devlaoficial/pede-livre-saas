    <!-- MODAL: FECHAMENTO DE TURNO DE CAIXA -->
    <div x-cloak x-show="modalFecharCaixa" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-3">
        <div @click.away="modalFecharCaixa = false"
             class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-3xl p-5 space-y-4 shadow-2xl">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center font-bold"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg></span>
                    <div>
                        <h4 class="font-bold text-white text-base">Fechar Turno de Caixa</h4>
                        <p class="text-[11px] text-slate-400">Faça a contagem física do dinheiro na gaveta</p>
                    </div>
                </div>
                <button @click="modalFecharCaixa = false" class="text-slate-400 hover:text-white font-bold text-lg"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>

            <form @submit.prevent="fecharTurnoCaixa()" class="space-y-4 text-xs">
                <!-- Resumo do Caixa Aberto -->
                <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 space-y-1.5">
                    <div class="flex justify-between text-slate-400">
                        <span>Fundo Inicial:</span>
                        <span class="font-bold text-slate-200" x-text="formatarCentavos(caixaStatus.sessao?.valor_inicial_centavos)"></span>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <span>(+) Vendas em Dinheiro:</span>
                        <span class="font-bold text-emerald-400" x-text="'+ ' + formatarCentavos(caixaStatus.sessao?.total_vendas_dinheiro_centavos)"></span>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <span>(+) Suprimentos (Entradas):</span>
                        <span class="font-bold text-emerald-400" x-text="'+ ' + formatarCentavos(caixaStatus.sessao?.total_suprimentos_centavos)"></span>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <span>(-) Sangrias (Retiradas):</span>
                        <span class="font-bold text-rose-400" x-text="'- ' + formatarCentavos(caixaStatus.sessao?.total_sangrias_centavos)"></span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-slate-800 font-semibold text-sm text-white">
                        <span>SALDO ESPERADO GAVETA:</span>
                        <span class="text-emerald-400 text-base" x-text="formatarCentavos(caixaStatus.sessao?.saldo_esperado_gaveta_centavos)"></span>
                    </div>
                </div>

                <!-- Campo: Dinheiro Contado Fisicamente -->
                <div>
                    <label class="block font-bold text-slate-300 mb-1.5 uppercase tracking-wider text-[11px]">
                        Dinheiro Físico Contado na Gaveta (R$) *
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-slate-400 font-bold">R$</span>
                        <input type="text" x-model="formFecharCaixa.valor_final_reais" required placeholder="0,00"
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-10 pr-3 py-2.5 text-white font-bold text-lg focus:border-amber-500 focus:outline-none">
                    </div>
                </div>

                <!-- Preview de Diferença / Quebra de Caixa -->
                <div class="p-3 rounded-xl border flex items-center justify-between"
                     :class="{
                         'bg-emerald-500/10 border-emerald-500/30 text-emerald-300': diferencaFechamentoCaixa() === 0,
                         'bg-amber-500/10 border-amber-500/30 text-amber-300': diferencaFechamentoCaixa() > 0,
                         'bg-rose-500/10 border-rose-500/30 text-rose-300': diferencaFechamentoCaixa() < 0
                     }">
                    <span class="font-bold">Diferença / Quebra:</span>
                    <span class="font-bold text-sm" x-text="rotuloDiferencaFechamentoCaixa()"></span>
                </div>

                <!-- Opção de Impressão 80mm -->
                <label class="flex items-center gap-2 cursor-pointer bg-slate-950 p-2.5 rounded-xl border border-slate-800">
                    <input type="checkbox" x-model="formFecharCaixa.imprimir_80mm" class="rounded text-amber-500 focus:ring-amber-500">
                    <span class="text-slate-300 font-bold">Imprimir extrato de fechamento em impressora 80mm</span>
                </label>

                <div class="flex gap-2 pt-2 border-t border-slate-800">
                    <button type="button" @click="modalFecharCaixa = false"
                        class="w-1/3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold py-2.5 rounded-xl transition">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="salvandoCaixa"
                        class="w-2/3 bg-amber-600 hover:bg-amber-500 disabled:opacity-50 text-white font-bold py-2.5 rounded-xl shadow-lg shadow-amber-600/25 transition flex items-center justify-center gap-1.5">
                        <span x-show="!salvandoCaixa">Confirmar Fechamento</span>
                        <span x-show="salvandoCaixa">Fechando Turno...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
