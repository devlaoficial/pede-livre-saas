<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>Painel Master do Dono do SaaS — PedeLivre</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        html, body { font-family: 'Inter', system-ui, -apple-system, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; text-rendering: optimizeLegibility; font-synthesis: none; }
        .font-extrabold { font-weight: 700; }
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body x-cloak x-show="autenticado" class="h-full flex flex-col lg:flex-row antialiased font-sans overflow-hidden" x-data="superAdminApp()" x-init="iniciar()">

    <!-- SIDEBAR FIXA PARA PC (Desktop lg:flex) -->
    <aside class="hidden lg:flex lg:flex-col lg:w-72 bg-slate-900 border-r border-slate-800 shrink-0 justify-between">
        <div class="p-5 space-y-6">
            <div class="flex items-center justify-between">
                <a href="/" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-rose-600 to-amber-500 flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-rose-600/20">P</div>
                    <div>
                        <span class="text-xl font-bold tracking-tight text-white">Pede<span class="text-rose-500">Livre</span></span>
                        <span class="block text-[10px] font-bold uppercase tracking-widest text-amber-400">SaaS Master OS</span>
                    </div>
                </a>
                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">Produção</span>
            </div>

            <!-- Resumo Rápido de MRR na Sidebar -->
            <div class="bg-gradient-to-br from-slate-950 to-slate-900 border border-slate-800 p-4 rounded-2xl space-y-2">
                <div class="flex items-center justify-between text-[11px] text-slate-400 font-bold uppercase">
                    <span>MRR Ativo</span>
                    <span class="text-emerald-400">● Online</span>
                </div>
                <div class="text-2xl font-bold text-white" x-text="formatarCentavos(metricas.mrr_centavos || 0)"></div>
                <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1 border-t border-slate-800/80">
                    <span x-text="(metricas.total_lojas || 0) + ' Lojas Cadastradas'"></span>
                    <span class="text-amber-400 font-bold" x-text="'ARR ' + formatarCentavos(metricas.arr_centavos || 0)"></span>
                </div>
            </div>

            <!-- Navegação Principal -->
            <nav class="space-y-1.5">
                <button @click="abaAtiva = 'dashboard'"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-xs font-bold transition"
                    :class="abaAtiva === 'dashboard' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                    <span class="flex items-center gap-2.5"><span><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg></span> Visão Geral &amp; KPIs</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-black/20">CEO</span>
                </button>

                <button @click="abaAtiva = 'lojas'"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-xs font-bold transition"
                    :class="abaAtiva === 'lojas' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                    <span class="flex items-center gap-2.5"><span><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg></span> Lojas &amp; Tenants</span>
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-black/20" x-text="lojas.length"></span>
                </button>

                <button @click="abaAtiva = 'planos'"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-xs font-bold transition"
                    :class="abaAtiva === 'planos' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                    <span class="flex items-center gap-2.5"><span><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg></span> Planos &amp; Preços SaaS</span>
                    <span class="text-[11px] opacity-75" x-text="(metricas.planos || []).length"></span>
                </button>

                <button @click="abaAtiva = 'monitoramento'; carregarMonitoramento()"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-xs font-bold transition"
                    :class="abaAtiva === 'monitoramento' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                    <span class="flex items-center gap-2.5"><span><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></span> Monitoramento &amp; Backup</span>
                    <span class="w-2 h-2 rounded-full" :class="monitoramento.status === 'healthy' ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400 animate-ping'"></span>
                </button>

                <button @click="abaAtiva = 'auditoria'"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-xs font-bold transition"
                    :class="abaAtiva === 'auditoria' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                    <span class="flex items-center gap-2.5"><span><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg></span> Auditoria &amp; Webhooks</span>
                </button>
            </nav>
        </div>

        <!-- Links Rápidos & Rodapé Sidebar -->
        <div class="p-4 border-t border-slate-800 space-y-2">
            <template x-if="lojas.length > 0">
                <a :href="'/p/' + lojas[0].slug" target="_blank" class="w-full flex items-center justify-center gap-2 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 px-3 py-2 rounded-xl text-xs font-bold transition">
                    <span><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg></span> <span x-text="'Ver Cardápio: ' + lojas[0].nome"></span> ↗
                </a>
            </template>
            <button @click="sair()" class="w-full flex items-center justify-center gap-2 bg-slate-800 hover:bg-rose-600/20 text-slate-300 hover:text-rose-300 px-3 py-2.5 rounded-xl text-xs font-bold transition">
                Sair do Painel Master
            </button>
        </div>
    </aside>

    <!-- ÁREA CENTRAL AUTO-ADAPTATIVA -->
    <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">

        <!-- Header Superior -->
        <header class="bg-slate-900 border-b border-slate-800 px-4 lg:px-8 py-3.5 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <a href="/" class="lg:hidden flex items-center gap-1.5 font-bold text-white text-base">
                    <span class="w-7 h-7 rounded-lg bg-rose-600 flex items-center justify-center text-xs">P</span>
                    <span>SaaS<span class="text-rose-500">Admin</span></span>
                </a>
                <div>
                    <h1 class="text-sm sm:text-lg font-bold text-white" x-text="tituloAba()"></h1>
                    <p class="hidden sm:block text-xs text-slate-400">Controle global de multi-tenancy, faturamento recorrente, planos e impersonação auditada</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <button @click="modalNovaLoja = true" class="bg-rose-600 hover:bg-rose-500 text-white text-xs font-extrabold px-3.5 py-2 rounded-xl shadow-md shadow-rose-600/20 transition">
                    + Nova Loja Cliente
                </button>
                <button @click="carregarTudo()" class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold px-3 py-2 rounded-xl">
                    ↻
                </button>
                <button @click="sair()" class="bg-slate-800 hover:bg-rose-600/20 text-slate-300 hover:text-rose-300 text-xs font-bold px-3 py-2 rounded-xl transition">
                    Sair
                </button>
            </div>
        </header>

        <!-- Conteúdo com Scroll -->
        <main class="flex-1 overflow-y-auto p-4 lg:p-8 pb-24 space-y-6">

            <!-- Notificação Toast -->
            <template x-if="avisoSucesso">
                <div class="bg-emerald-500/15 border border-emerald-500/40 text-emerald-300 px-4 py-3 rounded-2xl text-xs font-bold flex items-center justify-between">
                    <span x-text="'' + avisoSucesso"></span>
                    <button @click="avisoSucesso = ''" class="text-emerald-400 font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
            </template>

            <!-- ========================================================= -->
            <!-- ABA 1: VISÃO GERAL & KPIS DO SAAS                         -->
            <!-- ========================================================= -->
            <div x-show="abaAtiva === 'dashboard'" class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">MRR (Receita Mensal SaaS)</span>
                            <span class="bg-emerald-500/15 text-emerald-400 text-[11px] font-bold px-2.5 py-0.5 rounded-full">Recorrente</span>
                        </div>
                        <div class="text-3xl font-bold text-emerald-400" x-text="formatarCentavos(metricas.mrr_centavos || 0)"></div>
                        <div class="text-xs text-slate-400 flex justify-between pt-1 border-t border-slate-800/70">
                            <span>ARR Projetado (12m):</span>
                            <strong class="text-white" x-text="formatarCentavos(metricas.arr_centavos || 0)"></strong>
                        </div>
                    </div>

                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">GMV Transacionado nas Lojas</span>
                            <span class="bg-rose-500/15 text-rose-400 text-[11px] font-bold px-2.5 py-0.5 rounded-full">0% Comissão</span>
                        </div>
                        <div class="text-3xl font-bold text-white" x-text="formatarCentavos(metricas.gmv_total_centavos || 0)"></div>
                        <div class="text-xs text-slate-400 flex justify-between pt-1 border-t border-slate-800/70">
                            <span>Pedidos Processados:</span>
                            <strong class="text-rose-400" x-text="(metricas.total_pedidos_plataforma || 0) + ' pedidos'"></strong>
                        </div>
                    </div>

                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Base de Lojas (Tenants)</span>
                            <span class="bg-amber-500/15 text-amber-400 text-[11px] font-bold px-2.5 py-0.5 rounded-full" x-text="(metricas.total_lojas || 0) + ' total'"></span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 pt-1">
                            <div class="bg-slate-950 p-2.5 rounded-xl text-center border border-slate-800">
                                <div class="text-lg font-bold text-emerald-400" x-text="metricas.lojas_ativas || 0"></div>
                                <div class="text-[10px] text-slate-400 uppercase font-bold">Ativas</div>
                            </div>
                            <div class="bg-slate-950 p-2.5 rounded-xl text-center border border-slate-800">
                                <div class="text-lg font-bold text-sky-400" x-text="metricas.lojas_trial || 0"></div>
                                <div class="text-[10px] text-slate-400 uppercase font-bold">Trial 14d</div>
                            </div>
                            <div class="bg-slate-950 p-2.5 rounded-xl text-center border border-slate-800">
                                <div class="text-lg font-bold text-rose-400" x-text="(metricas.lojas_inadimplentes || 0) + (metricas.lojas_suspensas || 0)"></div>
                                <div class="text-[10px] text-slate-400 uppercase font-bold">Pendentes</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Distribuição por Plano + Automação da Régua de Cobrança -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <div class="lg:col-span-7 bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="font-bold text-base text-white">Receita por Plano de Assinatura</h2>
                                <p class="text-xs text-slate-400">Performance comercial de cada tier do PedeLivre</p>
                            </div>
                            <button @click="abaAtiva = 'planos'" class="text-xs font-bold text-rose-400 hover:underline">Editar Preços →</button>
                        </div>

                        <div class="space-y-3">
                            <template x-for="pl in (metricas.planos || [])" :key="pl.id">
                                <div class="bg-slate-950 border border-slate-800 p-3.5 rounded-2xl flex items-center justify-between gap-4">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-sm uppercase text-white" x-text="'Plano ' + pl.nome"></span>
                                            <span class="text-xs text-slate-400" x-text="formatarCentavos(pl.preco_centavos) + '/mês'"></span>
                                        </div>
                                        <div class="text-xs text-slate-500 mt-0.5" x-text="pl.lojas_count + ' loja(s) ativa(s) neste plano'"></div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-bold text-emerald-400" x-text="formatarCentavos(pl.mrr_plano_centavos)"></div>
                                        <div class="text-[10px] text-slate-500 uppercase font-bold">MRR do Plano</div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="lg:col-span-5 bg-slate-900 border border-slate-800 rounded-3xl p-5 flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-400">Automação Financeira &amp; Infra</span>
                            <h3 class="text-base font-bold text-white">Régua de Inadimplência (3, 7 e 15 dias)</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                O motor verifica faturas vencidas: envia lembrete com +3 dias, ativa Modo Somente Leitura (HTTP 402) com +7 dias e suspende o cardápio público com +15 dias.
                            </p>
                            <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3 space-y-1.5 text-xs">
                                <div class="flex justify-between"><span class="text-slate-400">Mercado Pago &amp; PIX BCB:</span><span class="text-emerald-400 font-bold">Ativo (HMAC-SHA256)</span></div>
                                <div class="flex justify-between"><span class="text-slate-400">Banco de Dados &amp; Cache:</span><span class="text-emerald-400 font-bold">Multi-Tenant Isolado</span></div>
                                <div class="flex justify-between"><span class="text-slate-400">Criptografia de Dados:</span><span class="text-emerald-400 font-bold">AES-256-GCM + Blind Index</span></div>
                            </div>
                        </div>

                        <button @click="executarRegua()" class="w-full bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-3 rounded-xl text-xs transition shadow-lg shadow-amber-500/10">
                            Executar Régua de Inadimplência Agora
                        </button>
                    </div>
                </div>

                <!-- Lista Rápida de Lojas na Dashboard -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="font-bold text-base text-white">Lojas Clientes em Operação</h2>
                            <p class="text-xs text-slate-400">Acesse o painel de qualquer cliente com 1 clique via Impersonação Auditada</p>
                        </div>
                        <button @click="abaAtiva = 'lojas'" class="text-xs font-bold text-rose-400 hover:underline">Ver Todas →</button>
                    </div>

                    <template x-if="lojas.length === 0">
                        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-8 text-center space-y-3">
                            <div class="text-3xl"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg></div>
                            <h4 class="font-extrabold text-white text-sm">Nenhuma loja cadastrada ainda</h4>
                            <p class="text-xs text-slate-400 max-w-md mx-auto">Clique em "+ Nova Loja Cliente" no topo para cadastrar seus primeiros restaurantes clientes em produção.</p>
                            <button @click="modalNovaLoja = true" class="bg-rose-600 hover:bg-rose-500 text-white text-xs font-extrabold px-4 py-2.5 rounded-xl">
                                + Cadastrar Primeira Loja Cliente
                            </button>
                        </div>
                    </template>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <template x-for="l in lojas.slice(0, 6)" :key="l.id">
                            <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between gap-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-sm text-white" x-text="l.nome"></span>
                                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full"
                                                  :class="l.status === 'ativa' ? 'bg-emerald-500/20 text-emerald-400' : (l.status === 'trial' ? 'bg-sky-500/20 text-sky-400' : 'bg-rose-500/20 text-rose-400')"
                                                  x-text="l.status"></span>
                                        </div>
                                        <p class="text-xs text-slate-400 mt-0.5" x-text="'/p/' + l.slug + ' • ' + (l.dono_nome || 'Dono')"></p>
                                    </div>
                                    <span class="text-xs font-extrabold uppercase px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 text-rose-400" x-text="l.plano_nome"></span>
                                </div>

                                <div class="flex items-center justify-between text-xs text-slate-400 pt-2 border-t border-slate-900">
                                    <span x-text="l.total_pedidos + ' pedidos (' + formatarCentavos(l.gmv_centavos) + ')'"></span>
                                    <span :class="l.mp_conectado ? 'text-emerald-400 font-bold' : 'text-amber-400'"
                                          x-text="l.mp_conectado ? 'PIX Produção' : 'PIX Direto'"></span>
                                </div>

                                <div class="flex gap-2 pt-1">
                                    <button @click="impersonarLoja(l)" class="flex-1 bg-rose-600 hover:bg-rose-500 text-white text-xs font-extrabold py-2 rounded-xl transition">
                                        Acessar Painel da Loja
                                    </button>
                                    <button @click="abrirModalEditarLoja(l)" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl">
                                        Editar
                                    </button>
                                    <a :href="'/p/' + l.slug" target="_blank" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-emerald-400 text-xs font-bold rounded-xl">
                                        ↗
                                    </a>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- ABA 2: GESTÃO COMPLETA DE LOJAS (TENANTS)                 -->
            <!-- ========================================================= -->
            <div x-cloak x-show="abaAtiva === 'lojas'" class="space-y-5">
                <div class="bg-slate-900 border border-slate-800 p-4 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="relative flex-1 max-w-md">
                        <input type="text" x-model="buscaLoja" placeholder="Buscar loja por nome, slug, proprietário ou cidade..."
                            class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-9 pr-4 py-2 text-xs text-white focus:border-rose-500 focus:outline-none">
                        <span class="absolute left-3 top-2 text-slate-500 text-xs"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></span>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="st in ['todas', 'ativa', 'trial', 'inadimplente', 'suspensa']" :key="st">
                            <button @click="filtroStatusLoja = st"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold uppercase transition"
                                :class="filtroStatusLoja === st ? 'bg-rose-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white'"
                                x-text="st"></button>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3">
                    <template x-for="loja in lojasFiltradas" :key="loja.id">
                        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <div class="space-y-1 min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-base font-bold text-white" x-text="loja.nome"></span>
                                    <span class="text-[10px] font-bold uppercase px-2.5 py-0.5 rounded-full"
                                          :class="loja.status === 'ativa' ? 'bg-emerald-500/20 text-emerald-400' : (loja.status === 'trial' ? 'bg-sky-500/20 text-sky-400' : 'bg-rose-500/20 text-rose-400')"
                                          x-text="loja.status"></span>
                                    <span class="text-[10px] font-bold uppercase px-2.5 py-0.5 rounded-full bg-rose-500/15 text-rose-400 border border-rose-500/30"
                                          x-text="'Plano ' + loja.plano_nome + ' (' + formatarCentavos(loja.plano_preco_centavos) + ')'"></span>
                                </div>
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-400">
                                    <span><a :href="'/p/' + loja.slug" target="_blank" class="text-emerald-400 hover:underline" x-text="'/p/' + loja.slug"></a></span>
                                    <span x-text="'' + loja.dono_nome + (loja.dono_email ? ' (' + loja.dono_email + ')' : '')"></span>
                                    <span x-text="'' + (loja.telefone_whatsapp || '-')"></span>
                                    <span x-text="'' + (loja.cidade || '-') + '/' + (loja.uf || '-')"></span>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-between lg:justify-end gap-3 pt-3 lg:pt-0 border-t lg:border-t-0 border-slate-800 shrink-0">
                                <div class="text-left lg:text-right pr-2">
                                    <div class="text-sm font-bold text-emerald-400" x-text="formatarCentavos(loja.gmv_centavos)"></div>
                                    <div class="text-[11px] text-slate-400" x-text="loja.total_pedidos + ' pedidos'"></div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button @click="impersonarLoja(loja)" class="bg-rose-600 hover:bg-rose-500 text-white text-xs font-extrabold px-3.5 py-2 rounded-xl transition">
                                        Entrar no Painel
                                    </button>
                                    <button @click="abrirModalEditarLoja(loja)" class="bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold px-3.5 py-2 rounded-xl transition">
                                        Plano / Status
                                    </button>
                                    <a :href="'/p/' + loja.slug" target="_blank" class="bg-slate-800 hover:bg-slate-700 text-emerald-400 text-xs font-bold px-3 py-2 rounded-xl">
                                        Cardápio ↗
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- ABA 3: PLANOS & PREÇOS DO SAAS                            -->
            <!-- ========================================================= -->
            <div x-cloak x-show="abaAtiva === 'planos'" class="space-y-6">
                <div class="bg-slate-900 border border-slate-800 p-5 rounded-3xl">
                    <h2 class="font-bold text-base text-white">Configuração de Planos e Mensalidades do SaaS</h2>
                    <p class="text-xs text-slate-400 mt-1">Altere o valor mensal ou o limite de usuários de qualquer plano comercializado na plataforma.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <template x-for="pl in (metricas.planos || [])" :key="pl.id">
                        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 flex flex-col justify-between space-y-5">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-lg font-bold uppercase text-white" x-text="'Plano ' + pl.nome"></span>
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-rose-500/15 text-rose-400" x-text="pl.lojas_count + ' lojas'"></span>
                                </div>

                                <div>
                                    <label class="text-[11px] font-bold uppercase text-slate-400 block mb-1">Mensalidade em Centavos (Ex: 24990 = R$ 249,90)</label>
                                    <input type="number" x-model.number="pl.preco_centavos" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm font-bold text-emerald-400">
                                    <span class="text-[11px] text-slate-500 mt-1 block" x-text="'Valor exibido: ' + formatarCentavos(pl.preco_centavos) + '/mês'"></span>
                                </div>

                                <div>
                                    <label class="text-[11px] font-bold uppercase text-slate-400 block mb-1">Limite de Usuários / Garçons</label>
                                    <input type="number" x-model.number="pl.limite_usuarios" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm font-bold text-white">
                                </div>
                            </div>

                            <button @click="salvarPlano(pl)" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-extrabold py-3 rounded-xl text-xs transition">
                                Salvar Alterações do Plano
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- ABA 4: AUDITORIA IMUTÁVEL & WEBHOOKS                      -->
            <!-- ========================================================= -->
            <div x-cloak x-show="abaAtiva === 'auditoria'" class="space-y-6">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-3">
                    <h2 class="font-bold text-base text-white">Endpoints Oficiais de Webhook (Produção SSL)</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                        <div class="bg-slate-950 border border-slate-800 p-3.5 rounded-2xl flex items-center justify-between gap-2">
                            <div class="truncate">
                                <span class="text-slate-400 block font-bold">Webhook PIX Mercado Pago (HMAC-SHA256):</span>
                                <code class="text-emerald-400 font-mono" x-text="window.location.origin + '/api/webhooks/mercadopago'"></code>
                            </div>
                            <button @click="navigator.clipboard.writeText(window.location.origin + '/api/webhooks/mercadopago'); avisoSucesso = 'URL do Webhook Mercado Pago copiada!'" class="bg-slate-800 hover:bg-slate-700 text-white px-3 py-1.5 rounded-xl font-bold shrink-0">Copiar</button>
                        </div>
                        <div class="bg-slate-950 border border-slate-800 p-3.5 rounded-2xl flex items-center justify-between gap-2">
                            <div class="truncate">
                                <span class="text-slate-400 block font-bold">Webhook WhatsApp Evolution API:</span>
                                <code class="text-emerald-400 font-mono" x-text="window.location.origin + '/api/webhooks/whatsapp/evolution'"></code>
                            </div>
                            <button @click="navigator.clipboard.writeText(window.location.origin + '/api/webhooks/whatsapp/evolution'); avisoSucesso = 'URL do Webhook WhatsApp copiada!'" class="bg-slate-800 hover:bg-slate-700 text-white px-3 py-1.5 rounded-xl font-bold shrink-0">Copiar</button>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                    <h2 class="font-bold text-base text-white">Trilha de Auditoria Imutável (Segurança &amp; LGPD)</h2>
                    <div class="space-y-2">
                        <template x-if="(metricas.auditorias_recentes || []).length === 0">
                            <p class="text-xs text-slate-500 py-6 text-center">Nenhum evento administrativo registrado ainda.</p>
                        </template>
                        <template x-for="log in (metricas.auditorias_recentes || [])" :key="log.id">
                            <div class="bg-slate-950 border border-slate-800 rounded-xl p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-2.5">
                                    <span class="px-2.5 py-0.5 rounded-full font-bold text-[10px] uppercase bg-rose-500/15 text-rose-400 border border-rose-500/30" x-text="log.acao"></span>
                                    <span class="text-slate-300 font-semibold" x-text="'Entidade: ' + log.entidade + ' #' + (log.entidade_id || '-')"></span>
                                </div>
                                <span class="text-slate-500 font-mono text-[11px]" x-text="log.created_at"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- ABA 5: MONITORAMENTO 24/7, ALERTAS DE QUEDA & BACKUPS      -->
            <!-- ========================================================= -->
            <div x-cloak x-show="abaAtiva === 'monitoramento'" class="space-y-6">
                <!-- 4 Cards de Saúde da Infraestrutura em Tempo Real -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase text-slate-400">Banco de Dados</span>
                            <span class="w-2.5 h-2.5 rounded-full" :class="monitoramento.checks?.database?.status === 'ok' ? 'bg-emerald-400' : 'bg-rose-500 animate-ping'"></span>
                        </div>
                        <div class="text-base font-bold text-white" x-text="monitoramento.checks?.database?.status === 'ok' ? 'Operacional' : 'Falha Crítica'"></div>
                        <p class="text-[11px] text-slate-400" x-text="monitoramento.checks?.database?.message || 'Verificando...'"></p>
                    </div>

                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase text-slate-400">Cache &amp; Sessões</span>
                            <span class="w-2.5 h-2.5 rounded-full" :class="monitoramento.checks?.cache?.status === 'ok' ? 'bg-emerald-400' : 'bg-rose-500 animate-ping'"></span>
                        </div>
                        <div class="text-base font-bold text-white" x-text="monitoramento.checks?.cache?.status === 'ok' ? 'Operacional' : 'Degradado'"></div>
                        <p class="text-[11px] text-slate-400" x-text="monitoramento.checks?.cache?.message || 'Verificando...'"></p>
                    </div>

                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase text-slate-400">Disco &amp; Storage</span>
                            <span class="w-2.5 h-2.5 rounded-full" :class="monitoramento.checks?.storage?.status === 'ok' ? 'bg-emerald-400' : 'bg-amber-400'"></span>
                        </div>
                        <div class="text-base font-bold text-emerald-400" x-text="(monitoramento.checks?.storage?.free_gb || '-') + ' GB livres'"></div>
                        <p class="text-[11px] text-slate-400" x-text="monitoramento.checks?.storage?.message || 'Verificando...'"></p>
                    </div>

                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase text-slate-400">Gateway WhatsApp</span>
                            <span class="w-2.5 h-2.5 rounded-full" :class="monitoramento.checks?.whatsapp_gateway?.status === 'ok' ? 'bg-emerald-400' : 'bg-amber-400'"></span>
                        </div>
                        <div class="text-base font-bold text-white" x-text="monitoramento.checks?.whatsapp_gateway?.status === 'ok' ? 'Conectado' : 'Verificar Porta'"></div>
                        <p class="text-[11px] text-slate-400" x-text="monitoramento.checks?.whatsapp_gateway?.message || 'Verificando...'"></p>
                    </div>
                </div>

                <!-- Grid Principal: Configuração de Alertas (E-mail + WhatsApp) & Central de Backups -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Coluna Esquerda: Alertas de Queda e Erros Críticos -->
                    <div class="lg:col-span-6 bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                            <div>
                                <h2 class="font-bold text-base text-white">Alertas de Queda &amp; Suporte (E-mail + WhatsApp)</h2>
                                <p class="text-xs text-slate-400">Receba avisos imediatos se qualquer serviço cair ou ocorrer erro 500, além dos backups diários no seu e-mail.</p>
                            </div>
                        </div>

                        <form @submit.prevent="salvarConfigMonitoramento()" class="space-y-3.5 text-xs">
                            <div>
                                <label class="font-bold text-slate-300 block mb-1">Seu E-mail Principal (Para receber Backups .ZIP e Alertas de Queda)</label>
                                <input type="email" x-model="configMonitoramento.alert_email" placeholder="Ex: seuemail@dominio.com.br"
                                       class="w-full bg-slate-950 border border-slate-800 focus:border-rose-500 rounded-xl px-3.5 py-2.5 text-white font-semibold focus:outline-none">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="font-bold text-slate-300 block mb-1">Seu WhatsApp de Plantão (Com DDD)</label>
                                    <input type="text" x-model="configMonitoramento.alert_whatsapp" placeholder="Ex: 31999999999"
                                           class="w-full bg-slate-950 border border-slate-800 focus:border-rose-500 rounded-xl px-3.5 py-2.5 text-white font-semibold focus:outline-none">
                                </div>
                                <div>
                                    <label class="font-bold text-slate-300 block mb-1">Horário do Backup Diário</label>
                                    <input type="time" x-model="configMonitoramento.backup_horario"
                                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-semibold">
                                </div>
                            </div>

                            <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 space-y-2.5">
                                <label class="flex items-center justify-between cursor-pointer">
                                    <span class="text-slate-200 font-semibold">Alertar no WhatsApp e E-mail se algum serviço cair</span>
                                    <input type="checkbox" x-model="configMonitoramento.alerta_queda_ativo" class="rounded text-rose-600">
                                </label>
                                <label class="flex items-center justify-between cursor-pointer">
                                    <span class="text-slate-200 font-semibold">Alertar automaticamente em caso de Erro Crítico (HTTP 500)</span>
                                    <input type="checkbox" x-model="configMonitoramento.alerta_erros_500_ativo" class="rounded text-rose-600">
                                </label>
                                <label class="flex items-center justify-between cursor-pointer">
                                    <span class="text-slate-200 font-semibold">Enviar cópia do Backup (.ZIP) diariamente por E-mail</span>
                                    <input type="checkbox" x-model="configMonitoramento.backup_enviar_email" class="rounded text-emerald-500">
                                </label>
                                <label class="flex items-center justify-between cursor-pointer">
                                    <span class="text-slate-200 font-semibold">Avisar no WhatsApp quando o Backup diário terminar</span>
                                    <input type="checkbox" x-model="configMonitoramento.backup_enviar_resumo_whatsapp" class="rounded text-emerald-500">
                                </label>
                            </div>

                            <div class="bg-slate-950 border border-slate-800/80 rounded-2xl p-3 flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <span class="text-[11px] font-bold text-slate-400 block">Endpoint Público de Healthcheck (UptimeRobot / Cron):</span>
                                    <code class="text-emerald-400 text-[11px] truncate block" x-text="window.location.origin + '/api/health'"></code>
                                </div>
                                <button type="button" @click="navigator.clipboard.writeText(window.location.origin + '/api/health'); avisoSucesso = 'Link de Healthcheck copiado!'"
                                        class="bg-slate-800 hover:bg-slate-700 text-white px-3 py-1.5 rounded-xl font-bold shrink-0">
                                    Copiar
                                </button>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-2.5 pt-1">
                                <button type="submit" :disabled="salvandoMonitoramento"
                                        class="flex-1 bg-rose-600 hover:bg-rose-500 disabled:opacity-50 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-rose-600/20">
                                    <span x-text="salvandoMonitoramento ? 'Salvando...' : 'Salvar Destinos de Alerta & Backup'"></span>
                                </button>
                                <button type="button" @click="dispararTesteAlerta()" :disabled="testandoAlerta"
                                        class="bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-bold px-4 py-3 rounded-xl transition">
                                    <span x-text="testandoAlerta ? 'Enviando...' : 'Testar Alerta Agora'"></span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Coluna Direita: Central de Backups (SQL + JSON em .ZIP) -->
                    <div class="lg:col-span-6 bg-slate-900 border border-slate-800 rounded-3xl p-6 flex flex-col justify-between space-y-4">
                        <div class="space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-3">
                                <div>
                                    <h2 class="font-bold text-base text-white">Backups do Sistema (SQL + JSON + E-mail)</h2>
                                    <p class="text-xs text-slate-400">Gere um backup completo agora mesmo (enviado direto para o seu e-mail) ou baixe qualquer pacote salvo.</p>
                                </div>
                                <button @click="gerarBackupManual(true)" :disabled="gerandoBackup"
                                        class="bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-lg shadow-emerald-600/20 shrink-0 transition">
                                    <span x-text="gerandoBackup ? '⏳ Gerando Backup...' : 'Gerar Backup & Enviar p/ E-mail'"></span>
                                </button>
                            </div>

                            <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                                <template x-if="(monitoramento.backups || []).length === 0">
                                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-8 text-center space-y-2">
                                        <div class="text-2xl"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg></div>
                                        <div class="text-xs font-bold text-white">Nenhum backup gerado ainda</div>
                                        <p class="text-[11px] text-slate-400">Clique em "Gerar Backup &amp; Enviar p/ E-mail" acima para criar o primeiro ponto de restauração completo.</p>
                                    </div>
                                </template>

                                <template x-for="bkp in (monitoramento.backups || [])" :key="bkp.arquivo">
                                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 flex items-center justify-between gap-3 text-xs">
                                        <div class="min-w-0">
                                            <div class="font-bold text-white truncate" x-text="bkp.arquivo"></div>
                                            <div class="text-[11px] text-slate-400 mt-0.5" x-text="bkp.data + ' • Tamanho: ' + bkp.tamanho_humano"></div>
                                        </div>
                                        <a :href="'/api/admin/backups/' + encodeURIComponent(bkp.arquivo) + '/download?token=' + encodeURIComponent(localStorage.getItem('pl_token') || '')"
                                           target="_blank"
                                           class="bg-slate-800 hover:bg-slate-700 text-emerald-400 border border-slate-700 font-bold px-3.5 py-2 rounded-xl shrink-0 transition">
                                            Baixar .ZIP
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Histórico de Incidentes / Erros 500 Capturados -->
                        <div class="pt-3 border-t border-slate-800 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-300">Últimos Erros Críticos Capturados</span>
                                <span class="text-[11px] text-slate-500" x-text="(monitoramento.incidentes_recentes || []).length + ' registro(s)'"></span>
                            </div>
                            <template x-if="(monitoramento.incidentes_recentes || []).length === 0">
                                <div class="bg-emerald-950/20 border border-emerald-500/20 text-emerald-400 rounded-xl p-2.5 text-xs font-semibold text-center">
                                    Zero erros críticos registrados recentemente. Sistema 100% estável!
                                </div>
                            </template>
                            <div class="space-y-1.5 max-h-40 overflow-y-auto">
                                <template x-for="inc in (monitoramento.incidentes_recentes || []).slice(0, 5)" :key="inc.id">
                                    <div class="bg-rose-950/25 border border-rose-500/30 rounded-xl p-2.5 text-[11px] space-y-0.5">
                                        <div class="flex justify-between font-bold text-rose-300">
                                            <span x-text="inc.tipo + ' (' + inc.arquivo + ')'"></span>
                                            <span x-text="inc.data"></span>
                                        </div>
                                        <div class="text-slate-300 truncate" x-text="inc.mensagem"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- BARRA DE NAVEGAÇÃO INFERIOR PARA CELULAR -->
    <nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-slate-900/95 backdrop-blur-md border-t border-slate-800 grid grid-cols-5 py-1.5 px-1">
        <button @click="abaAtiva = 'dashboard'" class="flex flex-col items-center py-1 text-[10px] font-bold"
            :class="abaAtiva === 'dashboard' ? 'text-rose-500' : 'text-slate-400'">
            <span class="text-base"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg></span>
            <span>KPIs SaaS</span>
        </button>
        <button @click="abaAtiva = 'lojas'" class="flex flex-col items-center py-1 text-[10px] font-bold"
            :class="abaAtiva === 'lojas' ? 'text-rose-500' : 'text-slate-400'">
            <span class="text-base"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg></span>
            <span>Lojas</span>
        </button>
        <button @click="abaAtiva = 'planos'" class="flex flex-col items-center py-1 text-[10px] font-bold"
            :class="abaAtiva === 'planos' ? 'text-rose-500' : 'text-slate-400'">
            <span class="text-base"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg></span>
            <span>Planos</span>
        </button>
        <button @click="abaAtiva = 'monitoramento'; carregarMonitoramento()" class="flex flex-col items-center py-1 text-[10px] font-bold"
            :class="abaAtiva === 'monitoramento' ? 'text-rose-500' : 'text-slate-400'">
            <span class="text-base"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></span>
            <span>Backup/Saúde</span>
        </button>
        <button @click="abaAtiva = 'auditoria'" class="flex flex-col items-center py-1 text-[10px] font-bold"
            :class="abaAtiva === 'auditoria' ? 'text-rose-500' : 'text-slate-400'">
            <span class="text-base"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg></span>
            <span>Auditoria</span>
        </button>
    </nav>

    <!-- Modal: Cadastrar Nova Loja Tenant -->
    <div x-cloak x-show="modalNovaLoja" class="fixed inset-0 z-50 bg-black/75 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="modalNovaLoja = false" class="bg-slate-900 border border-slate-800 w-full max-w-lg rounded-t-3xl sm:rounded-3xl p-5 space-y-4 max-h-[92vh] overflow-y-auto">
            <div class="flex justify-between items-center">
                <h3 class="font-bold text-base text-white">Cadastrar Nova Loja Cliente no SaaS</h3>
                <button @click="modalNovaLoja = false" class="text-slate-400 font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <form @submit.prevent="criarNovaLoja()" class="space-y-3 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Nome do Restaurante *</label>
                        <input type="text" x-model="novaLoja.nome_loja" required placeholder="Ex: Pizzaria Dom Bosco" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                    </div>
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Slug do Link (/p/slug)</label>
                        <input type="text" x-model="novaLoja.slug" placeholder="Ex: pizzaria-dom-bosco" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">CNPJ ou CPF</label>
                        <input type="text" x-model="novaLoja.cnpj_cpf" placeholder="00.000.000/0001-00" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                    </div>
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Cidade</label>
                        <input type="text" x-model="novaLoja.cidade" placeholder="Cidade" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                    </div>
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">UF</label>
                        <input type="text" x-model="novaLoja.uf" maxlength="2" placeholder="MG" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white uppercase">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Plano de Assinatura</label>
                        <select x-model.number="novaLoja.plano_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-bold">
                            <template x-for="pl in (metricas.planos || [])" :key="pl.id">
                                <option :value="pl.id" x-text="pl.nome.toUpperCase() + ' — ' + formatarCentavos(pl.preco_centavos)"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Status Inicial</label>
                        <select x-model="novaLoja.status" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-bold">
                            <option value="ativa">Ativa (Liberada)</option>
                            <option value="trial">Trial Grátis (14 dias)</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Nome do Proprietário *</label>
                        <input type="text" x-model="novaLoja.nome_dono" required placeholder="Nome completo" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                    </div>
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">WhatsApp da Loja *</label>
                        <input type="text" x-model="novaLoja.telefone_whatsapp" required placeholder="31999999999" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">E-mail de Login *</label>
                        <input type="email" x-model="novaLoja.email_dono" required placeholder="dono@restaurante.com" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                    </div>
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Senha Inicial *</label>
                        <input type="text" x-model="novaLoja.senha_dono" required placeholder="Mínimo 6 caracteres" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                    </div>
                </div>
                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-bold py-3.5 rounded-xl text-sm shadow-lg transition">
                    Criar Loja Limpa e Liberar Acesso
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Editar Loja / Plano / Status -->
    <div x-cloak x-show="lojaEditando" class="fixed inset-0 z-50 bg-black/75 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="lojaEditando = null" class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-t-3xl sm:rounded-3xl p-5 space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="font-bold text-base text-white" x-text="'Gerenciar: ' + (lojaEditando?.nome || '')"></h3>
                <button @click="lojaEditando = null" class="text-slate-400 font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <template x-if="lojaEditando">
                <form @submit.prevent="salvarEdicaoLoja()" class="space-y-3 text-xs">
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Nome da Loja</label>
                        <input type="text" x-model="lojaEditando.nome" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                    </div>
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Plano Contratado</label>
                        <select x-model.number="lojaEditando.plano_id" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-bold">
                            <template x-for="pl in (metricas.planos || [])" :key="pl.id">
                                <option :value="pl.id" x-text="pl.nome.toUpperCase() + ' (' + formatarCentavos(pl.preco_centavos) + ')'"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Status Operacional da Loja</label>
                        <select x-model="lojaEditando.status" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-bold">
                            <option value="ativa">Ativa (Operação Total)</option>
                            <option value="trial">⏳ Trial (Período de Teste)</option>
                            <option value="inadimplente">Inadimplente (Modo Somente Leitura)</option>
                            <option value="suspensa">Suspensa (Cardápio Bloqueado)</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">WhatsApp</label>
                        <input type="text" x-model="lojaEditando.telefone_whatsapp" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                    </div>
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 rounded-xl text-sm">
                        Salvar Alterações da Loja
                    </button>
                </form>
            </template>
        </div>
    </div>

    <script>
        function superAdminApp() {
            return {
                autenticado: false,
                abaAtiva: 'dashboard',
                buscaLoja: '',
                filtroStatusLoja: 'todas',
                avisoSucesso: '',
                metricas: {},
                lojas: [],
                monitoramento: { status: 'healthy', checks: {}, backups: [], incidentes_recentes: [] },
                configMonitoramento: {
                    alert_email: '',
                    alert_whatsapp: '',
                    whatsapp_instance: '',
                    alerta_queda_ativo: true,
                    alerta_erros_500_ativo: true,
                    backup_auto_ativo: true,
                    backup_enviar_email: true,
                    backup_enviar_resumo_whatsapp: true,
                    backup_horario: '03:00'
                },
                salvandoMonitoramento: false,
                testandoAlerta: false,
                gerandoBackup: false,
                modalNovaLoja: false,
                lojaEditando: null,
                novaLoja: {
                    nome_loja: '',
                    slug: '',
                    cnpj_cpf: '',
                    cidade: 'Coronel Fabriciano',
                    uf: 'MG',
                    plano_id: 3,
                    status: 'ativa',
                    telefone_whatsapp: '',
                    nome_dono: '',
                    email_dono: '',
                    senha_dono: 'Mudar123!'
                },

                tituloAba() {
                    const map = {
                        dashboard: 'Painel Master do Dono do SaaS — Visão Executiva',
                        lojas: 'Gestão de Lojas, Assinaturas e Impersonação',
                        planos: 'Planos Comerciais e Precificação do SaaS',
                        monitoramento: 'Monitoramento 24/7, Alertas de Queda (E-mail/WhatsApp) e Backups',
                        auditoria: 'Auditoria Imutável, Segurança e Webhooks'
                    };
                    return map[this.abaAtiva] || 'Painel Master SaaS';
                },

                get lojasFiltradas() {
                    return this.lojas.filter(l => {
                        if (this.filtroStatusLoja !== 'todas' && l.status !== this.filtroStatusLoja) return false;
                        if (!this.buscaLoja.trim()) return true;
                        const q = this.buscaLoja.toLowerCase();
                        return (l.nome || '').toLowerCase().includes(q)
                            || (l.slug || '').toLowerCase().includes(q)
                            || (l.dono_nome || '').toLowerCase().includes(q)
                            || (l.cidade || '').toLowerCase().includes(q);
                    });
                },

                headersAuth() {
                    const token = localStorage.getItem('pl_token') || '';
                    return {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': token ? `Bearer ${token}` : ''
                    };
                },

                iniciar() {
                    const token = localStorage.getItem('pl_token');
                    if (!token) {
                        window.location.replace('/login');
                        return;
                    }
                    this.carregarTudo();
                },

                sair() {
                    fetch('/api/auth/logout', { method: 'POST', headers: this.headersAuth() }).catch(() => {});
                    localStorage.removeItem('pl_token');
                    localStorage.removeItem('pl_usuario');
                    sessionStorage.removeItem('pl_superadmin_token');
                    window.location.replace('/login');
                },

                carregarTudo() {
                    fetch('/api/admin/metricas', { headers: this.headersAuth() })
                        .then(r => {
                            if (r.status === 403) {
                                window.location.replace('/painel/pedidos');
                                return null;
                            }
                            if (r.status === 401 || !r.ok) {
                                localStorage.removeItem('pl_token');
                                localStorage.removeItem('pl_usuario');
                                window.location.replace('/login');
                                return null;
                            }
                            return r.json();
                        })
                        .then(d => {
                            if (!d) return;
                            this.autenticado = true;
                            this.metricas = d || {};
                            if (this.metricas.planos?.length > 0 && !this.novaLoja.plano_id) {
                                this.novaLoja.plano_id = this.metricas.planos[this.metricas.planos.length - 1].id;
                            }
                        })
                        .catch(() => {
                            window.location.replace('/login');
                        });

                    fetch('/api/admin/lojas', { headers: this.headersAuth() })
                        .then(r => r.ok ? r.json() : null)
                        .then(d => {
                            if (d) this.lojas = d.lojas || d.data || [];
                        });

                    this.carregarMonitoramento();
                },

                carregarMonitoramento() {
                    fetch('/api/admin/monitoramento', { headers: this.headersAuth() })
                        .then(r => r.ok ? r.json() : null)
                        .then(d => {
                            if (d) {
                                this.monitoramento = d;
                                if (d.config) {
                                    this.configMonitoramento = { ...this.configMonitoramento, ...d.config };
                                }
                            }
                        })
                        .catch(() => {});
                },

                salvarConfigMonitoramento() {
                    this.salvandoMonitoramento = true;
                    fetch('/api/admin/monitoramento/config', {
                        method: 'POST',
                        headers: this.headersAuth(),
                        body: JSON.stringify(this.configMonitoramento)
                    })
                    .then(r => r.json())
                    .then(d => {
                        this.avisoSucesso = d.message || 'Configurações salvas!';
                        if (d.config) this.configMonitoramento = { ...this.configMonitoramento, ...d.config };
                    })
                    .finally(() => {
                        this.salvandoMonitoramento = false;
                    });
                },

                dispararTesteAlerta() {
                    this.testandoAlerta = true;
                    fetch('/api/admin/monitoramento/testar-alerta', {
                        method: 'POST',
                        headers: this.headersAuth(),
                        body: JSON.stringify(this.configMonitoramento)
                    })
                    .then(r => r.json())
                    .then(d => {
                        this.avisoSucesso = d.message || 'Alerta de teste disparado!';
                    })
                    .finally(() => {
                        this.testandoAlerta = false;
                    });
                },

                gerarBackupManual(enviarEmail = true) {
                    this.gerandoBackup = true;
                    fetch('/api/admin/backups/gerar', {
                        method: 'POST',
                        headers: this.headersAuth(),
                        body: JSON.stringify({ enviar_email: enviarEmail })
                    })
                    .then(r => r.json())
                    .then(d => {
                        this.avisoSucesso = d.message || 'Backup gerado com sucesso!';
                        if (d.backups) {
                            this.monitoramento.backups = d.backups;
                        } else {
                            this.carregarMonitoramento();
                        }
                    })
                    .finally(() => {
                        this.gerandoBackup = false;
                    });
                },

                abrirModalEditarLoja(loja) {
                    this.lojaEditando = { ...loja };
                },

                salvarEdicaoLoja() {
                    if (!this.lojaEditando) return;
                    fetch(`/api/admin/lojas/${this.lojaEditando.id}`, {
                        method: 'PATCH',
                        headers: this.headersAuth(),
                        body: JSON.stringify({
                            nome: this.lojaEditando.nome,
                            plano_id: Number(this.lojaEditando.plano_id),
                            status: this.lojaEditando.status,
                            telefone_whatsapp: this.lojaEditando.telefone_whatsapp
                        })
                    })
                    .then(r => r.json())
                    .then(d => {
                        this.avisoSucesso = d.message || 'Loja atualizada!';
                        this.lojaEditando = null;
                        this.carregarTudo();
                    });
                },

                criarNovaLoja() {
                    fetch('/api/admin/lojas', {
                        method: 'POST',
                        headers: this.headersAuth(),
                        body: JSON.stringify(this.novaLoja)
                    })
                    .then(async r => {
                        const d = await r.json();
                        if (!r.ok) throw new Error(d.message || 'Erro ao criar loja');
                        this.avisoSucesso = `Loja "${this.novaLoja.nome_loja}" criada com sucesso!`;
                        this.modalNovaLoja = false;
                        this.novaLoja.nome_loja = '';
                        this.novaLoja.slug = '';
                        this.novaLoja.cnpj_cpf = '';
                        this.novaLoja.email_dono = '';
                        this.carregarTudo();
                    })
                    .catch(e => alert(e.message));
                },

                salvarPlano(pl) {
                    fetch(`/api/admin/planos/${pl.id}`, {
                        method: 'PATCH',
                        headers: this.headersAuth(),
                        body: JSON.stringify({
                            preco_centavos: Number(pl.preco_centavos),
                            limite_usuarios: Number(pl.limite_usuarios)
                        })
                    })
                    .then(r => r.json())
                    .then(d => {
                        this.avisoSucesso = d.message || 'Plano atualizado com sucesso!';
                        this.carregarTudo();
                    });
                },

                executarRegua() {
                    fetch('/api/admin/regua-inadimplencia', {
                        method: 'POST',
                        headers: this.headersAuth()
                    })
                    .then(r => r.json())
                    .then(d => {
                        this.avisoSucesso = d.message || 'Régua de cobrança executada com sucesso!';
                        this.carregarTudo();
                    });
                },

                impersonarLoja(loja) {
                    const currentToken = localStorage.getItem('pl_token') || '';
                    fetch(`/api/admin/lojas/${loja.id}/impersonar`, {
                        method: 'POST',
                        headers: this.headersAuth(),
                        body: JSON.stringify({
                            ticket_id: 'ADM-' + Date.now().toString().slice(-5),
                            motivo: 'Acesso administrativo de suporte e verificação pelo Dono do SaaS'
                        })
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (d.impersonation_token) {
                            if (currentToken) {
                                sessionStorage.setItem('pl_superadmin_token', currentToken);
                            }
                            localStorage.setItem('pl_token', d.impersonation_token);
                            window.location.href = '/painel/pedidos';
                        }
                    });
                },

                formatarCentavos(centavos) {
                    return ((centavos || 0) / 100).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                }
            };
        }
    </script>
</body>
</html>
