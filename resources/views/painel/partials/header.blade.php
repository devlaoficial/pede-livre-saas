<!-- Header Superior Compacto (Design Claude Fiel) -->
<header class="bg-[#0b0f19] border-b border-[#151b2b] px-4 lg:px-6 py-4 flex flex-col md:flex-row md:items-center justify-between shrink-0 gap-3">
    <!-- Título e Subtítulo Data -->
    <div class="min-w-0">
        <h1 class="text-xl lg:text-2xl font-black text-white tracking-tight" x-text="tituloModulo()"></h1>
        <p class="text-xs text-slate-400 font-medium capitalize" x-text="dataHojeFormatada()"></p>
    </div>

    <!-- Controles & Badges da Direita -->
    <div class="flex flex-wrap items-center gap-2 shrink-0">
        <!-- Status Loja Aberta -->
        <div class="bg-[#131927] border border-[#1e2638] px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-300 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>Loja aberta</span>
        </div>

        <!-- Status WhatsApp -->
        <button @click="mudarModulo('whatsapp')"
            class="bg-[#131927] border border-[#1e2638] hover:border-slate-600 px-3.5 py-1.5 rounded-xl text-xs font-bold text-slate-300 flex items-center gap-2 transition">
            <span class="w-2 h-2 rounded-full" :class="whatsappConectado ? 'bg-emerald-400' : 'bg-amber-400'"></span>
            <span x-text="whatsappConectado ? 'WhatsApp conectado' : 'WhatsApp desconectado'"></span>
        </button>

        <!-- Sino de Alertas / Notificações -->
        <button @click="tocarChime()" class="bg-[#131927] border border-[#1e2638] hover:border-slate-600 p-2 rounded-xl text-slate-300 hover:text-white transition relative" title="Alertas da Loja">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            <span x-show="chamadosGarcom.length > 0" class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-amber-400 rounded-full animate-ping"></span>
        </button>

        <!-- Link do Cardápio -->
        <button @click="copiarLinkCardapio()"
            class="bg-[#131927] border border-[#1e2638] hover:border-slate-600 text-slate-300 hover:text-white px-3.5 py-1.5 rounded-xl text-xs font-bold flex items-center gap-1.5 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
            <span>Link do cardápio</span>
        </button>

        <!-- Botão Novo Pedido F2 -->
        <button @click="abrirModalPdv()"
            class="bg-[#f43f5e] hover:bg-[#e11d48] text-white text-xs font-black px-4 py-2 rounded-xl shadow-lg shadow-rose-600/30 flex items-center gap-1.5 transition active:scale-95">
            <span>+ Novo pedido · F2</span>
        </button>
    </div>
</header>

<!-- ALERTA AO VIVO: MESA CHAMANDO GARÇOM -->
<template x-if="chamadosGarcom.length > 0">
    <div class="bg-amber-500 text-slate-950 px-4 lg:px-6 py-2.5 flex flex-wrap items-center justify-between gap-2 shrink-0 shadow-lg z-30">
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

<!-- Toast Feedback -->
<template x-if="toastMsg">
    <div class="bg-emerald-500/20 border-b border-emerald-500/40 text-emerald-300 px-4 py-2 text-xs font-bold flex justify-between items-center shrink-0">
        <span x-text="'✓ ' + toastMsg"></span>
        <button @click="toastMsg = ''">✕</button>
    </div>
</template>
