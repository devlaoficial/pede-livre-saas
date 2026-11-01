    <div x-cloak x-show="modalNovaCategoria" class="fixed inset-0 z-50 bg-black/75 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="modalNovaCategoria = false" class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-t-3xl sm:rounded-3xl p-5 space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="font-bold text-base text-white">Nova Categoria do Cardápio</h3>
                <button @click="modalNovaCategoria = false" class="text-slate-400">✕</button>
            </div>
            <form @submit.prevent="salvarNovaCategoria()" class="space-y-3 text-xs">
                <div>
                    <label class="font-bold text-slate-400 block mb-1">Nome da Categoria *</label>
                    <input type="text" x-model="novaCat.nome" required placeholder="Ex: Sobremesas Artesanais" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                </div>
                <div>
                    <label class="font-bold text-slate-400 block mb-1">Descrição Curta</label>
                    <input type="text" x-model="novaCat.descricao" placeholder="Doces feitos na casa..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                </div>
                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-extrabold py-3 rounded-xl text-sm">
                    Criar Categoria
                </button>
            </form>
        </div>
    </div>
