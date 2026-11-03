    <div x-cloak x-show="modalNovaArea" class="fixed inset-0 z-50 bg-black/75 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="modalNovaArea = false" class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-t-3xl sm:rounded-3xl p-5 space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="font-bold text-base text-white">Nova Taxa de Entrega</h3>
                <button @click="modalNovaArea = false" class="text-slate-400"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <form @submit.prevent="salvarNovaArea()" class="space-y-3 text-xs">
                <div>
                    <label class="font-bold text-slate-400 block mb-1">Tipo de Regra</label>
                    <select x-model="novaArea.tipo" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-bold">
                        <option value="bairro">Por Nome do Bairro</option>
                        <option value="raio">Por Raio de Distância (KM)</option>
                    </select>
                </div>

                <template x-if="novaArea.tipo === 'bairro'">
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Nome do Bairro *</label>
                        <input type="text" x-model="novaArea.nome_bairro" required placeholder="Ex: Centro" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                    </div>
                </template>

                <template x-if="novaArea.tipo === 'raio'">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="font-bold text-slate-400 block mb-1">Raio Inicial (KM)</label>
                            <input type="number" step="0.5" min="0" x-model.number="novaArea.raio_km_de" required placeholder="0" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                        </div>
                        <div>
                            <label class="font-bold text-slate-400 block mb-1">Raio Final (KM) *</label>
                            <input type="number" step="0.5" min="0.5" x-model.number="novaArea.raio_km_ate" required placeholder="5" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                        </div>
                    </div>
                </template>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Taxa de Entrega (R$) *</label>
                        <input type="text" x-model="novaArea.taxa_reais" required placeholder="6,00" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-bold">
                    </div>
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Tempo Estimado (min)</label>
                        <input type="number" x-model.number="novaArea.tempo_estimado_min" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Pedido Mínimo (R$)</label>
                        <input type="text" x-model="novaArea.pedido_minimo_reais" placeholder="20,00" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                    </div>
                    <div>
                        <label class="font-bold text-emerald-400 block mb-1">Frete Grátis Acima (R$)</label>
                        <input type="text" x-model="novaArea.frete_gratis_acima_reais" placeholder="Opcional (ex: 80,00)" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-emerald-300">
                    </div>
                </div>

                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-semibold py-3 rounded-xl text-sm">
                    Salvar Regra de Entrega
                </button>
            </form>
        </div>
    </div>
