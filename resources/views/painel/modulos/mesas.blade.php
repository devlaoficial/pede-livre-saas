        <div x-cloak x-show="moduloAtivo === 'mesas'" class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-900 border border-slate-800 p-4 rounded-2xl">
                <div>
                    <h2 class="font-bold text-base text-white">Mesas, Comandas &amp; Divisão de Conta Inteligente</h2>
                    <p class="text-xs text-slate-400">O cliente escaneia o QR Code na mesa, pede sozinho, chama o garçom e você divide a conta por pessoa em 1 clique.</p>
                </div>
                <button @click="criarNovaMesa()" class="bg-rose-600 hover:bg-rose-500 text-white text-xs font-extrabold px-4 py-2.5 rounded-xl">
                    + Adicionar Mesa
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <template x-for="mesa in mesas" :key="mesa.id">
                    <div class="bg-slate-900 border rounded-2xl p-4 space-y-3 flex flex-col justify-between"
                        :class="chamadosGarcom.some(c => Number(c.mesa_id) === Number(mesa.id)) ? 'border-amber-400 shadow-lg shadow-amber-500/20' : 'border-slate-800'">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="text-lg font-black text-white" x-text="'🪑 Mesa #' + mesa.numero"></span>
                                <p class="text-xs text-slate-400" x-text="(mesa.nome || 'Salão Principal') + ' • ' + mesa.capacidade + ' lugares'"></p>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-[11px] font-bold uppercase px-2.5 py-0.5 rounded-full"
                                    :class="mesa.status === 'livre' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400'"
                                    x-text="mesa.status"></span>
                                <button @click="excluirMesa(mesa.id)" title="Excluir Mesa" class="text-xs text-slate-500 hover:text-rose-400 px-1">✕</button>
                            </div>
                        </div>

                        <template x-if="chamadosGarcom.some(c => Number(c.mesa_id) === Number(mesa.id))">
                            <div class="bg-amber-500 text-slate-950 rounded-xl p-2.5 flex items-center justify-between text-xs font-black">
                                <span>🔔 Chamando Garçom!</span>
                                <button @click="atenderChamadoGarcom(mesa.id)" class="bg-slate-950 text-amber-300 px-2.5 py-1 rounded-lg text-[10px]">✓ Atender</button>
                            </div>
                        </template>

                        <div class="bg-white p-2.5 rounded-2xl flex items-center justify-center mx-auto w-32 h-32">
                            <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(window.location.origin + mesa.qr_url)"
                                 :alt="'QR Mesa ' + mesa.numero" class="w-full h-full object-contain">
                        </div>

                        <div class="grid grid-cols-3 gap-1.5 pt-1">
                            <a :href="mesa.qr_url" target="_blank" class="bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 border border-emerald-500/30 text-center font-bold py-2 rounded-xl text-[11px]">
                                Cardápio ↗
                            </a>
                            <button @click="abrirDivisaoMesa(mesa)" class="bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 border border-rose-500/30 font-bold py-2 rounded-xl text-[11px]">
                                🧮 Dividir
                            </button>
                            <button @click="regenerarQrMesa(mesa.id)" class="bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-[11px] font-bold py-2" title="Regenerar Token QR">
                                🔄 QR
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
