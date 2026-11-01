    <div x-cloak x-show="modalNovaArea" class="fixed inset-0 z-50 bg-black/75 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="modalNovaArea = false" class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-t-3xl sm:rounded-3xl p-5 space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="font-bold text-base text-white">Nova Taxa de Entrega por Bairro</h3>
                <button @click="modalNovaArea = false" class="text-slate-400">✕</button>
            </div>
            <form @submit.prevent="salvarNovaArea()" class="space-y-3 text-xs">
                <div>
                    <label class="font-bold text-slate-400 block mb-1">Nome do Bairro *</label>
                    <input type="text" x-model="novaArea.nome_bairro" required placeholder="Ex: Santa Helena" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Taxa de Entrega (R$)</label>
                        <input type="text" x-model="novaArea.taxa_reais" required placeholder="6,00" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                    </div>
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Tempo Estimado (min)</label>
                        <input type="number" x-model.number="novaArea.tempo_estimado_min" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                    </div>
                </div>
                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-extrabold py-3 rounded-xl text-sm">
                    Salvar Taxa de Bairro
                </button>
            </form>
        </div>
    </div>
