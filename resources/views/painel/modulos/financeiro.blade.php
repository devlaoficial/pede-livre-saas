        <div x-cloak x-show="moduloAtivo === 'financeiro'" class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
                    <span class="text-xs font-bold uppercase text-slate-400">Faturamento Concluído</span>
                    <div class="text-2xl font-black text-emerald-400" x-text="formatarCentavos(relatorio.faturamento_total_centavos || volumeDiaCentavos)"></div>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
                    <span class="text-xs font-bold uppercase text-slate-400">Pedidos no Período</span>
                    <div class="text-2xl font-black text-white" x-text="relatorio.total_pedidos_concluidos || pedidos.length"></div>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
                    <span class="text-xs font-bold uppercase text-slate-400">Ticket Médio</span>
                    <div class="text-2xl font-black text-rose-400" x-text="formatarCentavos(relatorio.ticket_medio_centavos || (pedidos.length ? Math.round(volumeDiaCentavos / pedidos.length) : 0))"></div>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
                    <span class="text-xs font-bold uppercase text-slate-400">Clientes Atendidos</span>
                    <div class="text-2xl font-black text-amber-400" x-text="((relatorio.clientes?.novos || 0) + (relatorio.clientes?.recorrentes || 0)) || pedidos.length"></div>
                </div>
            </div>

            <!-- Controle de Frente de Caixa -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-base text-white">💵 Controle de Frente de Caixa (Abertura, Sangria e Suprimento)</h3>
                        <p class="text-xs text-slate-400">Registre abertura de turno e movimentações em espécie da gaveta.</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="abrirCaixa()" class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-extrabold px-4 py-2.5 rounded-xl">
                            Abrir Turno de Caixa
                        </button>
                    </div>
                </div>
            </div>

            <!-- Central Completa de Cadastro e Autorização PIX Mercado Pago (0% Custódia) -->
            <div class="bg-slate-900 border-2 rounded-3xl p-6 space-y-5"
                 :class="mpStatus?.conectado ? 'border-emerald-500/40' : 'border-amber-500/40'">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-base font-black text-white">⚡ Cadastro e Autorização da Conta Mercado Pago (PIX 0% Taxa)</span>
                            <span class="text-xs font-black px-3 py-0.5 rounded-full border"
                                  :class="mpStatus?.conectado ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border-amber-500/30'"
                                  x-text="mpStatus?.conectado ? '✓ Conta Conectada (' + (mpStatus.mp_conta_nome || mpStatus.mp_user_id || 'Ativa') + ')' : '⚠️ Conta Ainda Não Vinculada'">
                            </span>
                        </div>
                        <p class="text-xs text-slate-400">
                            Os pagamentos PIX dos seus clientes geram QR Code dinâmico do Banco Central e o dinheiro cai <strong>100% direto na sua conta Mercado Pago</strong> em segundos.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2 shrink-0">
                        <template x-if="mpStatus?.conectado">
                            <button @click="testarPixMercadoPago()" :disabled="testandoPix" class="bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs px-4 py-2.5 rounded-xl transition shadow-md">
                                <span x-text="testandoPix ? 'Gerando PIX...' : '🧪 Testar PIX Real (R$ 1,00)'"></span>
                            </button>
                        </template>
                        <button @click="conectarMercadoPago()" class="bg-sky-600 hover:bg-sky-500 text-white font-extrabold text-xs px-4 py-2.5 rounded-xl transition shadow-md">
                            🔗 Conectar com Mercado Pago (Login 1 Clique)
                        </button>
                        <template x-if="mpStatus?.conectado">
                            <button @click="desconectarMercadoPago()" class="bg-slate-800 hover:bg-rose-600/20 text-slate-300 hover:text-rose-300 border border-slate-700 font-bold text-xs px-3.5 py-2.5 rounded-xl transition">
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
                        <div class="space-y-2 text-xs flex-1 min-w-0">
                            <div class="font-black text-emerald-400 text-sm" x-text="'✓ ' + pixTesteResultado.message"></div>
                            <div class="text-slate-300">ID da Transação Mercado Pago: <strong class="text-white" x-text="pixTesteResultado.mp_payment_id"></strong></div>
                            <input type="text" readonly :value="pixTesteResultado.pix_copia_cola" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-[11px] text-emerald-300 font-mono">
                        </div>
                        <button @click="pixTesteResultado = null" class="text-slate-400 font-bold text-sm self-start">✕</button>
                    </div>
                </template>

                <!-- Formulário Direto de Credenciais do Lojista (Access Token APP_USR-...) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    <form @submit.prevent="salvarTokenMercadoPago()" class="lg:col-span-7 space-y-3 text-xs">
                        <div class="font-extrabold text-sm text-white">🔐 Cadastrar / Autorizar via Credencial Oficial (Access Token)</div>
                        <div>
                            <label class="font-bold text-slate-300 block mb-1">Access Token de Produção do Mercado Pago (<code class="text-emerald-400">APP_USR-...</code>) *</label>
                            <input type="password" x-model="formMp.access_token" required placeholder="Cole aqui seu token APP_USR-..."
                                   class="w-full bg-slate-950 border border-slate-800 focus:border-sky-500 rounded-xl px-3.5 py-2.5 text-white font-mono">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="font-bold text-slate-400 block mb-1">Public Key (Opcional)</label>
                                <input type="text" x-model="formMp.public_key" placeholder="APP_USR-..."
                                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-mono">
                            </div>
                            <div>
                                <label class="font-bold text-slate-400 block mb-1">Sua Chave PIX Principal (Opcional)</label>
                                <input type="text" x-model="formMp.chave_pix" placeholder="CNPJ, CPF, E-mail ou Celular"
                                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                            </div>
                        </div>
                        <button type="submit" :disabled="salvandoMp" class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-black py-3 rounded-xl text-sm shadow-lg transition">
                            <span x-text="salvandoMp ? 'Validando conta no Mercado Pago...' : '✓ Validar e Autorizar Minha Conta Mercado Pago'"></span>
                        </button>
                    </form>

                    <!-- Passo a Passo Rápido para o Lojista -->
                    <div class="lg:col-span-5 bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-2.5 text-xs">
                        <div class="font-black text-sky-400 uppercase tracking-wider text-[11px]">📖 Como pegar seu Access Token em 1 minuto:</div>
                        <ol class="list-decimal list-inside space-y-1.5 text-slate-300 leading-relaxed">
                            <li>Acesse o <a href="https://www.mercadopago.com.br/developers/panel/app" target="_blank" class="text-sky-400 underline font-bold">Painel Mercado Pago Developers ↗</a> com a conta da sua loja.</li>
                            <li>Clique em <strong>Criar aplicação</strong> (ou abra a sua) e vá em <strong>Credenciais de Produção</strong> no menu lateral.</li>
                            <li>Copie o <strong>Access Token</strong> (começa com <code class="text-emerald-400">APP_USR-</code>) e cole no campo ao lado.</li>
                        </ol>
                        <div class="pt-2 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                            <span>🔒 Criptografado em AES-256-GCM</span>
                            <span class="text-emerald-400 font-bold">0% Taxa do SaaS</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
