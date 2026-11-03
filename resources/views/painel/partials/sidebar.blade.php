<!-- SIDEBAR FIXA PARA DESKTOP (Layout SaaS Premium — 100% SVG) -->
<aside class="hidden lg:flex lg:flex-col lg:w-[272px] bg-[#080c17]/95 backdrop-blur-xl border-r border-slate-800/80 shrink-0 justify-between select-none z-20">
    <div class="p-4 space-y-5 overflow-y-auto no-scrollbar">
        <!-- Topo: Identidade PedeLivre OS + Status de Conexão -->
        <div class="flex items-center justify-between px-1.5 pt-0.5">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#f43f5e] to-[#be123c] flex items-center justify-center text-white font-bold text-base shadow-lg shadow-rose-600/30 ring-1 ring-white/15">
                        P
                    </div>
                    <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-[#080c17]"
                          :class="onlineStatus ? 'bg-emerald-400' : 'bg-amber-400 animate-ping'"
                          :title="onlineStatus ? 'Conectado em tempo real' : 'Reconectando...'"></span>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-base font-bold tracking-tight text-white">Pede<span class="text-rose-500">Livre</span></span>
                        <span class="text-[9px] font-mono font-semibold uppercase px-1.5 py-0.5 rounded-md bg-rose-500/15 text-rose-400 border border-rose-500/25">OS</span>
                    </div>
                    <p class="text-[10px] text-slate-400 font-medium truncate max-w-[145px]" x-text="loja?.nome || 'Painel Operacional'"></p>
                </div>
            </div>

            <a :href="'/p/' + (loja?.slug || '')" target="_blank"
               title="Abrir cardápio público em nova guia"
               class="p-2 rounded-xl bg-[#111726] hover:bg-slate-800 text-slate-400 hover:text-white border border-slate-800/90 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
            </a>
        </div>

        <!-- Botão Rápido: Lançar Pedido PDV (Balcão / Telefone) -->
        <button @click="abrirModalPdv()"
                class="w-full bg-gradient-to-r from-[#f43f5e] to-[#e11d48] hover:from-[#fb7185] hover:to-[#f43f5e] text-white rounded-2xl p-3 flex items-center justify-between shadow-lg shadow-rose-600/20 transition active:scale-[0.99] group">
            <span class="flex items-center gap-2.5 text-xs font-semibold">
                <span class="w-7 h-7 rounded-xl bg-white/15 flex items-center justify-center group-hover:scale-105 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                </span>
                <span>Lançar Pedido PDV</span>
            </span>
            <kbd class="text-[10px] font-mono font-bold bg-black/25 text-rose-100 px-2 py-0.5 rounded-lg border border-white/10">F2</kbd>
        </button>

        <!-- Seções de Navegação -->
        <div class="space-y-5">
            <!-- 1. OPERAÇÃO EM TEMPO REAL -->
            <div class="space-y-1">
                <div class="flex items-center justify-between px-3 pb-1">
                    <span class="text-[10px] font-semibold uppercase tracking-widest text-slate-500">Operação ao Vivo</span>
                    <span x-show="colunas.novo.length > 0" class="text-[10px] font-bold text-rose-400 animate-pulse" x-text="colunas.novo.length + ' novo(s)'"></span>
                </div>

                <!-- Pedidos (Kanban) -->
                <button @click="mudarModulo('pedidos')"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition relative group"
                    :class="moduloAtivo === 'pedidos'
                        ? 'bg-gradient-to-r from-rose-500/20 to-rose-500/5 text-white border border-rose-500/35 shadow-sm'
                        : 'text-slate-400 hover:bg-[#111726] hover:text-slate-100 border border-transparent'">
                    <span class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center transition"
                              :class="moduloAtivo === 'pedidos' ? 'bg-rose-500 text-white shadow-md shadow-rose-500/30' : 'bg-[#111726] text-slate-400 group-hover:text-white'">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        </span>
                        <span>Gestor de Pedidos</span>
                    </span>
                    <div class="flex items-center gap-1.5">
                        <span x-show="colunas.novo.length > 0"
                              class="bg-rose-500 text-white font-mono font-bold text-[10px] px-2 py-0.5 rounded-full shadow-md shadow-rose-500/40 animate-pulse"
                              x-text="colunas.novo.length"></span>
                        <span x-show="colunas.novo.length === 0 && pedidos.length > 0"
                              class="text-[10px] font-mono text-slate-500"
                              x-text="pedidos.length"></span>
                    </div>
                </button>

                <!-- Cozinha (KDS) -->
                <button @click="mudarModulo('kds')"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition group"
                    :class="moduloAtivo === 'kds'
                        ? 'bg-gradient-to-r from-rose-500/20 to-rose-500/5 text-white border border-rose-500/35 shadow-sm'
                        : 'text-slate-400 hover:bg-[#111726] hover:text-slate-100 border border-transparent'">
                    <span class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center transition"
                              :class="moduloAtivo === 'kds' ? 'bg-rose-500 text-white shadow-md shadow-rose-500/30' : 'bg-[#111726] text-slate-400 group-hover:text-white'">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"></path></svg>
                        </span>
                        <span>Cozinha (KDS)</span>
                    </span>
                    <span x-show="(colunas.novo.length + colunas.em_preparo.length) > 0"
                          class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-300 border border-amber-500/30"
                          x-text="colunas.novo.length + colunas.em_preparo.length"></span>
                </button>

                <!-- Mesas e Comandas -->
                <button @click="mudarModulo('mesas')"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition group"
                    :class="moduloAtivo === 'mesas'
                        ? 'bg-gradient-to-r from-rose-500/20 to-rose-500/5 text-white border border-rose-500/35 shadow-sm'
                        : 'text-slate-400 hover:bg-[#111726] hover:text-slate-100 border border-transparent'">
                    <span class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center transition"
                              :class="moduloAtivo === 'mesas' ? 'bg-rose-500 text-white shadow-md shadow-rose-500/30' : 'bg-[#111726] text-slate-400 group-hover:text-white'">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        </span>
                        <span>Mesas &amp; Salão QR</span>
                    </span>
                    <span x-show="chamadosGarcom.length > 0"
                          class="bg-amber-400 text-slate-950 font-bold text-[10px] px-2 py-0.5 rounded-full animate-bounce inline-flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <span x-text="chamadosGarcom.length"></span>
                    </span>
                </button>

                <!-- Entregas e Motoboys -->
                <button @click="mudarModulo('entregas')"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition group"
                    :class="moduloAtivo === 'entregas'
                        ? 'bg-gradient-to-r from-rose-500/20 to-rose-500/5 text-white border border-rose-500/35 shadow-sm'
                        : 'text-slate-400 hover:bg-[#111726] hover:text-slate-100 border border-transparent'">
                    <span class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center transition"
                              :class="moduloAtivo === 'entregas' ? 'bg-rose-500 text-white shadow-md shadow-rose-500/30' : 'bg-[#111726] text-slate-400 group-hover:text-white'">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                        </span>
                        <span>Entregas &amp; Motoboys</span>
                    </span>
                    <span class="text-[10px] font-mono text-slate-500" x-text="entregadores.length"></span>
                </button>
            </div>

            <!-- 2. GESTÃO & INTELIGÊNCIA -->
            <div class="space-y-1">
                <div class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 px-3 pb-1">
                    Gestão &amp; Finanças
                </div>

                <!-- Cardápio e Cupons -->
                <button @click="mudarModulo('catalogo')"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition group"
                    :class="moduloAtivo === 'catalogo'
                        ? 'bg-gradient-to-r from-rose-500/20 to-rose-500/5 text-white border border-rose-500/35 shadow-sm'
                        : 'text-slate-400 hover:bg-[#111726] hover:text-slate-100 border border-transparent'">
                    <span class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center transition"
                              :class="moduloAtivo === 'catalogo' ? 'bg-rose-500 text-white shadow-md shadow-rose-500/30' : 'bg-[#111726] text-slate-400 group-hover:text-white'">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </span>
                        <span>Cardápio &amp; Cupons</span>
                    </span>
                    <span class="text-[10px] font-mono text-slate-500" x-text="produtos.length"></span>
                </button>

                <!-- Caixa e PIX -->
                <button @click="mudarModulo('financeiro')"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition group"
                    :class="moduloAtivo === 'financeiro'
                        ? 'bg-gradient-to-r from-rose-500/20 to-rose-500/5 text-white border border-rose-500/35 shadow-sm'
                        : 'text-slate-400 hover:bg-[#111726] hover:text-slate-100 border border-transparent'">
                    <span class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center transition"
                              :class="moduloAtivo === 'financeiro' ? 'bg-rose-500 text-white shadow-md shadow-rose-500/30' : 'bg-[#111726] text-slate-400 group-hover:text-white'">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        </span>
                        <span>Caixa &amp; PIX 0%</span>
                    </span>
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full border"
                          :class="caixaStatus.aberto ? 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30' : 'bg-slate-800 text-slate-400 border-slate-700'"
                          x-text="caixaStatus.aberto ? 'Aberto' : 'Fechado'"></span>
                </button>

                <!-- Clientes e CRM -->
                <button @click="mudarModulo('crm')"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition group"
                    :class="moduloAtivo === 'crm'
                        ? 'bg-gradient-to-r from-rose-500/20 to-rose-500/5 text-white border border-rose-500/35 shadow-sm'
                        : 'text-slate-400 hover:bg-[#111726] hover:text-slate-100 border border-transparent'">
                    <span class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center transition"
                              :class="moduloAtivo === 'crm' ? 'bg-rose-500 text-white shadow-md shadow-rose-500/30' : 'bg-[#111726] text-slate-400 group-hover:text-white'">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </span>
                        <span>Clientes &amp; Cashback</span>
                    </span>
                </button>
            </div>

            <!-- 3. CANAIS & AUTOMAÇÕES -->
            <div class="space-y-1">
                <div class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 px-3 pb-1">
                    Canais &amp; Automação
                </div>

                <!-- WhatsApp e Bot -->
                <button @click="mudarModulo('whatsapp')"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition group"
                    :class="moduloAtivo === 'whatsapp'
                        ? 'bg-gradient-to-r from-rose-500/20 to-rose-500/5 text-white border border-rose-500/35 shadow-sm'
                        : 'text-slate-400 hover:bg-[#111726] hover:text-slate-100 border border-transparent'">
                    <span class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center transition"
                              :class="moduloAtivo === 'whatsapp' ? 'bg-rose-500 text-white shadow-md shadow-rose-500/30' : 'bg-[#111726] text-slate-400 group-hover:text-white'">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        </span>
                        <span>WhatsApp &amp; Bot IA</span>
                    </span>
                    <div class="flex items-center gap-1.5">
                        <span x-show="totalAguardandoHumano > 0"
                              class="bg-amber-400 text-slate-950 font-bold text-[10px] px-1.5 py-0.5 rounded-full animate-pulse"
                              x-text="totalAguardandoHumano"></span>
                        <span class="w-2 h-2 rounded-full" :class="whatsappConectado ? 'bg-emerald-400' : 'bg-amber-400'"></span>
                    </div>
                </button>

                <!-- Loja e Horários -->
                <button @click="mudarModulo('config')"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition group"
                    :class="moduloAtivo === 'config'
                        ? 'bg-gradient-to-r from-rose-500/20 to-rose-500/5 text-white border border-rose-500/35 shadow-sm'
                        : 'text-slate-400 hover:bg-[#111726] hover:text-slate-100 border border-transparent'">
                    <span class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center transition"
                              :class="moduloAtivo === 'config' ? 'bg-rose-500 text-white shadow-md shadow-rose-500/30' : 'bg-[#111726] text-slate-400 group-hover:text-white'">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                        </span>
                        <span>Loja &amp; Horários</span>
                    </span>
                </button>
            </div>
        </div>

        <!-- Card PWA & Controles Rápidos de Estação (100% SVG) -->
        <div class="bg-[#0d1322] border border-slate-800/90 rounded-2xl p-3 space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Estação &amp; PWA</span>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/15 text-emerald-400"
                      x-text="pwaStandalone ? 'App Instalado' : 'Web App'"></span>
            </div>

            <div class="grid grid-cols-3 gap-1.5 text-[10px] font-bold">
                <button @click="alternarSom()"
                        title="Ativar/Desativar campainha sonora de novos pedidos"
                        class="p-2 rounded-xl border flex flex-col items-center gap-1 transition"
                        :class="somHabilitado ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-[#080c17] border-slate-800 text-slate-500'">
                    <svg x-show="somHabilitado" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <svg x-show="!somHabilitado" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"></path></svg>
                    <span>Som</span>
                </button>
                <button @click="alternarWakeLock()"
                        title="Manter tela sempre acesa (não apagar no balcão/cozinha)"
                        class="p-2 rounded-xl border flex flex-col items-center gap-1 transition"
                        :class="wakeLockAtivo ? 'bg-amber-500/10 border-amber-500/30 text-amber-300' : 'bg-[#080c17] border-slate-800 text-slate-500'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span>Tela Viva</span>
                </button>
                <button @click="alternarAutoImpressao()"
                        title="Imprimir cupom térmico 80mm automaticamente ao aceitar pedido"
                        class="p-2 rounded-xl border flex flex-col items-center gap-1 transition"
                        :class="autoImprimir80mm ? 'bg-sky-500/10 border-sky-500/30 text-sky-300' : 'bg-[#080c17] border-slate-800 text-slate-500'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>80mm</span>
                </button>
            </div>

            <template x-if="!pwaStandalone">
                <button @click="instalarAppPwa()"
                        class="w-full bg-[#131b2e] hover:bg-slate-800 text-slate-200 border border-slate-700/80 rounded-xl py-2 px-3 text-[11px] font-semibold flex items-center justify-center gap-1.5 transition">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span x-text="pwaPromptDisponivel ? 'Instalar Aplicativo Agora' : 'Como Instalar no Celular/PC'"></span>
                </button>
            </template>
        </div>
    </div>

    <!-- Rodapé: Card da Loja, Retorno SuperAdmin & Sair -->
    <div class="p-3.5 border-t border-slate-800/80 bg-[#060911]/60 space-y-2">
        <template x-if="temSessaoSuperAdmin">
            <button @click="voltarSuperAdmin()"
                    class="w-full bg-amber-500/15 hover:bg-amber-500/25 border border-amber-500/30 text-amber-300 rounded-xl py-2 px-3 text-[11px] font-bold flex items-center justify-center gap-1.5 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                <span>Voltar ao Super Admin</span>
            </button>
        </template>

        <div class="bg-[#0d1322] border border-slate-800/90 p-2.5 rounded-2xl flex items-center justify-between gap-2.5">
            <div class="flex items-center gap-2.5 min-w-0">
                <template x-if="loja?.logo_url">
                    <img :src="loja.logo_url.startsWith('http') || loja.logo_url.startsWith('/') ? loja.logo_url : '/storage/' + loja.logo_url"
                         class="w-9 h-9 rounded-xl object-cover border border-slate-700 shrink-0">
                </template>
                <template x-if="!loja?.logo_url">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 flex items-center justify-center text-white font-bold text-sm uppercase shrink-0"
                         x-text="(loja?.nome || 'P').charAt(0)">
                    </div>
                </template>
                <div class="min-w-0">
                    <div class="text-xs font-semibold text-white truncate" x-text="loja?.nome || 'Minha Loja'"></div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-bold uppercase text-rose-400" x-text="'Plano ' + (loja?.plano || 'PRO')"></span>
                        <span class="text-slate-600">•</span>
                        <span class="text-[10px] text-emerald-400 font-bold">0% Taxa</span>
                    </div>
                </div>
            </div>

            <button @click="sair()" title="Encerrar sessão com segurança"
                    class="p-2 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </button>
        </div>
    </div>
</aside>
