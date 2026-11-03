        <div x-cloak x-show="moduloAtivo === 'config'" class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Dados Operacionais & Identidade Visual -->
                <div class="lg:col-span-7 bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4">
                    <h2 class="font-bold text-base text-white">Dados Operacionais &amp; Identidade Visual do Restaurante</h2>
                    
                    <!-- Upload de Logo e Banner de Capa -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-slate-950 border border-slate-800 rounded-2xl p-3.5 text-xs">
                        <div class="space-y-1.5">
                            <label class="font-bold text-slate-300 block">Logo da Loja (Quadrada)</label>
                            <div class="flex items-center gap-2.5">
                                <template x-if="loja?.logo_url">
                                    <img :src="loja.logo_url.startsWith('http') || loja.logo_url.startsWith('/') ? loja.logo_url : '/storage/' + loja.logo_url" class="w-11 h-11 rounded-xl object-cover border border-slate-700 shrink-0">
                                </template>
                                <input type="file" accept="image/jpeg,image/png,image/webp" @change="uploadImagemLoja('logo', $event.target.files[0])"
                                    class="w-full text-[11px] text-slate-400 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-rose-600/20 file:text-rose-300">
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <label class="font-bold text-slate-300 block">Banner de Capa do Cardápio</label>
                            <div class="flex items-center gap-2.5">
                                <template x-if="loja?.capa_url">
                                    <img :src="loja.capa_url.startsWith('http') || loja.capa_url.startsWith('/') ? loja.capa_url : '/storage/' + loja.capa_url" class="w-16 h-11 rounded-xl object-cover border border-slate-700 shrink-0">
                                </template>
                                <input type="file" accept="image/jpeg,image/png,image/webp" @change="uploadImagemLoja('capa', $event.target.files[0])"
                                    class="w-full text-[11px] text-slate-400 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-rose-600/20 file:text-rose-300">
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="salvarDadosLoja()" class="space-y-3 text-xs">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="font-bold text-slate-400 block mb-1">Nome Fantasia da Loja *</label>
                                <input type="text" x-model="configLoja.nome" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-bold">
                            </div>
                            <div>
                                <label class="font-bold text-slate-400 block mb-1">CNPJ ou CPF</label>
                                <input type="text" x-model="configLoja.cnpj_cpf" placeholder="00.000.000/0001-00" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="font-bold text-slate-400 block mb-1">WhatsApp de Atendimento</label>
                                <input type="text" x-model="configLoja.telefone_whatsapp" placeholder="31999999999" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                            </div>
                            <div>
                                <label class="font-bold text-slate-400 block mb-1">Cor Primária da Marca (Hex)</label>
                                <div class="flex gap-2">
                                    <input type="color" x-model="configLoja.cor_primaria" class="h-10 w-12 rounded-xl bg-slate-950 border border-slate-800 cursor-pointer">
                                    <input type="text" x-model="configLoja.cor_primaria" class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-mono">
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="font-bold text-slate-400 block mb-1">Endereço Completo</label>
                            <input type="text" x-model="configLoja.endereco" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-bold text-slate-400 block mb-1">Cidade</label>
                                <input type="text" x-model="configLoja.cidade" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                            </div>
                            <div>
                                <label class="font-bold text-slate-400 block mb-1">UF</label>
                                <input type="text" x-model="configLoja.uf" maxlength="2" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white uppercase">
                            </div>
                        </div>

                        <!-- Parâmetros Operacionais Avançados -->
                        <div class="border-t border-slate-800 pt-3 mt-2 space-y-3">
                            <span class="font-semibold text-slate-300 block">Parâmetros Operacionais (Preparo, Cashback, Salão &amp; Pizzaria)</span>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                <div>
                                    <label class="text-[11px] font-bold text-slate-400 block mb-1">Tempo Preparo (min)</label>
                                    <input type="number" min="5" max="180" x-model.number="configLoja.tempo_medio_preparo_min" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white font-bold">
                                </div>
                                <div>
                                    <label class="text-[11px] font-bold text-slate-400 block mb-1">Taxa Garçom (%)</label>
                                    <input type="number" min="0" max="30" x-model.number="configLoja.taxa_servico_pct" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white font-bold">
                                </div>
                                <div>
                                    <label class="text-[11px] font-bold text-slate-400 block mb-1">Cashback (%)</label>
                                    <input type="number" min="0" max="50" x-model.number="configLoja.cashback_pct" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-emerald-400 font-bold">
                                </div>
                                <div>
                                    <label class="text-[11px] font-bold text-slate-400 block mb-1">Pizza Meio a Meio</label>
                                    <select x-model="configLoja.estrategia_pizza_meio_a_meio" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-2.5 py-2 text-white">
                                        <option value="maior_preco">Maior valor</option>
                                        <option value="media">Média dos sabores</option>
                                    </select>
                                </div>
                            </div>
                            <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer pt-1">
                                <input type="checkbox" x-model="configLoja.impressao_automatica_ao_aceitar" class="rounded text-rose-600">
                                <span>Imprimir comanda automaticamente ao aceitar pedido</span>
                            </label>
                        </div>

                        <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-semibold py-3 rounded-xl text-sm shadow-lg transition">
                            Salvar Dados e Configurações Operacionais
                        </button>
                    </form>
                </div>

                <!-- Minha Assinatura & Horários de Funcionamento -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4 flex flex-col justify-between">
                        <div class="space-y-3">
                            <span class="text-xs font-bold uppercase text-rose-400">Minha Assinatura PedeLivre</span>
                            <h3 class="text-xl font-bold text-white" x-text="'Plano ' + (loja?.plano || 'Premium')"></h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Seu ambiente opera com 0% de comissão sobre vendas e isolamento total de dados da sua loja.
                            </p>
                            <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 space-y-2 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Status da Conta:</span>
                                    <span class="font-bold uppercase"
                                          :class="loja?.status === 'ativa' || loja?.status === 'trial' ? 'text-emerald-400' : 'text-amber-400'"
                                          x-text="loja?.status === 'trial' ? 'Em Período Trial' : (loja?.status === 'ativa' ? 'Ativa e Regular' : (loja?.status || 'Ativa'))"></span>
                                </div>
                                <div class="flex justify-between"><span class="text-slate-400">Comissão sobre Vendas:</span><span class="text-emerald-400 font-bold">0% (Isento)</span></div>
                                <div class="flex justify-between"><span class="text-slate-400">Link Público:</span><a :href="'/p/' + (loja?.slug || '')" target="_blank" class="text-rose-400 font-bold hover:underline" x-text="'/p/' + (loja?.slug || '')"></a></div>
                            </div>
                        </div>

                        <button @click="copiarLinkCardapio()" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 rounded-xl text-center text-xs transition shadow-lg">
                            Copiar Link do Cardápio p/ WhatsApp &amp; Bio
                        </button>
                    </div>

                    <!-- Horários de Funcionamento -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-3 text-xs">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-sm text-white">Horários de Funcionamento</h3>
                                <p class="text-[11px] text-slate-400">Configure os turnos em que a loja aceita pedidos</p>
                            </div>
                            <button type="button" @click="adicionarTurnoHorario()" class="bg-slate-800 hover:bg-slate-700 text-white font-bold px-2.5 py-1.5 rounded-xl text-[11px]">
                                + Turno
                            </button>
                        </div>
                        <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                            <template x-for="(h, idx) in horariosLoja" :key="idx">
                                <div class="bg-slate-950 border border-slate-800 rounded-xl p-2.5 flex flex-wrap items-center justify-between gap-2">
                                    <select x-model.number="h.dia_semana" class="bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-white font-bold text-[11px]">
                                        <option :value="0">Domingo</option>
                                        <option :value="1">Segunda</option>
                                        <option :value="2">Terça</option>
                                        <option :value="3">Quarta</option>
                                        <option :value="4">Quinta</option>
                                        <option :value="5">Sexta</option>
                                        <option :value="6">Sábado</option>
                                    </select>
                                    <div class="flex items-center gap-1">
                                        <input type="time" x-model="h.abre_as" class="bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-white text-[11px]">
                                        <span class="text-slate-500">às</span>
                                        <input type="time" x-model="h.fecha_as" class="bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-white text-[11px]">
                                    </div>
                                    <button type="button" @click="horariosLoja.splice(idx, 1)" class="text-rose-400 font-bold px-1.5"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="salvarHorariosLoja()" class="w-full bg-slate-800 hover:bg-slate-700 text-white font-bold py-2.5 rounded-xl transition">
                            Salvar Horários de Atendimento
                        </button>
                    </div>
                </div>
            </div>

            <!-- Card de Conexão WhatsApp na aba Loja/Config -->
            <div class="bg-slate-900 border-2 rounded-3xl p-6 space-y-4"
                 :class="whatsappConectado ? 'border-emerald-500/40' : 'border-slate-800'">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xl"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg></span>
                            <h3 class="font-semibold text-sm text-white">WhatsApp &amp; Automações da Loja</h3>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold"
                                  :class="whatsappConectado ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700'"
                                  x-text="whatsappConectado ? ('Conectado: +' + (whatsappStatus.phone || 'Ativo')) : 'Desconectado'"></span>
                        </div>
                        <p class="text-xs text-slate-400">
                            Dispare avisos aos clientes a cada fase do pedido, acione motoboys com rota no Google Maps e tenha atendimento IA automático no WhatsApp.
                        </p>
                    </div>
                    <button @click="mudarModulo('whatsapp')" class="bg-emerald-600 hover:bg-emerald-500 text-white font-semibold px-4 py-2 rounded-xl text-xs shadow-lg transition shrink-0">
                        <span x-text="whatsappConectado ? 'Gerenciar Automações' : 'Conectar WhatsApp (QR Code)'"></span>
                    </button>
                </div>
            </div>

            <!-- Card de Recebimento PIX: Mercado Pago API ou Chave PIX Direta BCB -->
            <div class="bg-slate-900 border-2 rounded-3xl p-6 space-y-5"
                 :class="mpStatus?.conectado ? 'border-emerald-500/40' : 'border-amber-500/40'">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-base font-bold text-white">Recebimento PIX no Checkout (Mercado Pago ou Chave PIX Direta)</h3>
                            <span class="text-xs font-bold px-3 py-0.5 rounded-full border"
                                  :class="mpStatus?.conectado ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border-amber-500/30'"
                                  x-text="mpStatus?.conectado ? 'Ativo (' + (mpStatus.mp_conta_nome || mpStatus.chave_pix || 'Configurado') + ')' : 'Configure sua Chave PIX ou Mercado Pago'">
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">
                            Informe sua <strong>Chave PIX Principal</strong> (para gerar QR Code PIX Copia e Cola padrão Banco Central direto para sua conta) e/ou seu <strong>Access Token do Mercado Pago</strong> para baixa automática via Webhook.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2 shrink-0">
                        <template x-if="mpStatus?.conectado">
                            <button @click="testarPixMercadoPago()" :disabled="testandoPix" class="bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs px-3.5 py-2.5 rounded-xl">
                                <span x-text="testandoPix ? 'Gerando...' : 'Testar PIX Real'"></span>
                            </button>
                        </template>
                        <button @click="conectarMercadoPago()" class="bg-sky-600 hover:bg-sky-500 text-white font-semibold text-xs px-3.5 py-2.5 rounded-xl">
                            OAuth Mercado Pago
                        </button>
                        <template x-if="mpStatus?.conectado">
                            <button @click="desconectarMercadoPago()" class="bg-slate-800 hover:bg-rose-600/20 text-slate-300 hover:text-rose-300 border border-slate-700 font-bold text-xs px-3 py-2.5 rounded-xl">
                                Desconectar
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Resultado do Teste de Emissão PIX Ao Vivo -->
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
                            <input type="text" readonly :value="pixTesteResultado.pix_copia_cola" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-[11px] text-emerald-300 font-mono">
                        </div>
                        <button @click="pixTesteResultado = null" class="text-slate-400 font-bold text-sm self-start"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                    </div>
                </template>

                <form @submit.prevent="salvarTokenMercadoPago()" class="grid grid-cols-1 md:grid-cols-12 gap-3 text-xs items-end">
                    <div class="md:col-span-5">
                        <label class="font-bold text-slate-300 block mb-1">Access Token Mercado Pago (<code class="text-emerald-400">APP_USR-...</code>) (Opcional)</label>
                        <input type="password" x-model="formMp.access_token" placeholder="APP_USR-0000000000000000-..."
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-mono">
                    </div>
                    <div class="md:col-span-4">
                        <label class="font-bold text-slate-300 block mb-1">Chave PIX Principal (CNPJ, CPF, E-mail, Celular)</label>
                        <input type="text" x-model="formMp.chave_pix" placeholder="Ex: 00.000.000/0001-00 ou pix@loja.com.br"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                    </div>
                    <div class="md:col-span-3">
                        <button type="submit" :disabled="salvandoMp" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2.5 rounded-xl text-xs shadow-lg transition">
                            <span x-text="salvandoMp ? 'Salvando...' : 'Salvar Recebimento PIX'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
