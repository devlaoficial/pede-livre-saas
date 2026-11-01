        <div x-cloak x-show="moduloAtivo === 'entregas'" class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Coluna Motoboys -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-bold text-base text-white">🛵 Equipe de Entregadores (Motoboys)</h2>
                            <p class="text-xs text-slate-400">Cadastre motoboys próprios ou freelancers para despacho</p>
                        </div>
                        <button @click="modalNovoEntregador = true" class="bg-rose-600 hover:bg-rose-500 text-white text-xs font-extrabold px-3.5 py-2 rounded-xl">
                            + Entregador
                        </button>
                    </div>
                    <div class="space-y-2.5">
                        <template x-for="ent in entregadores" :key="ent.id">
                            <div class="bg-slate-950 border border-slate-800 p-3.5 rounded-2xl flex items-center justify-between">
                                <div>
                                    <div class="font-bold text-sm text-white" x-text="ent.nome"></div>
                                    <div class="text-xs text-slate-400" x-text="(ent.tipo || 'proprio').toUpperCase() + ' • Taxa/corrida: ' + formatarCentavos(ent.valor_por_entrega_centavos)"></div>
                                </div>
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-500/15 text-emerald-400">Ativo</span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Coluna Áreas e Taxas de Entrega -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-bold text-base text-white">📍 Taxas de Entrega (Bairro &amp; Raio KM)</h2>
                            <p class="text-xs text-slate-400">Regras de frete calculadas automaticamente no checkout</p>
                        </div>
                        <button @click="modalNovaArea = true" class="bg-rose-600 hover:bg-rose-500 text-white text-xs font-extrabold px-3.5 py-2 rounded-xl">
                            + Nova Taxa
                        </button>
                    </div>
                    <div class="space-y-2.5">
                        <template x-for="area in areasEntrega" :key="area.id">
                            <div class="bg-slate-950 border border-slate-800 p-3.5 rounded-2xl flex items-center justify-between">
                                <div>
                                    <div class="font-bold text-sm text-white"
                                         x-text="area.tipo === 'bairro' ? 'Bairro: ' + area.nome_bairro : 'Raio: ' + area.raio_km_de + 'km até ' + area.raio_km_ate + 'km'"></div>
                                    <div class="text-xs text-slate-400" x-text="'Mínimo: ' + formatarCentavos(area.pedido_minimo_centavos) + ' • Tempo: ' + area.tempo_estimado_min + ' min'"></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-black text-emerald-400" x-text="formatarCentavos(area.taxa_centavos)"></div>
                                    <button @click="removerAreaEntrega(area.id)" class="text-[11px] text-rose-400 hover:underline">Remover</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
