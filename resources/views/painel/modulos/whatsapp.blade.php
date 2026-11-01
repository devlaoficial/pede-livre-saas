        <div x-cloak x-show="moduloAtivo === 'whatsapp'" class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-6">
            
            <!-- Sub-Navegação do Módulo WhatsApp -->
            <div class="flex items-center gap-3 border-b border-slate-800 pb-3">
                <button @click="mudarSubAbaWhatsapp('chat')"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition relative"
                    :class="subAbaWhatsapp === 'chat' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800'">
                    <span>💬</span>
                    <span>Central de Atendimento ao Vivo</span>
                    <span x-show="totalAguardandoHumano > 0"
                        class="bg-amber-400 text-slate-950 text-[10px] font-black px-1.5 py-0.2 rounded-full animate-pulse"
                        x-text="totalAguardandoHumano + ' humano'"></span>
                </button>

                <button @click="mudarSubAbaWhatsapp('config')"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition"
                    :class="subAbaWhatsapp === 'config' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800'">
                    <span>⚙️</span>
                    <span>Robôs &amp; Automações</span>
                    <span class="w-2 h-2 rounded-full" :class="whatsappConectado ? 'bg-emerald-400' : 'bg-slate-600'"></span>
                </button>
            </div>

            <!-- ========================================================= -->
            <!-- SUB-ABA 1: CENTRAL DE ATENDIMENTO AO VIVO (WEBCHAT)       -->
            <!-- ========================================================= -->
            <div x-show="subAbaWhatsapp === 'chat'" class="grid grid-cols-1 lg:grid-cols-12 gap-5 h-[calc(100vh-210px)] min-h-[580px]">
                
                <!-- Coluna Esquerda: Lista de Conversas -->
                <div class="lg:col-span-4 bg-slate-900 border border-slate-800 rounded-3xl p-4 flex flex-col h-full shadow-xl">
                    <div class="space-y-3 pb-3 border-b border-slate-800">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-black text-white flex items-center gap-2">
                                <span>💬</span> Conversas WhatsApp
                            </h3>
                            <button @click="carregarConversasWhatsapp()" class="text-[11px] text-slate-400 hover:text-white flex items-center gap-1 transition">
                                🔄 Atualizar
                            </button>
                        </div>
                        
                        <!-- Barra de Busca -->
                        <div class="relative">
                            <input type="text" x-model="buscaConversas" @input.debounce.300ms="carregarConversasWhatsapp()"
                                placeholder="Buscar cliente, número ou mensagem..."
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 pl-9 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500">
                            <span class="absolute left-3 top-2.5 text-xs text-slate-500">🔍</span>
                        </div>

                        <!-- Filtros Rápidos -->
                        <div class="flex items-center gap-1.5 pt-1">
                            <button @click="filtroEstadoConversa = 'todos'; carregarConversasWhatsapp()"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition"
                                :class="filtroEstadoConversa === 'todos' ? 'bg-slate-700 text-white' : 'bg-slate-950 text-slate-400 hover:text-slate-200 border border-slate-800'">
                                Todos
                            </button>
                            <button @click="filtroEstadoConversa = 'humano'; carregarConversasWhatsapp()"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition flex items-center gap-1"
                                :class="filtroEstadoConversa === 'humano' ? 'bg-rose-600 text-white' : 'bg-slate-950 text-rose-400 hover:text-rose-300 border border-slate-800'">
                                <span>🚨 Humano</span>
                                <span x-show="totalAguardandoHumano > 0" class="bg-white/20 text-white px-1.5 py-0.2 rounded-full text-[9px] font-black" x-text="totalAguardandoHumano"></span>
                            </button>
                            <button @click="filtroEstadoConversa = 'ia_ativa'; carregarConversasWhatsapp()"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition"
                                :class="filtroEstadoConversa === 'ia_ativa' ? 'bg-emerald-600 text-white' : 'bg-slate-950 text-emerald-400 hover:text-emerald-300 border border-slate-800'">
                                🤖 Com o Robô
                            </button>
                        </div>
                    </div>

                    <!-- Lista com Scroll -->
                    <div class="flex-1 overflow-y-auto mt-3 space-y-2 pr-1 custom-scrollbar">
                        <template x-if="chatCarregando && conversasWhatsapp.length === 0">
                            <div class="text-center py-12 text-slate-500 text-xs">
                                Carregando conversas...
                            </div>
                        </template>

                        <template x-if="!chatCarregando && conversasWhatsapp.length === 0">
                            <div class="text-center py-12 text-slate-500 text-xs space-y-2">
                                <span class="text-3xl block">📭</span>
                                <p>Nenhuma conversa encontrada.</p>
                                <p class="text-[11px] text-slate-600">As conversas aparecerão aqui assim que os clientes enviarem mensagens no WhatsApp.</p>
                            </div>
                        </template>

                        <template x-for="c in conversasWhatsapp" :key="c.id">
                            <div @click="selecionarConversa(c)"
                                class="p-3 rounded-2xl border transition cursor-pointer flex items-start gap-3"
                                :class="conversaSelecionada?.id === c.id 
                                    ? 'bg-rose-600/15 border-rose-500/50 shadow-md' 
                                    : (c.estado === 'humano' ? 'bg-rose-950/20 border-rose-800/40 hover:bg-slate-800/80' : 'bg-slate-950/60 border-slate-800/80 hover:bg-slate-800/50')">
                                
                                <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-sm font-black shrink-0"
                                    :class="c.estado === 'humano' ? 'bg-rose-600/20 text-rose-300 border border-rose-500/30' : 'bg-emerald-600/20 text-emerald-300 border border-emerald-500/30'">
                                    <span x-text="c.cliente_nome ? c.cliente_nome.charAt(0).toUpperCase() : '📱'"></span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1">
                                        <h4 class="text-xs font-black text-white truncate" x-text="c.cliente_nome"></h4>
                                        <span class="text-[10px] text-slate-500 shrink-0 font-medium" x-text="c.ultimo_horario"></span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 truncate mt-0.5" x-text="c.ultima_mensagem || 'Sem mensagens'"></p>
                                    
                                    <div class="flex items-center gap-1.5 mt-1.5">
                                        <template x-if="c.estado === 'humano'">
                                            <span class="bg-rose-500/20 text-rose-300 border border-rose-500/30 text-[9px] px-2 py-0.5 rounded-md font-black flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-ping"></span>
                                                🚨 Aguardando Atendente
                                            </span>
                                        </template>
                                        <template x-if="c.estado === 'ia_ativa'">
                                            <span class="bg-slate-800 text-slate-400 border border-slate-700/60 text-[9px] px-1.5 py-0.5 rounded-md font-bold">
                                                🤖 Robô Ativo
                                            </span>
                                        </template>
                                        <span class="text-[10px] text-slate-500" x-text="c.telefone"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Coluna Direita: Janela de Chat Ativo -->
                <div class="lg:col-span-8 bg-slate-900 border border-slate-800 rounded-3xl flex flex-col h-full overflow-hidden shadow-xl">
                    
                    <template x-if="!conversaSelecionada">
                        <div class="flex-1 flex flex-col items-center justify-center p-8 text-center space-y-4 bg-slate-950/40">
                            <div class="w-16 h-16 rounded-3xl bg-slate-800/80 border border-slate-700 flex items-center justify-center text-3xl shadow-inner">
                                💬
                            </div>
                            <div class="max-w-md space-y-1">
                                <h3 class="text-base font-black text-white">Central de Atendimento ao Vivo</h3>
                                <p class="text-xs text-slate-400 leading-relaxed">
                                    Selecione uma conversa ao lado para acompanhar as mensagens em tempo real ou atender o cliente diretamente pelo painel sem precisar abrir o WhatsApp Web.
                                </p>
                            </div>
                            <div class="text-[11px] text-slate-500 bg-slate-900 px-4 py-2 rounded-xl border border-slate-800">
                                ⚡ Suporte multiatendente integrado em tempo real com Evolution API
                            </div>
                        </div>
                    </template>

                    <template x-if="conversaSelecionada">
                        <div class="flex flex-col h-full">
                            
                            <!-- Header da Conversa Ativa -->
                            <div class="p-4 bg-slate-950/80 border-b border-slate-800 flex items-center justify-between gap-3 shrink-0">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-2xl bg-rose-600/20 text-rose-300 border border-rose-500/30 flex items-center justify-center font-black shrink-0">
                                        <span x-text="conversaSelecionada.cliente_nome ? conversaSelecionada.cliente_nome.charAt(0).toUpperCase() : '📱'"></span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-sm font-black text-white truncate" x-text="conversaSelecionada.cliente_nome"></h3>
                                            <span class="text-xs text-slate-400" x-text="conversaSelecionada.telefone"></span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <template x-if="conversaSelecionada.estado === 'humano'">
                                                <span class="bg-rose-500/20 text-rose-300 border border-rose-500/30 text-[10px] px-2 py-0.2 rounded-md font-bold flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-pulse"></span>
                                                    Atendimento Humano em Andamento
                                                </span>
                                            </template>
                                            <template x-if="conversaSelecionada.estado === 'ia_ativa'">
                                                <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] px-2 py-0.2 rounded-md font-bold flex items-center gap-1">
                                                    <span>🤖</span> Robô Respondendo Automaticamente
                                                </span>
                                            </template>
                                            <template x-if="conversaSelecionada.pedido_recente">
                                                <span class="text-[10px] text-slate-400 bg-slate-900 px-2 py-0.2 rounded-md border border-slate-800"
                                                    x-text="'Pedido #' + conversaSelecionada.pedido_recente.codigo + ' (' + conversaSelecionada.pedido_recente.status + ')'"></span>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <template x-if="conversaSelecionada.estado === 'humano'">
                                        <button @click="devolverConversaBot(conversaSelecionada.id)" :disabled="chatEnviando"
                                            class="bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold px-3 py-1.5 rounded-xl text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                                            <span>🤖</span> Devolver p/ o Robô
                                        </button>
                                    </template>
                                    <template x-if="conversaSelecionada.estado === 'ia_ativa'">
                                        <button @click="assumirConversaHumano(conversaSelecionada.id)" :disabled="chatEnviando"
                                            class="bg-rose-600 hover:bg-rose-500 text-white font-extrabold px-3 py-1.5 rounded-xl text-xs shadow-md shadow-rose-600/20 transition flex items-center gap-1.5">
                                            <span>👨‍💼</span> Assumir Atendimento
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Feed de Mensagens -->
                            <div x-ref="chatMessagesBox" class="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-950/40 custom-scrollbar">
                                <template x-if="chatCarregando && mensagensConversa.length === 0">
                                    <div class="text-center py-12 text-slate-500 text-xs">
                                        Carregando histórico de mensagens...
                                    </div>
                                </template>

                                <template x-for="m in mensagensConversa" :key="m.id">
                                    <div class="flex" :class="m.direcao === 'saida' ? 'justify-end' : 'justify-start'">
                                        <div class="max-w-[78%] rounded-2xl px-4 py-2.5 text-xs shadow-md"
                                            :class="m.direcao === 'saida' 
                                                ? 'bg-rose-600 text-white rounded-tr-none border border-rose-500/50' 
                                                : 'bg-slate-800 text-slate-100 rounded-tl-none border border-slate-700/60'">
                                            <p class="whitespace-pre-wrap leading-relaxed" x-text="m.mensagem"></p>
                                            <div class="flex items-center justify-end gap-1 mt-1 text-[10px]"
                                                :class="m.direcao === 'saida' ? 'text-rose-200' : 'text-slate-400'">
                                                <span x-text="m.horario"></span>
                                                <template x-if="m.direcao === 'saida'">
                                                    <span>✓✓</span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Atalhos de Resposta Rápida (1-Clique) -->
                            <div class="px-4 py-2 bg-slate-950/80 border-t border-slate-800/80 flex items-center gap-2 overflow-x-auto whitespace-nowrap text-[11px] custom-scrollbar">
                                <span class="text-slate-500 text-[10px] uppercase font-bold shrink-0">⚡ Respostas Rápidas:</span>
                                <button @click="usarRespostaRapida('Olá! Seu pedido já está prontinho e saindo para entrega com o nosso motoboy! 🛵💨')"
                                    class="bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 px-2.5 py-1 rounded-lg transition shrink-0">
                                    🛵 Saiu p/ Entrega
                                </button>
                                <button @click="usarRespostaRapida('Olá! Nosso chef está finalizando seu pedido na cozinha agora mesmo com muito capricho! 👨‍🍳🔥')"
                                    class="bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 px-2.5 py-1 rounded-lg transition shrink-0">
                                    👨‍🍳 Em Finalização
                                </button>
                                <button @click="usarRespostaRapida('Poderia nos confirmar por gentileza o seu endereço completo com ponto de referência? 📍')"
                                    class="bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 px-2.5 py-1 rounded-lg transition shrink-0">
                                    📍 Confirmar Endereço
                                </button>
                                <button @click="usarRespostaRapida('Nosso entregador levará a maquininha de cartão! Você prefere débito ou crédito? 💳')"
                                    class="bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 px-2.5 py-1 rounded-lg transition shrink-0">
                                    💳 Maquininha Cartão
                                </button>
                                <button @click="usarRespostaRapida('Pedimos imensas desculpas pelo transtorno! Já estamos resolvendo com prioridade máxima para você! 🙏')"
                                    class="bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 px-2.5 py-1 rounded-lg transition shrink-0">
                                    🙏 Pedir Desculpas
                                </button>
                            </div>

                            <!-- Barra de Envio de Mensagem -->
                            <div class="p-3 bg-slate-950 border-t border-slate-800 flex items-center gap-2">
                                <input type="text" x-model="mensagemChatInput" @keydown.enter.prevent="enviarMensagemChat()"
                                    placeholder="Digite sua resposta e pressione Enter para enviar pelo WhatsApp..."
                                    class="flex-1 bg-slate-900 border border-slate-800 rounded-2xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500">
                                
                                <button @click="enviarMensagemChat()" :disabled="chatEnviando || !mensagemChatInput.trim()"
                                    class="bg-rose-600 hover:bg-rose-500 disabled:opacity-40 text-white font-extrabold px-5 py-2.5 rounded-2xl text-xs shadow-lg shadow-rose-600/20 transition flex items-center gap-1.5 shrink-0">
                                    <span x-show="!chatEnviando">Enviar ➔</span>
                                    <span x-show="chatEnviando">Enviando...</span>
                                </button>
                            </div>

                        </div>
                    </template>

                </div>

            </div>

            <!-- ========================================================= -->
            <!-- SUB-ABA 2: AUTOMAÇÕES, ROBÔS & QR CODE                     -->
            <!-- ========================================================= -->
            <div x-show="subAbaWhatsapp === 'config'" class="space-y-6">
                
                <!-- Banner de Conexão do WhatsApp -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 relative overflow-hidden">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-2xl">💬</span>
                                <h2 class="text-lg font-black text-white">Integração WhatsApp Evolution</h2>
                                <template x-if="whatsappConectado">
                                    <span class="bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 text-xs px-2.5 py-0.5 rounded-full font-extrabold flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                        Conectado
                                    </span>
                                </template>
                                <template x-if="!whatsappConectado">
                                    <span class="bg-amber-500/15 text-amber-300 border border-amber-500/30 text-xs px-2.5 py-0.5 rounded-full font-extrabold">
                                        Desconectado
                                    </span>
                                </template>
                            </div>
                            <p class="text-xs text-slate-400">
                                Conecte o WhatsApp do seu restaurante para disparar atualizações aos clientes, enviar dados aos motoboys e ativar o assistente virtual anti-ansiedade.
                            </p>
                        </div>

                        <div class="flex items-center gap-2 w-full md:w-auto">
                            <template x-if="!whatsappConectado">
                                <button @click="conectarWhatsapp()" :disabled="whatsappCarregando"
                                    class="w-full md:w-auto bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold px-5 py-2.5 rounded-xl text-xs shadow-lg shadow-emerald-600/20 transition flex items-center justify-center gap-2">
                                    <span x-show="!whatsappCarregando">📱 Conectar meu WhatsApp (QR Code)</span>
                                    <span x-show="whatsappCarregando">Gerando QR Code...</span>
                                </button>
                            </template>
                            <template x-if="whatsappConectado">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-slate-300 font-bold bg-slate-950 px-3 py-1.5 rounded-xl border border-slate-800" x-text="whatsappStatus.phone ? '+' + whatsappStatus.phone : 'Número Ativo'"></span>
                                    <button @click="desconectarWhatsapp()" :disabled="whatsappCarregando"
                                        class="bg-rose-500/15 hover:bg-rose-500/25 text-rose-300 border border-rose-500/30 font-bold px-4 py-2 rounded-xl text-xs transition">
                                        Desconectar
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Box QR Code quando desconectado e gerado -->
                    <template x-if="!whatsappConectado && whatsappStatus.qrBase64">
                        <div class="mt-6 pt-6 border-t border-slate-800/80 flex flex-col md:flex-row items-center justify-center gap-8 bg-slate-950/60 p-6 rounded-2xl">
                            <div class="bg-white p-3 rounded-2xl shadow-2xl border-4 border-emerald-500/40">
                                <img :src="whatsappStatus.qrBase64" alt="QR Code WhatsApp" class="w-56 h-56 rounded-lg object-contain">
                            </div>
                            <div class="space-y-3 max-w-md text-left">
                                <h3 class="text-white font-bold text-sm flex items-center gap-2">
                                    <span>📸</span> Como conectar seu celular:
                                </h3>
                                <ol class="text-xs text-slate-300 space-y-2 list-decimal list-inside leading-relaxed">
                                    <li>Abra o aplicativo do <strong>WhatsApp</strong> no seu celular</li>
                                    <li>Acesse <strong>Aparelhos Conectados</strong> (nos 3 pontinhos ou Ajustes)</li>
                                    <li>Toque em <strong>Conectar um aparelho</strong></li>
                                    <li>Aponte a câmera para o QR Code ao lado</li>
                                </ol>
                                <div class="pt-2 flex items-center gap-3">
                                    <button @click="conectarWhatsapp()" class="text-xs text-emerald-400 hover:underline font-bold flex items-center gap-1">
                                        🔄 Atualizar QR Code
                                    </button>
                                    <span class="text-[11px] text-slate-400">• O QR Code expira a cada 40 segundos</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Grade de Automações & Bot Inteligente -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Card 1: Notificações de Pedido para Clientes -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-2xl">📦</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="whatsappStatus.config.notificar_pedidos" @change="salvarConfigWhatsapp()" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                            </div>
                            <h3 class="font-extrabold text-sm text-white">Status em Tempo Real (Cliente)</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Dispara mensagens automáticas com link de rastreamento assinado a cada etapa:
                            </p>
                            <div class="space-y-1.5 text-[11px]">
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    <strong class="text-rose-400">1. Confirmado:</strong> Aviso de entrada em preparo na cozinha.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    <strong class="text-amber-400">2. Pronto:</strong> Separado na bolsa térmica ou no balcão.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    <strong class="text-sky-400">3. A Caminho:</strong> Nome do entregador + alerta campainha.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    <strong class="text-emerald-400">4. Entregue:</strong> Agradecimento carinhoso e feedback.
                                </div>
                            </div>
                        </div>
                        <div class="text-[11px] text-emerald-400 font-bold pt-2 border-t border-slate-800 flex items-center gap-1.5">
                            <span>✓</span> Zero custo de SMS ou taxas extras
                        </div>
                    </div>

                    <!-- Card 2: Despacho para Motoboys -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-2xl">🛵</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="whatsappStatus.config.notificar_motoboy" @change="salvarConfigWhatsapp()" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                            </div>
                            <h3 class="font-extrabold text-sm text-white">Despacho &amp; GPS para Motoboys</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Assim que o pedido fica <strong>Pronto</strong>, o sistema aciona o motoboy com todos os detalhes da entrega:
                            </p>
                            <div class="space-y-1.5 text-[11px]">
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    📍 <strong>Endereço Completo:</strong> Rua, número, complemento e bairro.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    🗺️ <strong>Google Maps 1-Toque:</strong> Abre a rota pronta no GPS do motoboy.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    💵 <strong>Financeiro:</strong> Taxa de entrega em R$ e forma de pagamento.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    ⚡ <strong>Comando Rápido:</strong> Motoboy responde <code class="text-emerald-400">ENTREGUE 0000</code> e conclui o pedido.
                                </div>
                            </div>
                        </div>
                        <div class="text-[11px] text-sky-400 font-bold pt-2 border-t border-slate-800 flex items-center gap-1.5">
                            <span>✓</span> Compatível com entregadores próprios e freelancers
                        </div>
                    </div>

                    <!-- Card 3: Bot Anti-Ansiedade e Atendimento Humanizado -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-2xl">🤖</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="whatsappStatus.config.bot_ativo" @change="salvarConfigWhatsapp()" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                            </div>
                            <h3 class="font-extrabold text-sm text-white">Assistente Anti-Ansiedade</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Responde os clientes de forma ultra educada, amigável e desescaladora:
                            </p>
                            <div class="space-y-1.5 text-[11px]">
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    🍔 <strong>Cardápio Rápido:</strong> Envia o link oficial ao pedir preço ou produtos.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    🕒 <strong>Previsão Precisa:</strong> Consulta status em tempo real do pedido ativo.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    ❤️ <strong>Acolhimento em Atrasos:</strong> Pede desculpas, explica o pico da cozinha e garante que é o <strong>próximo a sair</strong>!
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    👨‍💼 <strong>Transbordo Humano:</strong> Pausa o bot quando o cliente digita <code class="text-rose-400">HUMANO</code>.
                                </div>
                            </div>
                        </div>
                        <div class="text-[11px] text-amber-300 font-bold pt-2 border-t border-slate-800 flex items-center gap-1.5">
                            <span>✓</span> Total conformidade LGPD (Opt-out imediato)
                        </div>
                    </div>

                    <!-- Card 4: Recuperador Automático de PIX Abandonado -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-2xl">💰</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="whatsappStatus.config.recuperar_carrinho" @change="salvarConfigWhatsapp()" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                            </div>
                            <h3 class="font-extrabold text-sm text-white">Recuperador de PIX Abandonado</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Resgata clientes que geraram o PIX e esqueceram de pagar antes que desistam:
                            </p>
                            <div class="space-y-1.5 text-[11px]">
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    ⏱️ <strong>Disparo aos 6 a 15 min:</strong> Lembra com carinho que os itens estão reservados na chapa.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    📋 <strong>Código PIX Pronto:</strong> Envia a chave Copia e Cola para pagamento em 1 toque no banco.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    💳 <strong>Flexibilidade:</strong> Permite ao cliente responder "CARTÃO" para pagar na maquininha!
                                </div>
                            </div>
                        </div>
                        <div class="text-[11px] text-emerald-400 font-bold pt-2 border-t border-slate-800 flex items-center gap-1.5">
                            <span>🔥</span> Aumento médio de +18% a +25% no faturamento
                        </div>
                    </div>

                    <!-- Card 5: Re-compra em 1-Clique ("Pedir o de Sempre") -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-2xl">⚡</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="whatsappStatus.config.recompra_um_clique" @change="salvarConfigWhatsapp()" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                            </div>
                            <h3 class="font-extrabold text-sm text-white">Re-compra 1-Clique ("O de Sempre")</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Fideliza clientes recorrentes permitindo pedir novamente sem preencher nada:
                            </p>
                            <div class="space-y-1.5 text-[11px]">
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    🍔 <strong>Memória do Pedido Favorito:</strong> Lembra os itens e endereço do último pedido do cliente.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    1️⃣ <strong>Basta Digitar "1":</strong> Clona o pedido, gera o PIX e avisa a cozinha na hora.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    🚀 <strong>Zero Fricção:</strong> O cliente não precisa nem abrir o navegador.
                                </div>
                            </div>
                        </div>
                        <div class="text-[11px] text-amber-300 font-bold pt-2 border-t border-slate-800 flex items-center gap-1.5">
                            <span>⭐</span> Aumenta a frequência semanal de compras em +30%
                        </div>
                    </div>

                    <!-- Card 6: Atendente Inteligente (IA Conversacional & Transcrição de Áudio) -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl">🤖</span>
                                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-purple-500/20 text-purple-300 border border-purple-500/30">Groq LPU Turbo</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="whatsappStatus.config.ia_atendente_ativa" @change="salvarConfigWhatsapp()" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-500"></div>
                                </label>
                            </div>
                            <h3 class="font-extrabold text-sm text-white">IA do Cardápio &amp; Transcrição de Voz</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Responde dúvidas sobre ingredientes, sabores, combos e transcreve áudios enviados pelos clientes:
                            </p>
                            <div class="space-y-1.5 text-[11px]">
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    🎙️ <strong>Entende Áudio de Voz:</strong> O cliente manda áudio ("tem lanche sem queijo?"), a IA ouve e responde na hora.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    🍔 <strong>Cardápio em Tempo Real:</strong> Lê seus produtos ativos e nunca inventa pratos ou preços inexistentes.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    ⚡ <strong>Ultra Rápido:</strong> Resposta humana, acolhedora e com link em menos de 1 segundo.
                                </div>
                            </div>
                        </div>
                        <div class="text-[11px] text-purple-300 font-bold pt-2 border-t border-slate-800 flex items-center gap-1.5">
                            <span>✨</span> Atendimento 24/7 sem fila de espera
                        </div>
                    </div>

                    <!-- Card 7: Alerta Proativo de Demora na Cozinha -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl">⏳</span>
                                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-rose-500/20 text-rose-300 border border-rose-500/30">Anti-Ansiedade</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="whatsappStatus.config.alerta_atraso_ativo" @change="salvarConfigWhatsapp()" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500"></div>
                                </label>
                            </div>
                            <h3 class="font-extrabold text-sm text-white">Alerta Proativo de Demora na Cozinha</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Acalma e encanta o cliente antes mesmo que ele pergunte onde está o pedido:
                            </p>
                            <div class="space-y-1.5 text-[11px]">
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    👨‍🍳 <strong>Mensagem de Satisfação:</strong> Avisa que o chef está caprichando nos detalhes e que o pedido é o próximo a sair.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80 flex items-center justify-between">
                                    <span>⏱️ <strong>Disparar após:</strong></span>
                                    <select x-model.number="whatsappStatus.config.minutos_alerta_atraso" @change="salvarConfigWhatsapp()" class="bg-slate-900 border border-slate-700 text-rose-300 text-xs rounded-lg px-2 py-1 font-bold">
                                        <option value="25">25 minutos</option>
                                        <option value="30">30 minutos</option>
                                        <option value="35">35 minutos</option>
                                        <option value="40">40 minutos</option>
                                        <option value="45">45 minutos</option>
                                        <option value="50">50 minutos</option>
                                    </select>
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    🛡️ <strong>Anti-Spam Garantido:</strong> Envia rigorosamente apenas 1 vez por pedido.
                                </div>
                            </div>
                        </div>
                        <div class="text-[11px] text-rose-300 font-bold pt-2 border-t border-slate-800 flex items-center gap-1.5">
                            <span>❤️</span> Reduz em até 90% as reclamações por atraso
                        </div>
                    </div>

                    <!-- Card 8: Avaliações Google Maps (NPS Viral) & Satisfação -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl">⭐</span>
                                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-300 border border-amber-500/30">NPS Viral</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="whatsappStatus.config.solicitar_avaliacao" @change="salvarConfigWhatsapp()" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                                </label>
                            </div>
                            <h3 class="font-extrabold text-sm text-white">Avaliações 5 Estrelas no Google Maps</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Convida clientes satisfeitos a avaliarem sua loja no Google e retém reclamações no chat privado:
                            </p>
                            <div class="space-y-1.5 text-[11px]">
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    🌟 <strong>Clientes 4 ou 5 Estrelas:</strong> Recebem seu link oficial do Google Maps p/ disparar avaliações públicas e turbinar o ranking da loja!
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    🛡️ <strong>Clientes 1 a 3 Estrelas:</strong> O bot pede desculpas com empatia, registra o feedback privadamente e alerta o gerente no painel antes de ir pro Google!
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80 flex items-center justify-between">
                                    <span>⏱️ <strong>Disparar após:</strong></span>
                                    <select x-model.number="whatsappStatus.config.minutos_solicitar_avaliacao" @change="salvarConfigWhatsapp()" class="bg-slate-900 border border-slate-700 text-amber-300 text-xs rounded-lg px-2 py-1 font-bold">
                                        <option value="15">15 minutos</option>
                                        <option value="20">20 minutos</option>
                                        <option value="25">25 minutos</option>
                                        <option value="30">30 minutos</option>
                                        <option value="45">45 minutos</option>
                                        <option value="60">60 minutos</option>
                                    </select>
                                </div>
                                <div class="space-y-1 pt-1">
                                    <label class="text-[10px] text-slate-400 font-bold block">Link de Avaliação do seu Google Maps:</label>
                                    <input type="url" x-model="whatsappStatus.config.google_maps_review_url" @change="salvarConfigWhatsapp()"
                                        placeholder="https://g.page/r/.../review ou link do Maps"
                                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-2.5 py-1.5 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-amber-500">
                                </div>
                            </div>
                        </div>
                        <div class="text-[11px] text-amber-300 font-bold pt-2 border-t border-slate-800 flex items-center gap-1.5">
                            <span>🚀</span> Mais de 10x mais avaliações 5 estrelas no Google
                        </div>
                    </div>

                    <!-- Card 9: Reativação Win-Back Automática & Anti-Ban -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4 flex flex-col justify-between"
                         x-init="carregarPreviewWinback()">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl">🎯</span>
                                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-purple-500/20 text-purple-300 border border-purple-500/30">CRM Win-Back</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="whatsappStatus.config.winback_ativo" @change="salvarConfigWhatsapp(); carregarPreviewWinback();" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-500"></div>
                                </label>
                            </div>
                            <h3 class="font-extrabold text-sm text-white">Reativação Automática de Clientes Sumidos</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Resgata clientes que não compram há tempos enviando um cupom exclusivo de retorno com link de 1-toque:
                            </p>
                            <div class="space-y-2 text-[11px]">
                                <div class="bg-slate-950 p-2.5 rounded-xl text-slate-300 border border-slate-800/80 flex items-center justify-between">
                                    <span>⏱️ <strong>Inatividade:</strong></span>
                                    <select x-model.number="whatsappStatus.config.winback_dias_inatividade" @change="salvarConfigWhatsapp(); carregarPreviewWinback();" class="bg-slate-900 border border-slate-700 text-purple-300 text-xs rounded-lg px-2 py-1 font-bold">
                                        <option value="15">Sem pedir há +15 dias</option>
                                        <option value="30">Sem pedir há +30 dias</option>
                                        <option value="45">Sem pedir há +45 dias</option>
                                        <option value="60">Sem pedir há +60 dias</option>
                                    </select>
                                </div>

                                <div class="bg-slate-950 p-2.5 rounded-xl text-slate-300 border border-slate-800/80 space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <span>🎁 <strong>Cupom / Desconto:</strong></span>
                                        <div class="flex items-center gap-1.5">
                                            <input type="text" x-model="whatsappStatus.config.winback_cupom_codigo" @change="salvarConfigWhatsapp(); carregarPreviewWinback();"
                                                placeholder="VOLTEI10" class="w-20 uppercase bg-slate-900 border border-slate-700 text-purple-300 text-center text-xs rounded-lg px-1.5 py-1 font-black">
                                            <input type="number" x-model.number="whatsappStatus.config.winback_desconto_valor" @change="salvarConfigWhatsapp(); carregarPreviewWinback();"
                                                min="1" max="90" class="w-12 bg-slate-900 border border-slate-700 text-purple-300 text-center text-xs rounded-lg px-1 py-1 font-bold">
                                            <span class="text-xs font-bold text-slate-400">%</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between text-[10px] text-slate-400">
                                        <span>⏰ Expiração: <strong>48 horas</strong> (urgência)</span>
                                        <span>🛡️ Anti-Ban: <strong>5-10s delay</strong></span>
                                    </div>
                                </div>

                                <!-- Estatísticas da Base de Clientes Inativos -->
                                <div class="bg-purple-950/40 border border-purple-800/50 p-2.5 rounded-xl flex items-center justify-between">
                                    <div class="text-[11px] text-purple-200">
                                        <span>👥 Clientes Inativos Elegíveis:</span>
                                        <div class="text-base font-black text-white" x-text="winbackPreview.total_inativos ?? '0'"></div>
                                    </div>
                                    <button type="button" @click="dispararCampanhaWinback()" :disabled="disparandoWinback || !whatsappConectado || (winbackPreview.total_inativos === 0)"
                                        class="bg-purple-600 hover:bg-purple-500 disabled:opacity-40 disabled:cursor-not-allowed text-white text-[11px] font-black px-3 py-1.5 rounded-xl transition shadow flex items-center gap-1.5">
                                        <span x-show="!disparandoWinback">🚀 Disparar Agora</span>
                                        <span x-show="disparandoWinback" class="animate-spin">⏳ Enviando...</span>
                                    </button>
                                </div>

                                <!-- Pré-visualização da Mensagem -->
                                <div x-data="{ verExemplo: false }" class="pt-1">
                                    <button type="button" @click="verExemplo = !verExemplo" class="text-[10px] text-purple-400 hover:underline flex items-center gap-1 font-semibold">
                                        <span x-text="verExemplo ? 'Ocultar exemplo' : '👁️ Ver mensagem que o cliente recebe'"></span>
                                    </button>
                                    <div x-show="verExemplo" x-cloak class="mt-2 p-2.5 bg-slate-950 border border-slate-800 rounded-xl text-[11px] text-slate-300 whitespace-pre-line font-mono leading-tight">
                                        <span x-text="winbackPreview.exemplo_mensagem || 'Carregando exemplo...'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-[11px] text-purple-300 font-bold pt-2 border-t border-slate-800 flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <span>📈</span> Retorno médio de 18% a 25% dos clientes
                            </span>
                            <button type="button" @click="carregarPreviewWinback()" class="text-slate-400 hover:text-white transition" title="Recarregar clientes elegíveis">🔄</button>
                        </div>
                    </div>

                </div>

                <!-- Caixa de Teste de Disparo ao Vivo -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4">
                    <h3 class="font-bold text-sm text-white flex items-center gap-2">
                        <span>🧪</span> Testar Envio de Mensagem pelo WhatsApp
                    </h3>
                    <p class="text-xs text-slate-400">
                        Digite o número do seu celular com DDD (ex: <code class="text-emerald-400">11999999999</code>) para testar o envio em tempo real.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        <div class="md:col-span-4">
                            <input type="text" x-model="whatsappTelefoneTeste" placeholder="DDD + Número (Ex: 31988887777)"
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white">
                        </div>
                        <div class="md:col-span-5">
                            <input type="text" x-model="whatsappMsgTeste" placeholder="Mensagem personalizada (opcional)"
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white">
                        </div>
                        <div class="md:col-span-3">
                            <button @click="enviarTesteWhatsapp()" :disabled="whatsappCarregando || !whatsappConectado"
                                class="w-full bg-slate-800 hover:bg-slate-700 disabled:opacity-50 text-white font-extrabold py-2.5 rounded-xl text-xs transition border border-slate-700">
                                🚀 Disparar Teste
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </div>
