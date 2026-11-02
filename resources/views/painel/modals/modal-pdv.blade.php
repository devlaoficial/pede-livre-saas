<!-- MODAL PRINCIPAL: PDV BALCÃO & NOVO PEDIDO MANUAL (DESIGN CLAUDE FIEL) -->
<div x-cloak x-show="modalPdv" class="fixed inset-0 z-50 bg-black/85 backdrop-blur-sm flex items-center justify-center p-2 sm:p-4">
    <div @click.away="fecharModalPdv()"
         class="bg-[#0b0e17] border border-[#1b2234] w-full max-w-5xl rounded-3xl shadow-2xl flex flex-col max-h-[94vh] overflow-hidden">
        
        <!-- Header Modal PDV (Fiel Imagem 3) -->
        <div class="px-5 py-4 bg-[#0b0e17] border-b border-[#1b2234] flex justify-between items-center shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-[#be123c]/20 border border-[#f43f5e]/30 flex items-center justify-center text-[#f43f5e] font-black text-base">
                    ⚡
                </div>
                <div>
                    <h3 class="font-black text-base sm:text-lg text-white">Novo pedido</h3>
                    <p class="text-xs text-slate-400">Balcão, retirada, delivery ou mesa</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[11px] font-medium text-slate-400 bg-[#141926] border border-[#1e2638] px-3 py-1.5 rounded-xl hidden sm:inline">
                    Esc para fechar
                </span>
                <button @click="fecharModalPdv()" 
                        class="text-slate-400 hover:text-white bg-[#141926] hover:bg-[#1f283e] border border-[#1e2638] w-9 h-9 rounded-xl flex items-center justify-center transition">
                    ✕
                </button>
            </div>
        </div>

        <!-- Corpo em 2 Colunas (Fiel Imagem 3) -->
        <div class="flex-1 overflow-y-auto flex flex-col lg:flex-row divide-y lg:divide-y-0 lg:divide-x divide-[#1b2234] text-xs">
            
            <!-- ==================== COLUNA DA ESQUERDA: CATÁLOGO & PRODUTOS ==================== -->
            <div class="w-full lg:w-7/12 p-4 sm:p-6 space-y-4 overflow-y-auto">
                
                <!-- Barra de Busca com Contador de Produtos -->
                <div class="relative">
                    <span class="absolute left-3.5 top-3 text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" x-model="buscaPdvProduto" placeholder="Buscar produto pelo nome"
                        class="w-full bg-[#121624] border border-[#1c2336] rounded-2xl pl-10 pr-24 py-2.5 text-white placeholder-slate-500 focus:border-[#f43f5e] focus:outline-none transition">
                    <span class="absolute right-3.5 top-2.5 text-[11px] font-bold text-slate-400 bg-[#192033] px-2.5 py-1 rounded-lg"
                          x-text="produtosPdvFiltrados.length + (produtosPdvFiltrados.length === 1 ? ' produto' : ' produtos')"></span>
                </div>

                <!-- Chips de Categorias (Todas, Lanches, Bebidas...) -->
                <div class="flex overflow-x-auto no-scrollbar gap-2 py-0.5">
                    <button type="button" @click="filtroPdvCategoria = ''"
                        class="px-3.5 py-1.5 rounded-full text-xs font-bold transition shrink-0"
                        :class="filtroPdvCategoria === '' ? 'bg-[#f43f5e] text-white shadow-sm' : 'bg-[#141926] text-slate-400 border border-[#1e2638] hover:text-white'">
                        Todas
                    </button>
                    <template x-for="cat in categorias" :key="cat.id">
                        <button type="button" @click="filtroPdvCategoria = cat.id"
                            class="px-3.5 py-1.5 rounded-full text-xs font-bold transition shrink-0"
                            :class="filtroPdvCategoria === cat.id ? 'bg-[#f43f5e] text-white shadow-sm' : 'bg-[#141926] text-slate-400 border border-[#1e2638] hover:text-white'"
                            x-text="cat.nome">
                        </button>
                    </template>
                </div>

                <!-- Grid de Produtos com Cartões Quadrados Escuros (Design Fiel Imagem 3) -->
                <div class="grid grid-cols-2 gap-3.5 max-h-[56vh] overflow-y-auto pr-1">
                    <template x-for="p in produtosPdvFiltrados" :key="p.id">
                        <div @click="clicarProdutoPdv(p)"
                             class="bg-[#121624] hover:bg-[#161c2d] border border-[#1c2336] hover:border-[#f43f5e]/60 rounded-2xl p-3 cursor-pointer transition flex flex-col justify-between group relative shadow-md">
                            
                            <!-- Badge de Quantidade se já adicionado -->
                            <template x-if="pdv.itens.find(it => it.produto_id === p.id)">
                                <span class="absolute top-2 right-2 bg-[#f43f5e] text-white text-[10px] font-black px-2 py-0.5 rounded-md shadow z-10"
                                      x-text="pdv.itens.filter(it => it.produto_id === p.id).reduce((acc, it) => acc + it.qtd, 0) + 'x'"></span>
                            </template>

                            <!-- Área Quadrada de Foto ou Letra Inicial Estilizada -->
                            <div class="w-full aspect-square rounded-xl overflow-hidden flex items-center justify-center relative mb-2.5"
                                 :class="p.imagem ? 'bg-[#182033]' : ((p.nome.toUpperCase().charCodeAt(0) % 2 === 0) ? 'bg-[#261622]' : 'bg-[#292212]')">
                                <template x-if="p.imagem">
                                    <img :src="p.imagem" :alt="p.nome" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                </template>
                                <template x-if="!p.imagem">
                                    <span class="text-4xl sm:text-5xl font-black select-none tracking-tighter"
                                          :class="(p.nome.toUpperCase().charCodeAt(0) % 2 === 0) ? 'text-[#f43f5e]' : 'text-[#fbbf24]'"
                                          x-text="p.nome.trim().charAt(0).toUpperCase()"></span>
                                </template>
                            </div>

                            <!-- Nome do Produto -->
                            <div class="font-bold text-white text-xs sm:text-sm line-clamp-2 leading-snug group-hover:text-rose-400 transition" 
                                 x-text="p.nome"></div>

                            <!-- Preço em Verde + Botão Circular + -->
                            <div class="flex items-center justify-between pt-2 mt-1">
                                <span class="font-extrabold text-[#10b981] text-sm" 
                                      x-text="formatarCentavos(p.preco_efetivo_centavos || p.preco_centavos)"></span>
                                
                                <span class="w-7 h-7 rounded-full bg-[#f43f5e] group-hover:bg-[#e11d48] text-white font-black text-sm flex items-center justify-center shadow-md transition active:scale-90">
                                    +
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- ==================== COLUNA DA DIREITA: CONFIGURAÇÃO, ITENS & FINALIZAÇÃO ==================== -->
            <div class="w-full lg:w-5/12 p-4 sm:p-6 space-y-4 overflow-y-auto bg-[#090c14]">
                
                <!-- 1. CANAL -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">CANAL</label>
                    <div class="grid grid-cols-4 gap-1.5">
                        <button type="button" @click="pdv.canal = 'balcao'"
                            class="py-2 px-1 rounded-xl font-bold text-xs transition text-center flex items-center justify-center gap-1"
                            :class="pdv.canal === 'balcao' ? 'bg-[#f43f5e] text-white shadow-md' : 'bg-[#121624] text-slate-400 border border-[#1c2336] hover:text-white'">
                            <span>🛍️</span> Balcão
                        </button>
                        <button type="button" @click="pdv.canal = 'retirada'"
                            class="py-2 px-1 rounded-xl font-bold text-xs transition text-center flex items-center justify-center gap-1"
                            :class="pdv.canal === 'retirada' ? 'bg-[#f43f5e] text-white shadow-md' : 'bg-[#121624] text-slate-400 border border-[#1c2336] hover:text-white'">
                            <span>📦</span> Retirada
                        </button>
                        <button type="button" @click="pdv.canal = 'delivery'"
                            class="py-2 px-1 rounded-xl font-bold text-xs transition text-center flex items-center justify-center gap-1"
                            :class="pdv.canal === 'delivery' ? 'bg-[#f43f5e] text-white shadow-md' : 'bg-[#121624] text-slate-400 border border-[#1c2336] hover:text-white'">
                            <span>🛵</span> Delivery
                        </button>
                        <button type="button" @click="pdv.canal = 'mesa'"
                            class="py-2 px-1 rounded-xl font-bold text-xs transition text-center flex items-center justify-center gap-1"
                            :class="pdv.canal === 'mesa' ? 'bg-[#f43f5e] text-white shadow-md' : 'bg-[#121624] text-slate-400 border border-[#1c2336] hover:text-white'">
                            <span>🪑</span> Mesa
                        </button>
                    </div>
                </div>

                <!-- 2. CLIENTE (OPCIONAL) -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">CLIENTE (OPCIONAL)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" x-model="pdv.cliente_nome" placeholder="Nome"
                            class="w-full bg-[#121624] border border-[#1c2336] rounded-xl px-3 py-2 text-white placeholder-slate-500 focus:border-[#f43f5e] focus:outline-none">
                        <input type="text" x-model="pdv.cliente_telefone" placeholder="WhatsApp com DDD"
                            class="w-full bg-[#121624] border border-[#1c2336] rounded-xl px-3 py-2 text-white placeholder-slate-500 focus:border-[#f43f5e] focus:outline-none">
                    </div>
                </div>

                <!-- Se Delivery selecionado: Endereço & Taxa -->
                <template x-if="pdv.canal === 'delivery'">
                    <div class="bg-[#121624] border border-[#1c2336] rounded-2xl p-3 space-y-2">
                        <span class="font-bold text-slate-300 text-xs flex items-center gap-1">
                            <span>📍</span> Endereço de Entrega
                        </span>
                        <div class="grid grid-cols-3 gap-2">
                            <input type="text" x-model="pdv.endereco.rua" placeholder="Rua *" class="col-span-2 bg-[#0b0e17] border border-[#1c2336] rounded-lg px-2.5 py-1.5 text-white placeholder-slate-500 text-xs">
                            <input type="text" x-model="pdv.endereco.numero" placeholder="Nº *" class="bg-[#0b0e17] border border-[#1c2336] rounded-lg px-2.5 py-1.5 text-white placeholder-slate-500 text-xs">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" x-model="pdv.endereco.bairro" placeholder="Bairro *" class="bg-[#0b0e17] border border-[#1c2336] rounded-lg px-2.5 py-1.5 text-white placeholder-slate-500 text-xs">
                            <input type="text" x-model="pdv.taxa_entrega_reais" placeholder="Taxa R$ (ex: 7,00)" class="bg-[#0b0e17] border border-[#1c2336] rounded-lg px-2.5 py-1.5 text-emerald-400 font-bold placeholder-slate-500 text-xs">
                        </div>
                    </div>
                </template>

                <!-- Se Mesa selecionada: Select da Mesa -->
                <template x-if="pdv.canal === 'mesa'">
                    <div class="bg-[#121624] border border-[#1c2336] rounded-2xl p-3 space-y-1.5">
                        <label class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">SELECIONAR MESA DO SALÃO</label>
                        <select x-model="pdv.mesa_id" class="w-full bg-[#0b0e17] border border-[#1c2336] rounded-xl px-3 py-2 text-white font-bold focus:border-[#f43f5e] focus:outline-none">
                            <option value="">Selecione uma mesa...</option>
                            <template x-for="m in mesas" :key="m.id">
                                <option :value="m.id" x-text="'Mesa #' + m.numero + ' (' + m.status + ')'"></option>
                            </template>
                        </select>
                    </div>
                </template>

                <!-- 3. ITENS DO PEDIDO -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400"
                               x-text="'ITENS DO PEDIDO · ' + pdv.itens.length"></label>
                        <button x-show="pdv.itens.length > 0" @click="pdv.itens = []" class="text-[11px] text-[#f43f5e] hover:underline font-bold">
                            Limpar
                        </button>
                    </div>

                    <!-- Lista de Itens -->
                    <div class="space-y-2 max-h-40 overflow-y-auto pr-1">
                        <template x-if="pdv.itens.length === 0">
                            <div class="text-center py-6 text-slate-500 space-y-1 bg-[#121624] border border-[#1c2336] rounded-2xl">
                                <span class="text-xl">🛍️</span>
                                <p class="text-xs font-medium">Nenhum item adicionado</p>
                            </div>
                        </template>

                        <template x-for="(it, idx) in pdv.itens" :key="idx">
                            <div class="bg-[#121624] border border-[#1c2336] rounded-2xl p-3 flex items-center justify-between gap-2 shadow-sm">
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-white text-xs truncate" x-text="it.nome"></div>
                                    <div class="text-[11px] text-slate-400 font-semibold mt-0.5" x-text="formatarCentavos(it.total_centavos)"></div>
                                    <template x-if="it.variacao_nome">
                                        <div class="text-[10px] text-rose-400" x-text="'Tam: ' + it.variacao_nome"></div>
                                    </template>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <!-- Controles de Quantidade: - 1 + -->
                                    <div class="flex items-center bg-[#0b0e17] border border-[#1c2336] rounded-xl px-2 py-1 gap-2">
                                        <button @click="alterarQtdItemPdv(idx, -1)" class="text-slate-400 hover:text-white font-black text-xs px-1">−</button>
                                        <span class="font-black text-white text-xs" x-text="it.qtd"></span>
                                        <button @click="alterarQtdItemPdv(idx, 1)" class="text-slate-400 hover:text-white font-black text-xs px-1">+</button>
                                    </div>
                                    
                                    <!-- Ícone Lixeira -->
                                    <button @click="removerItemPdv(idx)" class="text-slate-500 hover:text-[#f43f5e] p-1.5 rounded-lg transition" title="Remover item">
                                        🗑️
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 4. RESUMO FINANCEIRO (Subtotal, Desconto, Total Verde Gigante) -->
                <div class="bg-[#121624] border border-[#1c2336] rounded-2xl p-3.5 space-y-2">
                    <div class="flex justify-between text-slate-400 text-xs">
                        <span>Subtotal</span>
                        <span class="font-bold text-white" x-text="formatarCentavos(calcularSubtotalPdvCentavos())"></span>
                    </div>

                    <div class="flex justify-between items-center text-slate-400 text-xs">
                        <span>Desconto</span>
                        <div class="flex items-center gap-1">
                            <span class="text-slate-400 font-bold">R$</span>
                            <input type="text" x-model="pdv.desconto_reais" placeholder="0,00"
                                class="w-20 bg-[#0b0e17] border border-[#1c2336] rounded-lg px-2 py-1 text-right text-emerald-400 font-bold focus:border-[#f43f5e] focus:outline-none text-xs">
                        </div>
                    </div>

                    <div class="flex justify-between items-baseline pt-2 border-t border-[#1c2336]">
                        <span class="font-black text-white text-sm">Total</span>
                        <span class="font-black text-[#10b981] text-2xl" x-text="formatarCentavos(calcularTotalPdvCentavos())"></span>
                    </div>
                </div>

                <!-- 5. PAGAMENTO -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-400">PAGAMENTO</label>
                    <div class="grid grid-cols-4 gap-1.5">
                        <button type="button" @click="pdv.forma_pagamento = 'dinheiro'"
                            class="py-2 px-1 rounded-xl font-bold text-xs transition text-center flex items-center justify-center gap-1"
                            :class="pdv.forma_pagamento === 'dinheiro' ? 'bg-[#1c2336] border border-[#f43f5e] text-white shadow-sm' : 'bg-[#121624] text-slate-400 border border-[#1c2336]'">
                            💵 Dinheiro
                        </button>
                        <button type="button" @click="pdv.forma_pagamento = 'pix'"
                            class="py-2 px-1 rounded-xl font-bold text-xs transition text-center flex items-center justify-center gap-1"
                            :class="pdv.forma_pagamento === 'pix' ? 'bg-[#1c2336] border border-[#f43f5e] text-white shadow-sm' : 'bg-[#121624] text-slate-400 border border-[#1c2336]'">
                            ⚡ PIX
                        </button>
                        <button type="button" @click="pdv.forma_pagamento = 'cartao_maquina'"
                            class="py-2 px-1 rounded-xl font-bold text-xs transition text-center flex items-center justify-center gap-1"
                            :class="pdv.forma_pagamento === 'cartao_maquina' ? 'bg-[#1c2336] border border-[#f43f5e] text-white shadow-sm' : 'bg-[#121624] text-slate-400 border border-[#1c2336]'">
                            💳 Cartão
                        </button>
                        <button type="button" @click="pdv.forma_pagamento = 'voucher'"
                            class="py-2 px-1 rounded-xl font-bold text-xs transition text-center flex items-center justify-center gap-1"
                            :class="pdv.forma_pagamento === 'voucher' ? 'bg-[#1c2336] border border-[#f43f5e] text-white shadow-sm' : 'bg-[#121624] text-slate-400 border border-[#1c2336]'">
                            🎫 Vale
                        </button>
                    </div>

                    <!-- Troco Calculado Ao Vivo se Dinheiro (Design Fiel Imagem 3) -->
                    <template x-if="pdv.forma_pagamento === 'dinheiro'">
                        <div class="bg-[#121624] border border-[#1c2336] rounded-2xl p-2.5 flex items-center justify-between gap-3 mt-2">
                            <input type="text" x-model="pdv.troco_para_reais" placeholder="Troco para quanto? ex: 50,00"
                                class="flex-1 bg-[#0b0e17] border border-[#1c2336] rounded-xl px-3 py-2 text-white font-bold placeholder-slate-500 text-xs focus:border-[#f43f5e] focus:outline-none">
                            <div class="text-right shrink-0">
                                <span class="block text-[9px] font-black uppercase text-slate-400 tracking-wider">TROCO</span>
                                <span class="font-black text-sm text-white" x-text="formatarCentavos(calcularTrocoPdvCentavos())"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- 6. DESTINO DO PEDIDO (Direto para a cozinha vs Aguardar aceite) -->
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" @click="pdv.status_inicial = 'aceito'"
                        class="py-2.5 px-2 rounded-xl font-bold border transition text-center flex items-center justify-center gap-1.5"
                        :class="pdv.status_inicial === 'aceito' ? 'bg-[#291f11] border-[#f59e0b] text-[#f59e0b]' : 'bg-[#121624] border-[#1c2336] text-slate-400 hover:text-white'">
                        Direto para a cozinha
                    </button>
                    <button type="button" @click="pdv.status_inicial = 'novo'"
                        class="py-2.5 px-2 rounded-xl font-bold border transition text-center flex items-center justify-center gap-1.5"
                        :class="pdv.status_inicial === 'novo' ? 'bg-[#2a131e] border-[#f43f5e] text-[#f43f5e]' : 'bg-[#121624] border-[#1c2336] text-slate-400 hover:text-white'">
                        Aguardar aceite
                    </button>
                </div>

                <!-- 7. OBSERVAÇÕES -->
                <input type="text" x-model="pdv.observacao" placeholder="Observações (ex: embalar para presente)"
                    class="w-full bg-[#121624] border border-[#1c2336] rounded-xl px-3 py-2.5 text-white placeholder-slate-500 focus:border-[#f43f5e] focus:outline-none text-xs">

                <!-- 8. BOTÃO DE AÇÃO: LANÇAR PEDIDO (Design Fiel Imagem 3) -->
                <button @click="enviarPedidoPdv()" :disabled="pdv.itens.length === 0 || salvandoPdv"
                    class="w-full bg-[#e11d48] hover:bg-[#be123c] disabled:opacity-40 disabled:cursor-not-allowed text-white font-black py-3.5 rounded-2xl text-sm shadow-xl shadow-rose-600/30 active:scale-98 transition flex items-center justify-center gap-2">
                    <span x-show="!salvandoPdv" class="flex items-center gap-1.5">
                        <span>⚡</span>
                        <span>Lançar pedido · </span>
                        <span x-text="formatarCentavos(calcularTotalPdvCentavos())"></span>
                    </span>
                    <span x-show="salvandoPdv" class="flex items-center gap-2">
                        <span class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span>Gravando pedido...</span>
                    </span>
                </button>

            </div>

        </div>

    </div>

    <!-- SUB-MODAL: PERSONALIZAÇÃO DE PRODUTO (VARIAÇÕES & ADICIONAIS) -->
    <template x-if="pdvItemCustomizando">
        <div class="fixed inset-0 z-60 bg-black/85 backdrop-blur-sm flex items-center justify-center p-3">
            <div @click.away="fecharCustomizacaoPdv()"
                 class="bg-[#0b0e17] border border-[#1b2234] w-full max-w-lg rounded-3xl p-5 space-y-4 max-h-[90vh] overflow-y-auto shadow-2xl">
                
                <div class="flex justify-between items-start border-b border-[#1b2234] pb-3">
                    <div>
                        <h4 class="font-black text-white text-base" x-text="pdvItemCustomizando.nome"></h4>
                        <p class="text-xs text-slate-400" x-text="pdvItemCustomizando.descricao || 'Personalize os adicionais e opções'"></p>
                    </div>
                    <button @click="fecharCustomizacaoPdv()" class="text-slate-400 hover:text-white font-bold text-lg p-1">✕</button>
                </div>

                <!-- Variações de Tamanho -->
                <template x-if="pdvItemCustomizando.variacoes && pdvItemCustomizando.variacoes.length > 0">
                    <div class="space-y-2">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400">Escolha a Opção / Tamanho *</label>
                        <div class="space-y-1.5">
                            <template x-for="v in pdvItemCustomizando.variacoes" :key="v.id">
                                <label class="flex justify-between items-center p-3 rounded-xl border cursor-pointer transition"
                                       :class="pdvVariacaoSelecionadaId === v.id ? 'bg-rose-500/10 border-[#f43f5e] text-white' : 'bg-[#121624] border-[#1c2336] text-slate-300 hover:border-slate-600'">
                                    <div class="flex items-center gap-2.5">
                                        <input type="radio" :value="v.id" x-model="pdvVariacaoSelecionadaId" class="text-rose-600 focus:ring-rose-500">
                                        <span class="font-bold text-xs" x-text="v.nome"></span>
                                    </div>
                                    <span class="font-extrabold text-[#10b981] text-xs" x-text="formatarCentavos(v.preco_centavos)"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Grupos de Adicionais -->
                <template x-if="pdvItemCustomizando.grupos_adicionais && pdvItemCustomizando.grupos_adicionais.length > 0">
                    <div class="space-y-3">
                        <template x-for="g in pdvItemCustomizando.grupos_adicionais" :key="g.id">
                            <div class="bg-[#121624] border border-[#1c2336] rounded-2xl p-3.5 space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-white text-xs" x-text="g.nome"></span>
                                    <span class="text-[10px] text-slate-400" x-text="'Máx: ' + (g.maximo || 1)"></span>
                                </div>
                                <div class="space-y-1.5">
                                    <template x-for="op in g.opcoes" :key="op.id">
                                        <label class="flex justify-between items-center p-2 rounded-xl bg-[#0b0e17] hover:bg-[#151a2a] border border-[#1c2336] cursor-pointer">
                                            <div class="flex items-center gap-2">
                                                <input type="checkbox" :checked="isAdicionalSelecionadoPdv(op.id)" @change="alternarAdicionalPdv(op, g)"
                                                    class="rounded text-rose-600 focus:ring-rose-500">
                                                <span class="text-xs text-slate-200" x-text="op.nome"></span>
                                            </div>
                                            <span class="text-xs font-bold text-[#10b981]" x-text="op.preco_centavos > 0 ? '+ ' + formatarCentavos(op.preco_centavos) : 'Grátis'"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Quantidade e Observações do Item -->
                <div class="space-y-2 pt-2 border-t border-[#1b2234]">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-300">Quantidade do Item:</span>
                        <div class="flex items-center gap-2 bg-[#121624] px-3 py-1 rounded-xl border border-[#1c2336]">
                            <button type="button" @click="pdvQtdItem = Math.max(1, pdvQtdItem - 1)" class="font-black text-slate-400 hover:text-white px-1">−</button>
                            <span class="font-black text-white text-sm px-2" x-text="pdvQtdItem"></span>
                            <button type="button" @click="pdvQtdItem++" class="font-black text-slate-400 hover:text-white px-1">+</button>
                        </div>
                    </div>
                    <input type="text" x-model="pdvObservacaoItem" placeholder="Observação deste item (ex: Sem cebola)..."
                        class="w-full bg-[#121624] border border-[#1c2336] rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 focus:border-[#f43f5e] focus:outline-none">
                </div>

                <!-- Botão Confirmar Adição do Item -->
                <div class="flex gap-2 pt-2">
                    <button type="button" @click="fecharCustomizacaoPdv()"
                        class="w-1/3 bg-[#121624] hover:bg-[#1a2034] text-slate-300 border border-[#1c2336] font-bold py-2.5 rounded-xl text-xs transition">
                        Cancelar
                    </button>
                    <button type="button" @click="confirmarItemCustomizadoPdv()"
                        class="w-2/3 bg-[#e11d48] hover:bg-[#be123c] text-white font-extrabold py-2.5 rounded-xl text-xs shadow-lg shadow-rose-600/30 transition">
                        ✓ Inserir no Pedido
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
