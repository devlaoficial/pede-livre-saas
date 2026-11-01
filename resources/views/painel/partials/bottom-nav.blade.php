<!-- BARRA DE NAVEGAÇÃO INFERIOR PARA CELULAR (Mobile Bottom App Bar < lg) -->
<nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-slate-900/95 backdrop-blur-md border-t border-slate-800 grid grid-cols-6 py-1.5 px-1">
    <button @click="mudarModulo('pedidos')" class="flex flex-col items-center py-1 text-[10px] font-bold relative"
        :class="moduloAtivo === 'pedidos' ? 'text-rose-500' : 'text-slate-400'">
        <span class="text-base">📋</span>
        <span>Pedidos</span>
        <span x-show="colunas.novo.length > 0" class="absolute top-0.5 right-2 bg-rose-600 text-white text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center" x-text="colunas.novo.length"></span>
    </button>
    <button @click="mudarModulo('catalogo')" class="flex flex-col items-center py-1 text-[10px] font-bold"
        :class="moduloAtivo === 'catalogo' ? 'text-rose-500' : 'text-slate-400'">
        <span class="text-base">🍔</span>
        <span>Cardápio</span>
    </button>
    <button @click="mudarModulo('kds')" class="flex flex-col items-center py-1 text-[10px] font-bold"
        :class="moduloAtivo === 'kds' ? 'text-rose-500' : 'text-slate-400'">
        <span class="text-base">🍳</span>
        <span>KDS</span>
    </button>
    <button @click="mudarModulo('mesas')" class="flex flex-col items-center py-1 text-[10px] font-bold relative"
        :class="moduloAtivo === 'mesas' ? 'text-rose-500' : 'text-slate-400'">
        <span class="text-base">🪑</span>
        <span>Mesas</span>
        <span x-show="chamadosGarcom.length > 0" class="absolute top-0.5 right-2 bg-amber-400 text-slate-950 text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center" x-text="chamadosGarcom.length"></span>
    </button>
    <button @click="mudarModulo('crm')" class="flex flex-col items-center py-1 text-[10px] font-bold"
        :class="moduloAtivo === 'crm' ? 'text-rose-500' : 'text-slate-400'">
        <span class="text-base">👥</span>
        <span>CRM</span>
    </button>
    <button @click="mudarModulo('config')" class="flex flex-col items-center py-1 text-[10px] font-bold"
        :class="moduloAtivo === 'config' ? 'text-rose-500' : 'text-slate-400'">
        <span class="text-base">⚙️</span>
        <span>Loja</span>
    </button>
</nav>
