    <div x-cloak x-show="modalNovoProduto" class="fixed inset-0 z-50 bg-black/75 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="modalNovoProduto = false" class="bg-slate-900 border border-slate-800 w-full max-w-lg rounded-t-3xl sm:rounded-3xl p-5 space-y-4 max-h-[92vh] overflow-y-auto">
            <div class="flex justify-between items-center">
                <h3 class="font-bold text-base text-white" x-text="novoProd.id ? '✏️ Editar Produto' : '🍔 Cadastrar Novo Produto'"></h3>
                <button @click="modalNovoProduto = false" class="text-slate-400 font-bold">✕</button>
            </div>
            <form @submit.prevent="salvarNovoProduto()" class="space-y-3 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Categoria *</label>
                        <select x-model="novoProd.categoria_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                            <template x-for="c in categorias" :key="c.id">
                                <option :value="c.id" x-text="c.nome"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Estação KDS na Cozinha</label>
                        <select x-model="novoProd.estacao_kds" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                            <option value="chapa">🔥 Chapa / Burgers</option>
                            <option value="forno">🍕 Forno / Pizzas</option>
                            <option value="fritadeira">🍟 Fritadeira / Porções</option>
                            <option value="bar">🥤 Bar / Bebidas</option>
                            <option value="sobremesa">🍨 Sobremesas / Açaí</option>
                            <option value="geral">👨‍🍳 Cozinha Geral</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="font-bold text-slate-400 block mb-1">Nome do Produto *</label>
                    <input type="text" x-model="novoProd.nome" required placeholder="Ex: Smash Burger Duplo Bacon" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-bold">
                </div>
                <div>
                    <label class="font-bold text-slate-400 block mb-1">Descrição Vendedora</label>
                    <input type="text" x-model="novoProd.descricao" placeholder="Pão brioche selado na manteiga, 2x blend angus..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Preço Normal "De" (R$) *</label>
                        <input type="text" x-model="novoProd.preco_reais" required placeholder="38,90" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-bold">
                    </div>
                    <div>
                        <label class="font-bold text-emerald-400 block mb-1">🔥 Preço Promo "Por" (Opcional)</label>
                        <input type="text" x-model="novoProd.preco_promocional_reais" placeholder="34,90 (destaca no topo)" class="w-full bg-slate-950 border border-emerald-500/40 rounded-xl px-3.5 py-2.5 text-emerald-300 font-bold">
                    </div>
                </div>

                <!-- Upload de Foto Real WebP ou URL -->
                <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3 space-y-2">
                    <label class="font-bold text-slate-300 block">📸 Foto do Produto (Upload Automático WebP ou Link)</label>
                    <input type="file" accept="image/jpeg,image/png,image/webp" @change="arquivoFotoProduto = $event.target.files[0]"
                        class="w-full text-[11px] text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-rose-600/20 file:text-rose-300 hover:file:bg-rose-600/30">
                    <input type="text" x-model="novoProd.imagem" placeholder="Ou cole a URL da imagem (https://...)" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-white">
                </div>

                <!-- Variações de Tamanho / Opções -->
                <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3 space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-slate-300">📏 Variações de Tamanho (Ex: P, M, G / 1 Carne, 2 Carnes)</span>
                        <button type="button" @click="novoProd.variacoes.push({ nome: '', preco_reais: novoProd.preco_reais || '29,90' })" class="text-rose-400 font-bold hover:underline">+ Variação</button>
                    </div>
                    <template x-for="(vr, idx) in novoProd.variacoes" :key="idx">
                        <div class="flex gap-2 items-center">
                            <input type="text" x-model="vr.nome" placeholder="Ex: Grande (8 fatias)" class="flex-1 bg-slate-900 border border-slate-800 rounded-xl px-2.5 py-1.5 text-white">
                            <input type="text" x-model="vr.preco_reais" placeholder="59,90" class="w-24 bg-slate-900 border border-slate-800 rounded-xl px-2.5 py-1.5 text-emerald-400 font-bold">
                            <button type="button" @click="novoProd.variacoes.splice(idx, 1)" class="text-rose-400 font-bold px-2">✕</button>
                        </div>
                    </template>
                </div>

                <!-- Vínculo com Grupos de Adicionais -->
                <template x-if="gruposAdicionais && gruposAdicionais.length > 0">
                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3 space-y-2">
                        <span class="font-bold text-slate-300 block">➕ Vincular Grupos de Adicionais a este Produto</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                            <template x-for="g in gruposAdicionais" :key="g.id">
                                <label class="flex items-center gap-2 p-2 rounded-xl bg-slate-900 border border-slate-800 cursor-pointer">
                                    <input type="checkbox" :value="g.id" x-model="novoProd.grupos_adicionais" class="rounded text-rose-600">
                                    <span class="text-white font-semibold truncate" x-text="g.nome"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>

                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-extrabold py-3 rounded-xl text-sm shadow-lg">
                    Salvar no Cardápio
                </button>
            </form>
        </div>
    </div>

    <!-- Modal de Criação / Edição de Grupo de Adicionais -->
    <div x-cloak x-show="modalGrupoAdicional" class="fixed inset-0 z-50 bg-black/75 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="modalGrupoAdicional = false" class="bg-slate-900 border border-slate-800 w-full max-w-lg rounded-t-3xl sm:rounded-3xl p-5 space-y-4 max-h-[92vh] overflow-y-auto">
            <div class="flex justify-between items-center">
                <h3 class="font-bold text-base text-white" x-text="formGrupoAdicional.id ? '✏️ Editar Grupo de Adicionais' : '➕ Novo Grupo de Adicionais'"></h3>
                <button @click="modalGrupoAdicional = false" class="text-slate-400 font-bold">✕</button>
            </div>
            <form @submit.prevent="salvarGrupoAdicional()" class="space-y-3 text-xs">
                <div>
                    <label class="font-bold text-slate-400 block mb-1">Nome do Grupo * (Ex: Escolha a Borda, Adicionais Extras, Ponto da Carne)</label>
                    <input type="text" x-model="formGrupoAdicional.nome" required placeholder="Ex: Adicionais do Burger" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-bold">
                </div>
                <div class="grid grid-cols-3 gap-2.5">
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Mín. Escolhas</label>
                        <input type="number" min="0" x-model.number="formGrupoAdicional.min_escolhas" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white">
                    </div>
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Máx. Escolhas</label>
                        <input type="number" min="1" x-model.number="formGrupoAdicional.max_escolhas" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white">
                    </div>
                    <div class="flex items-end pb-2">
                        <label class="flex items-center gap-2 text-white font-bold cursor-pointer">
                            <input type="checkbox" x-model="formGrupoAdicional.obrigatorio" class="rounded text-rose-600">
                            <span>Obrigatório</span>
                        </label>
                    </div>
                </div>

                <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3 space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-slate-300">Opções do Grupo</span>
                        <button type="button" @click="formGrupoAdicional.opcoes.push({ nome: '', preco_reais: '0,00' })" class="text-amber-400 font-bold hover:underline">+ Opção</button>
                    </div>
                    <template x-for="(op, idx) in formGrupoAdicional.opcoes" :key="idx">
                        <div class="flex gap-2 items-center">
                            <input type="text" x-model="op.nome" required placeholder="Ex: Bacon Extra" class="flex-1 bg-slate-900 border border-slate-800 rounded-xl px-2.5 py-1.5 text-white">
                            <input type="text" x-model="op.preco_reais" placeholder="5,00" class="w-24 bg-slate-900 border border-slate-800 rounded-xl px-2.5 py-1.5 text-emerald-400 font-bold">
                            <button type="button" @click="formGrupoAdicional.opcoes.splice(idx, 1)" class="text-rose-400 font-bold px-2">✕</button>
                        </div>
                    </template>
                </div>

                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-400 text-slate-950 font-black py-3 rounded-xl text-sm shadow-lg">
                    Salvar Grupo de Adicionais
                </button>
            </form>
        </div>
    </div>
