        <div x-cloak x-show="moduloAtivo === 'config'" class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-7 bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4">
                    <h2 class="font-bold text-base text-white">⚙️ Dados Operacionais &amp; Identidade Visual do Restaurante</h2>
                    <form @submit.prevent="salvarDadosLoja()" class="space-y-3 text-xs">
                        <div>
                            <label class="font-bold text-slate-400 block mb-1">Nome Fantasia da Loja</label>
                            <input type="text" x-model="configLoja.nome" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-bold">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="font-bold text-slate-400 block mb-1">WhatsApp de Atendimento</label>
                                <input type="text" x-model="configLoja.telefone_whatsapp" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
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
                        <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-extrabold py-3 rounded-xl text-sm shadow-lg transition">
                            💾 Salvar Dados e Aplicar Cor no Cardápio
                        </button>
                    </form>
                </div>

                <div class="lg:col-span-5 bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4 flex flex-col justify-between">
                    <div class="space-y-3">
                        <span class="text-xs font-black uppercase text-rose-400">Minha Assinatura PedeLivre</span>
                        <h3 class="text-xl font-black text-white">Plano Premium Total</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Você possui todos os módulos liberados: Cardápio Digital de Alta Conversão, Delivery, QR Code de Mesa, KDS por Estação, CRM Cashback e PIX Mercado Pago 0% taxa.
                        </p>
                        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 space-y-2 text-xs">
                            <div class="flex justify-between"><span class="text-slate-400">Status da Conta:</span><span class="text-emerald-400 font-bold">✓ Ativa e Regular</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Comissão sobre Vendas:</span><span class="text-emerald-400 font-bold">0% (Isento)</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Link Público:</span><span class="text-rose-400 font-bold" x-text="'/p/' + (loja?.slug || '')"></span></div>
                        </div>
                    </div>

                    <button @click="copiarLinkCardapio()" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black py-3 rounded-xl text-center text-xs transition shadow-lg">
                        📲 Copiar Link do Cardápio p/ WhatsApp &amp; Bio
                    </button>
                </div>
            </div>

            <!-- Card de Conexão WhatsApp na aba Loja/Config -->
            <div class="bg-slate-900 border-2 rounded-3xl p-6 space-y-4"
                 :class="whatsappConectado ? 'border-emerald-500/40' : 'border-slate-800'">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">💬</span>
                            <h3 class="font-extrabold text-sm text-white">WhatsApp &amp; Automações da Loja</h3>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-extrabold"
                                  :class="whatsappConectado ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700'"
                                  x-text="whatsappConectado ? ('🟢 Conectado: +' + (whatsappStatus.phone || 'Ativo')) : '⚪ Desconectado'"></span>
                        </div>
                        <p class="text-xs text-slate-400">
                            Dispare avisos aos clientes a cada fase do pedido, acione motoboys com rota no Google Maps e tenha um bot empático para acalmar atrasos.
                        </p>
                    </div>
                    <button @click="mudarModulo('whatsapp')" class="bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold px-4 py-2 rounded-xl text-xs shadow-lg transition shrink-0">
                        <span x-text="whatsappConectado ? '⚙️ Gerenciar Automações' : '📱 Conectar WhatsApp (QR Code)'"></span>
                    </button>
                </div>
            </div>

            <!-- Card de Cadastro e Autorização da Conta Mercado Pago também dentro de Loja/Config (acessível no Celular e PC) -->
            <div class="bg-slate-900 border-2 rounded-3xl p-6 space-y-5"
                 :class="mpStatus?.conectado ? 'border-emerald-500/40' : 'border-amber-500/40'">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-base font-black text-white">⚡ Conta Mercado Pago para Receber PIX (0% Comissão)</h3>
                            <span class="text-xs font-black px-3 py-0.5 rounded-full border"
                                  :class="mpStatus?.conectado ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border-amber-500/30'"
                                  x-text="mpStatus?.conectado ? '✓ Conectada (' + (mpStatus.mp_conta_nome || mpStatus.mp_user_id || 'Ativa') + ')' : '⚠️ Pendente de Autorização'">
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Cadastre seu Access Token do Mercado Pago ou autorize via OAuth para receber os pagamentos PIX direto na sua conta bancária.</p>
                    </div>
                    <div class="flex flex-wrap gap-2 shrink-0">
                        <template x-if="mpStatus?.conectado">
                            <button @click="testarPixMercadoPago()" :disabled="testandoPix" class="bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs px-3.5 py-2.5 rounded-xl">
                                <span x-text="testandoPix ? 'Gerando...' : '🧪 Testar PIX Real'"></span>
                            </button>
                        </template>
                        <button @click="conectarMercadoPago()" class="bg-sky-600 hover:bg-sky-500 text-white font-extrabold text-xs px-3.5 py-2.5 rounded-xl">
                            🔗 Conectar com Mercado Pago
                        </button>
                        <template x-if="mpStatus?.conectado">
                            <button @click="desconectarMercadoPago()" class="bg-slate-800 hover:bg-rose-600/20 text-slate-300 hover:text-rose-300 border border-slate-700 font-bold text-xs px-3 py-2.5 rounded-xl">
                                Desconectar
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Resultado do Teste de Emissão PIX Ao Vivo (Mobile/Config) -->
                <template x-if="pixTesteResultado">
                    <div class="bg-emerald-950/40 border border-emerald-500/40 rounded-2xl p-4 flex flex-col sm:flex-row items-center gap-4">
                        <template x-if="pixTesteResultado.pix_qr_base64">
                            <img :src="'data:image/png;base64,' + pixTesteResultado.pix_qr_base64" class="w-28 h-28 rounded-xl bg-white p-1.5 shrink-0">
                        </template>
                        <div class="space-y-2 text-xs flex-1 min-w-0 w-full">
                            <div class="font-black text-emerald-400 text-sm" x-text="'✓ ' + pixTesteResultado.message"></div>
                            <div class="text-slate-300">ID Mercado Pago: <strong class="text-white" x-text="pixTesteResultado.mp_payment_id"></strong></div>
                            <input type="text" readonly :value="pixTesteResultado.pix_copia_cola" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-[11px] text-emerald-300 font-mono">
                        </div>
                        <button @click="pixTesteResultado = null" class="text-slate-400 font-bold text-sm self-start">✕</button>
                    </div>
                </template>

                <form @submit.prevent="salvarTokenMercadoPago()" class="grid grid-cols-1 md:grid-cols-12 gap-3 text-xs items-end">
                    <div class="md:col-span-5">
                        <label class="font-bold text-slate-300 block mb-1">Access Token Mercado Pago (<code class="text-emerald-400">APP_USR-...</code>) *</label>
                        <input type="password" x-model="formMp.access_token" required placeholder="APP_USR-0000000000000000-..."
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-mono">
                    </div>
                    <div class="md:col-span-4">
                        <label class="font-bold text-slate-400 block mb-1">Sua Chave PIX Principal (Opcional)</label>
                        <input type="text" x-model="formMp.chave_pix" placeholder="CNPJ, CPF, E-mail ou Celular"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                    </div>
                    <div class="md:col-span-3">
                        <button type="submit" :disabled="salvandoMp" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black py-2.5 rounded-xl text-xs shadow-lg transition">
                            <span x-text="salvandoMp ? 'Validando...' : '✓ Autorizar Conta MP'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
