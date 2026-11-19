        <div x-cloak x-show="moduloAtivo === 'config'" class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-5">

            <!-- Barra de Sub-Abas de Configurações (1 Assunto por Tela — Zero Mistura) -->
            <div class="bg-[#0d1322] border border-slate-800/90 p-2 rounded-2xl flex items-center gap-1.5 overflow-x-auto no-scrollbar text-xs">
                <button type="button" @click="abaConfig = 'dados'"
                    class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition flex items-center gap-2"
                    :class="abaConfig === 'dados' ? 'bg-rose-600 text-white shadow' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <span>1. Dados &amp; Identidade Visual</span>
                </button>

                <button type="button" @click="abaConfig = 'horarios'"
                    class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition flex items-center gap-2"
                    :class="abaConfig === 'horarios' ? 'bg-rose-600 text-white shadow' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>2. Horários de Funcionamento</span>
                </button>

                <button type="button" @click="abaConfig = 'operacao'"
                    class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition flex items-center gap-2"
                    :class="abaConfig === 'operacao' ? 'bg-emerald-600 text-white shadow' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    <span>3. Piloto Automático &amp; Regras</span>
                </button>

                <button type="button" @click="abaConfig = 'pix'"
                    class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition flex items-center gap-2"
                    :class="abaConfig === 'pix' ? 'bg-sky-600 text-white shadow' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>4. Recebimento PIX</span>
                </button>

                <button type="button" @click="abaConfig = 'assinatura'"
                    class="px-3.5 py-2 rounded-xl font-bold whitespace-nowrap transition flex items-center gap-2"
                    :class="abaConfig === 'assinatura' ? 'bg-amber-500 text-slate-950 shadow' : 'text-slate-400 hover:text-white hover:bg-slate-800/60'">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span>5. Minha Assinatura SaaS</span>
                </button>
            </div>

            <!-- ============================================================== -->
            <!-- SUB-ABA 1: DADOS DA LOJA & IDENTIDADE VISUAL                    -->
            <!-- ============================================================== -->
            <div x-show="abaConfig === 'dados'" class="max-w-3xl bg-[#0d1322] border border-slate-800/90 rounded-3xl p-6 space-y-5">
                <div class="border-b border-slate-800 pb-3">
                    <h2 class="font-bold text-base text-white">Dados Cadastrais &amp; Identidade Visual</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Personalize a logo, o banner de capa, a cor da marca e o endereço exibido no seu cardápio digital.</p>
                </div>

                <!-- Upload de Logo e Banner de Capa -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-[#080c17] border border-slate-800 rounded-2xl p-4 text-xs">
                    <div class="space-y-2">
                        <label class="font-bold text-slate-300 block">Logo da Loja (Quadrada)</label>
                        <div class="flex items-center gap-3">
                            <template x-if="loja?.logo_url">
                                <img :src="loja.logo_url.startsWith('http') || loja.logo_url.startsWith('/') ? loja.logo_url : '/storage/' + loja.logo_url" class="w-12 h-12 rounded-xl object-cover border border-slate-700 shrink-0">
                            </template>
                            <input type="file" accept="image/jpeg,image/png,image/webp" @change="uploadImagemLoja('logo', $event.target.files[0])"
                                class="w-full text-[11px] text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-rose-600/20 file:text-rose-300">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="font-bold text-slate-300 block">Banner de Capa do Cardápio</label>
                        <div class="flex items-center gap-3">
                            <template x-if="loja?.capa_url">
                                <img :src="loja.capa_url.startsWith('http') || loja.capa_url.startsWith('/') ? loja.capa_url : '/storage/' + loja.capa_url" class="w-20 h-12 rounded-xl object-cover border border-slate-700 shrink-0">
                            </template>
                            <input type="file" accept="image/jpeg,image/png,image/webp" @change="uploadImagemLoja('capa', $event.target.files[0])"
                                class="w-full text-[11px] text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-rose-600/20 file:text-rose-300">
                        </div>
                    </div>
                </div>

                <form @submit.prevent="salvarDadosLoja()" class="space-y-4 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="font-bold text-slate-400 block mb-1">Nome Fantasia da Loja *</label>
                            <input type="text" x-model="configLoja.nome" required class="w-full bg-[#080c17] border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-bold">
                        </div>
                        <div>
                            <label class="font-bold text-slate-400 block mb-1">CNPJ ou CPF</label>
                            <input type="text" x-model="configLoja.cnpj_cpf" placeholder="00.000.000/0001-00" class="w-full bg-[#080c17] border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="font-bold text-slate-400 block mb-1">WhatsApp de Atendimento</label>
                            <input type="text" x-model="configLoja.telefone_whatsapp" placeholder="31999999999" class="w-full bg-[#080c17] border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                        </div>
                        <div>
                            <label class="font-bold text-slate-400 block mb-1">Cor Primária da Marca (Hex)</label>
                            <div class="flex gap-2">
                                <input type="color" x-model="configLoja.cor_primaria" class="h-10 w-12 rounded-xl bg-[#080c17] border border-slate-800 cursor-pointer">
                                <input type="text" x-model="configLoja.cor_primaria" class="flex-1 bg-[#080c17] border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-mono">
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Endereço Completo</label>
                        <input type="text" x-model="configLoja.endereco" class="w-full bg-[#080c17] border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                    </div>
                    <div class="grid grid-cols-2 gap-3.5">
                        <div>
                            <label class="font-bold text-slate-400 block mb-1">Cidade</label>
                            <input type="text" x-model="configLoja.cidade" class="w-full bg-[#080c17] border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                        </div>
                        <div>
                            <label class="font-bold text-slate-400 block mb-1">UF</label>
                            <input type="text" x-model="configLoja.uf" maxlength="2" class="w-full bg-[#080c17] border border-slate-800 rounded-xl px-3.5 py-2.5 text-white uppercase">
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-semibold py-3 rounded-xl text-sm shadow-lg transition">
                        Salvar Dados da Loja
                    </button>
                </form>
            </div>

            <!-- ============================================================== -->
            <!-- SUB-ABA 2: HORÁRIOS DE FUNCIONAMENTO                            -->
            <!-- ============================================================== -->
            <div x-show="abaConfig === 'horarios'" class="max-w-3xl bg-[#0d1322] border border-slate-800/90 rounded-3xl p-6 space-y-4 text-xs">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="font-bold text-base text-white">Horários de Funcionamento</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Configure os dias e turnos em que sua loja abre e fecha automaticamente no cardápio e no robô WhatsApp.</p>
                    </div>
                    <button type="button" @click="adicionarTurnoHorario()" class="bg-rose-600 hover:bg-rose-500 text-white font-bold px-3.5 py-2 rounded-xl text-xs shadow">
                        + Adicionar Turno
                    </button>
                </div>

                <div class="space-y-2.5 max-h-96 overflow-y-auto pr-1 custom-scrollbar">
                    <template x-for="(h, idx) in horariosLoja" :key="idx">
                        <div class="bg-[#080c17] border border-slate-800 rounded-xl p-3 flex flex-wrap items-center justify-between gap-3">
                            <select x-model.number="h.dia_semana" class="bg-[#0d1322] border border-slate-700 rounded-lg px-3 py-1.5 text-white font-bold text-xs">
                                <option :value="0">Domingo</option>
                                <option :value="1">Segunda-feira</option>
                                <option :value="2">Terça-feira</option>
                                <option :value="3">Quarta-feira</option>
                                <option :value="4">Quinta-feira</option>
                                <option :value="5">Sexta-feira</option>
                                <option :value="6">Sábado</option>
                            </select>
                            <div class="flex items-center gap-2">
                                <span class="text-slate-400">Abre às</span>
                                <input type="time" x-model="h.abre_as" class="bg-[#0d1322] border border-slate-700 rounded-lg px-2.5 py-1.5 text-white text-xs">
                                <span class="text-slate-400">Fecha às</span>
                                <input type="time" x-model="h.fecha_as" class="bg-[#0d1322] border border-slate-700 rounded-lg px-2.5 py-1.5 text-white text-xs">
                            </div>
                            <button type="button" @click="horariosLoja.splice(idx, 1)" class="text-rose-400 hover:bg-rose-500/15 p-1.5 rounded-lg font-bold">
                                <svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </template>
                </div>

                <button type="button" @click="salvarHorariosLoja()" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 rounded-xl text-sm transition shadow-lg">
                    Salvar Horários de Atendimento
                </button>
            </div>

            <!-- ============================================================== -->
            <!-- SUB-ABA 3: PILOTO AUTOMÁTICO & REGRAS OPERACIONAIS             -->
            <!-- ============================================================== -->
            <div x-show="abaConfig === 'operacao'" class="max-w-3xl bg-[#0d1322] border border-slate-800/90 rounded-3xl p-6 space-y-5 text-xs">
                <form @submit.prevent="salvarDadosLoja()" class="space-y-5">
                    <!-- Modo 100% Autônomo -->
                    <div class="space-y-3 bg-gradient-to-r from-emerald-950/30 to-[#080c17] p-4 rounded-2xl border border-emerald-500/25">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="font-bold text-sm text-white">Modo 100% Autônomo (Piloto Automático)</span>
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                Zero Fila &amp; Sem Atendente
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            Permite que sua operação funcione 24/7 de forma independente: pedidos entram direto para a cozinha, clientes são notificados no WhatsApp e pedidos em rota finalizam sozinhos.
                        </p>
                        <div class="space-y-2 pt-1">
                            <label class="flex items-start gap-2.5 text-xs text-slate-200 cursor-pointer bg-[#0d1322]/90 p-3 rounded-xl border border-slate-800 hover:border-emerald-500/40 transition">
                                <input type="checkbox" x-model="configLoja.auto_aceitar_pedidos" class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <strong class="text-white block">Auto-Aceite e Roteamento Direto para a Cozinha</strong>
                                    <span class="text-[11px] text-slate-400">Aceita pedidos (PIX aprovados e pagamentos na entrega) e envia imediatamente para o KDS/cozinha sem esperar clique humano.</span>
                                </div>
                            </label>
                            <label class="flex items-start gap-2.5 text-xs text-slate-200 cursor-pointer bg-[#0d1322]/90 p-3 rounded-xl border border-slate-800 hover:border-emerald-500/40 transition">
                                <input type="checkbox" x-model="configLoja.notificar_whatsapp_automatico" class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <strong class="text-white block">Notificações WhatsApp em Tempo Real</strong>
                                    <span class="text-[11px] text-slate-400">Envia link de rastreio ao vivo, alertas de preparo, saída para entrega com dados do motoboy e pesquisa NPS pós-entrega.</span>
                                </div>
                            </label>
                            <label class="flex items-start gap-2.5 text-xs text-slate-200 cursor-pointer bg-[#0d1322]/90 p-3 rounded-xl border border-slate-800 hover:border-emerald-500/40 transition">
                                <input type="checkbox" x-model="configLoja.auto_finalizar_pedidos" class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <strong class="text-white block">Auto-Finalização de Pedidos em Rota (Auto-Complete)</strong>
                                    <span class="text-[11px] text-slate-400">Conclui pedidos esquecidos em rota após 120min, creditando cashback e liberando convite para avaliação 5 estrelas.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Parâmetros Operacionais -->
                    <div class="bg-[#080c17] border border-slate-800 rounded-2xl p-4 space-y-3.5">
                        <span class="font-bold text-sm text-white block">Regras de Preparo, Salão, Cashback &amp; Pizzaria</span>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="text-[11px] font-bold text-slate-400 block mb-1">Tempo Preparo (min)</label>
                                <input type="number" min="5" max="180" x-model.number="configLoja.tempo_medio_preparo_min" class="w-full bg-[#0d1322] border border-slate-800 rounded-xl px-3 py-2 text-white font-bold">
                            </div>
                            <div>
                                <label class="text-[11px] font-bold text-slate-400 block mb-1">Taxa Garçom (%)</label>
                                <input type="number" min="0" max="30" x-model.number="configLoja.taxa_servico_pct" class="w-full bg-[#0d1322] border border-slate-800 rounded-xl px-3 py-2 text-white font-bold">
                            </div>
                            <div>
                                <label class="text-[11px] font-bold text-slate-400 block mb-1">Cashback (%)</label>
                                <input type="number" min="0" max="50" x-model.number="configLoja.cashback_pct" class="w-full bg-[#0d1322] border border-slate-800 rounded-xl px-3 py-2 text-emerald-400 font-bold">
                            </div>
                            <div>
                                <label class="text-[11px] font-bold text-slate-400 block mb-1">Pizza Meio a Meio</label>
                                <select x-model="configLoja.estrategia_pizza_meio_a_meio" class="w-full bg-[#0d1322] border border-slate-800 rounded-xl px-2.5 py-2 text-white">
                                    <option value="maior_preco">Maior valor</option>
                                    <option value="media">Média dos sabores</option>
                                </select>
                            </div>
                        </div>
                        <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer pt-1">
                            <input type="checkbox" x-model="configLoja.impressao_automatica_ao_aceitar" class="rounded text-rose-600">
                            <span>Imprimir comanda térmica 80mm automaticamente ao aceitar pedido</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 rounded-xl text-sm shadow-lg transition">
                        Salvar Regras Operacionais &amp; Piloto Automático
                    </button>
                </form>
            </div>

            <!-- ============================================================== -->
            <!-- SUB-ABA 4: RECEBIMENTO PIX (ZERO ACCESS TOKEN OBRIGATÓRIO)     -->
            <!-- ============================================================== -->
            <div x-show="abaConfig === 'pix'" class="max-w-3xl bg-[#0d1322] border-2 rounded-3xl p-6 space-y-5"
                 :class="(mpStatus?.conectado || mpStatus?.chave_pix) ? 'border-emerald-500/40' : 'border-amber-500/40'">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-base font-bold text-white">Recebimento PIX dos Clientes</h3>
                            <span class="text-xs font-bold px-3 py-0.5 rounded-full border"
                                  :class="(mpStatus?.conectado || mpStatus?.chave_pix) ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border-amber-500/30'"
                                  x-text="mpStatus?.conectado
                                      ? 'API Mercado Pago Ativa (' + (mpStatus.mp_conta_nome || 'Conectado') + ')'
                                      : (mpStatus?.chave_pix ? 'PIX Ativo (' + mpStatus.chave_pix + ')' : 'Aguardando Chave PIX')">
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">
                            Basta informar sua <strong>Chave PIX de qualquer banco</strong> (Nubank, Inter, Itaú, Sicredi, Mercado Pago, Caixa). O dinheiro cai 100% direto na sua conta sem taxas!
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2 shrink-0">
                        <template x-if="mpStatus?.conectado || mpStatus?.chave_pix">
                            <button @click="testarPixMercadoPago()" :disabled="testandoPix" class="bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs px-3.5 py-2.5 rounded-xl shadow">
                                <span x-text="testandoPix ? 'Gerando...' : 'Testar QR Code PIX (R$ 1,00)'"></span>
                            </button>
                        </template>
                        <template x-if="mpStatus?.oauth_disponivel">
                            <button @click="conectarMercadoPago()" class="bg-sky-600 hover:bg-sky-500 text-white font-semibold text-xs px-3.5 py-2.5 rounded-xl">
                                Conectar Mercado Pago (1-Clique)
                            </button>
                        </template>
                    </div>
                </div>

                <template x-if="pixTesteResultado">
                    <div class="bg-emerald-950/40 border border-emerald-500/40 rounded-2xl p-4 flex flex-col sm:flex-row items-center gap-4">
                        <template x-if="pixTesteResultado.pix_qr_base64">
                            <img :src="'data:image/png;base64,' + pixTesteResultado.pix_qr_base64" class="w-28 h-28 rounded-xl bg-white p-1.5 shrink-0">
                        </template>
                        <template x-if="!pixTesteResultado.pix_qr_base64 && pixTesteResultado.pix_copia_cola">
                            <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' + encodeURIComponent(pixTesteResultado.pix_copia_cola)" class="w-28 h-28 rounded-xl bg-white p-1.5 shrink-0">
                        </template>
                        <div class="space-y-2 text-xs flex-1 min-w-0 w-full">
                            <div class="font-bold text-emerald-400 text-sm" x-text="'' + pixTesteResultado.message"></div>
                            <div class="text-slate-300">Identificador: <strong class="text-white" x-text="pixTesteResultado.mp_payment_id"></strong></div>
                            <input type="text" readonly :value="pixTesteResultado.pix_copia_cola" class="w-full bg-[#080c17] border border-slate-800 rounded-xl px-3 py-2 text-[11px] text-emerald-300 font-mono">
                        </div>
                        <button @click="pixTesteResultado = null" class="text-slate-400 font-bold text-sm self-start"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                    </div>
                </template>

                <form @submit.prevent="salvarTokenMercadoPago()" class="space-y-4 text-xs">
                    <!-- Campo Único e Simples para Qualquer Lojista -->
                    <div class="bg-[#080c17] border border-slate-800 rounded-2xl p-4 space-y-3.5">
                        <div>
                            <label class="font-bold text-white text-sm block mb-1">Sua Chave PIX Principal (Qualquer Banco)</label>
                            <p class="text-[11px] text-slate-400 mb-2">Digite seu CNPJ, CPF, Celular, E-mail ou Chave Aleatória onde deseja receber o dinheiro dos pedidos:</p>
                            <input type="text" x-model="formMp.chave_pix" placeholder="Ex: 00.000.000/0001-00, celular ou seuemail@gmail.com"
                                   class="w-full bg-[#0d1322] border border-slate-700 focus:border-emerald-500 rounded-xl px-4 py-3 text-white font-bold text-sm focus:outline-none">
                        </div>

                        <!-- Switch de Baixa Automática sem Access Token -->
                        <label class="flex items-center justify-between bg-emerald-950/20 p-3.5 rounded-xl border border-emerald-500/30 cursor-pointer">
                            <div class="pr-3">
                                <span class="font-bold text-emerald-300 block">⚡ Baixa Automática de PIX sem Access Token (Recomendado)</span>
                                <span class="text-[11px] text-slate-400">Quando o cliente envia o comprovante no WhatsApp ou clica em "Já paguei o PIX" na tela do pedido, o sistema libera o pedido para a cozinha automaticamente.</span>
                            </div>
                            <input type="checkbox" x-model="formMp.auto_confirmar_pix_chave" class="w-5 h-5 text-emerald-500 rounded">
                        </label>
                    </div>

                    <!-- Opção Avançada Oculta (Só abre quem quiser colar token de API do Mercado Pago) -->
                    <details class="bg-[#080c17]/70 border border-slate-800/80 rounded-2xl px-4 py-3 group">
                        <summary class="font-bold text-slate-400 hover:text-slate-200 cursor-pointer select-none flex items-center justify-between text-xs">
                            <span>⚙️ Opção avançada: Usar token de API do Mercado Pago (Opcional)</span>
                            <span class="text-[10px] text-slate-500">Clique para expandir</span>
                        </summary>
                        <div class="pt-3 mt-3 border-t border-slate-800/80 space-y-3">
                            <p class="text-[11px] text-slate-400">
                                Se você possui uma conta Mercado Pago e deseja conciliação bancária via API direta (Webhook), cole seu Access Token de produção abaixo. Caso contrário, <strong>deixe em branco</strong> e use apenas sua Chave PIX acima.
                            </p>
                            <div>
                                <label class="font-bold text-slate-300 block mb-1">Access Token Mercado Pago (<code class="text-emerald-400">APP_USR-...</code>)</label>
                                <input type="password" x-model="formMp.access_token" placeholder="Opcional — deixe em branco para usar apenas sua Chave PIX"
                                       class="w-full bg-[#0d1322] border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-mono">
                            </div>
                            <template x-if="mpStatus?.conectado">
                                <button type="button" @click="desconectarMercadoPago()" class="bg-slate-800 hover:bg-rose-600/20 text-slate-300 hover:text-rose-300 border border-slate-700 font-bold text-xs px-3 py-2 rounded-xl">
                                    Remover Access Token do Mercado Pago
                                </button>
                            </template>
                        </div>
                    </details>

                    <button type="submit" :disabled="salvandoMp" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3.5 rounded-xl text-sm shadow-lg shadow-emerald-600/20 transition">
                        <span x-text="salvandoMp ? 'Salvando...' : 'Salvar Chave PIX e Ativar Recebimento'"></span>
                    </button>
                </form>
            </div>

            <!-- ============================================================== -->
            <!-- SUB-ABA 5: MINHA ASSINATURA SAAS & RENOVAÇÃO PIX               -->
            <!-- ============================================================== -->
            <div x-show="abaConfig === 'assinatura'" class="max-w-2xl bg-[#0d1322] border border-slate-800/90 rounded-3xl p-6 space-y-5">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase text-rose-400">Minha Assinatura PedeLivre</span>
                        <span class="text-[10px] font-bold uppercase px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">0% Comissão</span>
                    </div>
                    <h3 class="text-2xl font-bold text-white" x-text="'Plano ' + (assinaturaSaaS?.plano_nome || loja?.plano || 'Premium').toUpperCase()"></h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Seu ambiente opera com 0% de comissão sobre vendas, renovação 100% automática via PIX e isolamento total de dados da sua loja.
                    </p>
                    <div class="bg-[#080c17] border border-slate-800 rounded-2xl p-4 space-y-2.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Status da Conta:</span>
                            <span class="font-bold uppercase"
                                  :class="loja?.status === 'ativa' || loja?.status === 'trial' ? 'text-emerald-400' : 'text-amber-400'"
                                  x-text="loja?.status === 'trial' ? 'Em Período Trial' : (loja?.status === 'ativa' ? 'Ativa e Regular' : (loja?.status || 'Ativa'))"></span>
                        </div>
                        <div class="flex justify-between" x-show="assinaturaSaaS?.proximo_vencimento">
                            <span class="text-slate-400">Próximo Vencimento:</span>
                            <span class="text-white font-bold" x-text="assinaturaSaaS?.proximo_vencimento"></span>
                        </div>
                        <div class="flex justify-between"><span class="text-slate-400">Comissão sobre Vendas:</span><span class="text-emerald-400 font-bold">0% (Isento)</span></div>
                        <div class="flex justify-between"><span class="text-slate-400">Link Público:</span><a :href="'/p/' + (loja?.slug || '')" target="_blank" class="text-rose-400 font-bold hover:underline" x-text="'/p/' + (loja?.slug || '')"></a></div>
                    </div>

                    <template x-if="faturaSaaSAtiva">
                        <div class="bg-amber-950/30 border border-amber-500/40 rounded-2xl p-4 space-y-3 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-amber-300">⚡ Fatura PIX da Assinatura</span>
                                <span class="font-bold text-white" x-text="'Vencimento: ' + (faturaSaaSAtiva.vencimento || '-')"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="text" readonly :value="faturaSaaSAtiva.pix_copia_cola" class="flex-1 bg-[#080c17] border border-slate-800 rounded-xl px-3 py-2 text-[11px] text-emerald-300 font-mono">
                                <button type="button" @click="navigator.clipboard.writeText(faturaSaaSAtiva.pix_copia_cola); toastMsg = 'PIX Copia e Cola da fatura copiado!'"
                                        class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-3.5 py-2 rounded-xl shrink-0">
                                    Copiar PIX
                                </button>
                            </div>
                            <p class="text-[11px] text-slate-400">Após o pagamento do PIX no seu banco, sua assinatura é renovada e liberada automaticamente em segundos.</p>
                        </div>
                    </template>
                </div>

                <div class="space-y-2.5">
                    <button type="button" @click="gerarFaturaAssinatura()" :disabled="gerandoFaturaSaaS" class="w-full bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-3 rounded-xl text-center text-xs transition shadow-lg">
                        <span x-text="gerandoFaturaSaaS ? 'Gerando PIX...' : (faturaSaaSAtiva ? 'Atualizar Código PIX da Fatura' : 'Gerar Fatura PIX de Renovação')"></span>
                    </button>
                    <button @click="copiarLinkCardapio()" class="w-full bg-slate-800 hover:bg-slate-700 text-white font-bold py-3 rounded-xl text-center text-xs transition">
                        Copiar Link do Cardápio p/ WhatsApp &amp; Bio
                    </button>
                </div>
            </div>

        </div>
