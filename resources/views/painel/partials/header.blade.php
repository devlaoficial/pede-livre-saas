<!-- Header Superior Compacto -->
<header class="bg-slate-900 border-b border-slate-800 px-4 lg:px-6 py-3 flex items-center justify-between shrink-0 gap-2">
    <div class="flex items-center gap-2.5 min-w-0">
        <a href="/" class="lg:hidden text-base font-black text-white shrink-0">Pede<span class="text-rose-500">Livre</span></a>
        <h1 class="text-xs sm:text-base font-extrabold text-white truncate" x-text="tituloModulo()"></h1>
    </div>

    <div class="flex items-center gap-2 shrink-0">
        <button @click="mudarModulo('whatsapp')" class="flex items-center gap-1.5 text-xs border px-3 py-1.5 rounded-xl font-bold transition shadow-sm relative"
            :class="whatsappConectado ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/20' : 'bg-slate-800/80 border-slate-700 text-slate-300 hover:border-slate-600'">
            <span>💬</span>
            <span class="hidden md:inline" x-text="whatsappConectado ? 'WhatsApp Ativo' : 'Conectar WhatsApp'"></span>
            <span class="w-2 h-2 rounded-full" :class="whatsappConectado ? 'bg-emerald-400 animate-pulse' : 'bg-slate-500'"></span>
            <span x-show="totalAguardandoHumano > 0" class="bg-rose-600 text-white text-[9px] font-black px-1.5 py-0.2 rounded-full animate-bounce" x-text="totalAguardandoHumano"></span>
        </button>
        <button @click="tocarChime()" class="hidden sm:inline-flex items-center text-xs bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 border border-amber-500/30 px-3 py-1.5 rounded-xl font-bold transition" title="Testar toque de campainha">
            📞 Testar Toque
        </button>
        <button @click="copiarLinkCardapio()" class="hidden md:inline-flex items-center gap-1.5 text-xs bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 px-3 py-1.5 rounded-xl font-bold transition">
            <span>🔗 Copiar Link</span>
        </button>
        <button @click="abrirModalPdv()" class="bg-rose-600 hover:bg-rose-500 text-white text-xs font-extrabold px-3 py-1.5 rounded-xl shadow-md shadow-rose-600/20 transition">
            + Pedido PDV
        </button>
        <a :href="'/p/' + (loja?.slug || 'brasa-artesanal')" target="_blank"
            class="hidden sm:inline-flex text-xs bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 px-3 py-1.5 rounded-xl font-bold hover:bg-emerald-500/25">
            Cardápio ↗
        </a>
        <button @click="atualizarTudo()" class="bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold px-2.5 py-1.5 rounded-xl" title="Atualizar Dados">
            ↻
        </button>
        <button @click="sair()" class="lg:hidden bg-slate-800 hover:bg-rose-600/20 text-slate-300 text-xs font-bold px-2.5 py-1.5 rounded-xl">
            Sair
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
