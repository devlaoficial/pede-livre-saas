    <div x-cloak x-show="modalNovaCategoria" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="modalNovaCategoria = false" class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-t-3xl sm:rounded-3xl p-5 space-y-4 shadow-2xl">
            <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                <h3 class="font-bold text-base text-white" x-text="novaCat.id ? 'Editar Categoria' : 'Nova Categoria do Cardápio'"></h3>
                <button @click="modalNovaCategoria = false" class="text-slate-400 hover:text-white p-1">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form @submit.prevent="salvarNovaCategoria()" class="space-y-3.5 text-xs">
                <div>
                    <label class="font-bold text-slate-300 block mb-1">Nome da Categoria *</label>
                    <input type="text" x-model="novaCat.nome" required placeholder="Ex: Hambúrgueres Artesanais" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-semibold focus:border-rose-500 focus:outline-none">
                </div>
                <div>
                    <label class="font-bold text-slate-300 block mb-1">Subtítulo / Descrição Curta (Opcional)</label>
                    <input type="text" x-model="novaCat.descricao" placeholder="Ex: Preparados na brasa com blend 100% Angus..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white focus:border-rose-500 focus:outline-none">
                </div>
                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-bold py-3 rounded-xl text-sm shadow-lg transition">
                    <span x-text="novaCat.id ? 'Salvar Alterações' : 'Criar Categoria'"></span>
                </button>
            </form>
        </div>
    </div>
