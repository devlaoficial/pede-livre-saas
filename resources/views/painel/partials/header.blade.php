<!-- HEADER OPERACIONAL PREMIUM (Desktop & Mobile PWA) -->
<header class="bg-[#080c17]/90 backdrop-blur-xl border-b border-slate-800/80 px-3.5 lg:px-6 py-3 flex flex-col gap-2.5 shrink-0 pt-safe z-20">
    <!-- Linha Principal do Header -->
    <div class="flex items-center justify-between gap-3">
        <!-- Esquerda: Identidade Mobile + Título do Módulo -->
        <div class="flex items-center gap-3 min-w-0">
            <!-- Avatar Mobile (< lg) -->
            <div class="lg:hidden relative shrink-0" @click="menuMobileAberto = true">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#f43f5e] to-[#be123c] flex items-center justify-center text-white font-black text-sm shadow-md shadow-rose-600/30">
                    P
                </div>
                <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-[#080c17]"
                      :class="onlineStatus ? 'bg-emerald-400' : 'bg-amber-400 animate-ping'"></span>
            </div>

            <div class="min-w-0">
                <div class="flex items-center gap-2.5">
                    <h1 class="text-base sm:text-lg lg:text-xl font-black text-white tracking-tight truncate" x-text="tituloModulo()"></h1>
                    <!-- Pílula Sincronização Tempo Real -->
                    <span class="hidden sm:inline-flex items-center gap-1.5 text-[10px] font-bold px-2 py-0.5 rounded-full border"
                          :class="onlineStatus ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-amber-500/15 text-amber-300 border-amber-500/30'">
                        <span class="w-1.5 h-1.5 rounded-full" :class="onlineStatus ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400 animate-ping'"></span>
                        <span x-text="onlineStatus ? 'Ao vivo' : 'Offline • Reconectando'"></span>
                    </span>
                </div>
                <p class="text-[11px] text-slate-400 font-medium capitalize truncate" x-text="dataHojeFormatada()"></p>
            </div>
        </div>

        <!-- Direita: Controles Operacionais & Ações Rápidas -->
        <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
            <!-- 1. Abrir / Pausar Loja (Visível em Mobile e Desktop) -->
            <button @click="alternarStatusLojaAberta()"
                title="Clique para abrir ou pausar o recebimento de pedidos no cardápio digital"
                class="bg-[#111726] border px-2.5 sm:px-3.5 py-1.5 rounded-xl text-[11px] sm:text-xs font-extrabold flex items-center gap-1.5 transition"
                :class="lojaAbertaManual !== false
                    ? 'border-emerald-500/40 text-emerald-300 hover:bg-emerald-500/10 shadow-sm shadow-emerald-500/10'
                    : 'border-rose-500/40 text-rose-300 hover:bg-rose-500/10'">
                <span class="w-2 h-2 rounded-full" :class="lojaAbertaManual !== false ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400'"></span>
                <span x-text="lojaAbertaManual !== false ? 'Loja aberta' : 'Pausada'"></span>
            </button>

            <!-- 2. Status WhatsApp (Desktop) -->
            <button @click="mudarModulo('whatsapp')"
                class="hidden md:flex bg-[#111726] border border-slate-800 hover:border-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-300 items-center gap-2 transition">
                <span class="w-2 h-2 rounded-full" :class="whatsappConectado ? 'bg-emerald-400' : 'bg-amber-400'"></span>
                <span x-text="whatsappConectado ? 'WhatsApp ativo' : 'Conectar WhatsApp'"></span>
                <span x-show="totalAguardandoHumano > 0"
                      class="bg-amber-400 text-slate-950 font-black text-[10px] px-1.5 py-0.2 rounded-full animate-pulse"
                      x-text="totalAguardandoHumano"></span>
            </button>

            <!-- 3. Status do Caixa (Desktop XL) -->
            <button @click="mudarModulo('financeiro')"
                class="hidden xl:flex bg-[#111726] border border-slate-800 hover:border-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-300 items-center gap-1.5 transition">
                <span>💵</span>
                <span x-text="caixaStatus.aberto ? 'Caixa aberto' : 'Caixa fechado'"></span>
            </button>

            <!-- 4. Botão Wake Lock (Manter Tela Acesa) -->
            <button @click="alternarWakeLock()"
                :title="wakeLockAtivo ? 'Modo Tela Sempre Acesa ATIVO (não bloqueia no balcão/cozinha)' : 'Ativar Modo Tela Sempre Acesa'"
                class="p-2 rounded-xl border text-xs font-bold transition flex items-center gap-1"
                :class="wakeLockAtivo ? 'bg-amber-500/15 border-amber-500/40 text-amber-300' : 'bg-[#111726] border-slate-800 text-slate-400 hover:text-white'">
                <span>☀️</span>
                <span class="hidden 2xl:inline text-[11px]" x-text="wakeLockAtivo ? 'Tela Ativa' : 'Tela'"></span>
            </button>

            <!-- 5. Sino de Alerta Sonoro -->
            <button @click="alternarSom()"
                :title="somHabilitado ? 'Campainha de pedidos ATIVADA (clique para testar/silenciar)' : 'Campainha SILENCIADA (clique para ativar)'"
                class="p-2 rounded-xl border transition relative"
                :class="somHabilitado ? 'bg-[#111726] border-slate-800 text-slate-200 hover:border-slate-700' : 'bg-rose-950/30 border-rose-500/30 text-rose-400'">
                <span class="text-xs" x-text="somHabilitado ? '🔔' : '🔕'"></span>
                <span x-show="colunas.novo.length > 0 || chamadosGarcom.length > 0"
                      class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-rose-500 rounded-full animate-ping"></span>
            </button>

            <!-- 6. Instalar PWA (quando disponível e fora do modo standalone) -->
            <template x-if="!pwaStandalone">
                <button @click="instalarAppPwa()"
                    title="Instalar aplicativo PedeLivre OS neste dispositivo"
                    class="hidden sm:flex bg-emerald-500/15 hover:bg-emerald-500/25 border border-emerald-500/30 text-emerald-300 px-3 py-1.5 rounded-xl text-xs font-extrabold items-center gap-1.5 transition">
                    <span>📲</span>
                    <span class="hidden lg:inline">Instalar App</span>
                </button>
            </template>

            <!-- 7. Copiar Link do Cardápio -->
            <button @click="copiarLinkCardapio()"
                title="Copiar link público do cardápio digital"
                class="hidden sm:flex bg-[#111726] border border-slate-800 hover:border-slate-700 text-slate-300 hover:text-white px-3 py-1.5 rounded-xl text-xs font-bold items-center gap-1.5 transition">
                <svg class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                <span class="hidden xl:inline">Cardápio</span>
            </button>

            <!-- 8. Botão Novo Pedido PDV (F2) -->
            <button @click="abrirModalPdv()"
                class="bg-gradient-to-r from-[#f43f5e] to-[#e11d48] hover:from-[#fb7185] hover:to-[#f43f5e] text-white text-xs font-black px-3 sm:px-4 py-2 rounded-xl shadow-lg shadow-rose-600/25 flex items-center gap-1.5 transition active:scale-95">
                <span>+</span>
                <span class="hidden sm:inline">Novo pedido</span>
                <span class="sm:hidden">PDV</span>
                <kbd class="hidden lg:inline-block text-[10px] font-mono bg-black/25 px-1.5 py-0.2 rounded ml-0.5">F2</kbd>
            </button>
        </div>
    </div>
</header>

<!-- BARRA DE ALERTA URGENTE: NOVOS PEDIDOS AGUARDANDO ACEITE -->
<template x-if="colunas.novo.length > 0">
    <div class="bg-gradient-to-r from-rose-600 via-rose-500 to-amber-500 text-white px-4 lg:px-6 py-2 flex flex-wrap items-center justify-between gap-2 shrink-0 shadow-lg z-30">
        <div class="flex items-center gap-2.5 text-xs font-black">
            <span class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center text-sm animate-bounce">🔔</span>
            <span x-text="colunas.novo.length === 1 ? '1 NOVO PEDIDO AGUARDANDO CONFIRMAÇÃO!' : (colunas.novo.length + ' NOVOS PEDIDOS AGUARDANDO CONFIRMAÇÃO!')"></span>
            <span class="hidden sm:inline text-rose-100 font-bold text-[11px]"
                  x-text="'(#' + colunas.novo[0]?.codigo_publico + ' • ' + nomeClienteOuBalcao(colunas.novo[0] || {}) + ')'"></span>
        </div>
        <div class="flex items-center gap-2">
            <button @click="mudarModulo('pedidos'); abaPedidoMobile = 'novo'; avancarStatus(colunas.novo[0])"
                class="bg-slate-950 hover:bg-slate-900 text-white font-black px-3 py-1 rounded-xl text-[11px] shadow transition active:scale-95">
                ⚡ Aceitar #<span x-text="colunas.novo[0]?.codigo_publico"></span> Agora
            </button>
            <button x-show="moduloAtivo !== 'pedidos'" @click="mudarModulo('pedidos'); abaPedidoMobile = 'novo'"
                class="bg-white/20 hover:bg-white/30 text-white font-bold px-2.5 py-1 rounded-xl text-[11px] transition">
                Ver Fila
            </button>
        </div>
    </div>
</template>

<!-- ALERTA AO VIVO: MESA CHAMANDO GARÇOM -->
<template x-if="chamadosGarcom.length > 0">
    <div class="bg-amber-500 text-slate-950 px-4 lg:px-6 py-2 flex flex-wrap items-center justify-between gap-2 shrink-0 shadow-lg z-30">
        <div class="flex flex-wrap items-center gap-2 text-xs font-black">
            <span class="text-base animate-bounce">🔔</span>
            <span>ATENÇÃO SALÃO — MESA CHAMANDO GARÇOM:</span>
            <template x-for="ch in chamadosGarcom" :key="ch.mesa_id">
                <span class="bg-slate-950 text-amber-300 px-2.5 py-1 rounded-xl inline-flex items-center gap-2">
                    <span x-text="'🪑 Mesa #' + ch.mesa_numero + ' (' + ch.horario + ')'"></span>
                    <button @click="atenderChamadoGarcom(ch.mesa_id)" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black px-2 py-0.5 rounded-lg text-[10px]">
                        ✓ Atender
                    </button>
                </span>
            </template>
        </div>
    </div>
</template>

<!-- Toast Feedback Premium -->
<template x-if="toastMsg">
    <div class="bg-emerald-500/15 border-b border-emerald-500/30 text-emerald-300 px-4 lg:px-6 py-2 text-xs font-bold flex justify-between items-center shrink-0 backdrop-blur-md">
        <span class="flex items-center gap-2">
            <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-[10px]">✓</span>
            <span x-text="toastMsg"></span>
        </span>
        <button @click="toastMsg = ''" class="text-emerald-400 hover:text-white px-1">✕</button>
    </div>
</template>
