<!-- BARRA DE NAVEGAÇÃO INFERIOR PARA CELULAR & TABLET PWA (< lg) -->
<nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-[#080c17]/95 backdrop-blur-xl border-t border-slate-800/90 grid grid-cols-5 items-center pt-1.5 px-1.5 pb-safe select-none shadow-2xl">
    <!-- 1. Pedidos -->
    <button @click="mudarModulo('pedidos'); menuMobileAberto = false"
        class="flex flex-col items-center justify-center py-1 text-[10px] font-extrabold relative transition"
        :class="moduloAtivo === 'pedidos' && !menuMobileAberto ? 'text-rose-500' : 'text-slate-400'">
        <div class="w-8 h-6 rounded-xl flex items-center justify-center"
             :class="moduloAtivo === 'pedidos' && !menuMobileAberto ? 'bg-rose-500/15' : ''">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
        </div>
        <span>Pedidos</span>
        <span x-show="colunas.novo.length > 0"
              class="absolute top-0 right-3 bg-rose-500 text-white font-mono text-[9px] font-black min-w-[16px] h-4 px-1 rounded-full flex items-center justify-center shadow"
              x-text="colunas.novo.length"></span>
    </button>

    <!-- 2. WhatsApp & Chat Ao Vivo -->
    <button @click="mudarModulo('whatsapp'); menuMobileAberto = false"
        class="flex flex-col items-center justify-center py-1 text-[10px] font-extrabold relative transition"
        :class="moduloAtivo === 'whatsapp' && !menuMobileAberto ? 'text-rose-500' : 'text-slate-400'">
        <div class="w-8 h-6 rounded-xl flex items-center justify-center"
             :class="moduloAtivo === 'whatsapp' && !menuMobileAberto ? 'bg-rose-500/15' : ''">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
        </div>
        <span>WhatsApp</span>
        <span x-show="totalAguardandoHumano > 0"
              class="absolute top-0 right-2.5 bg-amber-400 text-slate-950 font-mono text-[9px] font-black min-w-[16px] h-4 px-1 rounded-full flex items-center justify-center animate-pulse"
              x-text="totalAguardandoHumano"></span>
    </button>

    <!-- 3. Botão Central Destaque: Lançar Pedido PDV -->
    <div class="flex flex-col items-center justify-center -mt-4">
        <button @click="abrirModalPdv(); menuMobileAberto = false"
            class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#f43f5e] to-[#be123c] text-white font-black text-xl shadow-lg shadow-rose-600/40 border-2 border-[#080c17] flex items-center justify-center active:scale-95 transition">
            +
        </button>
        <span class="text-[9px] font-extrabold text-slate-300 mt-0.5">PDV</span>
    </div>

    <!-- 4. Cozinha KDS -->
    <button @click="mudarModulo('kds'); menuMobileAberto = false"
        class="flex flex-col items-center justify-center py-1 text-[10px] font-extrabold relative transition"
        :class="moduloAtivo === 'kds' && !menuMobileAberto ? 'text-rose-500' : 'text-slate-400'">
        <div class="w-8 h-6 rounded-xl flex items-center justify-center"
             :class="moduloAtivo === 'kds' && !menuMobileAberto ? 'bg-rose-500/15' : ''">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path></svg>
        </div>
        <span>Cozinha</span>
        <span x-show="(colunas.novo.length + colunas.em_preparo.length) > 0"
              class="absolute top-0 right-3 bg-amber-500 text-slate-950 font-mono text-[9px] font-black min-w-[16px] h-4 px-1 rounded-full flex items-center justify-center"
              x-text="colunas.novo.length + colunas.em_preparo.length"></span>
    </button>

    <!-- 5. Gaveta Completa de Módulos (Todos os 9 Módulos + Controles PWA) -->
    <button @click="menuMobileAberto = !menuMobileAberto"
        class="flex flex-col items-center justify-center py-1 text-[10px] font-extrabold relative transition"
        :class="menuMobileAberto || ['catalogo','mesas','entregas','financeiro','crm','config'].includes(moduloAtivo) ? 'text-rose-500' : 'text-slate-400'">
        <div class="w-8 h-6 rounded-xl flex items-center justify-center"
             :class="menuMobileAberto ? 'bg-rose-500/15' : ''">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </div>
        <span>Módulos</span>
        <span x-show="chamadosGarcom.length > 0"
              class="absolute top-0 right-3 bg-amber-400 text-slate-950 font-mono text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center animate-bounce"
              x-text="chamadosGarcom.length"></span>
    </button>
</nav>

<!-- GAVETA OPERACIONAL MOBILE COMPLETA (Bottom Sheet do PWA) -->
<div x-cloak x-show="menuMobileAberto"
     class="lg:hidden fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex flex-col justify-end"
     @click.self="menuMobileAberto = false">
    <div class="bg-[#0b101e] border-t border-slate-800 rounded-t-3xl p-5 pb-8 space-y-5 max-h-[86vh] overflow-y-auto shadow-2xl">
        <!-- Cabeçalho da Gaveta -->
        <div class="flex items-center justify-between border-b border-slate-800/80 pb-3.5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#f43f5e] to-[#be123c] flex items-center justify-center text-white font-black text-base shadow-md">
                    <span x-text="(loja?.nome || 'P').charAt(0)"></span>
                </div>
                <div>
                    <h3 class="text-sm font-black text-white" x-text="loja?.nome || 'PedeLivre OS'"></h3>
                    <div class="flex items-center gap-2 text-[11px]">
                        <span class="text-rose-400 font-extrabold uppercase" x-text="'Plano ' + (loja?.plano || 'PRO')"></span>
                        <span class="text-slate-600">•</span>
                        <span :class="onlineStatus ? 'text-emerald-400' : 'text-amber-400'" x-text="onlineStatus ? '● Online' : '○ Offline'"></span>
                    </div>
                </div>
            </div>
            <button @click="menuMobileAberto = false" class="w-8 h-8 rounded-xl bg-slate-900 text-slate-400 hover:text-white flex items-center justify-center font-bold">✕</button>
        </div>

        <!-- Grade com Todos os 9 Módulos Operacionais -->
        <div>
            <div class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500 mb-2.5">Navegação Completa da Loja</div>
            <div class="grid grid-cols-3 gap-2.5 text-xs font-bold">
                <button @click="mudarModulo('pedidos'); menuMobileAberto = false"
                    class="p-3 rounded-2xl border flex flex-col items-start gap-1.5 transition"
                    :class="moduloAtivo === 'pedidos' ? 'bg-rose-500/15 border-rose-500/40 text-white' : 'bg-[#111726] border-slate-800/90 text-slate-300'">
                    <span class="text-lg">📋</span>
                    <span>Pedidos</span>
                </button>
                <button @click="mudarModulo('kds'); menuMobileAberto = false"
                    class="p-3 rounded-2xl border flex flex-col items-start gap-1.5 transition"
                    :class="moduloAtivo === 'kds' ? 'bg-rose-500/15 border-rose-500/40 text-white' : 'bg-[#111726] border-slate-800/90 text-slate-300'">
                    <span class="text-lg">🍳</span>
                    <span>Cozinha KDS</span>
                </button>
                <button @click="mudarModulo('mesas'); menuMobileAberto = false"
                    class="p-3 rounded-2xl border flex flex-col items-start gap-1.5 transition relative"
                    :class="moduloAtivo === 'mesas' ? 'bg-rose-500/15 border-rose-500/40 text-white' : 'bg-[#111726] border-slate-800/90 text-slate-300'">
                    <span class="text-lg">🪑</span>
                    <span>Mesas &amp; QR</span>
                    <span x-show="chamadosGarcom.length > 0" class="absolute top-2 right-2 bg-amber-400 text-slate-950 text-[9px] font-black px-1.5 rounded-full" x-text="chamadosGarcom.length"></span>
                </button>
                <button @click="mudarModulo('entregas'); menuMobileAberto = false"
                    class="p-3 rounded-2xl border flex flex-col items-start gap-1.5 transition"
                    :class="moduloAtivo === 'entregas' ? 'bg-rose-500/15 border-rose-500/40 text-white' : 'bg-[#111726] border-slate-800/90 text-slate-300'">
                    <span class="text-lg">🛵</span>
                    <span>Motoboys</span>
                </button>
                <button @click="mudarModulo('catalogo'); menuMobileAberto = false"
                    class="p-3 rounded-2xl border flex flex-col items-start gap-1.5 transition"
                    :class="moduloAtivo === 'catalogo' ? 'bg-rose-500/15 border-rose-500/40 text-white' : 'bg-[#111726] border-slate-800/90 text-slate-300'">
                    <span class="text-lg">🍔</span>
                    <span>Cardápio</span>
                </button>
                <button @click="mudarModulo('financeiro'); menuMobileAberto = false"
                    class="p-3 rounded-2xl border flex flex-col items-start gap-1.5 transition"
                    :class="moduloAtivo === 'financeiro' ? 'bg-rose-500/15 border-rose-500/40 text-white' : 'bg-[#111726] border-slate-800/90 text-slate-300'">
                    <span class="text-lg">💵</span>
                    <span>Caixa &amp; PIX</span>
                </button>
                <button @click="mudarModulo('whatsapp'); menuMobileAberto = false"
                    class="p-3 rounded-2xl border flex flex-col items-start gap-1.5 transition relative"
                    :class="moduloAtivo === 'whatsapp' ? 'bg-rose-500/15 border-rose-500/40 text-white' : 'bg-[#111726] border-slate-800/90 text-slate-300'">
                    <span class="text-lg">💬</span>
                    <span>WhatsApp</span>
                    <span x-show="totalAguardandoHumano > 0" class="absolute top-2 right-2 bg-amber-400 text-slate-950 text-[9px] font-black px-1.5 rounded-full" x-text="totalAguardandoHumano"></span>
                </button>
                <button @click="mudarModulo('crm'); menuMobileAberto = false"
                    class="p-3 rounded-2xl border flex flex-col items-start gap-1.5 transition"
                    :class="moduloAtivo === 'crm' ? 'bg-rose-500/15 border-rose-500/40 text-white' : 'bg-[#111726] border-slate-800/90 text-slate-300'">
                    <span class="text-lg">👥</span>
                    <span>Clientes CRM</span>
                </button>
                <button @click="mudarModulo('config'); menuMobileAberto = false"
                    class="p-3 rounded-2xl border flex flex-col items-start gap-1.5 transition"
                    :class="moduloAtivo === 'config' ? 'bg-rose-500/15 border-rose-500/40 text-white' : 'bg-[#111726] border-slate-800/90 text-slate-300'">
                    <span class="text-lg">⚙️</span>
                    <span>Ajustes Loja</span>
                </button>
            </div>
        </div>

        <!-- Controles Nativos do PWA no Celular -->
        <div class="space-y-2">
            <div class="text-[10px] font-extrabold uppercase tracking-widest text-slate-500">Controles do Aparelho (PWA)</div>
            <div class="grid grid-cols-3 gap-2 text-xs font-bold">
                <button @click="alternarSom()"
                    class="p-3 rounded-2xl border flex flex-col items-center gap-1"
                    :class="somHabilitado ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-300' : 'bg-[#111726] border-slate-800 text-slate-400'">
                    <span class="text-base" x-text="somHabilitado ? '🔔' : '🔕'"></span>
                    <span x-text="somHabilitado ? 'Som Ativo' : 'Sem Som'"></span>
                </button>
                <button @click="alternarWakeLock()"
                    class="p-3 rounded-2xl border flex flex-col items-center gap-1"
                    :class="wakeLockAtivo ? 'bg-amber-500/15 border-amber-500/30 text-amber-300' : 'bg-[#111726] border-slate-800 text-slate-400'">
                    <span class="text-base">☀️</span>
                    <span x-text="wakeLockAtivo ? 'Tela Acesa' : 'Tela Normal'"></span>
                </button>
                <button @click="alternarAutoImpressao()"
                    class="p-3 rounded-2xl border flex flex-col items-center gap-1"
                    :class="autoImprimir80mm ? 'bg-sky-500/15 border-sky-500/30 text-sky-300' : 'bg-[#111726] border-slate-800 text-slate-400'">
                    <span class="text-base">🖨️</span>
                    <span>Auto 80mm</span>
                </button>
            </div>

            <template x-if="!pwaStandalone">
                <button @click="menuMobileAberto = false; instalarAppPwa()"
                    class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold py-3 rounded-2xl text-xs shadow-lg flex items-center justify-center gap-2">
                    <span>📲</span> Instalar Aplicativo PedeLivre no Celular
                </button>
            </template>
        </div>

        <!-- Ações de Conta -->
        <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between gap-3">
            <button @click="copiarLinkCardapio(); menuMobileAberto = false"
                class="flex-1 bg-[#111726] border border-slate-800 text-slate-200 font-bold py-2.5 rounded-xl text-xs flex items-center justify-center gap-1.5">
                <span>🔗</span> Copiar Link do Cardápio
            </button>
            <button @click="sair()"
                class="px-4 bg-rose-500/15 border border-rose-500/30 text-rose-300 font-bold py-2.5 rounded-xl text-xs">
                Sair
            </button>
        </div>
    </div>
</div>
