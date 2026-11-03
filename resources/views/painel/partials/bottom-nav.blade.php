<!-- BARRA DE NAVEGAÇÃO INFERIOR PARA CELULAR & TABLET PWA (< lg — 100% SVG) -->
<nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-[#080c17]/95 backdrop-blur-xl border-t border-slate-800/90 grid grid-cols-5 items-center pt-1.5 px-1.5 pb-safe select-none shadow-2xl">
    <!-- 1. Pedidos -->
    <button @click="mudarModulo('pedidos'); menuMobileAberto = false"
        class="flex flex-col items-center justify-center py-1 text-[10px] font-semibold relative transition"
        :class="moduloAtivo === 'pedidos' && !menuMobileAberto ? 'text-rose-500' : 'text-slate-400'">
        <div class="w-8 h-6 rounded-xl flex items-center justify-center"
             :class="moduloAtivo === 'pedidos' && !menuMobileAberto ? 'bg-rose-500/15' : ''">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
        </div>
        <span>Pedidos</span>
        <span x-show="colunas.novo.length > 0"
              class="absolute top-0 right-3 bg-rose-500 text-white font-mono text-[9px] font-bold min-w-[16px] h-4 px-1 rounded-full flex items-center justify-center shadow"
              x-text="colunas.novo.length"></span>
    </button>

    <!-- 2. WhatsApp & Chat Ao Vivo -->
    <button @click="mudarModulo('whatsapp'); menuMobileAberto = false"
        class="flex flex-col items-center justify-center py-1 text-[10px] font-semibold relative transition"
        :class="moduloAtivo === 'whatsapp' && !menuMobileAberto ? 'text-rose-500' : 'text-slate-400'">
        <div class="w-8 h-6 rounded-xl flex items-center justify-center"
             :class="moduloAtivo === 'whatsapp' && !menuMobileAberto ? 'bg-rose-500/15' : ''">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
        </div>
        <span>WhatsApp</span>
        <span x-show="totalAguardandoHumano > 0"
              class="absolute top-0 right-2.5 bg-amber-400 text-slate-950 font-mono text-[9px] font-bold min-w-[16px] h-4 px-1 rounded-full flex items-center justify-center animate-pulse"
              x-text="totalAguardandoHumano"></span>
    </button>

    <!-- 3. Botão Central Destaque: Lançar Pedido PDV -->
    <div class="flex flex-col items-center justify-center -mt-4">
        <button @click="abrirModalPdv(); menuMobileAberto = false"
            class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#f43f5e] to-[#be123c] text-white font-bold shadow-lg shadow-rose-600/40 border-2 border-[#080c17] flex items-center justify-center active:scale-95 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
        </button>
        <span class="text-[9px] font-semibold text-slate-300 mt-0.5">PDV</span>
    </div>

    <!-- 4. Cozinha KDS -->
    <button @click="mudarModulo('kds'); menuMobileAberto = false"
        class="flex flex-col items-center justify-center py-1 text-[10px] font-semibold relative transition"
        :class="moduloAtivo === 'kds' && !menuMobileAberto ? 'text-rose-500' : 'text-slate-400'">
        <div class="w-8 h-6 rounded-xl flex items-center justify-center"
             :class="moduloAtivo === 'kds' && !menuMobileAberto ? 'bg-rose-500/15' : ''">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path></svg>
        </div>
        <span>Cozinha</span>
        <span x-show="(colunas.novo.length + colunas.em_preparo.length) > 0"
              class="absolute top-0 right-3 bg-amber-500 text-slate-950 font-mono text-[9px] font-bold min-w-[16px] h-4 px-1 rounded-full flex items-center justify-center"
              x-text="colunas.novo.length + colunas.em_preparo.length"></span>
    </button>

    <!-- 5. Gaveta Completa de Módulos (Todos os 9 Módulos + Controles PWA) -->
    <button @click="menuMobileAberto = !menuMobileAberto"
        class="flex flex-col items-center justify-center py-1 text-[10px] font-semibold relative transition"
        :class="menuMobileAberto || ['catalogo','mesas','entregas','financeiro','crm','config'].includes(moduloAtivo) ? 'text-rose-500' : 'text-slate-400'">
        <div class="w-8 h-6 rounded-xl flex items-center justify-center"
             :class="menuMobileAberto ? 'bg-rose-500/15' : ''">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </div>
        <span>Módulos</span>
        <span x-show="chamadosGarcom.length > 0"
              class="absolute top-0 right-3 bg-amber-400 text-slate-950 font-mono text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center animate-bounce"
              x-text="chamadosGarcom.length"></span>
    </button>
</nav>

<!-- GAVETA OPERACIONAL MOBILE COMPLETA (Bottom Sheet do PWA — 100% SVG) -->
<div x-cloak x-show="menuMobileAberto"
     class="lg:hidden fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex flex-col justify-end"
     @click.self="menuMobileAberto = false">
    <div class="bg-[#0b101e] border-t border-slate-800 rounded-t-3xl p-5 pb-8 space-y-5 max-h-[86vh] overflow-y-auto shadow-2xl">
        <!-- Cabeçalho da Gaveta -->
        <div class="flex items-center justify-between border-b border-slate-800/80 pb-3.5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#f43f5e] to-[#be123c] flex items-center justify-center text-white font-bold text-base shadow-md">
                    <span x-text="(loja?.nome || 'P').charAt(0)"></span>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white" x-text="loja?.nome || 'PedeLivre OS'"></h3>
                    <div class="flex items-center gap-2 text-[11px]">
                        <span class="text-rose-400 font-semibold uppercase" x-text="'Plano ' + (loja?.plano || 'PRO')"></span>
                        <span class="text-slate-600">•</span>
                        <span :class="onlineStatus ? 'text-emerald-400' : 'text-amber-400'" x-text="onlineStatus ? 'Online' : 'Offline'"></span>
                    </div>
                </div>
            </div>
            <button @click="menuMobileAberto = false" class="w-8 h-8 rounded-xl bg-slate-900 text-slate-400 hover:text-white flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Grade com Todos os 9 Módulos Operacionais -->
        <div>
            <div class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 mb-2.5">Navegação Completa da Loja</div>
            <div class="grid grid-cols-3 gap-2.5 text-xs font-bold">
                <button @click="mudarModulo('pedidos'); menuMobileAberto = false"
                    class="p-3 rounded-2xl border flex flex-col items-start gap-2 transition"
                    :class="moduloAtivo === 'pedidos' ? 'bg-rose-500/15 border-rose-500/40 text-white' : 'bg-[#111726] border-slate-800/90 text-slate-300'">
                    <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <span>Pedidos</span>
                </button>
                <button @click="mudarModulo('kds'); menuMobileAberto = false"
                    class="p-3 rounded-2xl border flex flex-col items-start gap-2 transition"
                    :class="moduloAtivo === 'kds' ? 'bg-rose-500/15 border-rose-500/40 text-white' : 'bg-[#111726] border-slate-800/90 text-slate-300'">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path></svg>
                    <span>Cozinha KDS</span>
                </button>
                <button @click="mudarModulo('mesas'); menuMobileAberto = false"
                    class="p-3 rounded-2xl border flex flex-col items-start gap-2 transition relative"
                    :class="moduloAtivo === 'mesas' ? 'bg-rose-500/15 border-rose-500/40 text-white' : 'bg-[#111726] border-slate-800/90 text-slate-300'">
                    <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span>Mesas &amp; QR</span>
                    <span x-show="chamadosGarcom.length > 0" class="absolute top-2 right-2 bg-amber-400 text-slate-950 text-[9px] font-bold px-1.5 rounded-full" x-text="chamadosGarcom.length"></span>
                </button>
                <button @click="mudarModulo('entregas'); menuMobileAberto = false"
                    class="p-3 rounded-2xl border flex flex-col items-start gap-2 transition"
                    :class="moduloAtivo === 'entregas' ? 'bg-rose-500/15 border-rose-500/40 text-white' : 'bg-[#111726] border-slate-800/90 text-slate-300'">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                    <span>Motoboys</span>
                </button>
                <button @click="mudarModulo('catalogo'); menuMobileAberto = false"
                    class="p-3 rounded-2xl border flex flex-col items-start gap-2 transition"
                    :class="moduloAtivo === 'catalogo' ? 'bg-rose-500/15 border-rose-500/40 text-white' : 'bg-[#111726] border-slate-800/90 text-slate-300'">
                    <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    <span>Cardápio</span>
                </button>
                <button @click="mudarModulo('financeiro'); menuMobileAberto = false"
                    class="p-3 rounded-2xl border flex flex-col items-start gap-2 transition"
                    :class="moduloAtivo === 'financeiro' ? 'bg-rose-500/15 border-rose-500/40 text-white' : 'bg-[#111726] border-slate-800/90 text-slate-300'">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    <span>Caixa &amp; PIX</span>
                </button>
                <button @click="mudarModulo('whatsapp'); menuMobileAberto = false"
                    class="p-3 rounded-2xl border flex flex-col items-start gap-2 transition relative"
                    :class="moduloAtivo === 'whatsapp' ? 'bg-rose-500/15 border-rose-500/40 text-white' : 'bg-[#111726] border-slate-800/90 text-slate-300'">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    <span>WhatsApp</span>
                    <span x-show="totalAguardandoHumano > 0" class="absolute top-2 right-2 bg-amber-400 text-slate-950 text-[9px] font-bold px-1.5 rounded-full" x-text="totalAguardandoHumano"></span>
                </button>
                <button @click="mudarModulo('crm'); menuMobileAberto = false"
                    class="p-3 rounded-2xl border flex flex-col items-start gap-2 transition"
                    :class="moduloAtivo === 'crm' ? 'bg-rose-500/15 border-rose-500/40 text-white' : 'bg-[#111726] border-slate-800/90 text-slate-300'">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span>Clientes CRM</span>
                </button>
                <button @click="mudarModulo('config'); menuMobileAberto = false"
                    class="p-3 rounded-2xl border flex flex-col items-start gap-2 transition"
                    :class="moduloAtivo === 'config' ? 'bg-rose-500/15 border-rose-500/40 text-white' : 'bg-[#111726] border-slate-800/90 text-slate-300'">
                    <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                    <span>Ajustes Loja</span>
                </button>
            </div>
        </div>

        <!-- Controles Nativos do PWA no Celular -->
        <div class="space-y-2">
            <div class="text-[10px] font-semibold uppercase tracking-widest text-slate-500">Controles do Aparelho (PWA)</div>
            <div class="grid grid-cols-3 gap-2 text-xs font-bold">
                <button @click="alternarSom()"
                    class="p-3 rounded-2xl border flex flex-col items-center gap-1.5"
                    :class="somHabilitado ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-300' : 'bg-[#111726] border-slate-800 text-slate-400'">
                    <svg x-show="somHabilitado" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <svg x-show="!somHabilitado" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"></path></svg>
                    <span x-text="somHabilitado ? 'Som Ativo' : 'Sem Som'"></span>
                </button>
                <button @click="alternarWakeLock()"
                    class="p-3 rounded-2xl border flex flex-col items-center gap-1.5"
                    :class="wakeLockAtivo ? 'bg-amber-500/15 border-amber-500/30 text-amber-300' : 'bg-[#111726] border-slate-800 text-slate-400'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span x-text="wakeLockAtivo ? 'Tela Acesa' : 'Tela Normal'"></span>
                </button>
                <button @click="alternarAutoImpressao()"
                    class="p-3 rounded-2xl border flex flex-col items-center gap-1.5"
                    :class="autoImprimir80mm ? 'bg-sky-500/15 border-sky-500/30 text-sky-300' : 'bg-[#111726] border-slate-800 text-slate-400'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Auto 80mm</span>
                </button>
            </div>

            <template x-if="!pwaStandalone">
                <button @click="menuMobileAberto = false; instalarAppPwa()"
                    class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-3 rounded-2xl text-xs shadow-lg flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>Instalar Aplicativo PedeLivre no Celular</span>
                </button>
            </template>
        </div>

        <!-- Ações de Conta -->
        <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between gap-3">
            <button @click="copiarLinkCardapio(); menuMobileAberto = false"
                class="flex-1 bg-[#111726] border border-slate-800 text-slate-200 font-bold py-2.5 rounded-xl text-xs flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                <span>Copiar Link do Cardápio</span>
            </button>
            <button @click="sair()"
                class="px-4 bg-rose-500/15 border border-rose-500/30 text-rose-300 font-bold py-2.5 rounded-xl text-xs">
                Sair
            </button>
        </div>
    </div>
</div>
