<!-- SIDEBAR FIXA PARA PC (Design Claude Fiel) -->
<aside class="hidden lg:flex lg:flex-col lg:w-64 bg-[#0a0e17] border-r border-[#151b2b] shrink-0 justify-between select-none">
    <div class="p-4 space-y-6 overflow-y-auto no-scrollbar">
        <!-- Logo PedeLivre -->
        <div class="flex items-center gap-2.5 px-2 pt-1">
            <div class="w-8 h-8 rounded-xl bg-[#f43f5e] flex items-center justify-center text-white font-black text-base shadow-lg shadow-rose-600/30">
                P
            </div>
            <span class="text-lg font-black tracking-tight text-white">
                Pede<span class="text-white">Livre</span>
            </span>
        </div>

        <!-- Seções de Navegação -->
        <div class="space-y-4">
            <!-- 1. OPERAÇÃO -->
            <div class="space-y-1">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 px-3 pb-1">
                    Operação
                </div>

                <button @click="mudarModulo('pedidos')"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold transition"
                    :class="moduloAtivo === 'pedidos' ? 'bg-[#f43f5e] text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-[#131927] hover:text-white'">
                    <span class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        Pedidos
                    </span>
                    <span x-show="colunas.novo.length > 0" class="bg-white text-[#f43f5e] font-black text-[10px] w-5 h-5 rounded-full flex items-center justify-center" x-text="colunas.novo.length"></span>
                </button>

                <button @click="mudarModulo('kds')"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold transition"
                    :class="moduloAtivo === 'kds' ? 'bg-[#f43f5e] text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-[#131927] hover:text-white'">
                    <span class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"></path></svg>
                        Cozinha (KDS)
                    </span>
                    <span x-show="kdsTickets.length > 0" class="text-[10px] opacity-75 font-mono" x-text="kdsTickets.length"></span>
                </button>

                <button @click="mudarModulo('mesas')"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold transition"
                    :class="moduloAtivo === 'mesas' ? 'bg-[#f43f5e] text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-[#131927] hover:text-white'">
                    <span class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Mesas e comandas
                    </span>
                    <span x-show="chamadosGarcom.length > 0" class="bg-amber-400 text-slate-950 font-black text-[9px] px-1.5 py-0.2 rounded-full animate-bounce" x-text="'🔔 ' + chamadosGarcom.length"></span>
                </button>

                <button @click="mudarModulo('entregas')"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold transition"
                    :class="moduloAtivo === 'entregas' ? 'bg-[#f43f5e] text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-[#131927] hover:text-white'">
                    <span class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Entregas e motoboys
                    </span>
                    <span class="text-[10px] opacity-75 font-mono" x-text="entregadores.length"></span>
                </button>
            </div>

            <!-- 2. GESTÃO -->
            <div class="space-y-1">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 px-3 pb-1">
                    Gestão
                </div>

                <button @click="mudarModulo('catalogo')"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold transition"
                    :class="moduloAtivo === 'catalogo' ? 'bg-[#f43f5e] text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-[#131927] hover:text-white'">
                    <span class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        Cardápio e cupons
                    </span>
                    <span class="text-[10px] opacity-75 font-mono" x-text="produtos.length"></span>
                </button>

                <button @click="mudarModulo('crm')"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold transition"
                    :class="moduloAtivo === 'crm' ? 'bg-[#f43f5e] text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-[#131927] hover:text-white'">
                    <span class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Clientes e CRM
                    </span>
                </button>

                <button @click="mudarModulo('financeiro')"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold transition"
                    :class="moduloAtivo === 'financeiro' ? 'bg-[#f43f5e] text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-[#131927] hover:text-white'">
                    <span class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        Caixa e PIX
                    </span>
                    <span class="w-2 h-2 rounded-full" :class="caixaStatus.aberto ? 'bg-emerald-400 animate-pulse' : 'bg-slate-600'"></span>
                </button>
            </div>

            <!-- 3. CANAIS -->
            <div class="space-y-1">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 px-3 pb-1">
                    Canais
                </div>

                <button @click="mudarModulo('whatsapp')"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold transition"
                    :class="moduloAtivo === 'whatsapp' ? 'bg-[#f43f5e] text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-[#131927] hover:text-white'">
                    <span class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        WhatsApp e Bot
                    </span>
                    <span class="w-2 h-2 rounded-full" :class="whatsappConectado ? 'bg-emerald-400' : 'bg-amber-400'"></span>
                </button>

                <button @click="mudarModulo('config')"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold transition"
                    :class="moduloAtivo === 'config' ? 'bg-[#f43f5e] text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-[#131927] hover:text-white'">
                    <span class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                        Loja e horários
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Rodapé: Card do Usuário / Loja e Sair da Conta -->
    <div class="p-4 border-t border-[#151b2b] space-y-2">
        <div class="bg-[#131927] border border-[#1e2638] p-3 rounded-2xl flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-slate-800 flex items-center justify-center text-white font-black text-sm uppercase shrink-0"
                 x-text="(loja?.nome || 'D').charAt(0)">
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-xs font-bold text-white truncate" x-text="loja?.nome || 'DevLa'"></div>
                <div class="text-[10px] font-extrabold uppercase text-[#f43f5e]" x-text="'Plano ' + (loja?.plano || 'PRO')"></div>
            </div>
        </div>
        <button @click="sair()" class="w-full text-center text-xs text-slate-500 hover:text-slate-300 font-bold py-1 transition">
            Sair da conta
        </button>
    </div>
</aside>
