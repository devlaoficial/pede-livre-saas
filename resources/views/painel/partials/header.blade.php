<!-- HEADER OPERACIONAL PREMIUM (Ultra Limpo no Celular & Completo no Desktop — 100% SVG) -->
<header class="bg-[#080c17]/95 backdrop-blur-xl border-b border-slate-800/80 px-3.5 lg:px-6 py-2.5 lg:py-3 flex items-center justify-between gap-2 shrink-0 pt-safe z-20">
    <!-- Esquerda: Avatar Mobile + Título do Módulo -->
    <div class="flex items-center gap-2.5 min-w-0">
        <!-- Botão Avatar Mobile (< lg) -->
        <button type="button" @click="menuMobileAberto = true"
                class="lg:hidden relative shrink-0 w-9 h-9 rounded-xl bg-gradient-to-br from-[#f43f5e] to-[#be123c] flex items-center justify-center text-white font-bold text-sm shadow-md shadow-rose-600/25 active:scale-95 transition">
            <span>P</span>
            <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-[#080c17]"
                  :class="onlineStatus ? 'bg-emerald-400' : 'bg-amber-400 animate-ping'"></span>
        </button>

        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <h1 class="text-sm sm:text-base lg:text-xl font-bold text-white tracking-tight truncate" x-text="tituloModulo()"></h1>
                <span class="hidden sm:inline-flex items-center gap-1.5 text-[10px] font-bold px-2 py-0.5 rounded-full border shrink-0"
                      :class="onlineStatus ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-amber-500/15 text-amber-300 border-amber-500/30'">
                    <span class="w-1.5 h-1.5 rounded-full" :class="onlineStatus ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400 animate-ping'"></span>
                    <span x-text="onlineStatus ? 'Ao vivo' : 'Reconectando'"></span>
                </span>
            </div>
            <p class="text-[10px] sm:text-[11px] text-slate-400 font-medium truncate" x-text="loja?.nome ? (loja.nome + ' • ' + dataHojeFormatada()) : dataHojeFormatada()"></p>
        </div>
    </div>

    <!-- Direita: Controles Enxutos no Mobile e Completos no Desktop -->
    <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
        <!-- 1. Status Loja Aberta / Pausada -->
        <button @click="alternarStatusLojaAberta()"
            title="Abrir ou pausar o recebimento de pedidos no cardápio digital"
            class="bg-[#111726] border px-2.5 sm:px-3.5 py-1.5 rounded-xl text-[11px] sm:text-xs font-semibold flex items-center gap-1.5 transition active:scale-95"
            :class="lojaAbertaManual !== false
                ? 'border-emerald-500/40 text-emerald-300 hover:bg-emerald-500/10'
                : 'border-rose-500/40 text-rose-300 hover:bg-rose-500/10'">
            <span class="w-2 h-2 rounded-full shrink-0" :class="lojaAbertaManual !== false ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400'"></span>
            <span x-text="lojaAbertaManual !== false ? 'Aberta' : 'Pausada'"></span>
        </button>

        <!-- 2. Status WhatsApp (Visível em Tablet/Desktop >= md) -->
        <button @click="mudarModulo('whatsapp')"
            class="hidden md:flex bg-[#111726] border border-slate-800 hover:border-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-300 items-center gap-2 transition">
            <span class="w-2 h-2 rounded-full" :class="whatsappConectado ? 'bg-emerald-400' : 'bg-amber-400'"></span>
            <span x-text="whatsappConectado ? 'WhatsApp ativo' : 'Conectar WhatsApp'"></span>
            <span x-show="totalAguardandoHumano > 0"
                  class="bg-amber-400 text-slate-950 font-bold text-[10px] px-1.5 py-0.2 rounded-full animate-pulse"
                  x-text="totalAguardandoHumano"></span>
        </button>

        <!-- 3. Status do Caixa (Desktop XL) -->
        <button @click="mudarModulo('financeiro')"
            class="hidden xl:flex bg-[#111726] border border-slate-800 hover:border-slate-700 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-300 items-center gap-1.5 transition">
            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            <span x-text="caixaStatus.aberto ? 'Caixa aberto' : 'Caixa fechado'"></span>
        </button>

        <!-- 4. Botão Wake Lock (Desktop/Tablet >= sm; no celular fica na gaveta Módulos) -->
        <button @click="alternarWakeLock()"
            :title="wakeLockAtivo ? 'Modo Tela Sempre Acesa ATIVO' : 'Ativar Modo Tela Sempre Acesa'"
            class="hidden sm:flex p-2 rounded-xl border text-xs font-bold transition items-center gap-1"
            :class="wakeLockAtivo ? 'bg-amber-500/15 border-amber-500/40 text-amber-300' : 'bg-[#111726] border-slate-800 text-slate-400 hover:text-white'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </button>

        <!-- 5. Sino de Alerta Sonoro (Mobile & Desktop) -->
        <button @click="alternarSom()"
            :title="somHabilitado ? 'Campainha ativada' : 'Campainha silenciada'"
            class="p-2 rounded-xl border transition relative"
            :class="somHabilitado ? 'bg-[#111726] border-slate-800 text-slate-200 hover:border-slate-700' : 'bg-rose-950/30 border-rose-500/30 text-rose-400'">
            <svg x-show="somHabilitado" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            <svg x-show="!somHabilitado" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"></path></svg>
            <span x-show="colunas.novo.length > 0 || chamadosGarcom.length > 0"
                  class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-rose-500 rounded-full animate-ping"></span>
        </button>

        <!-- 6. Copiar Link do Cardápio (Desktop >= sm) -->
        <button @click="copiarLinkCardapio()"
            title="Copiar link público do cardápio digital"
            class="hidden sm:flex bg-[#111726] border border-slate-800 hover:border-slate-700 text-slate-300 hover:text-white px-3 py-1.5 rounded-xl text-xs font-bold items-center gap-1.5 transition">
            <svg class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
            <span class="hidden xl:inline">Cardápio</span>
        </button>

        <!-- 7. Botão Novo Pedido PDV (Visível no Desktop >= lg; no Mobile já fica no centro da barra inferior) -->
        <button @click="abrirModalPdv()"
            class="hidden lg:flex bg-gradient-to-r from-[#f43f5e] to-[#e11d48] hover:from-[#fb7185] hover:to-[#f43f5e] text-white text-xs font-bold px-4 py-2 rounded-xl shadow-lg shadow-rose-600/25 items-center gap-1.5 transition active:scale-95">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            <span>Novo pedido</span>
            <kbd class="text-[10px] font-mono bg-black/25 px-1.5 py-0.2 rounded ml-0.5">F2</kbd>
        </button>
    </div>
</header>

<!-- BARRA DE ALERTA URGENTE: NOVOS PEDIDOS AGUARDANDO ACEITE (Compacta em 1 linha no Mobile) -->
<template x-if="colunas.novo.length > 0">
    <div class="bg-gradient-to-r from-rose-600 via-rose-500 to-amber-500 text-white px-3.5 lg:px-6 py-2 flex items-center justify-between gap-2 shrink-0 shadow-lg z-30">
        <div class="flex items-center gap-2 text-xs font-bold min-w-0">
            <span class="w-5 h-5 rounded-lg bg-white/20 flex items-center justify-center shrink-0 animate-bounce">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            </span>
            <span class="truncate" x-text="colunas.novo.length === 1 ? ('Novo pedido #' + colunas.novo[0]?.codigo_publico + ' aguardando!') : (colunas.novo.length + ' novos pedidos aguardando!')"></span>
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
            <button @click="mudarModulo('pedidos'); abaPedidoMobile = 'novo'; avancarStatus(colunas.novo[0])"
                class="bg-slate-950 hover:bg-slate-900 text-white font-bold px-3 py-1 rounded-xl text-[11px] shadow transition active:scale-95 flex items-center gap-1">
                <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                <span>Aceitar #<span x-text="colunas.novo[0]?.codigo_publico"></span></span>
            </button>
        </div>
    </div>
</template>

<!-- ALERTA AO VIVO: MESA CHAMANDO GARÇOM -->
<template x-if="chamadosGarcom.length > 0">
    <div class="bg-amber-500 text-slate-950 px-3.5 lg:px-6 py-2 flex items-center justify-between gap-2 shrink-0 shadow-lg z-30 overflow-x-auto no-scrollbar">
        <div class="flex items-center gap-2 text-xs font-bold shrink-0">
            <svg class="w-4 h-4 animate-bounce shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            <span>Garçom chamado:</span>
            <template x-for="ch in chamadosGarcom" :key="ch.mesa_id">
                <span class="bg-slate-950 text-amber-300 px-2.5 py-1 rounded-xl inline-flex items-center gap-2">
                    <span x-text="'Mesa #' + ch.mesa_numero"></span>
                    <button @click="atenderChamadoGarcom(ch.mesa_id)" class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold px-2 py-0.5 rounded-lg text-[10px]">
                        Atender
                    </button>
                </span>
            </template>
        </div>
    </div>
</template>

<!-- Toast Feedback -->
<template x-if="toastMsg">
    <div class="bg-emerald-500/15 border-b border-emerald-500/30 text-emerald-300 px-3.5 lg:px-6 py-2 text-xs font-bold flex justify-between items-center shrink-0 backdrop-blur-md">
        <span class="flex items-center gap-2 truncate">
            <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </span>
            <span class="truncate" x-text="toastMsg"></span>
        </span>
        <button @click="toastMsg = ''" class="text-emerald-400 hover:text-white px-1 shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
</template>
