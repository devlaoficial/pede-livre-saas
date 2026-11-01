<!-- SIDEBAR FIXA PARA PC (Desktop lg:flex) -->
<aside class="hidden lg:flex lg:flex-col lg:w-64 bg-slate-900 border-r border-slate-800 shrink-0 justify-between">
    <div class="p-4 space-y-5 overflow-y-auto no-scrollbar">
        <div class="flex items-center justify-between px-1">
            <a href="/" class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-rose-600 flex items-center justify-center text-white font-black text-base shadow-md shadow-rose-600/30">P</div>
                <span class="text-xl font-black tracking-tight text-white">Pede<span class="text-rose-500">Livre</span></span>
            </a>
            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 animate-pulse">Ao Vivo</span>
        </div>

        <!-- Dados da Loja Logada -->
        <div class="bg-slate-950/90 border border-slate-800 p-3.5 rounded-2xl space-y-2">
            <div class="flex items-center justify-between gap-2">
                <div class="text-xs font-black text-white truncate" x-text="loja?.nome || 'Minha Loja'"></div>
                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1 border-t border-slate-800/80">
                <span>Plano <strong class="text-rose-400 uppercase" x-text="loja?.plano || 'Pro'"></strong></span>
                <a :href="'/p/' + (loja?.slug || '')" target="_blank" class="text-emerald-400 hover:underline font-bold">Abrir Cardápio ↗</a>
            </div>
        </div>

        <!-- Menu de Navegação Desktop (9 Módulos) -->
        <nav class="space-y-1">
            <button @click="mudarModulo('pedidos')"
                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
                :class="moduloAtivo === 'pedidos' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                <span class="flex items-center gap-2.5"><span>📋</span> Pedidos &amp; PDV</span>
                <span x-show="colunas.novo.length > 0" class="bg-white text-rose-600 font-black text-[11px] px-2 py-0.5 rounded-full" x-text="colunas.novo.length"></span>
            </button>

            <button @click="mudarModulo('catalogo')"
                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
                :class="moduloAtivo === 'catalogo' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                <span class="flex items-center gap-2.5"><span>🍔</span> Cardápio &amp; Cupons</span>
                <span class="text-[11px] opacity-75" x-text="produtos.length"></span>
            </button>

            <button @click="mudarModulo('kds')"
                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
                :class="moduloAtivo === 'kds' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                <span class="flex items-center gap-2.5"><span>🍳</span> KDS Cozinha</span>
                <span class="text-[11px] opacity-75" x-text="kdsTickets.length"></span>
            </button>

            <button @click="mudarModulo('mesas')"
                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
                :class="moduloAtivo === 'mesas' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                <span class="flex items-center gap-2.5"><span>🪑</span> Mesas &amp; Comandas</span>
                <div class="flex items-center gap-1">
                    <span x-show="chamadosGarcom.length > 0" class="bg-amber-400 text-slate-950 font-black text-[10px] px-1.5 py-0.2 rounded-full animate-bounce" x-text="'🔔 ' + chamadosGarcom.length"></span>
                    <span class="text-[11px] opacity-75" x-text="mesas.length"></span>
                </div>
            </button>

            <button @click="mudarModulo('entregas')"
                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
                :class="moduloAtivo === 'entregas' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                <span class="flex items-center gap-2.5"><span>🛵</span> Motoboys &amp; Frete</span>
                <span class="text-[11px] opacity-75" x-text="entregadores.length"></span>
            </button>

            <button @click="mudarModulo('crm')"
                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
                :class="moduloAtivo === 'crm' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                <span class="flex items-center gap-2.5"><span>👥</span> Clientes &amp; CRM</span>
                <span class="text-[10px] bg-emerald-500/20 text-emerald-400 px-1.5 py-0.5 rounded-full font-black">Cashback</span>
            </button>

            <button @click="mudarModulo('financeiro')"
                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
                :class="moduloAtivo === 'financeiro' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                <span class="flex items-center gap-2.5"><span>💰</span> Caixa &amp; PIX</span>
            </button>

            <button @click="mudarModulo('whatsapp')"
                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
                :class="moduloAtivo === 'whatsapp' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                <span class="flex items-center gap-2.5">
                    <span>💬</span> WhatsApp &amp; Bot
                    <span x-show="totalAguardandoHumano > 0" class="bg-amber-400 text-slate-950 text-[10px] font-black px-1.5 py-0.2 rounded-full animate-pulse" x-text="totalAguardandoHumano"></span>
                </span>
                <span class="w-2.5 h-2.5 rounded-full" :class="whatsappConectado ? 'bg-emerald-400 shadow-sm shadow-emerald-400/50' : 'bg-slate-600'"></span>
            </button>

            <button @click="mudarModulo('config')"
                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
                :class="moduloAtivo === 'config' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                <span class="flex items-center gap-2.5"><span>⚙️</span> Loja &amp; Horários</span>
            </button>
        </nav>
    </div>

    <div class="p-4 border-t border-slate-800 space-y-2">
        <button @click="alternarSom()" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-bold transition"
            :class="somHabilitado ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400'">
            <span x-text="somHabilitado ? '🔊 Alerta Sonoro Ativo' : '🔇 Som Silenciado'"></span>
        </button>
        <template x-if="temSessaoSuperAdmin">
            <button @click="voltarSuperAdmin()" class="w-full bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 border border-amber-500/30 px-3 py-2 rounded-xl text-xs font-bold transition">
                👑 Voltar ao SaaS Admin
            </button>
        </template>
        <button @click="sair()" class="w-full bg-slate-800 hover:bg-rose-600/20 text-slate-300 hover:text-rose-300 px-3 py-2 rounded-xl text-xs font-bold transition">
            Sair da Conta
        </button>
    </div>
</aside>
