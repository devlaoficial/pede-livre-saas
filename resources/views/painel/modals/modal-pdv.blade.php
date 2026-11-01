    <!-- MODAL PRINCIPAL: PDV BALCÃO & NOVO PEDIDO MANUAL -->
    <div x-cloak x-show="modalPdv" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-2 sm:p-4">
        <div @click.away="fecharModalPdv()"
             class="bg-slate-900 border border-slate-800 w-full max-w-5xl rounded-3xl shadow-2xl flex flex-col max-h-[94vh] overflow-hidden">
            
            <!-- Header Modal PDV -->
            <div class="px-5 py-3.5 bg-slate-950 border-b border-slate-800 flex justify-between items-center shrink-0">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-rose-600/20 text-rose-500 border border-rose-500/30 flex items-center justify-center text-base font-black">⚡</span>
                    <div>
                        <h3 class="font-black text-sm sm:text-base text-white">PDV Balcão &amp; Lançamento Rápido</h3>
                        <p class="text-[11px] text-slate-400">Lance pedidos presenciais, telefone ou WhatsApp direto no Kanban e na Cozinha</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-mono text-slate-500 bg-slate-900 border border-slate-800 px-2 py-1 rounded-lg hidden sm:inline">ESC fechar</span>
                    <button @click="fecharModalPdv()" class="text-slate-400 hover:text-white text-lg font-bold w-8 h-8 rounded-xl hover:bg-slate-800 flex items-center justify-center transition">✕</button>
                </div>
            </div>

            <!-- Corpo em 2 Colunas -->
            <div class="flex-1 overflow-y-auto flex flex-col lg:flex-row divide-y lg:divide-y-0 lg:divide-x divide-slate-800 text-xs">
                
                <!-- COLUNA DA ESQUERDA: CLIENTE & CATÁLOGO (7/12) -->
                <div class="w-full lg:w-7/12 p-4 sm:p-5 space-y-4 overflow-y-auto">
                    
                    <!-- 1. Canal de Venda -->
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">1. Canal do Pedido</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <button type="button" @click="pdv.canal = 'balcao'"
                                class="py-2.5 px-3 rounded-xl font-bold border transition flex items-center justify-center gap-1.5"
                                :class="pdv.canal === 'balcao' ? 'bg-rose-600 border-rose-500 text-white shadow-md' : 'bg-slate-950 border-slate-800 text-slate-400 hover:text-slate-200'">
                                <span>🛍️</span> Balcão
                            </button>
                            <button type="button" @click="pdv.canal = 'retirada'"
                                class="py-2.5 px-3 rounded-xl font-bold border transition flex items-center justify-center gap-1.5"
                                :class="pdv.canal === 'retirada' ? 'bg-rose-600 border-rose-500 text-white shadow-md' : 'bg-slate-950 border-slate-800 text-slate-400 hover:text-slate-200'">
                                <span>📦</span> Retirada
                            </button>
                            <button type="button" @click="pdv.canal = 'delivery'"
                                class="py-2.5 px-3 rounded-xl font-bold border transition flex items-center justify-center gap-1.5"
                                :class="pdv.canal === 'delivery' ? 'bg-rose-600 border-rose-500 text-white shadow-md' : 'bg-slate-950 border-slate-800 text-slate-400 hover:text-slate-200'">
                                <span>🛵</span> Delivery
                            </button>
                            <button type="button" @click="pdv.canal = 'mesa'"
                                class="py-2.5 px-3 rounded-xl font-bold border transition flex items-center justify-center gap-1.5"
                                :class="pdv.canal === 'mesa' ? 'bg-rose-600 border-rose-500 text-white shadow-md' : 'bg-slate-950 border-slate-800 text-slate-400 hover:text-slate-200'">
                                <span>🪑</span> Mesa
                            </button>
                        </div>
                    </div>

                    <!-- 2. Identificação do Cliente -->
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">2. Identificação do Cliente</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <input type="text" x-model="pdv.cliente_nome" placeholder="Nome do Cliente (opcional se balcão)"
                                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none">
                            </div>
                            <div>
                                <input type="text" x-model="pdv.cliente_telefone" placeholder="WhatsApp (ex: 31987654321)"
                                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Campos condicionais para DELIVERY -->
                    <template x-if="pdv.canal === 'delivery'">
                        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 space-y-2.5">
                            <span class="font-bold text-slate-300 flex items-center gap-1.5">
                                <span>📍</span> Endereço de Entrega &amp; Frete
                            </span>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <div class="sm:col-span-2">
                                    <input type="text" x-model="pdv.endereco.rua" placeholder="Rua / Logradouro *"
                                        class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-1.5 text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none">
                                </div>
                                <div>
                                    <input type="text" x-model="pdv.endereco.numero" placeholder="Número *"
                                        class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-1.5 text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <div>
                                    <input type="text" x-model="pdv.endereco.bairro" placeholder="Bairro *"
                                        class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-1.5 text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none">
                                </div>
                                <div>
                                    <input type="text" x-model="pdv.endereco.complemento" placeholder="Apt / Complemento"
                                        class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-1.5 text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none">
                                </div>
                                <div>
                                    <input type="text" x-model="pdv.taxa_entrega_reais" placeholder="Taxa Entrega R$ (Ex: 7,00)"
                                        class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-1.5 text-emerald-400 font-bold placeholder-slate-500 focus:border-rose-500 focus:outline-none">
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Campos condicionais para MESA -->
                    <template x-if="pdv.canal === 'mesa'">
                        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 space-y-2">
                            <span class="font-bold text-slate-300 flex items-center gap-1.5">
                                <span>🪑</span> Selecionar Mesa do Salão
                            </span>
                            <select x-model="pdv.mesa_id" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-white font-bold focus:border-rose-500 focus:outline-none">
                                <option value="">Selecione uma mesa...</option>
                                <template x-for="m in mesas" :key="m.id">
                                    <option :value="m.id" x-text="'Mesa #' + m.numero + ' (' + m.status + ')'"></option>
                                </template>
                            </select>
                        </div>
                    </template>

                    <!-- 3. Catálogo de Produtos e Busca Rápida -->
                    <div class="space-y-2.5 pt-1">
                        <div class="flex items-center justify-between">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">3. Cardápio / Itens</label>
                            <span class="text-[11px] text-slate-500" x-text="produtosPdvFiltrados.length + ' produtos disponíveis'"></span>
                        </div>

                        <!-- Barra de Busca e Filtro de Categoria -->
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input type="text" x-model="buscaPdvProduto" placeholder="Buscar por nome..."
                                    class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-8 pr-3 py-2 text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none">
                                <span class="absolute left-2.5 top-2 text-slate-500">🔍</span>
                            </div>
                            <select x-model="filtroPdvCategoria" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-slate-300 focus:border-rose-500 focus:outline-none max-w-[140px]">
                                <option value="">Todas categorias</option>
                                <template x-for="cat in categorias" :key="cat.id">
                                    <option :value="cat.id" x-text="cat.nome"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Grid de Produtos com Rolagem -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-64 sm:max-h-80 overflow-y-auto pr-1">
                            <template x-for="p in produtosPdvFiltrados" :key="p.id">
                                <div @click="clicarProdutoPdv(p)"
                                     class="p-2.5 rounded-2xl bg-slate-950 hover:bg-slate-800/80 border border-slate-800/80 hover:border-rose-500/50 cursor-pointer transition flex items-center justify-between gap-2.5 group">
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-slate-100 group-hover:text-rose-400 transition truncate" x-text="p.nome"></div>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="font-extrabold text-emerald-400" x-text="formatarCentavos(p.preco_efetivo_centavos || p.preco_centavos)"></span>
                                            <template x-if="(p.variacoes && p.variacoes.length > 0) || (p.grupos_adicionais && p.grupos_adicionais.length > 0)">
                                                <span class="text-[9px] bg-rose-500/20 text-rose-300 border border-rose-500/30 px-1.5 py-0.2 rounded font-bold">Opções</span>
                                            </template>
                                        </div>
                                    </div>
                                    <button type="button" class="shrink-0 w-7 h-7 rounded-lg bg-rose-600/20 group-hover:bg-rose-600 text-rose-300 group-hover:text-white font-bold flex items-center justify-center transition">
                                        +
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- COLUNA DA DIREITA: CARRINHO & FECHAMENTO (5/12) -->
                <div class="w-full lg:w-5/12 p-4 sm:p-5 flex flex-col justify-between bg-slate-950/50 space-y-4">
                    <div class="space-y-3">
                        <div class="flex justify-between items-center border-b border-slate-800 pb-2">
                            <h4 class="font-black text-slate-200 flex items-center gap-1.5">
                                <span>🛒</span> Itens do Pedido
                                <span class="bg-rose-500/20 text-rose-400 text-[10px] px-2 py-0.5 rounded-full font-bold" x-text="pdv.itens.length"></span>
                            </h4>
                            <button x-show="pdv.itens.length > 0" @click="pdv.itens = []" class="text-[11px] text-rose-400 hover:text-rose-300 font-bold">
                                Limpar
                            </button>
                        </div>

                        <!-- Lista de Itens do Carrinho -->
                        <div class="max-h-52 overflow-y-auto space-y-2 pr-1">
                            <template x-if="pdv.itens.length === 0">
                                <div class="text-center py-10 text-slate-500 space-y-1">
                                    <div class="text-2xl">🛍️</div>
                                    <p class="font-semibold text-xs">Carrinho vazio</p>
                                    <p class="text-[11px] text-slate-600">Clique nos produtos ao lado para adicionar ao pedido</p>
                                </div>
                            </template>

                            <template x-for="(it, idx) in pdv.itens" :key="idx">
                                <div class="bg-slate-900 border border-slate-800 rounded-xl p-2.5 space-y-1.5">
                                    <div class="flex justify-between items-start gap-1">
                                        <div class="min-w-0 flex-1">
                                            <span class="font-bold text-white text-xs block" x-text="it.nome"></span>
                                            <template x-if="it.variacao_nome">
                                                <span class="text-[11px] text-rose-400 font-semibold block" x-text="'Tamanho: ' + it.variacao_nome"></span>
                                            </template>
                                            <template x-if="it.adicionais && it.adicionais.length > 0">
                                                <div class="text-[10px] text-slate-400 pl-1" x-text="it.adicionais.map(a => '+ ' + a.nome).join(', ')"></div>
                                            </template>
                                            <template x-if="it.observacao">
                                                <div class="text-[10px] text-amber-400 font-semibold pl-1" x-text="'Obs: ' + it.observacao"></div>
                                            </template>
                                        </div>
                                        <span class="font-black text-emerald-400 shrink-0" x-text="formatarCentavos(it.total_centavos)"></span>
                                    </div>
                                    <div class="flex justify-between items-center pt-1 border-t border-slate-800/80">
                                        <div class="flex items-center gap-1.5 bg-slate-950 px-2 py-0.5 rounded-lg border border-slate-800">
                                            <button @click="alterarQtdItemPdv(idx, -1)" class="text-slate-400 hover:text-white font-black px-1">-</button>
                                            <span class="font-bold text-white text-xs px-1" x-text="it.qtd"></span>
                                            <button @click="alterarQtdItemPdv(idx, 1)" class="text-slate-400 hover:text-white font-black px-1">+</button>
                                        </div>
                                        <button @click="removerItemPdv(idx)" class="text-slate-500 hover:text-rose-400 font-bold text-[11px] transition">
                                            Remover
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Resumo Financeiro & Totais -->
                        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-3 space-y-1.5">
                            <div class="flex justify-between text-slate-400">
                                <span>Subtotal:</span>
                                <span class="font-bold text-slate-200" x-text="formatarCentavos(calcularSubtotalPdvCentavos())"></span>
                            </div>
                            <template x-if="pdv.canal === 'delivery'">
                                <div class="flex justify-between text-slate-400">
                                    <span>Taxa de Entrega:</span>
                                    <span class="font-bold text-slate-200" x-text="formatarCentavos(centavos(pdv.taxa_entrega_reais || '0'))"></span>
                                </div>
                            </template>
                            <div class="flex justify-between items-center text-slate-400">
                                <span>Desconto Manual (R$):</span>
                                <input type="text" x-model="pdv.desconto_reais" placeholder="0,00"
                                    class="w-20 bg-slate-950 border border-slate-800 rounded-lg px-2 py-1 text-right text-emerald-400 font-bold focus:border-rose-500 focus:outline-none">
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-slate-800">
                                <span class="font-extrabold text-white text-sm">TOTAL A COBRAR:</span>
                                <span class="font-black text-emerald-400 text-lg sm:text-xl" x-text="formatarCentavos(calcularTotalPdvCentavos())"></span>
                            </div>
                        </div>

                        <!-- Forma de Pagamento & Troco -->
                        <div class="space-y-2">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">Forma de Pagamento</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" @click="pdv.forma_pagamento = 'dinheiro'"
                                    class="py-2 px-2.5 rounded-xl font-bold border transition text-center"
                                    :class="pdv.forma_pagamento === 'dinheiro' ? 'bg-slate-800 border-rose-500 text-white shadow-sm' : 'bg-slate-950 border-slate-800 text-slate-400'">
                                    💵 Dinheiro
                                </button>
                                <button type="button" @click="pdv.forma_pagamento = 'pix'"
                                    class="py-2 px-2.5 rounded-xl font-bold border transition text-center"
                                    :class="pdv.forma_pagamento === 'pix' ? 'bg-slate-800 border-rose-500 text-white shadow-sm' : 'bg-slate-950 border-slate-800 text-slate-400'">
                                    ⚡ PIX
                                </button>
                                <button type="button" @click="pdv.forma_pagamento = 'cartao_maquina'"
                                    class="py-2 px-2.5 rounded-xl font-bold border transition text-center"
                                    :class="pdv.forma_pagamento === 'cartao_maquina' ? 'bg-slate-800 border-rose-500 text-white shadow-sm' : 'bg-slate-950 border-slate-800 text-slate-400'">
                                    💳 Cartão Máquina
                                </button>
                                <button type="button" @click="pdv.forma_pagamento = 'voucher'"
                                    class="py-2 px-2.5 rounded-xl font-bold border transition text-center"
                                    :class="pdv.forma_pagamento === 'voucher' ? 'bg-slate-800 border-rose-500 text-white shadow-sm' : 'bg-slate-950 border-slate-800 text-slate-400'">
                                    🎫 Vale / Voucher
                                </button>
                            </div>

                            <!-- Campo de Troco se Dinheiro -->
                            <template x-if="pdv.forma_pagamento === 'dinheiro'">
                                <div class="bg-slate-900 border border-slate-800 rounded-xl p-2.5 flex items-center justify-between gap-2">
                                    <div class="flex-1">
                                        <label class="block text-[10px] text-slate-400 font-bold uppercase">Troco para quanto? (R$)</label>
                                        <input type="text" x-model="pdv.troco_para_reais" placeholder="Ex: 50,00"
                                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-2 py-1 text-white font-bold focus:border-rose-500 focus:outline-none">
                                    </div>
                                    <div class="text-right">
                                        <span class="block text-[10px] text-slate-400 font-bold uppercase">Troco:</span>
                                        <span class="font-black text-sm" :class="calcularTrocoPdvCentavos() > 0 ? 'text-amber-400' : 'text-slate-500'"
                                              x-text="formatarCentavos(calcularTrocoPdvCentavos())"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Status Inicial -->
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">Enviar Pedido Como:</label>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <button type="button" @click="pdv.status_inicial = 'aceito'"
                                    class="py-2 px-2 rounded-xl font-bold border transition text-center"
                                    :class="pdv.status_inicial === 'aceito' ? 'bg-amber-600/30 border-amber-500 text-amber-300' : 'bg-slate-950 border-slate-800 text-slate-400'">
                                    🍳 Direto p/ Cozinha
                                </button>
                                <button type="button" @click="pdv.status_inicial = 'novo'"
                                    class="py-2 px-2 rounded-xl font-bold border transition text-center"
                                    :class="pdv.status_inicial === 'novo' ? 'bg-rose-600/30 border-rose-500 text-rose-300' : 'bg-slate-950 border-slate-800 text-slate-400'">
                                    📥 Aguardando Aceite
                                </button>
                            </div>
                        </div>

                        <!-- Observação Geral -->
                        <div>
                            <input type="text" x-model="pdv.observacao" placeholder="Observações gerais (ex: embalar para presente)..."
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- Botão de Ação: Lançar Pedido -->
                    <button @click="enviarPedidoPdv()" :disabled="pdv.itens.length === 0 || salvandoPdv"
                        class="w-full bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black py-3.5 rounded-2xl text-sm shadow-xl shadow-rose-600/25 active:scale-98 transition flex items-center justify-center gap-2">
                        <span x-show="!salvandoPdv">🚀 Lançar Pedido — <span x-text="formatarCentavos(calcularTotalPdvCentavos())"></span></span>
                        <span x-show="salvandoPdv" class="flex items-center gap-2">
                            <span class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span>Gravando Pedido...</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- SUB-MODAL: PERSONALIZAÇÃO DE PRODUTO (VARIAÇÕES & ADICIONAIS) -->
        <template x-if="pdvItemCustomizando">
            <div class="fixed inset-0 z-60 bg-black/85 flex items-center justify-center p-3">
                <div @click.away="fecharCustomizacaoPdv()"
                     class="bg-slate-900 border border-slate-800 w-full max-w-lg rounded-3xl p-5 space-y-4 max-h-[90vh] overflow-y-auto shadow-2xl">
                    <div class="flex justify-between items-start border-b border-slate-800 pb-3">
                        <div>
                            <h4 class="font-black text-white text-base" x-text="pdvItemCustomizando.nome"></h4>
                            <p class="text-xs text-slate-400" x-text="pdvItemCustomizando.descricao || 'Personalize os adicionais e opções'"></p>
                        </div>
                        <button @click="fecharCustomizacaoPdv()" class="text-slate-400 hover:text-white font-bold text-lg">✕</button>
                    </div>

                    <!-- Variações de Tamanho -->
                    <template x-if="pdvItemCustomizando.variacoes && pdvItemCustomizando.variacoes.length > 0">
                        <div class="space-y-2">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">Escolha o Tamanho / Variação *</label>
                            <div class="space-y-1.5">
                                <template x-for="v in pdvItemCustomizando.variacoes" :key="v.id">
                                    <label class="flex justify-between items-center p-3 rounded-xl border cursor-pointer transition"
                                           :class="pdvVariacaoSelecionadaId === v.id ? 'bg-rose-500/10 border-rose-500 text-white' : 'bg-slate-950 border-slate-800 text-slate-300 hover:border-slate-700'">
                                        <div class="flex items-center gap-2.5">
                                            <input type="radio" :value="v.id" x-model="pdvVariacaoSelecionadaId" class="text-rose-600 focus:ring-rose-500">
                                            <span class="font-bold text-xs" x-text="v.nome"></span>
                                        </div>
                                        <span class="font-extrabold text-emerald-400 text-xs" x-text="formatarCentavos(v.preco_centavos)"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Grupos de Adicionais -->
                    <template x-if="pdvItemCustomizando.grupos_adicionais && pdvItemCustomizando.grupos_adicionais.length > 0">
                        <div class="space-y-3">
                            <template x-for="g in pdvItemCustomizando.grupos_adicionais" :key="g.id">
                                <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold text-white text-xs" x-text="g.nome"></span>
                                        <span class="text-[10px] text-slate-400" x-text="'Máx: ' + (g.maximo || 1)"></span>
                                    </div>
                                    <div class="space-y-1.5">
                                        <template x-for="op in g.opcoes" :key="op.id">
                                            <label class="flex justify-between items-center p-2 rounded-xl bg-slate-900 hover:bg-slate-850 border border-slate-800 cursor-pointer">
                                                <div class="flex items-center gap-2">
                                                    <input type="checkbox" :checked="isAdicionalSelecionadoPdv(op.id)" @change="alternarAdicionalPdv(op, g)"
                                                        class="rounded text-rose-600 focus:ring-rose-500">
                                                    <span class="text-xs text-slate-200" x-text="op.nome"></span>
                                                </div>
                                                <span class="text-xs font-bold text-emerald-400" x-text="op.preco_centavos > 0 ? '+ ' + formatarCentavos(op.preco_centavos) : 'Grátis'"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Quantidade e Observações do Item -->
                    <div class="space-y-2 pt-2 border-t border-slate-800">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-300">Quantidade do Item:</span>
                            <div class="flex items-center gap-2 bg-slate-950 px-3 py-1 rounded-xl border border-slate-800">
                                <button type="button" @click="pdvQtdItem = Math.max(1, pdvQtdItem - 1)" class="font-black text-slate-400 hover:text-white px-1">-</button>
                                <span class="font-black text-white text-sm px-2" x-text="pdvQtdItem"></span>
                                <button type="button" @click="pdvQtdItem++" class="font-black text-slate-400 hover:text-white px-1">+</button>
                            </div>
                        </div>
                        <input type="text" x-model="pdvObservacaoItem" placeholder="Observação deste item (ex: Sem cebola, massa fina)..."
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none">
                    </div>

                    <!-- Botão Confirmar Adição do Item -->
                    <div class="flex gap-2 pt-2">
                        <button type="button" @click="fecharCustomizacaoPdv()"
                            class="w-1/3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold py-2.5 rounded-xl text-xs transition">
                            Cancelar
                        </button>
                        <button type="button" @click="confirmarItemCustomizadoPdv()"
                            class="w-2/3 bg-rose-600 hover:bg-rose-500 text-white font-extrabold py-2.5 rounded-xl text-xs shadow-lg shadow-rose-600/30 transition">
                            ✓ Inserir no Pedido
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>