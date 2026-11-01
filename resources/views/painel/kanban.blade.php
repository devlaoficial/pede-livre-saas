<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="theme-color" content="#020617">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/manifest.json">
    <title>Painel do Lojista — PedeLivre OS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body x-cloak x-show="autenticado" class="h-full flex flex-col lg:flex-row antialiased font-sans overflow-hidden" x-data="painelApp()" x-init="iniciar()">

    <!-- SIDEBAR FIXA PARA PC (Desktop lg:flex) -->
    <aside class="hidden lg:flex lg:flex-col lg:w-64 bg-slate-900 border-r border-slate-800 shrink-0 justify-between">
        <div class="p-4 space-y-5 overflow-y-auto no-scrollbar">
            <div class="flex items-center justify-between px-1">
                <a href="/" class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-rose-600 flex items-center justify-center text-white font-black text-base shadow-md shadow-rose-600/30">P</div>
                    <span class="text-xl font-black tracking-tight text-white">Pede<span class="text-rose-500">Livre</span></span>
                </a>
                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 animate-pulse">Ao Vivo</span>
            </div>

            <!-- Dados da Loja Logada -->
            <div class="bg-slate-950/90 border border-slate-800 p-3.5 rounded-2xl space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <div class="text-xs font-black text-white truncate" x-text="loja?.nome || 'Brasa & Sabor Artesanal'"></div>
                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1 border-t border-slate-800/80">
                    <span>Plano <strong class="text-rose-400 uppercase" x-text="loja?.plano || 'Premium'"></strong></span>
                    <a :href="'/p/' + (loja?.slug || 'brasa-artesanal')" target="_blank" class="text-emerald-400 hover:underline font-bold">Abrir Cardápio ↗</a>
                </div>
            </div>

            <!-- Menu de Navegação Desktop (8 Módulos) -->
            <nav class="space-y-1">
                <button @click="mudarModulo('pedidos')"
                    class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
                    :class="moduloAtivo === 'pedidos' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                    <span class="flex items-center gap-2.5"><span>📋</span> Pedidos &amp; PDV</span>
                    <span x-show="colunas.novo.length > 0" class="bg-white text-rose-600 font-black text-[11px] px-2 py-0.5 rounded-full" x-text="colunas.novo.length"></span>
                </button>

                <button @click="mudarModulo('catalogo')"
                    class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
                    :class="moduloAtivo === 'catalogo' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                    <span class="flex items-center gap-2.5"><span>🍔</span> Cardápio &amp; Cupons</span>
                    <span class="text-[11px] opacity-75" x-text="produtos.length"></span>
                </button>

                <button @click="mudarModulo('kds')"
                    class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
                    :class="moduloAtivo === 'kds' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                    <span class="flex items-center gap-2.5"><span>🍳</span> KDS Cozinha</span>
                    <span class="text-[11px] opacity-75" x-text="kdsTickets.length"></span>
                </button>

                <button @click="mudarModulo('mesas')"
                    class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
                    :class="moduloAtivo === 'mesas' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                    <span class="flex items-center gap-2.5"><span>🪑</span> Mesas &amp; Comandas</span>
                    <div class="flex items-center gap-1">
                        <span x-show="chamadosGarcom.length > 0" class="bg-amber-400 text-slate-950 font-black text-[10px] px-1.5 py-0.2 rounded-full animate-bounce" x-text="'🔔 ' + chamadosGarcom.length"></span>
                        <span class="text-[11px] opacity-75" x-text="mesas.length"></span>
                    </div>
                </button>

                <button @click="mudarModulo('entregas')"
                    class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
                    :class="moduloAtivo === 'entregas' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                    <span class="flex items-center gap-2.5"><span>🛵</span> Motoboys &amp; Frete</span>
                    <span class="text-[11px] opacity-75" x-text="entregadores.length"></span>
                </button>

                <button @click="mudarModulo('crm')"
                    class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
                    :class="moduloAtivo === 'crm' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                    <span class="flex items-center gap-2.5"><span>👥</span> Clientes &amp; CRM</span>
                    <span class="text-[10px] bg-emerald-500/20 text-emerald-400 px-1.5 py-0.5 rounded-full font-black">Cashback</span>
                </button>

                <button @click="mudarModulo('financeiro')"
                    class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
                    :class="moduloAtivo === 'financeiro' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                    <span class="flex items-center gap-2.5"><span>💰</span> Caixa &amp; PIX</span>
                </button>

                <button @click="mudarModulo('whatsapp')"
                    class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
                    :class="moduloAtivo === 'whatsapp' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                    <span class="flex items-center gap-2.5">
                        <span>💬</span> WhatsApp &amp; Bot
                        <span x-show="totalAguardandoHumano > 0" class="bg-amber-400 text-slate-950 text-[10px] font-black px-1.5 py-0.2 rounded-full animate-pulse" x-text="totalAguardandoHumano"></span>
                    </span>
                    <span class="w-2.5 h-2.5 rounded-full" :class="whatsappConectado ? 'bg-emerald-400 shadow-sm shadow-emerald-400/50' : 'bg-slate-600'"></span>
                </button>

                <button @click="mudarModulo('config')"
                    class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition"
                    :class="moduloAtivo === 'config' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
                    <span class="flex items-center gap-2.5"><span>⚙️</span> Loja &amp; Horários</span>
                </button>
            </nav>
        </div>

        <div class="p-4 border-t border-slate-800 space-y-2">
            <button @click="alternarSom()" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-bold transition"
                :class="somHabilitado ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400'">
                <span x-text="somHabilitado ? '🔊 Alerta Sonoro Ativo' : '🔇 Som Silenciado'"></span>
            </button>
            <template x-if="temSessaoSuperAdmin">
                <button @click="voltarSuperAdmin()" class="w-full bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 border border-amber-500/30 px-3 py-2 rounded-xl text-xs font-bold transition">
                    👑 Voltar ao SaaS Admin
                </button>
            </template>
            <button @click="sair()" class="w-full bg-slate-800 hover:bg-rose-600/20 text-slate-300 hover:text-rose-300 px-3 py-2 rounded-xl text-xs font-bold transition">
                Sair da Conta
            </button>
        </div>
    </aside>

    <!-- ÁREA CENTRAL (Adaptada para PC e Celular) -->
    <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">

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

        <!-- ALERTA AO VIVO: MESA CHAMANDO GARÇOM (Etapa 3) -->
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

        <!-- ========================================================= -->
        <!-- MÓDULO 1: PEDIDOS AO VIVO (Abas no Mobile / Kanban no PC) -->
        <!-- ========================================================= -->
        <div x-show="moduloAtivo === 'pedidos'" class="flex-1 flex flex-col min-h-0 overflow-hidden">

            <!-- Checklist de Ativação Rápida "Sua Loja Pronta em 3 Passos" (Etapa 5) -->
            <template x-if="!onboardingOculto">
                <div class="bg-gradient-to-r from-slate-900 via-slate-900 to-rose-950/40 border-b border-slate-800 px-4 lg:px-5 py-2.5 flex flex-wrap items-center justify-between gap-3 shrink-0">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="font-black text-white flex items-center gap-1.5">🚀 Guia de Ativação:</span>
                        <button @click="mudarModulo('config')" class="px-2.5 py-1 rounded-lg font-bold flex items-center gap-1 border transition"
                            :class="loja?.nome && loja?.telefone_whatsapp ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-300' : 'bg-slate-800 border-slate-700 text-slate-300'">
                            <span x-text="loja?.nome && loja?.telefone_whatsapp ? '✅' : '1.'"></span> Marca &amp; WhatsApp
                        </button>
                        <button @click="mudarModulo('catalogo')" class="px-2.5 py-1 rounded-lg font-bold flex items-center gap-1 border transition"
                            :class="produtos.length > 0 ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-300' : 'bg-slate-800 border-slate-700 text-slate-300'">
                            <span x-text="produtos.length > 0 ? '✅' : '2.'"></span> Cardápio Ativo
                        </button>
                        <button @click="mudarModulo('financeiro')" class="px-2.5 py-1 rounded-lg font-bold flex items-center gap-1 border transition"
                            :class="mpStatus?.conectado ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-300' : 'bg-slate-800 border-slate-700 text-slate-300'">
                            <span x-text="mpStatus?.conectado ? '✅' : '3.'"></span> PIX 0% Taxa
                        </button>
                        <button @click="copiarLinkCardapio()" class="px-2.5 py-1 rounded-lg font-bold bg-rose-600/20 hover:bg-rose-600/30 text-rose-300 border border-rose-500/30 transition">
                            📲 Copiar Link p/ Instagram
                        </button>
                    </div>
                    <button @click="ocultarOnboarding()" class="text-[11px] text-slate-400 hover:text-white font-bold">Ocultar ✕</button>
                </div>
            </template>

            <!-- Faixa de Resumo Rápido + Auto-Impressão 80mm + Busca de Pedidos -->
            <div class="bg-slate-900/60 border-b border-slate-800 px-4 lg:px-5 py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 shrink-0">
                <div class="flex items-center gap-2.5 overflow-x-auto no-scrollbar text-xs">
                    <span class="bg-slate-900 border border-slate-800 px-3 py-1 rounded-xl whitespace-nowrap">
                        Hoje: <strong class="text-white" x-text="pedidos.length + ' pedidos'"></strong>
                    </span>
                    <span class="bg-slate-900 border border-slate-800 px-3 py-1 rounded-xl whitespace-nowrap">
                        Volume: <strong class="text-emerald-400" x-text="formatarCentavos(volumeDiaCentavos)"></strong>
                    </span>
                    <span class="bg-slate-900 border border-slate-800 px-3 py-1 rounded-xl whitespace-nowrap">
                        Na Cozinha: <strong class="text-amber-400" x-text="colunas.em_preparo.length"></strong>
                    </span>
                    <button @click="alternarAutoImpressao()"
                        class="px-3 py-1 rounded-xl whitespace-nowrap font-bold border transition"
                        :class="autoImprimir80mm ? 'bg-emerald-500/15 border-emerald-500/40 text-emerald-300' : 'bg-slate-900 border-slate-800 text-slate-400'">
                        <span x-text="autoImprimir80mm ? '🖨️ Auto-Imprimir 80mm: ON' : '🖨️ Auto-Imprimir 80mm: OFF'"></span>
                    </button>
                </div>
                <div class="relative sm:w-64">
                    <input type="text" x-model="buscaPedido" placeholder="Buscar #código ou cliente..."
                        class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-8 pr-3 py-1.5 text-xs text-white focus:border-rose-500 focus:outline-none">
                    <span class="absolute left-2.5 top-1.5 text-slate-500 text-xs">🔍</span>
                </div>
            </div>

            <!-- Seletor de Abas Mobile (Exibido apenas em telas < lg) -->
            <div class="lg:hidden bg-slate-900 border-b border-slate-800 px-3 py-2 flex overflow-x-auto no-scrollbar gap-1.5 shrink-0">
                <button @click="abaPedidoMobile = 'novo'"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap flex items-center gap-1.5 transition"
                    :class="abaPedidoMobile === 'novo' ? 'bg-rose-600 text-white' : 'bg-slate-800 text-slate-400'">
                    <span>Novos</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/30" x-text="colunas.novo.length"></span>
                </button>
                <button @click="abaPedidoMobile = 'em_preparo'"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap flex items-center gap-1.5 transition"
                    :class="abaPedidoMobile === 'em_preparo' ? 'bg-amber-600 text-white' : 'bg-slate-800 text-slate-400'">
                    <span>Preparo</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/30" x-text="colunas.em_preparo.length"></span>
                </button>
                <button @click="abaPedidoMobile = 'pronto'"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap flex items-center gap-1.5 transition"
                    :class="abaPedidoMobile === 'pronto' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-400'">
                    <span>Prontos</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/30" x-text="colunas.pronto.length"></span>
                </button>
                <button @click="abaPedidoMobile = 'saiu_entrega'"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap flex items-center gap-1.5 transition"
                    :class="abaPedidoMobile === 'saiu_entrega' ? 'bg-sky-600 text-white' : 'bg-slate-800 text-slate-400'">
                    <span>Em Rota</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/30" x-text="colunas.saiu_entrega.length"></span>
                </button>
                <button @click="abaPedidoMobile = 'finalizado'"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap flex items-center gap-1.5 transition"
                    :class="abaPedidoMobile === 'finalizado' ? 'bg-slate-700 text-white' : 'bg-slate-800 text-slate-400'">
                    <span>Concluídos</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-black/30" x-text="colunas.finalizado.length"></span>
                </button>
            </div>

            <!-- VISÃO MOBILE DE PEDIDOS -->
            <div class="lg:hidden flex-1 overflow-y-auto p-3 pb-24 space-y-3">
                <template x-if="(colunas[abaPedidoMobile] || []).length === 0">
                    <div class="text-center py-16 text-slate-500 space-y-2">
                        <div class="text-3xl">📭</div>
                        <p class="text-sm font-bold">Nenhum pedido nesta etapa</p>
                    </div>
                </template>

                <template x-for="pedido in (colunas[abaPedidoMobile] || [])" :key="pedido.id">
                    <div class="bg-slate-900 rounded-2xl p-4 border border-slate-800 space-y-3 shadow-lg">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                <button @click="abrirDetalhesPedido(pedido)" class="font-black text-rose-400 text-base hover:underline" x-text="'#' + pedido.codigo_publico"></button>
                                <span class="text-[11px] font-bold uppercase px-2 py-0.5 rounded bg-slate-800 text-slate-300" x-text="pedido.canal"></span>
                                <span class="text-[11px] font-bold uppercase px-2 py-0.5 rounded"
                                    :class="pedido.pagamento_status === 'aprovado' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400'"
                                    x-text="pedido.forma_pagamento + ' • ' + pedido.pagamento_status"></span>
                            </div>
                            <span class="text-xs text-slate-400" x-text="pedido.tempo_decorrido_minutos + ' min'"></span>
                        </div>

                        <div class="flex justify-between items-center">
                            <div>
                                <div class="font-bold text-white text-sm" x-text="pedido.cliente?.nome || 'Cliente Balcão'"></div>
                                <div class="text-xs text-slate-400" x-text="pedido.cliente?.telefone || ''"></div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button @click="abrirDetalhesPedido(pedido)" class="bg-slate-800 text-slate-200 border border-slate-700 text-xs font-bold px-2.5 py-1.5 rounded-xl">
                                    🔍 Detalhes
                                </button>
                                <template x-if="pedido.whatsapp_notificacao_url">
                                    <a :href="pedido.whatsapp_notificacao_url" target="_blank" class="bg-emerald-600/20 text-emerald-400 border border-emerald-500/30 text-xs font-bold px-3 py-1.5 rounded-xl">
                                        💬 WhatsApp
                                    </a>
                                </template>
                            </div>
                        </div>

                        <template x-if="pedido.observacao">
                            <div class="bg-amber-500/15 border border-amber-500/30 text-amber-300 text-xs font-bold px-3 py-2 rounded-xl" x-text="'⚠️ Obs: ' + pedido.observacao"></div>
                        </template>

                        <div class="bg-slate-950/70 rounded-xl p-3 space-y-1.5 text-xs">
                            <template x-for="item in pedido.itens" :key="item.id">
                                <div>
                                    <div class="flex justify-between font-semibold text-slate-200">
                                        <span x-text="item.qtd + 'x ' + item.nome + (item.variacao ? ' (' + item.variacao + ')' : '')"></span>
                                        <span x-text="formatarCentavos(item.total)"></span>
                                    </div>
                                    <template x-if="item.adicionais && item.adicionais.length > 0">
                                        <div class="text-[11px] text-slate-400 pl-3" x-text="item.adicionais.map(a => '+ ' + a.nome).join(', ')"></div>
                                    </template>
                                    <template x-if="item.observacao">
                                        <div class="text-[11px] text-amber-400 font-bold pl-3" x-text="'Obs: ' + item.observacao"></div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <div class="flex justify-between items-center pt-1">
                            <span class="text-xs text-slate-400 font-bold">Total do Pedido</span>
                            <span class="text-base font-black text-emerald-400" x-text="formatarCentavos(pedido.total_centavos)"></span>
                        </div>

                        <div class="flex gap-2 pt-1">
                            <template x-if="proximoStatus(pedido)">
                                <button @click="avancarPedido(pedido)" class="flex-1 bg-rose-600 hover:bg-rose-500 text-white font-extrabold py-3 rounded-xl text-xs shadow-md active:scale-95 transition">
                                    <span x-text="rotuloProximoStatus(pedido)"></span>
                                </button>
                            </template>
                            <button @click="imprimirPedido(pedido.id, 'cozinha')" class="px-3.5 py-3 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-bold">
                                🖨️ 80mm
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- VISÃO PC DE PEDIDOS (Quadro Kanban Widescreen de 5 Colunas) -->
            <main class="hidden lg:flex flex-1 overflow-x-auto p-5 gap-4 min-w-full">
                <!-- Coluna 1: Novos -->
                <div class="w-80 shrink-0 flex flex-col bg-slate-900/90 rounded-2xl border border-rose-500/30 shadow-xl overflow-hidden">
                    <div class="p-3.5 bg-rose-950/40 border-b border-rose-500/20 flex justify-between items-center">
                        <h2 class="font-bold text-sm text-rose-400 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                            1. Novos Pedidos
                        </h2>
                        <span class="bg-rose-500 text-white text-xs font-extrabold px-2 py-0.5 rounded-full" x-text="colunas.novo.length"></span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 space-y-3">
                        <template x-for="pedido in colunas.novo" :key="pedido.id">
                            <div class="bg-slate-800/90 rounded-xl p-3.5 border border-rose-500/40 shadow-lg space-y-2.5">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-center gap-1.5">
                                        <button @click="abrirDetalhesPedido(pedido)" class="font-black text-rose-400 text-sm hover:underline" x-text="'#' + pedido.codigo_publico"></button>
                                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-slate-700 text-slate-200" x-text="pedido.canal"></span>
                                    </div>
                                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded"
                                          :class="pedido.pagamento_status === 'aprovado' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400'"
                                          x-text="pedido.forma_pagamento + ' • ' + pedido.pagamento_status"></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="font-bold text-slate-100 text-sm truncate" x-text="pedido.cliente?.nome || 'Cliente'"></div>
                                        <div class="text-xs text-slate-400" x-text="pedido.cliente?.telefone || ''"></div>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button @click="abrirDetalhesPedido(pedido)" class="text-xs bg-slate-700 hover:bg-slate-600 text-slate-200 px-2 py-1 rounded-lg font-bold" title="Ver Detalhes e Endereço">🔍</button>
                                        <template x-if="pedido.whatsapp_notificacao_url">
                                            <a :href="pedido.whatsapp_notificacao_url" target="_blank" class="text-xs bg-emerald-500/20 text-emerald-400 px-2 py-1 rounded-lg font-bold">💬</a>
                                        </template>
                                    </div>
                                </div>
                                <template x-if="pedido.observacao">
                                    <div class="bg-amber-500/15 border border-amber-500/30 text-amber-300 text-[11px] font-bold px-2.5 py-1.5 rounded-lg" x-text="'⚠️ Obs: ' + pedido.observacao"></div>
                                </template>
                                <div class="text-xs text-slate-300 space-y-1 bg-slate-900/60 p-2.5 rounded-lg cursor-pointer" @click="abrirDetalhesPedido(pedido)">
                                    <template x-for="item in pedido.itens" :key="item.id">
                                        <div>
                                            <div class="flex justify-between">
                                                <span class="font-semibold" x-text="item.qtd + 'x ' + item.nome + (item.variacao ? ' (' + item.variacao + ')' : '')"></span>
                                                <span class="font-bold" x-text="formatarCentavos(item.total)"></span>
                                            </div>
                                            <template x-if="item.adicionais?.length > 0">
                                                <div class="text-[10px] text-slate-400 pl-2" x-text="item.adicionais.map(a => '+ ' + a.nome).join(', ')"></div>
                                            </template>
                                            <template x-if="item.observacao">
                                                <div class="text-[10px] text-amber-400 font-bold pl-2" x-text="'Obs: ' + item.observacao"></div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex justify-between items-center pt-1 border-t border-slate-700/50">
                                    <span class="text-xs font-bold text-slate-400" x-text="pedido.tempo_decorrido_minutos + ' min atrás'"></span>
                                    <span class="text-sm font-black text-emerald-400" x-text="formatarCentavos(pedido.total_centavos)"></span>
                                </div>
                                <div class="flex gap-1.5">
                                    <button @click="avancarPedido(pedido)" class="flex-1 bg-rose-600 hover:bg-rose-700 text-white font-bold py-2 rounded-lg text-xs shadow-md transition">
                                        ✓ Aceitar
                                    </button>
                                    <button @click="imprimirPedido(pedido.id, 'cozinha')" class="px-2.5 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg text-xs font-bold" title="Imprimir 80mm">
                                        🖨️
                                    </button>
                                    <button @click="cancelarPedido(pedido)" class="px-2.5 py-2 bg-slate-700 hover:bg-rose-900 text-slate-400 hover:text-rose-200 rounded-lg text-xs font-bold" title="Cancelar">
                                        ✕
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Coluna 2: Em Preparo -->
                <div class="w-80 shrink-0 flex flex-col bg-slate-900/90 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
                    <div class="p-3.5 bg-amber-950/20 border-b border-amber-500/20 flex justify-between items-center">
                        <h2 class="font-bold text-sm text-amber-400">2. Em Preparo</h2>
                        <span class="bg-amber-500/20 text-amber-400 text-xs font-extrabold px-2 py-0.5 rounded-full" x-text="colunas.em_preparo.length"></span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 space-y-3">
                        <template x-for="pedido in colunas.em_preparo" :key="pedido.id">
                            <div class="bg-slate-800 rounded-xl p-3.5 border border-slate-700 space-y-2.5">
                                <div class="flex justify-between items-start">
                                    <button @click="abrirDetalhesPedido(pedido)" class="font-bold text-amber-400 text-sm hover:underline" x-text="'#' + pedido.codigo_publico"></button>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs text-slate-400" x-text="pedido.tempo_decorrido_minutos + ' min'"></span>
                                        <button @click="abrirDetalhesPedido(pedido)" class="text-xs bg-slate-700 px-1.5 py-0.5 rounded">🔍</button>
                                    </div>
                                </div>
                                <div class="font-bold text-sm text-slate-200" x-text="pedido.cliente?.nome"></div>
                                <div class="text-xs text-slate-300 space-y-1 bg-slate-900/50 p-2 rounded-lg cursor-pointer" @click="abrirDetalhesPedido(pedido)">
                                    <template x-for="item in pedido.itens" :key="item.id">
                                        <div>
                                            <span x-text="item.qtd + 'x ' + item.nome + (item.variacao ? ' (' + item.variacao + ')' : '')"></span>
                                            <template x-if="item.observacao">
                                                <div class="text-[10px] text-amber-400 font-bold" x-text="'⚠️ ' + item.observacao"></div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex gap-2">
                                    <button @click="avancarPedido(pedido)" class="flex-1 bg-amber-600 hover:bg-amber-500 text-white font-bold py-2 rounded-lg text-xs transition">
                                        <span x-text="rotuloProximoStatus(pedido)"></span>
                                    </button>
                                    <button @click="imprimirPedido(pedido.id, 'cozinha')" class="px-2.5 py-2 bg-slate-700 text-slate-200 rounded-lg text-xs">🖨️</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Coluna 3: Prontos -->
                <div class="w-80 shrink-0 flex flex-col bg-slate-900/90 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
                    <div class="p-3.5 bg-emerald-950/20 border-b border-emerald-500/20 flex justify-between items-center">
                        <h2 class="font-bold text-sm text-emerald-400">3. Prontos p/ Despacho</h2>
                        <span class="bg-emerald-500/20 text-emerald-400 text-xs font-extrabold px-2 py-0.5 rounded-full" x-text="colunas.pronto.length"></span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 space-y-3">
                        <template x-for="pedido in colunas.pronto" :key="pedido.id">
                            <div class="bg-slate-800 rounded-xl p-3.5 border border-slate-700 space-y-2.5">
                                <div class="flex justify-between items-start">
                                    <button @click="abrirDetalhesPedido(pedido)" class="font-bold text-emerald-400 text-sm hover:underline" x-text="'#' + pedido.codigo_publico"></button>
                                    <div class="flex items-center gap-1">
                                        <span class="text-[11px] uppercase px-2 py-0.5 rounded bg-slate-700 text-slate-300" x-text="pedido.canal"></span>
                                        <button @click="abrirDetalhesPedido(pedido)" class="text-xs bg-slate-700 px-1.5 py-0.5 rounded">🔍</button>
                                    </div>
                                </div>
                                <div class="font-bold text-sm text-slate-200" x-text="pedido.cliente?.nome"></div>
                                <template x-if="pedido.canal === 'delivery' && entregadores.length > 0">
                                    <select @change="atribuirMotoboy(pedido.id, $event.target.value)" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-200">
                                        <option value="">🛵 Selecionar Entregador...</option>
                                        <template x-for="ent in entregadores" :key="ent.id">
                                            <option :value="ent.id" :selected="pedido.entregador_id === ent.id" x-text="ent.nome"></option>
                                        </template>
                                    </select>
                                </template>
                                <button @click="avancarPedido(pedido)" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-2 rounded-lg text-xs transition">
                                    <span x-text="rotuloProximoStatus(pedido)"></span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Coluna 4: Saiu para Entrega -->
                <div class="w-80 shrink-0 flex flex-col bg-slate-900/90 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
                    <div class="p-3.5 bg-sky-950/20 border-b border-sky-500/20 flex justify-between items-center">
                        <h2 class="font-bold text-sm text-sky-400">4. Em Rota de Entrega</h2>
                        <span class="bg-sky-500/20 text-sky-400 text-xs font-extrabold px-2 py-0.5 rounded-full" x-text="colunas.saiu_entrega.length"></span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 space-y-3">
                        <template x-for="pedido in colunas.saiu_entrega" :key="pedido.id">
                            <div class="bg-slate-800 rounded-xl p-3.5 border border-slate-700 space-y-2.5">
                                <div class="flex justify-between">
                                    <button @click="abrirDetalhesPedido(pedido)" class="font-bold text-sky-400 text-sm hover:underline" x-text="'#' + pedido.codigo_publico"></button>
                                    <span class="text-xs text-slate-400">🛵 Em trânsito</span>
                                </div>
                                <div class="font-bold text-sm text-slate-200" x-text="pedido.cliente?.nome"></div>
                                <button @click="avancarPedido(pedido)" class="w-full bg-sky-600 hover:bg-sky-500 text-white font-bold py-2 rounded-lg text-xs transition">
                                    ✓ Confirmar Entrega
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Coluna 5: Concluídos Hoje -->
                <div class="w-72 shrink-0 flex flex-col bg-slate-900/50 rounded-2xl border border-slate-800/80 overflow-hidden">
                    <div class="p-3.5 border-b border-slate-800 flex justify-between items-center">
                        <h2 class="font-bold text-sm text-slate-400">5. Concluídos Hoje</h2>
                        <span class="bg-slate-800 text-slate-400 text-xs font-bold px-2 py-0.5 rounded-full" x-text="colunas.finalizado.length"></span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-3 space-y-2">
                        <template x-for="pedido in colunas.finalizado" :key="pedido.id">
                            <div @click="abrirDetalhesPedido(pedido)" class="bg-slate-900 hover:bg-slate-800 cursor-pointer rounded-xl p-3 border border-slate-800/80 flex justify-between items-center opacity-80">
                                <div>
                                    <span class="font-bold text-xs text-slate-300" x-text="'#' + pedido.codigo_publico"></span>
                                    <span class="text-xs text-slate-500 block" x-text="pedido.cliente?.nome"></span>
                                </div>
                                <span class="text-xs font-bold text-emerald-400" x-text="formatarCentavos(pedido.total_centavos)"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </main>
        </div>

        <!-- ========================================================= -->
        <!-- MÓDULO 2: CARDÁPIO, CUPONS & ESTOQUE (PC & Mobile)         -->
        <!-- ========================================================= -->
        <div x-cloak x-show="moduloAtivo === 'catalogo'" class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-900 border border-slate-800 p-4 rounded-2xl">
                <div>
                    <h2 class="font-bold text-base text-white">Gestão Completa de Cardápio, Promoções &amp; Cupons</h2>
                    <p class="text-xs text-slate-400">Edite fotos WebP, preços promocionais ("De/Por"), variações de tamanho e cupons de desconto.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button @click="modalNovoCupom = true" class="bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 text-xs font-bold px-3.5 py-2.5 rounded-xl border border-emerald-500/30">
                        🎟️ + Novo Cupom
                    </button>
                    <button @click="modalNovaCategoria = true" class="bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold px-3.5 py-2.5 rounded-xl border border-slate-700">
                        + Nova Categoria
                    </button>
                    <button @click="abrirModalProduto()" class="bg-rose-600 hover:bg-rose-500 text-white text-xs font-extrabold px-4 py-2.5 rounded-xl shadow-md">
                        + Novo Produto
                    </button>
                </div>
            </div>

            <!-- Faixa de Cupons de Desconto Ativos (Etapa 2) -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-black text-white">🎟️ Cupons de Desconto da Loja</span>
                        <span class="text-[11px] bg-emerald-500/15 text-emerald-400 font-bold px-2 py-0.5 rounded-full" x-text="cupons.length + ' cadastrados'"></span>
                    </div>
                    <button @click="modalNovoCupom = true" class="text-xs font-bold text-rose-400 hover:underline">+ Criar Cupom</button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
                    <template x-for="cp in cupons" :key="cp.id">
                        <div class="bg-slate-950 border rounded-xl p-3 flex items-center justify-between gap-2"
                            :class="cp.ativo ? 'border-emerald-500/30' : 'border-slate-800 opacity-60'">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-black text-xs text-emerald-400 tracking-wider" x-text="cp.codigo"></span>
                                    <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-slate-800 text-slate-300"
                                        x-text="cp.tipo === 'percentual' ? (cp.valor + '% OFF') : formatarCentavos(cp.valor) + ' OFF'"></span>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5" x-text="'Mín: ' + formatarCentavos(cp.minimo_centavos || 0) + ' • Usos: ' + (cp.usos || 0)"></div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button @click="alternarCupom(cp.id)" class="text-[10px] font-bold px-2 py-1 rounded-lg"
                                    :class="cp.ativo ? 'bg-slate-800 text-slate-300' : 'bg-emerald-600 text-white'"
                                    x-text="cp.ativo ? 'Pausar' : 'Ativar'"></button>
                                <button @click="excluirCupom(cp.id)" class="text-xs text-rose-400 hover:bg-rose-500/15 px-1.5 py-1 rounded-lg">✕</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Filtro por Categoria -->
            <div class="flex overflow-x-auto no-scrollbar gap-2">
                <button @click="filtroCategoriaId = ''"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition"
                    :class="filtroCategoriaId === '' ? 'bg-rose-600 text-white' : 'bg-slate-900 text-slate-400 border border-slate-800'">
                    Todas as Categorias (<span x-text="produtos.length"></span>)
                </button>
                <template x-for="cat in categorias" :key="cat.id">
                    <button @click="filtroCategoriaId = cat.id"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition"
                        :class="filtroCategoriaId === cat.id ? 'bg-rose-600 text-white' : 'bg-slate-900 text-slate-400 border border-slate-800'"
                        x-text="cat.nome"></button>
                </template>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <template x-for="prod in produtosFiltrados" :key="prod.id">
                    <div class="bg-slate-900 border rounded-2xl p-4 flex flex-col justify-between gap-3"
                        :class="prod.esgotado ? 'border-rose-500/40 opacity-75' : 'border-slate-800'">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-1.5 mb-1">
                                    <span x-show="prod.esgotado" class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-rose-500/20 text-rose-400">Esgotado</span>
                                    <span x-show="prod.preco_promocional_centavos && prod.preco_promocional_centavos < prod.preco_centavos" class="text-[10px] font-black uppercase px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400">🔥 Promoção</span>
                                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-slate-800 text-slate-400" x-text="'KDS: ' + (prod.estacao_kds || 'geral')"></span>
                                </div>
                                <h3 class="font-bold text-sm text-white leading-snug" x-text="prod.nome"></h3>
                                <p class="text-xs text-slate-400 line-clamp-2 mt-1" x-text="prod.descricao"></p>
                                <template x-if="prod.variacoes && prod.variacoes.length > 0">
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        <template x-for="v in prod.variacoes" :key="v.id || v.nome">
                                            <span class="text-[10px] bg-slate-950 border border-slate-800 text-slate-300 px-2 py-0.5 rounded-lg" x-text="v.nome + ': ' + formatarCentavos(v.preco_centavos)"></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                            <template x-if="prod.imagem && (prod.imagem.startsWith('http') || prod.imagem.startsWith('/'))">
                                <img :src="prod.imagem" class="w-16 h-16 rounded-xl object-cover shrink-0 border border-slate-800">
                            </template>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-slate-800/80">
                            <div>
                                <span class="text-sm font-black text-emerald-400" x-text="formatarCentavos(prod.preco_efetivo_centavos)"></span>
                                <span x-show="prod.preco_promocional_centavos" class="text-xs text-slate-500 line-through ml-1" x-text="formatarCentavos(prod.preco_centavos)"></span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button @click="abrirModalProduto(prod)" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-200">
                                    ✏️ Editar
                                </button>
                                <button @click="alternarEstoqueProduto(prod)"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition"
                                    :class="prod.esgotado ? 'bg-emerald-600 text-white' : 'bg-slate-800 hover:bg-rose-600/20 text-slate-300 hover:text-rose-300 border border-slate-700'">
                                    <span x-text="prod.esgotado ? '✓ Ativar' : '⏸️ Pausar'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- MÓDULO 3: KDS COZINHA POR ESTAÇÃO (PC & Tablet/Mobile)    -->
        <!-- ========================================================= -->
        <div x-cloak x-show="moduloAtivo === 'kds'" class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3 bg-slate-900 border border-slate-800 p-4 rounded-2xl">
                <div>
                    <h2 class="font-bold text-base text-white">KDS — Tela de Produção da Cozinha por Estação</h2>
                    <p class="text-xs text-slate-400">Separa automaticamente Chapa, Forno, Fritadeira e Bar com cronômetro SLA de atraso.</p>
                </div>
                <div class="flex flex-wrap items-center gap-1.5">
                    <template x-for="est in ['', 'chapa', 'forno', 'fritadeira', 'bar', 'geral']" :key="est">
                        <button @click="estacaoKds = est; carregarKds()"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold uppercase transition"
                            :class="estacaoKds === est ? 'bg-rose-600 text-white' : 'bg-slate-800 text-slate-400'"
                            x-text="est === '' ? 'Todas' : est"></button>
                    </template>
                    <button @click="toggleFullscreenKds()" class="bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/30 px-3 py-1.5 rounded-xl text-xs font-extrabold transition">
                        📺 Modo TV Tela Cheia
                    </button>
                </div>
            </div>

            <template x-if="kdsTickets.length === 0">
                <div class="text-center py-16 text-slate-500 space-y-2">
                    <div class="text-3xl">👨‍🍳</div>
                    <p class="text-sm font-bold">Nenhum item pendente na fila de produção</p>
                </div>
            </template>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <template x-for="t in kdsTickets" :key="t.item_id">
                    <div class="bg-slate-900 border-2 rounded-2xl p-4 space-y-3 shadow-lg"
                        :class="t.atrasado ? 'border-rose-500 animate-pulse' : (t.status_cozinha === 'preparando' ? 'border-amber-500/60' : 'border-emerald-500/40')">
                        <div class="flex justify-between items-center">
                            <span class="font-black text-rose-400 text-sm" x-text="'#' + t.codigo_publico"></span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded"
                                    :class="t.atrasado ? 'bg-rose-500 text-white' : 'bg-emerald-500/20 text-emerald-400'"
                                    x-text="t.atrasado ? '⚠️ ATRASADO' : 'NO PRAZO'"></span>
                                <span class="text-[11px] font-bold uppercase px-2 py-0.5 rounded bg-slate-800 text-amber-400" x-text="t.estacao_kds"></span>
                            </div>
                        </div>
                        <div class="font-extrabold text-base text-white" x-text="t.qtd + 'x ' + t.produto_nome + (t.variacao ? ' (' + t.variacao + ')' : '')"></div>
                        <template x-if="t.adicionais && t.adicionais.length > 0">
                            <div class="text-xs text-slate-300 bg-slate-950 p-2.5 rounded-xl" x-text="t.adicionais.join(', ')"></div>
                        </template>
                        <template x-if="t.observacao">
                            <div class="text-xs font-bold text-amber-300 bg-amber-500/15 border border-amber-500/30 p-2 rounded-xl" x-text="'⚠️ Obs: ' + t.observacao"></div>
                        </template>
                        <button @click="avancarItemKds(t)"
                            class="w-full font-bold py-2.5 rounded-xl text-xs transition"
                            :class="t.status_cozinha === 'fila' ? 'bg-amber-600 hover:bg-amber-500 text-white' : 'bg-emerald-600 hover:bg-emerald-500 text-white'">
                            <span x-text="t.status_cozinha === 'fila' ? '🔥 Iniciar Preparo' : '✓ Marcar Item Pronto'"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- MÓDULO 4: MESAS, COMANDAS & DIVISÃO DE CONTA (Etapa 3)    -->
        <!-- ========================================================= -->
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
                            <span class="text-[11px] font-bold uppercase px-2.5 py-0.5 rounded-full"
                                :class="mesa.status === 'livre' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400'"
                                x-text="mesa.status"></span>
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

        <!-- ========================================================= -->
        <!-- MÓDULO 5: ENTREGADORES & TAXAS DE FRETE                   -->
        <!-- ========================================================= -->
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

        <!-- ========================================================= -->
        <!-- MÓDULO 6: CAIXA, FINANCEIRO & PIX MERCADO PAGO            -->
        <!-- ========================================================= -->
        <div x-cloak x-show="moduloAtivo === 'financeiro'" class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
                    <span class="text-xs font-bold uppercase text-slate-400">Faturamento Concluído</span>
                    <div class="text-2xl font-black text-emerald-400" x-text="formatarCentavos(relatorio.faturamento_total_centavos || volumeDiaCentavos)"></div>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
                    <span class="text-xs font-bold uppercase text-slate-400">Pedidos no Período</span>
                    <div class="text-2xl font-black text-white" x-text="relatorio.total_pedidos_concluidos || pedidos.length"></div>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
                    <span class="text-xs font-bold uppercase text-slate-400">Ticket Médio</span>
                    <div class="text-2xl font-black text-rose-400" x-text="formatarCentavos(relatorio.ticket_medio_centavos || (pedidos.length ? Math.round(volumeDiaCentavos / pedidos.length) : 0))"></div>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
                    <span class="text-xs font-bold uppercase text-slate-400">Clientes Atendidos</span>
                    <div class="text-2xl font-black text-amber-400" x-text="((relatorio.clientes?.novos || 0) + (relatorio.clientes?.recorrentes || 0)) || pedidos.length"></div>
                </div>
            </div>

            <!-- Controle de Frente de Caixa -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-base text-white">💵 Controle de Frente de Caixa (Abertura, Sangria e Suprimento)</h3>
                        <p class="text-xs text-slate-400">Registre abertura de turno e movimentações em espécie da gaveta.</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="abrirCaixa()" class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-extrabold px-4 py-2.5 rounded-xl">
                            Abrir Turno de Caixa
                        </button>
                    </div>
                </div>
            </div>

            <!-- Central Completa de Cadastro e Autorização PIX Mercado Pago (0% Custódia) -->
            <div class="bg-slate-900 border-2 rounded-3xl p-6 space-y-5"
                 :class="mpStatus?.conectado ? 'border-emerald-500/40' : 'border-amber-500/40'">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-base font-black text-white">⚡ Cadastro e Autorização da Conta Mercado Pago (PIX 0% Taxa)</span>
                            <span class="text-xs font-black px-3 py-0.5 rounded-full border"
                                  :class="mpStatus?.conectado ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border-amber-500/30'"
                                  x-text="mpStatus?.conectado ? '✓ Conta Conectada (' + (mpStatus.mp_conta_nome || mpStatus.mp_user_id || 'Ativa') + ')' : '⚠️ Conta Ainda Não Vinculada'">
                            </span>
                        </div>
                        <p class="text-xs text-slate-400">
                            Os pagamentos PIX dos seus clientes geram QR Code dinâmico do Banco Central e o dinheiro cai <strong>100% direto na sua conta Mercado Pago</strong> em segundos.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2 shrink-0">
                        <template x-if="mpStatus?.conectado">
                            <button @click="testarPixMercadoPago()" :disabled="testandoPix" class="bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs px-4 py-2.5 rounded-xl transition shadow-md">
                                <span x-text="testandoPix ? 'Gerando PIX...' : '🧪 Testar PIX Real (R$ 1,00)'"></span>
                            </button>
                        </template>
                        <button @click="conectarMercadoPago()" class="bg-sky-600 hover:bg-sky-500 text-white font-extrabold text-xs px-4 py-2.5 rounded-xl transition shadow-md">
                            🔗 Conectar com Mercado Pago (Login 1 Clique)
                        </button>
                        <template x-if="mpStatus?.conectado">
                            <button @click="desconectarMercadoPago()" class="bg-slate-800 hover:bg-rose-600/20 text-slate-300 hover:text-rose-300 border border-slate-700 font-bold text-xs px-3.5 py-2.5 rounded-xl transition">
                                Desconectar
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Resultado do Teste de Emissão PIX Ao Vivo -->
                <template x-if="pixTesteResultado">
                    <div class="bg-emerald-950/40 border border-emerald-500/40 rounded-2xl p-4 flex flex-col sm:flex-row items-center gap-4">
                        <template x-if="pixTesteResultado.pix_qr_base64">
                            <img :src="'data:image/png;base64,' + pixTesteResultado.pix_qr_base64" class="w-28 h-28 rounded-xl bg-white p-1.5 shrink-0">
                        </template>
                        <div class="space-y-2 text-xs flex-1 min-w-0">
                            <div class="font-black text-emerald-400 text-sm" x-text="'✓ ' + pixTesteResultado.message"></div>
                            <div class="text-slate-300">ID da Transação Mercado Pago: <strong class="text-white" x-text="pixTesteResultado.mp_payment_id"></strong></div>
                            <input type="text" readonly :value="pixTesteResultado.pix_copia_cola" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-[11px] text-emerald-300 font-mono">
                        </div>
                        <button @click="pixTesteResultado = null" class="text-slate-400 font-bold text-sm self-start">✕</button>
                    </div>
                </template>

                <!-- Formulário Direto de Credenciais do Lojista (Access Token APP_USR-...) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    <form @submit.prevent="salvarTokenMercadoPago()" class="lg:col-span-7 space-y-3 text-xs">
                        <div class="font-extrabold text-sm text-white">🔐 Cadastrar / Autorizar via Credencial Oficial (Access Token)</div>
                        <div>
                            <label class="font-bold text-slate-300 block mb-1">Access Token de Produção do Mercado Pago (<code class="text-emerald-400">APP_USR-...</code>) *</label>
                            <input type="password" x-model="formMp.access_token" required placeholder="Cole aqui seu token APP_USR-..."
                                   class="w-full bg-slate-950 border border-slate-800 focus:border-sky-500 rounded-xl px-3.5 py-2.5 text-white font-mono">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="font-bold text-slate-400 block mb-1">Public Key (Opcional)</label>
                                <input type="text" x-model="formMp.public_key" placeholder="APP_USR-..."
                                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-mono">
                            </div>
                            <div>
                                <label class="font-bold text-slate-400 block mb-1">Sua Chave PIX Principal (Opcional)</label>
                                <input type="text" x-model="formMp.chave_pix" placeholder="CNPJ, CPF, E-mail ou Celular"
                                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                            </div>
                        </div>
                        <button type="submit" :disabled="salvandoMp" class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-black py-3 rounded-xl text-sm shadow-lg transition">
                            <span x-text="salvandoMp ? 'Validando conta no Mercado Pago...' : '✓ Validar e Autorizar Minha Conta Mercado Pago'"></span>
                        </button>
                    </form>

                    <!-- Passo a Passo Rápido para o Lojista -->
                    <div class="lg:col-span-5 bg-slate-950 border border-slate-800 rounded-2xl p-4 space-y-2.5 text-xs">
                        <div class="font-black text-sky-400 uppercase tracking-wider text-[11px]">📖 Como pegar seu Access Token em 1 minuto:</div>
                        <ol class="list-decimal list-inside space-y-1.5 text-slate-300 leading-relaxed">
                            <li>Acesse o <a href="https://www.mercadopago.com.br/developers/panel/app" target="_blank" class="text-sky-400 underline font-bold">Painel Mercado Pago Developers ↗</a> com a conta da sua loja.</li>
                            <li>Clique em <strong>Criar aplicação</strong> (ou abra a sua) e vá em <strong>Credenciais de Produção</strong> no menu lateral.</li>
                            <li>Copie o <strong>Access Token</strong> (começa com <code class="text-emerald-400">APP_USR-</code>) e cole no campo ao lado.</li>
                        </ol>
                        <div class="pt-2 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                            <span>🔒 Criptografado em AES-256-GCM</span>
                            <span class="text-emerald-400 font-bold">0% Taxa do SaaS</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- MÓDULO CRM: CLIENTES, CASHBACK & RECUPERAÇÃO WHATSAPP     -->
        <!-- ========================================================= -->
        <div x-cloak x-show="moduloAtivo === 'crm'" class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
                    <span class="text-xs font-bold uppercase text-slate-400">Base Total de Clientes</span>
                    <div class="text-2xl font-black text-white" x-text="(relatorio.clientes_lista || []).length"></div>
                    <p class="text-[11px] text-slate-500">Cadastrados automaticamente no checkout</p>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
                    <span class="text-xs font-bold uppercase text-emerald-400">Clientes Fidelizados (2+ pedidos)</span>
                    <div class="text-2xl font-black text-emerald-400" x-text="relatorio.clientes?.recorrentes || 0"></div>
                    <p class="text-[11px] text-slate-500">Compram com recorrência e Cashback</p>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
                    <span class="text-xs font-bold uppercase text-amber-400">Sumidos há 30+ Dias (Win-Back)</span>
                    <div class="text-2xl font-black text-amber-400" x-text="relatorio.clientes?.elegiveis_winback_30d || 0"></div>
                    <p class="text-[11px] text-slate-500">Prontos p/ disparo 1-clique no WhatsApp</p>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-1">
                    <span class="text-xs font-bold uppercase text-rose-400">Cupons Ativos na Loja</span>
                    <div class="text-2xl font-black text-rose-400" x-text="cupons.filter(c => c.ativo).length"></div>
                    <p class="text-[11px] text-slate-500">Ex: BEMVINDO10 / VOLTE10</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Ranking de Clientes & Disparo Win-Back WhatsApp -->
                <div class="lg:col-span-8 bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h2 class="font-bold text-base text-white">👥 Ranking de Clientes, Saldo Cashback &amp; Recuperação WhatsApp</h2>
                            <p class="text-xs text-slate-400">Envie cupons personalizados em 1 clique para clientes fiéis ou que não pedem há mais de 30 dias.</p>
                        </div>
                        <button @click="carregarFinanceiro()" class="bg-slate-800 hover:bg-slate-700 text-xs font-bold px-3 py-2 rounded-xl text-slate-200 self-start">
                            ↻ Atualizar CRM
                        </button>
                    </div>

                    <template x-if="!relatorio.clientes_lista || relatorio.clientes_lista.length === 0">
                        <div class="text-center py-12 text-slate-500 text-xs font-bold">
                            Nenhum cliente registrado ainda. Assim que o primeiro pedido for feito, o ranking aparecerá aqui!
                        </div>
                    </template>

                    <div class="space-y-2.5">
                        <template x-for="cli in (relatorio.clientes_lista || [])" :key="cli.id">
                            <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="space-y-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-bold text-sm text-white" x-text="cli.nome"></span>
                                        <span class="text-xs text-slate-400" x-text="cli.telefone"></span>
                                        <span x-show="cli.inativo_30d" class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                            ⚠️ Sumido há <span x-text="cli.dias_sem_pedir"></span>d
                                        </span>
                                        <span x-show="!cli.inativo_30d && cli.total_pedidos > 1" class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300">
                                            ⭐ Cliente Fiel
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400">
                                        <span>Pedidos: <strong class="text-white" x-text="cli.total_pedidos"></strong></span>
                                        <span>Total gasto: <strong class="text-emerald-400" x-text="formatarCentavos(cli.total_gasto_centavos)"></strong></span>
                                        <span>Saldo Cashback: <strong class="text-amber-400" x-text="formatarCentavos(cli.saldo_cashback_centavos)"></strong></span>
                                        <span x-show="cli.ultimo_pedido_em" x-text="'Último: ' + cli.ultimo_pedido_em"></span>
                                    </div>
                                </div>
                                <template x-if="cli.whatsapp_winback_url">
                                    <a :href="cli.whatsapp_winback_url" target="_blank"
                                        class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-extrabold px-3.5 py-2.5 rounded-xl flex items-center justify-center gap-1.5 shrink-0 shadow-md">
                                        <span>💬 Enviar Cupom WhatsApp ↗</span>
                                    </a>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Gráfico Visual de Horários de Pico -->
                <div class="lg:col-span-4 bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4">
                    <div>
                        <h3 class="font-bold text-base text-white">📊 Horários de Pico da Operação</h3>
                        <p class="text-xs text-slate-400">Volume de pedidos concluídos por faixa de horário para dimensionar cozinha e motoboys.</p>
                    </div>

                    <template x-if="Object.keys(relatorio.horarios_pico || {}).length === 0">
                        <div class="text-center py-10 text-slate-500 text-xs">
                            Conclua pedidos hoje para visualizar o mapa de calor por horário.
                        </div>
                    </template>

                    <div class="space-y-2.5">
                        <template x-for="[hora, qtd] in Object.entries(relatorio.horarios_pico || {})" :key="hora">
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs font-bold">
                                    <span class="text-slate-300" x-text="'🕒 ' + hora"></span>
                                    <span class="text-rose-400" x-text="qtd + ' pedido(s)'"></span>
                                </div>
                                <div class="w-full h-2.5 bg-slate-950 rounded-full overflow-hidden border border-slate-800">
                                    <div class="h-full bg-gradient-to-r from-rose-600 to-amber-500 rounded-full"
                                         :style="'width: ' + Math.min(100, Math.max(15, Math.round((qtd / Math.max(1, ...Object.values(relatorio.horarios_pico || {a:1}))) * 100))) + '%'"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- MÓDULO 7: CONFIGURAÇÕES DA LOJA & ASSINATURA SAAS         -->
        <!-- ========================================================= -->
        <div x-cloak x-show="moduloAtivo === 'config'" class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-7 bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4">
                    <h2 class="font-bold text-base text-white">⚙️ Dados Operacionais &amp; Identidade Visual do Restaurante</h2>
                    <form @submit.prevent="salvarDadosLoja()" class="space-y-3 text-xs">
                        <div>
                            <label class="font-bold text-slate-400 block mb-1">Nome Fantasia da Loja</label>
                            <input type="text" x-model="configLoja.nome" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-bold">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="font-bold text-slate-400 block mb-1">WhatsApp de Atendimento</label>
                                <input type="text" x-model="configLoja.telefone_whatsapp" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                            </div>
                            <div>
                                <label class="font-bold text-slate-400 block mb-1">Cor Primária da Marca (Hex)</label>
                                <div class="flex gap-2">
                                    <input type="color" x-model="configLoja.cor_primaria" class="h-10 w-12 rounded-xl bg-slate-950 border border-slate-800 cursor-pointer">
                                    <input type="text" x-model="configLoja.cor_primaria" class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-mono">
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="font-bold text-slate-400 block mb-1">Endereço Completo</label>
                            <input type="text" x-model="configLoja.endereco" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="font-bold text-slate-400 block mb-1">Cidade</label>
                                <input type="text" x-model="configLoja.cidade" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                            </div>
                            <div>
                                <label class="font-bold text-slate-400 block mb-1">UF</label>
                                <input type="text" x-model="configLoja.uf" maxlength="2" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white uppercase">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-extrabold py-3 rounded-xl text-sm shadow-lg transition">
                            💾 Salvar Dados e Aplicar Cor no Cardápio
                        </button>
                    </form>
                </div>

                <div class="lg:col-span-5 bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4 flex flex-col justify-between">
                    <div class="space-y-3">
                        <span class="text-xs font-black uppercase text-rose-400">Minha Assinatura PedeLivre</span>
                        <h3 class="text-xl font-black text-white">Plano Premium Total</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Você possui todos os módulos liberados: Cardápio Digital de Alta Conversão, Delivery, QR Code de Mesa, KDS por Estação, CRM Cashback e PIX Mercado Pago 0% taxa.
                        </p>
                        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 space-y-2 text-xs">
                            <div class="flex justify-between"><span class="text-slate-400">Status da Conta:</span><span class="text-emerald-400 font-bold">✓ Ativa e Regular</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Comissão sobre Vendas:</span><span class="text-emerald-400 font-bold">0% (Isento)</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Link Público:</span><span class="text-rose-400 font-bold" x-text="'/p/' + (loja?.slug || '')"></span></div>
                        </div>
                    </div>

                    <button @click="copiarLinkCardapio()" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black py-3 rounded-xl text-center text-xs transition shadow-lg">
                        📲 Copiar Link do Cardápio p/ WhatsApp &amp; Bio
                    </button>
                </div>
            </div>

            <!-- Card de Conexão WhatsApp na aba Loja/Config -->
            <div class="bg-slate-900 border-2 rounded-3xl p-6 space-y-4"
                 :class="whatsappConectado ? 'border-emerald-500/40' : 'border-slate-800'">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">💬</span>
                            <h3 class="font-extrabold text-sm text-white">WhatsApp &amp; Automações da Loja</h3>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-extrabold"
                                  :class="whatsappConectado ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700'"
                                  x-text="whatsappConectado ? ('🟢 Conectado: +' + (whatsappStatus.phone || 'Ativo')) : '⚪ Desconectado'"></span>
                        </div>
                        <p class="text-xs text-slate-400">
                            Dispare avisos aos clientes a cada fase do pedido, acione motoboys com rota no Google Maps e tenha um bot empático para acalmar atrasos.
                        </p>
                    </div>
                    <button @click="mudarModulo('whatsapp')" class="bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold px-4 py-2 rounded-xl text-xs shadow-lg transition shrink-0">
                        <span x-text="whatsappConectado ? '⚙️ Gerenciar Automações' : '📱 Conectar WhatsApp (QR Code)'"></span>
                    </button>
                </div>
            </div>

            <!-- Card de Cadastro e Autorização da Conta Mercado Pago também dentro de Loja/Config (acessível no Celular e PC) -->
            <div class="bg-slate-900 border-2 rounded-3xl p-6 space-y-5"
                 :class="mpStatus?.conectado ? 'border-emerald-500/40' : 'border-amber-500/40'">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-base font-black text-white">⚡ Conta Mercado Pago para Receber PIX (0% Comissão)</h3>
                            <span class="text-xs font-black px-3 py-0.5 rounded-full border"
                                  :class="mpStatus?.conectado ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border-amber-500/30'"
                                  x-text="mpStatus?.conectado ? '✓ Conectada (' + (mpStatus.mp_conta_nome || mpStatus.mp_user_id || 'Ativa') + ')' : '⚠️ Pendente de Autorização'">
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Cadastre seu Access Token do Mercado Pago ou autorize via OAuth para receber os pagamentos PIX direto na sua conta bancária.</p>
                    </div>
                    <div class="flex flex-wrap gap-2 shrink-0">
                        <template x-if="mpStatus?.conectado">
                            <button @click="testarPixMercadoPago()" :disabled="testandoPix" class="bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs px-3.5 py-2.5 rounded-xl">
                                <span x-text="testandoPix ? 'Gerando...' : '🧪 Testar PIX Real'"></span>
                            </button>
                        </template>
                        <button @click="conectarMercadoPago()" class="bg-sky-600 hover:bg-sky-500 text-white font-extrabold text-xs px-3.5 py-2.5 rounded-xl">
                            🔗 Conectar com Mercado Pago
                        </button>
                        <template x-if="mpStatus?.conectado">
                            <button @click="desconectarMercadoPago()" class="bg-slate-800 hover:bg-rose-600/20 text-slate-300 hover:text-rose-300 border border-slate-700 font-bold text-xs px-3 py-2.5 rounded-xl">
                                Desconectar
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Resultado do Teste de Emissão PIX Ao Vivo (Mobile/Config) -->
                <template x-if="pixTesteResultado">
                    <div class="bg-emerald-950/40 border border-emerald-500/40 rounded-2xl p-4 flex flex-col sm:flex-row items-center gap-4">
                        <template x-if="pixTesteResultado.pix_qr_base64">
                            <img :src="'data:image/png;base64,' + pixTesteResultado.pix_qr_base64" class="w-28 h-28 rounded-xl bg-white p-1.5 shrink-0">
                        </template>
                        <div class="space-y-2 text-xs flex-1 min-w-0 w-full">
                            <div class="font-black text-emerald-400 text-sm" x-text="'✓ ' + pixTesteResultado.message"></div>
                            <div class="text-slate-300">ID Mercado Pago: <strong class="text-white" x-text="pixTesteResultado.mp_payment_id"></strong></div>
                            <input type="text" readonly :value="pixTesteResultado.pix_copia_cola" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-[11px] text-emerald-300 font-mono">
                        </div>
                        <button @click="pixTesteResultado = null" class="text-slate-400 font-bold text-sm self-start">✕</button>
                    </div>
                </template>

                <form @submit.prevent="salvarTokenMercadoPago()" class="grid grid-cols-1 md:grid-cols-12 gap-3 text-xs items-end">
                    <div class="md:col-span-5">
                        <label class="font-bold text-slate-300 block mb-1">Access Token Mercado Pago (<code class="text-emerald-400">APP_USR-...</code>) *</label>
                        <input type="password" x-model="formMp.access_token" required placeholder="APP_USR-0000000000000000-..."
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-mono">
                    </div>
                    <div class="md:col-span-4">
                        <label class="font-bold text-slate-400 block mb-1">Sua Chave PIX Principal (Opcional)</label>
                        <input type="text" x-model="formMp.chave_pix" placeholder="CNPJ, CPF, E-mail ou Celular"
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                    </div>
                    <div class="md:col-span-3">
                        <button type="submit" :disabled="salvandoMp" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black py-2.5 rounded-xl text-xs shadow-lg transition">
                            <span x-text="salvandoMp ? 'Validando...' : '✓ Autorizar Conta MP'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- MÓDULO 8: WHATSAPP INTEGRADO, NOTIFICAÇÕES & BOT ANTI-ANSIEDADE -->
        <!-- ========================================================= -->
        <div x-cloak x-show="moduloAtivo === 'whatsapp'" class="flex-1 overflow-y-auto p-4 lg:p-6 pb-24 space-y-6">
            
            <!-- Sub-Navegação do Módulo WhatsApp -->
            <div class="flex items-center gap-3 border-b border-slate-800 pb-3">
                <button @click="mudarSubAbaWhatsapp('chat')"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition relative"
                    :class="subAbaWhatsapp === 'chat' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800'">
                    <span>💬</span>
                    <span>Central de Atendimento ao Vivo</span>
                    <span x-show="totalAguardandoHumano > 0"
                        class="bg-amber-400 text-slate-950 text-[10px] font-black px-1.5 py-0.2 rounded-full animate-pulse"
                        x-text="totalAguardandoHumano + ' humano'"></span>
                </button>

                <button @click="mudarSubAbaWhatsapp('config')"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition"
                    :class="subAbaWhatsapp === 'config' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800'">
                    <span>⚙️</span>
                    <span>Robôs &amp; Automações</span>
                    <span class="w-2 h-2 rounded-full" :class="whatsappConectado ? 'bg-emerald-400' : 'bg-slate-600'"></span>
                </button>
            </div>

            <!-- ========================================================= -->
            <!-- SUB-ABA 1: CENTRAL DE ATENDIMENTO AO VIVO (WEBCHAT)       -->
            <!-- ========================================================= -->
            <div x-show="subAbaWhatsapp === 'chat'" class="grid grid-cols-1 lg:grid-cols-12 gap-5 h-[calc(100vh-210px)] min-h-[580px]">
                
                <!-- Coluna Esquerda: Lista de Conversas -->
                <div class="lg:col-span-4 bg-slate-900 border border-slate-800 rounded-3xl p-4 flex flex-col h-full shadow-xl">
                    <div class="space-y-3 pb-3 border-b border-slate-800">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-black text-white flex items-center gap-2">
                                <span>💬</span> Conversas WhatsApp
                            </h3>
                            <button @click="carregarConversasWhatsapp()" class="text-[11px] text-slate-400 hover:text-white flex items-center gap-1 transition">
                                🔄 Atualizar
                            </button>
                        </div>
                        
                        <!-- Barra de Busca -->
                        <div class="relative">
                            <input type="text" x-model="buscaConversas" @input.debounce.300ms="carregarConversasWhatsapp()"
                                placeholder="Buscar cliente, número ou mensagem..."
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 pl-9 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500">
                            <span class="absolute left-3 top-2.5 text-xs text-slate-500">🔍</span>
                        </div>

                        <!-- Filtros Rápidos -->
                        <div class="flex items-center gap-1.5 pt-1">
                            <button @click="filtroEstadoConversa = 'todos'; carregarConversasWhatsapp()"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition"
                                :class="filtroEstadoConversa === 'todos' ? 'bg-slate-700 text-white' : 'bg-slate-950 text-slate-400 hover:text-slate-200 border border-slate-800'">
                                Todos
                            </button>
                            <button @click="filtroEstadoConversa = 'humano'; carregarConversasWhatsapp()"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition flex items-center gap-1"
                                :class="filtroEstadoConversa === 'humano' ? 'bg-rose-600 text-white' : 'bg-slate-950 text-rose-400 hover:text-rose-300 border border-slate-800'">
                                <span>🚨 Humano</span>
                                <span x-show="totalAguardandoHumano > 0" class="bg-white/20 text-white px-1.5 py-0.2 rounded-full text-[9px] font-black" x-text="totalAguardandoHumano"></span>
                            </button>
                            <button @click="filtroEstadoConversa = 'ia_ativa'; carregarConversasWhatsapp()"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition"
                                :class="filtroEstadoConversa === 'ia_ativa' ? 'bg-emerald-600 text-white' : 'bg-slate-950 text-emerald-400 hover:text-emerald-300 border border-slate-800'">
                                🤖 Com o Robô
                            </button>
                        </div>
                    </div>

                    <!-- Lista com Scroll -->
                    <div class="flex-1 overflow-y-auto mt-3 space-y-2 pr-1 custom-scrollbar">
                        <template x-if="chatCarregando && conversasWhatsapp.length === 0">
                            <div class="text-center py-12 text-slate-500 text-xs">
                                Carregando conversas...
                            </div>
                        </template>

                        <template x-if="!chatCarregando && conversasWhatsapp.length === 0">
                            <div class="text-center py-12 text-slate-500 text-xs space-y-2">
                                <span class="text-3xl block">📭</span>
                                <p>Nenhuma conversa encontrada.</p>
                                <p class="text-[11px] text-slate-600">As conversas aparecerão aqui assim que os clientes enviarem mensagens no WhatsApp.</p>
                            </div>
                        </template>

                        <template x-for="c in conversasWhatsapp" :key="c.id">
                            <div @click="selecionarConversa(c)"
                                class="p-3 rounded-2xl border transition cursor-pointer flex items-start gap-3"
                                :class="conversaSelecionada?.id === c.id 
                                    ? 'bg-rose-600/15 border-rose-500/50 shadow-md' 
                                    : (c.estado === 'humano' ? 'bg-rose-950/20 border-rose-800/40 hover:bg-slate-800/80' : 'bg-slate-950/60 border-slate-800/80 hover:bg-slate-800/50')">
                                
                                <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-sm font-black shrink-0"
                                    :class="c.estado === 'humano' ? 'bg-rose-600/20 text-rose-300 border border-rose-500/30' : 'bg-emerald-600/20 text-emerald-300 border border-emerald-500/30'">
                                    <span x-text="c.cliente_nome ? c.cliente_nome.charAt(0).toUpperCase() : '📱'"></span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1">
                                        <h4 class="text-xs font-black text-white truncate" x-text="c.cliente_nome"></h4>
                                        <span class="text-[10px] text-slate-500 shrink-0 font-medium" x-text="c.ultimo_horario"></span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 truncate mt-0.5" x-text="c.ultima_mensagem || 'Sem mensagens'"></p>
                                    
                                    <div class="flex items-center gap-1.5 mt-1.5">
                                        <template x-if="c.estado === 'humano'">
                                            <span class="bg-rose-500/20 text-rose-300 border border-rose-500/30 text-[9px] px-2 py-0.5 rounded-md font-black flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-ping"></span>
                                                🚨 Aguardando Atendente
                                            </span>
                                        </template>
                                        <template x-if="c.estado === 'ia_ativa'">
                                            <span class="bg-slate-800 text-slate-400 border border-slate-700/60 text-[9px] px-1.5 py-0.5 rounded-md font-bold">
                                                🤖 Robô Ativo
                                            </span>
                                        </template>
                                        <span class="text-[10px] text-slate-500" x-text="c.telefone"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Coluna Direita: Janela de Chat Ativo -->
                <div class="lg:col-span-8 bg-slate-900 border border-slate-800 rounded-3xl flex flex-col h-full overflow-hidden shadow-xl">
                    
                    <template x-if="!conversaSelecionada">
                        <div class="flex-1 flex flex-col items-center justify-center p-8 text-center space-y-4 bg-slate-950/40">
                            <div class="w-16 h-16 rounded-3xl bg-slate-800/80 border border-slate-700 flex items-center justify-center text-3xl shadow-inner">
                                💬
                            </div>
                            <div class="max-w-md space-y-1">
                                <h3 class="text-base font-black text-white">Central de Atendimento ao Vivo</h3>
                                <p class="text-xs text-slate-400 leading-relaxed">
                                    Selecione uma conversa ao lado para acompanhar as mensagens em tempo real ou atender o cliente diretamente pelo painel sem precisar abrir o WhatsApp Web.
                                </p>
                            </div>
                            <div class="text-[11px] text-slate-500 bg-slate-900 px-4 py-2 rounded-xl border border-slate-800">
                                ⚡ Suporte multiatendente integrado em tempo real com Evolution API
                            </div>
                        </div>
                    </template>

                    <template x-if="conversaSelecionada">
                        <div class="flex flex-col h-full">
                            
                            <!-- Header da Conversa Ativa -->
                            <div class="p-4 bg-slate-950/80 border-b border-slate-800 flex items-center justify-between gap-3 shrink-0">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-2xl bg-rose-600/20 text-rose-300 border border-rose-500/30 flex items-center justify-center font-black shrink-0">
                                        <span x-text="conversaSelecionada.cliente_nome ? conversaSelecionada.cliente_nome.charAt(0).toUpperCase() : '📱'"></span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-sm font-black text-white truncate" x-text="conversaSelecionada.cliente_nome"></h3>
                                            <span class="text-xs text-slate-400" x-text="conversaSelecionada.telefone"></span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <template x-if="conversaSelecionada.estado === 'humano'">
                                                <span class="bg-rose-500/20 text-rose-300 border border-rose-500/30 text-[10px] px-2 py-0.2 rounded-md font-bold flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-pulse"></span>
                                                    Atendimento Humano em Andamento
                                                </span>
                                            </template>
                                            <template x-if="conversaSelecionada.estado === 'ia_ativa'">
                                                <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] px-2 py-0.2 rounded-md font-bold flex items-center gap-1">
                                                    <span>🤖</span> Robô Respondendo Automaticamente
                                                </span>
                                            </template>
                                            <template x-if="conversaSelecionada.pedido_recente">
                                                <span class="text-[10px] text-slate-400 bg-slate-900 px-2 py-0.2 rounded-md border border-slate-800"
                                                    x-text="'Pedido #' + conversaSelecionada.pedido_recente.codigo + ' (' + conversaSelecionada.pedido_recente.status + ')'"></span>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <template x-if="conversaSelecionada.estado === 'humano'">
                                        <button @click="devolverConversaBot(conversaSelecionada.id)" :disabled="chatEnviando"
                                            class="bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold px-3 py-1.5 rounded-xl text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                                            <span>🤖</span> Devolver p/ o Robô
                                        </button>
                                    </template>
                                    <template x-if="conversaSelecionada.estado === 'ia_ativa'">
                                        <button @click="assumirConversaHumano(conversaSelecionada.id)" :disabled="chatEnviando"
                                            class="bg-rose-600 hover:bg-rose-500 text-white font-extrabold px-3 py-1.5 rounded-xl text-xs shadow-md shadow-rose-600/20 transition flex items-center gap-1.5">
                                            <span>👨‍💼</span> Assumir Atendimento
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Feed de Mensagens -->
                            <div x-ref="chatMessagesBox" class="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-950/40 custom-scrollbar">
                                <template x-if="chatCarregando && mensagensConversa.length === 0">
                                    <div class="text-center py-12 text-slate-500 text-xs">
                                        Carregando histórico de mensagens...
                                    </div>
                                </template>

                                <template x-for="m in mensagensConversa" :key="m.id">
                                    <div class="flex" :class="m.direcao === 'saida' ? 'justify-end' : 'justify-start'">
                                        <div class="max-w-[78%] rounded-2xl px-4 py-2.5 text-xs shadow-md"
                                            :class="m.direcao === 'saida' 
                                                ? 'bg-rose-600 text-white rounded-tr-none border border-rose-500/50' 
                                                : 'bg-slate-800 text-slate-100 rounded-tl-none border border-slate-700/60'">
                                            <p class="whitespace-pre-wrap leading-relaxed" x-text="m.mensagem"></p>
                                            <div class="flex items-center justify-end gap-1 mt-1 text-[10px]"
                                                :class="m.direcao === 'saida' ? 'text-rose-200' : 'text-slate-400'">
                                                <span x-text="m.horario"></span>
                                                <template x-if="m.direcao === 'saida'">
                                                    <span>✓✓</span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Atalhos de Resposta Rápida (1-Clique) -->
                            <div class="px-4 py-2 bg-slate-950/80 border-t border-slate-800/80 flex items-center gap-2 overflow-x-auto whitespace-nowrap text-[11px] custom-scrollbar">
                                <span class="text-slate-500 text-[10px] uppercase font-bold shrink-0">⚡ Respostas Rápidas:</span>
                                <button @click="usarRespostaRapida('Olá! Seu pedido já está prontinho e saindo para entrega com o nosso motoboy! 🛵💨')"
                                    class="bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 px-2.5 py-1 rounded-lg transition shrink-0">
                                    🛵 Saiu p/ Entrega
                                </button>
                                <button @click="usarRespostaRapida('Olá! Nosso chef está finalizando seu pedido na cozinha agora mesmo com muito capricho! 👨‍🍳🔥')"
                                    class="bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 px-2.5 py-1 rounded-lg transition shrink-0">
                                    👨‍🍳 Em Finalização
                                </button>
                                <button @click="usarRespostaRapida('Poderia nos confirmar por gentileza o seu endereço completo com ponto de referência? 📍')"
                                    class="bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 px-2.5 py-1 rounded-lg transition shrink-0">
                                    📍 Confirmar Endereço
                                </button>
                                <button @click="usarRespostaRapida('Nosso entregador levará a maquininha de cartão! Você prefere débito ou crédito? 💳')"
                                    class="bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 px-2.5 py-1 rounded-lg transition shrink-0">
                                    💳 Maquininha Cartão
                                </button>
                                <button @click="usarRespostaRapida('Pedimos imensas desculpas pelo transtorno! Já estamos resolvendo com prioridade máxima para você! 🙏')"
                                    class="bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 px-2.5 py-1 rounded-lg transition shrink-0">
                                    🙏 Pedir Desculpas
                                </button>
                            </div>

                            <!-- Barra de Envio de Mensagem -->
                            <div class="p-3 bg-slate-950 border-t border-slate-800 flex items-center gap-2">
                                <input type="text" x-model="mensagemChatInput" @keydown.enter.prevent="enviarMensagemChat()"
                                    placeholder="Digite sua resposta e pressione Enter para enviar pelo WhatsApp..."
                                    class="flex-1 bg-slate-900 border border-slate-800 rounded-2xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500">
                                
                                <button @click="enviarMensagemChat()" :disabled="chatEnviando || !mensagemChatInput.trim()"
                                    class="bg-rose-600 hover:bg-rose-500 disabled:opacity-40 text-white font-extrabold px-5 py-2.5 rounded-2xl text-xs shadow-lg shadow-rose-600/20 transition flex items-center gap-1.5 shrink-0">
                                    <span x-show="!chatEnviando">Enviar ➔</span>
                                    <span x-show="chatEnviando">Enviando...</span>
                                </button>
                            </div>

                        </div>
                    </template>

                </div>

            </div>

            <!-- ========================================================= -->
            <!-- SUB-ABA 2: AUTOMAÇÕES, ROBÔS & QR CODE                     -->
            <!-- ========================================================= -->
            <div x-show="subAbaWhatsapp === 'config'" class="space-y-6">
                
                <!-- Banner de Conexão do WhatsApp -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 relative overflow-hidden">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-2xl">💬</span>
                                <h2 class="text-lg font-black text-white">Integração WhatsApp Evolution</h2>
                                <template x-if="whatsappConectado">
                                    <span class="bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 text-xs px-2.5 py-0.5 rounded-full font-extrabold flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                        Conectado
                                    </span>
                                </template>
                                <template x-if="!whatsappConectado">
                                    <span class="bg-amber-500/15 text-amber-300 border border-amber-500/30 text-xs px-2.5 py-0.5 rounded-full font-extrabold">
                                        Desconectado
                                    </span>
                                </template>
                            </div>
                            <p class="text-xs text-slate-400">
                                Conecte o WhatsApp do seu restaurante para disparar atualizações aos clientes, enviar dados aos motoboys e ativar o assistente virtual anti-ansiedade.
                            </p>
                        </div>

                        <div class="flex items-center gap-2 w-full md:w-auto">
                            <template x-if="!whatsappConectado">
                                <button @click="conectarWhatsapp()" :disabled="whatsappCarregando"
                                    class="w-full md:w-auto bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold px-5 py-2.5 rounded-xl text-xs shadow-lg shadow-emerald-600/20 transition flex items-center justify-center gap-2">
                                    <span x-show="!whatsappCarregando">📱 Conectar meu WhatsApp (QR Code)</span>
                                    <span x-show="whatsappCarregando">Gerando QR Code...</span>
                                </button>
                            </template>
                            <template x-if="whatsappConectado">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs text-slate-300 font-bold bg-slate-950 px-3 py-1.5 rounded-xl border border-slate-800" x-text="whatsappStatus.phone ? '+' + whatsappStatus.phone : 'Número Ativo'"></span>
                                    <button @click="desconectarWhatsapp()" :disabled="whatsappCarregando"
                                        class="bg-rose-500/15 hover:bg-rose-500/25 text-rose-300 border border-rose-500/30 font-bold px-4 py-2 rounded-xl text-xs transition">
                                        Desconectar
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Box QR Code quando desconectado e gerado -->
                    <template x-if="!whatsappConectado && whatsappStatus.qrBase64">
                        <div class="mt-6 pt-6 border-t border-slate-800/80 flex flex-col md:flex-row items-center justify-center gap-8 bg-slate-950/60 p-6 rounded-2xl">
                            <div class="bg-white p-3 rounded-2xl shadow-2xl border-4 border-emerald-500/40">
                                <img :src="whatsappStatus.qrBase64" alt="QR Code WhatsApp" class="w-56 h-56 rounded-lg object-contain">
                            </div>
                            <div class="space-y-3 max-w-md text-left">
                                <h3 class="text-white font-bold text-sm flex items-center gap-2">
                                    <span>📸</span> Como conectar seu celular:
                                </h3>
                                <ol class="text-xs text-slate-300 space-y-2 list-decimal list-inside leading-relaxed">
                                    <li>Abra o aplicativo do <strong>WhatsApp</strong> no seu celular</li>
                                    <li>Acesse <strong>Aparelhos Conectados</strong> (nos 3 pontinhos ou Ajustes)</li>
                                    <li>Toque em <strong>Conectar um aparelho</strong></li>
                                    <li>Aponte a câmera para o QR Code ao lado</li>
                                </ol>
                                <div class="pt-2 flex items-center gap-3">
                                    <button @click="conectarWhatsapp()" class="text-xs text-emerald-400 hover:underline font-bold flex items-center gap-1">
                                        🔄 Atualizar QR Code
                                    </button>
                                    <span class="text-[11px] text-slate-400">• O QR Code expira a cada 40 segundos</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Grade de Automações & Bot Inteligente -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Card 1: Notificações de Pedido para Clientes -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-2xl">📦</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="whatsappStatus.config.notificar_pedidos" @change="salvarConfigWhatsapp()" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                            </div>
                            <h3 class="font-extrabold text-sm text-white">Status em Tempo Real (Cliente)</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Dispara mensagens automáticas com link de rastreamento assinado a cada etapa:
                            </p>
                            <div class="space-y-1.5 text-[11px]">
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    <strong class="text-rose-400">1. Confirmado:</strong> Aviso de entrada em preparo na cozinha.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    <strong class="text-amber-400">2. Pronto:</strong> Separado na bolsa térmica ou no balcão.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    <strong class="text-sky-400">3. A Caminho:</strong> Nome do entregador + alerta campainha.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    <strong class="text-emerald-400">4. Entregue:</strong> Agradecimento carinhoso e feedback.
                                </div>
                            </div>
                        </div>
                        <div class="text-[11px] text-emerald-400 font-bold pt-2 border-t border-slate-800 flex items-center gap-1.5">
                            <span>✓</span> Zero custo de SMS ou taxas extras
                        </div>
                    </div>

                    <!-- Card 2: Despacho para Motoboys -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-2xl">🛵</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="whatsappStatus.config.notificar_motoboy" @change="salvarConfigWhatsapp()" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                            </div>
                            <h3 class="font-extrabold text-sm text-white">Despacho &amp; GPS para Motoboys</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Assim que o pedido fica <strong>Pronto</strong>, o sistema aciona o motoboy com todos os detalhes da entrega:
                            </p>
                            <div class="space-y-1.5 text-[11px]">
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    📍 <strong>Endereço Completo:</strong> Rua, número, complemento e bairro.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    🗺️ <strong>Google Maps 1-Toque:</strong> Abre a rota pronta no GPS do motoboy.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    💵 <strong>Financeiro:</strong> Taxa de entrega em R$ e forma de pagamento.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    ⚡ <strong>Comando Rápido:</strong> Motoboy responde <code class="text-emerald-400">ENTREGUE 0000</code> e conclui o pedido.
                                </div>
                            </div>
                        </div>
                        <div class="text-[11px] text-sky-400 font-bold pt-2 border-t border-slate-800 flex items-center gap-1.5">
                            <span>✓</span> Compatível com entregadores próprios e freelancers
                        </div>
                    </div>

                    <!-- Card 3: Bot Anti-Ansiedade e Atendimento Humanizado -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-2xl">🤖</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="whatsappStatus.config.bot_ativo" @change="salvarConfigWhatsapp()" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                            </div>
                            <h3 class="font-extrabold text-sm text-white">Assistente Anti-Ansiedade</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Responde os clientes de forma ultra educada, amigável e desescaladora:
                            </p>
                            <div class="space-y-1.5 text-[11px]">
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    🍔 <strong>Cardápio Rápido:</strong> Envia o link oficial ao pedir preço ou produtos.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    🕒 <strong>Previsão Precisa:</strong> Consulta status em tempo real do pedido ativo.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    ❤️ <strong>Acolhimento em Atrasos:</strong> Pede desculpas, explica o pico da cozinha e garante que é o <strong>próximo a sair</strong>!
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    👨‍💼 <strong>Transbordo Humano:</strong> Pausa o bot quando o cliente digita <code class="text-rose-400">HUMANO</code>.
                                </div>
                            </div>
                        </div>
                        <div class="text-[11px] text-amber-300 font-bold pt-2 border-t border-slate-800 flex items-center gap-1.5">
                            <span>✓</span> Total conformidade LGPD (Opt-out imediato)
                        </div>
                    </div>

                    <!-- Card 4: Recuperador Automático de PIX Abandonado -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-2xl">💰</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="whatsappStatus.config.recuperar_carrinho" @change="salvarConfigWhatsapp()" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                            </div>
                            <h3 class="font-extrabold text-sm text-white">Recuperador de PIX Abandonado</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Resgata clientes que geraram o PIX e esqueceram de pagar antes que desistam:
                            </p>
                            <div class="space-y-1.5 text-[11px]">
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    ⏱️ <strong>Disparo aos 6 a 15 min:</strong> Lembra com carinho que os itens estão reservados na chapa.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    📋 <strong>Código PIX Pronto:</strong> Envia a chave Copia e Cola para pagamento em 1 toque no banco.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    💳 <strong>Flexibilidade:</strong> Permite ao cliente responder "CARTÃO" para pagar na maquininha!
                                </div>
                            </div>
                        </div>
                        <div class="text-[11px] text-emerald-400 font-bold pt-2 border-t border-slate-800 flex items-center gap-1.5">
                            <span>🔥</span> Aumento médio de +18% a +25% no faturamento
                        </div>
                    </div>

                    <!-- Card 5: Re-compra em 1-Clique ("Pedir o de Sempre") -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-2xl">⚡</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="whatsappStatus.config.recompra_um_clique" @change="salvarConfigWhatsapp()" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                </label>
                            </div>
                            <h3 class="font-extrabold text-sm text-white">Re-compra 1-Clique ("O de Sempre")</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Fideliza clientes recorrentes permitindo pedir novamente sem preencher nada:
                            </p>
                            <div class="space-y-1.5 text-[11px]">
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    🍔 <strong>Memória do Pedido Favorito:</strong> Lembra os itens e endereço do último pedido do cliente.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    1️⃣ <strong>Basta Digitar "1":</strong> Clona o pedido, gera o PIX e avisa a cozinha na hora.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    🚀 <strong>Zero Fricção:</strong> O cliente não precisa nem abrir o navegador.
                                </div>
                            </div>
                        </div>
                        <div class="text-[11px] text-amber-300 font-bold pt-2 border-t border-slate-800 flex items-center gap-1.5">
                            <span>⭐</span> Aumenta a frequência semanal de compras em +30%
                        </div>
                    </div>

                    <!-- Card 6: Atendente Inteligente (IA Conversacional & Transcrição de Áudio) -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl">🤖</span>
                                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-purple-500/20 text-purple-300 border border-purple-500/30">Groq LPU Turbo</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="whatsappStatus.config.ia_atendente_ativa" @change="salvarConfigWhatsapp()" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-500"></div>
                                </label>
                            </div>
                            <h3 class="font-extrabold text-sm text-white">IA do Cardápio &amp; Transcrição de Voz</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Responde dúvidas sobre ingredientes, sabores, combos e transcreve áudios enviados pelos clientes:
                            </p>
                            <div class="space-y-1.5 text-[11px]">
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    🎙️ <strong>Entende Áudio de Voz:</strong> O cliente manda áudio ("tem lanche sem queijo?"), a IA ouve e responde na hora.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    🍔 <strong>Cardápio em Tempo Real:</strong> Lê seus produtos ativos e nunca inventa pratos ou preços inexistentes.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    ⚡ <strong>Ultra Rápido:</strong> Resposta humana, acolhedora e com link em menos de 1 segundo.
                                </div>
                            </div>
                        </div>
                        <div class="text-[11px] text-purple-300 font-bold pt-2 border-t border-slate-800 flex items-center gap-1.5">
                            <span>✨</span> Atendimento 24/7 sem fila de espera
                        </div>
                    </div>

                    <!-- Card 7: Alerta Proativo de Demora na Cozinha -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl">⏳</span>
                                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-rose-500/20 text-rose-300 border border-rose-500/30">Anti-Ansiedade</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="whatsappStatus.config.alerta_atraso_ativo" @change="salvarConfigWhatsapp()" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500"></div>
                                </label>
                            </div>
                            <h3 class="font-extrabold text-sm text-white">Alerta Proativo de Demora na Cozinha</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Acalma e encanta o cliente antes mesmo que ele pergunte onde está o pedido:
                            </p>
                            <div class="space-y-1.5 text-[11px]">
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    👨‍🍳 <strong>Mensagem de Satisfação:</strong> Avisa que o chef está caprichando nos detalhes e que o pedido é o próximo a sair.
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80 flex items-center justify-between">
                                    <span>⏱️ <strong>Disparar após:</strong></span>
                                    <select x-model.number="whatsappStatus.config.minutos_alerta_atraso" @change="salvarConfigWhatsapp()" class="bg-slate-900 border border-slate-700 text-rose-300 text-xs rounded-lg px-2 py-1 font-bold">
                                        <option value="25">25 minutos</option>
                                        <option value="30">30 minutos</option>
                                        <option value="35">35 minutos</option>
                                        <option value="40">40 minutos</option>
                                        <option value="45">45 minutos</option>
                                        <option value="50">50 minutos</option>
                                    </select>
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    🛡️ <strong>Anti-Spam Garantido:</strong> Envia rigorosamente apenas 1 vez por pedido.
                                </div>
                            </div>
                        </div>
                        <div class="text-[11px] text-rose-300 font-bold pt-2 border-t border-slate-800 flex items-center gap-1.5">
                            <span>❤️</span> Reduz em até 90% as reclamações por atraso
                        </div>
                    </div>

                    <!-- Card 8: Avaliações Google Maps (NPS Viral) & Satisfação -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4 flex flex-col justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl">⭐</span>
                                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-300 border border-amber-500/30">NPS Viral</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="whatsappStatus.config.solicitar_avaliacao" @change="salvarConfigWhatsapp()" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                                </label>
                            </div>
                            <h3 class="font-extrabold text-sm text-white">Avaliações 5 Estrelas no Google Maps</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Convida clientes satisfeitos a avaliarem sua loja no Google e retém reclamações no chat privado:
                            </p>
                            <div class="space-y-1.5 text-[11px]">
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    🌟 <strong>Clientes 4 ou 5 Estrelas:</strong> Recebem seu link oficial do Google Maps p/ disparar avaliações públicas e turbinar o ranking da loja!
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80">
                                    🛡️ <strong>Clientes 1 a 3 Estrelas:</strong> O bot pede desculpas com empatia, registra o feedback privadamente e alerta o gerente no painel antes de ir pro Google!
                                </div>
                                <div class="bg-slate-950 p-2 rounded-xl text-slate-300 border border-slate-800/80 flex items-center justify-between">
                                    <span>⏱️ <strong>Disparar após:</strong></span>
                                    <select x-model.number="whatsappStatus.config.minutos_solicitar_avaliacao" @change="salvarConfigWhatsapp()" class="bg-slate-900 border border-slate-700 text-amber-300 text-xs rounded-lg px-2 py-1 font-bold">
                                        <option value="15">15 minutos</option>
                                        <option value="20">20 minutos</option>
                                        <option value="25">25 minutos</option>
                                        <option value="30">30 minutos</option>
                                        <option value="45">45 minutos</option>
                                        <option value="60">60 minutos</option>
                                    </select>
                                </div>
                                <div class="space-y-1 pt-1">
                                    <label class="text-[10px] text-slate-400 font-bold block">Link de Avaliação do seu Google Maps:</label>
                                    <input type="url" x-model="whatsappStatus.config.google_maps_review_url" @change="salvarConfigWhatsapp()"
                                        placeholder="https://g.page/r/.../review ou link do Maps"
                                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-2.5 py-1.5 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-amber-500">
                                </div>
                            </div>
                        </div>
                        <div class="text-[11px] text-amber-300 font-bold pt-2 border-t border-slate-800 flex items-center gap-1.5">
                            <span>🚀</span> Mais de 10x mais avaliações 5 estrelas no Google
                        </div>
                    </div>

                    <!-- Card 9: Reativação Win-Back Automática & Anti-Ban -->
                    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 space-y-4 flex flex-col justify-between"
                         x-init="carregarPreviewWinback()">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl">🎯</span>
                                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-purple-500/20 text-purple-300 border border-purple-500/30">CRM Win-Back</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="whatsappStatus.config.winback_ativo" @change="salvarConfigWhatsapp(); carregarPreviewWinback();" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-500"></div>
                                </label>
                            </div>
                            <h3 class="font-extrabold text-sm text-white">Reativação Automática de Clientes Sumidos</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Resgata clientes que não compram há tempos enviando um cupom exclusivo de retorno com link de 1-toque:
                            </p>
                            <div class="space-y-2 text-[11px]">
                                <div class="bg-slate-950 p-2.5 rounded-xl text-slate-300 border border-slate-800/80 flex items-center justify-between">
                                    <span>⏱️ <strong>Inatividade:</strong></span>
                                    <select x-model.number="whatsappStatus.config.winback_dias_inatividade" @change="salvarConfigWhatsapp(); carregarPreviewWinback();" class="bg-slate-900 border border-slate-700 text-purple-300 text-xs rounded-lg px-2 py-1 font-bold">
                                        <option value="15">Sem pedir há +15 dias</option>
                                        <option value="30">Sem pedir há +30 dias</option>
                                        <option value="45">Sem pedir há +45 dias</option>
                                        <option value="60">Sem pedir há +60 dias</option>
                                    </select>
                                </div>

                                <div class="bg-slate-950 p-2.5 rounded-xl text-slate-300 border border-slate-800/80 space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <span>🎁 <strong>Cupom / Desconto:</strong></span>
                                        <div class="flex items-center gap-1.5">
                                            <input type="text" x-model="whatsappStatus.config.winback_cupom_codigo" @change="salvarConfigWhatsapp(); carregarPreviewWinback();"
                                                placeholder="VOLTEI10" class="w-20 uppercase bg-slate-900 border border-slate-700 text-purple-300 text-center text-xs rounded-lg px-1.5 py-1 font-black">
                                            <input type="number" x-model.number="whatsappStatus.config.winback_desconto_valor" @change="salvarConfigWhatsapp(); carregarPreviewWinback();"
                                                min="1" max="90" class="w-12 bg-slate-900 border border-slate-700 text-purple-300 text-center text-xs rounded-lg px-1 py-1 font-bold">
                                            <span class="text-xs font-bold text-slate-400">%</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between text-[10px] text-slate-400">
                                        <span>⏰ Expiração: <strong>48 horas</strong> (urgência)</span>
                                        <span>🛡️ Anti-Ban: <strong>5-10s delay</strong></span>
                                    </div>
                                </div>

                                <!-- Estatísticas da Base de Clientes Inativos -->
                                <div class="bg-purple-950/40 border border-purple-800/50 p-2.5 rounded-xl flex items-center justify-between">
                                    <div class="text-[11px] text-purple-200">
                                        <span>👥 Clientes Inativos Elegíveis:</span>
                                        <div class="text-base font-black text-white" x-text="winbackPreview.total_inativos ?? '0'"></div>
                                    </div>
                                    <button type="button" @click="dispararCampanhaWinback()" :disabled="disparandoWinback || !whatsappConectado || (winbackPreview.total_inativos === 0)"
                                        class="bg-purple-600 hover:bg-purple-500 disabled:opacity-40 disabled:cursor-not-allowed text-white text-[11px] font-black px-3 py-1.5 rounded-xl transition shadow flex items-center gap-1.5">
                                        <span x-show="!disparandoWinback">🚀 Disparar Agora</span>
                                        <span x-show="disparandoWinback" class="animate-spin">⏳ Enviando...</span>
                                    </button>
                                </div>

                                <!-- Pré-visualização da Mensagem -->
                                <div x-data="{ verExemplo: false }" class="pt-1">
                                    <button type="button" @click="verExemplo = !verExemplo" class="text-[10px] text-purple-400 hover:underline flex items-center gap-1 font-semibold">
                                        <span x-text="verExemplo ? 'Ocultar exemplo' : '👁️ Ver mensagem que o cliente recebe'"></span>
                                    </button>
                                    <div x-show="verExemplo" x-cloak class="mt-2 p-2.5 bg-slate-950 border border-slate-800 rounded-xl text-[11px] text-slate-300 whitespace-pre-line font-mono leading-tight">
                                        <span x-text="winbackPreview.exemplo_mensagem || 'Carregando exemplo...'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-[11px] text-purple-300 font-bold pt-2 border-t border-slate-800 flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <span>📈</span> Retorno médio de 18% a 25% dos clientes
                            </span>
                            <button type="button" @click="carregarPreviewWinback()" class="text-slate-400 hover:text-white transition" title="Recarregar clientes elegíveis">🔄</button>
                        </div>
                    </div>

                </div>

                <!-- Caixa de Teste de Disparo ao Vivo -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 space-y-4">
                    <h3 class="font-bold text-sm text-white flex items-center gap-2">
                        <span>🧪</span> Testar Envio de Mensagem pelo WhatsApp
                    </h3>
                    <p class="text-xs text-slate-400">
                        Digite o número do seu celular com DDD (ex: <code class="text-emerald-400">11999999999</code>) para testar o envio em tempo real.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        <div class="md:col-span-4">
                            <input type="text" x-model="whatsappTelefoneTeste" placeholder="DDD + Número (Ex: 31988887777)"
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white">
                        </div>
                        <div class="md:col-span-5">
                            <input type="text" x-model="whatsappMsgTeste" placeholder="Mensagem personalizada (opcional)"
                                class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white">
                        </div>
                        <div class="md:col-span-3">
                            <button @click="enviarTesteWhatsapp()" :disabled="whatsappCarregando || !whatsappConectado"
                                class="w-full bg-slate-800 hover:bg-slate-700 disabled:opacity-50 text-white font-extrabold py-2.5 rounded-xl text-xs transition border border-slate-700">
                                🚀 Disparar Teste
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

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

    <!-- Modal: Criar / Editar Produto (Etapa 2: De/Por, Upload WebP e Variações) -->
    <div x-cloak x-show="modalNovoProduto" class="fixed inset-0 z-50 bg-black/75 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="modalNovoProduto = false" class="bg-slate-900 border border-slate-800 w-full max-w-lg rounded-t-3xl sm:rounded-3xl p-5 space-y-4 max-h-[92vh] overflow-y-auto">
            <div class="flex justify-between items-center">
                <h3 class="font-bold text-base text-white" x-text="novoProd.id ? '✏️ Editar Produto' : '🍔 Cadastrar Novo Produto'"></h3>
                <button @click="modalNovoProduto = false" class="text-slate-400 font-bold">✕</button>
            </div>
            <form @submit.prevent="salvarNovoProduto()" class="space-y-3 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Categoria *</label>
                        <select x-model="novoProd.categoria_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                            <template x-for="c in categorias" :key="c.id">
                                <option :value="c.id" x-text="c.nome"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Estação KDS na Cozinha</label>
                        <select x-model="novoProd.estacao_kds" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                            <option value="chapa">🔥 Chapa / Burgers</option>
                            <option value="forno">🍕 Forno / Pizzas</option>
                            <option value="fritadeira">🍟 Fritadeira / Porções</option>
                            <option value="bar">🥤 Bar / Bebidas</option>
                            <option value="geral">👨‍🍳 Cozinha Geral</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="font-bold text-slate-400 block mb-1">Nome do Produto *</label>
                    <input type="text" x-model="novoProd.nome" required placeholder="Ex: Smash Burger Duplo Bacon" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-bold">
                </div>
                <div>
                    <label class="font-bold text-slate-400 block mb-1">Descrição Vendedora</label>
                    <input type="text" x-model="novoProd.descricao" placeholder="Pão brioche selado na manteiga, 2x blend angus..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Preço Normal "De" (R$) *</label>
                        <input type="text" x-model="novoProd.preco_reais" required placeholder="38,90" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-bold">
                    </div>
                    <div>
                        <label class="font-bold text-emerald-400 block mb-1">🔥 Preço Promo "Por" (Opcional)</label>
                        <input type="text" x-model="novoProd.preco_promocional_reais" placeholder="34,90 (destaca no topo)" class="w-full bg-slate-950 border border-emerald-500/40 rounded-xl px-3.5 py-2.5 text-emerald-300 font-bold">
                    </div>
                </div>

                <!-- Upload de Foto Real WebP ou URL -->
                <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3 space-y-2">
                    <label class="font-bold text-slate-300 block">📸 Foto do Produto (Upload Automático WebP ou Link)</label>
                    <input type="file" accept="image/jpeg,image/png,image/webp" @change="arquivoFotoProduto = $event.target.files[0]"
                        class="w-full text-[11px] text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-rose-600/20 file:text-rose-300 hover:file:bg-rose-600/30">
                    <input type="text" x-model="novoProd.imagem" placeholder="Ou cole a URL da imagem (https://...)" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-white">
                </div>

                <!-- Variações de Tamanho / Opções -->
                <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3 space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-slate-300">📏 Variações de Tamanho (Ex: P, M, G / 1 Carne, 2 Carnes)</span>
                        <button type="button" @click="novoProd.variacoes.push({ nome: '', preco_reais: novoProd.preco_reais || '29,90' })" class="text-rose-400 font-bold hover:underline">+ Variação</button>
                    </div>
                    <template x-for="(vr, idx) in novoProd.variacoes" :key="idx">
                        <div class="flex gap-2 items-center">
                            <input type="text" x-model="vr.nome" placeholder="Ex: Grande (8 fatias)" class="flex-1 bg-slate-900 border border-slate-800 rounded-xl px-2.5 py-1.5 text-white">
                            <input type="text" x-model="vr.preco_reais" placeholder="59,90" class="w-24 bg-slate-900 border border-slate-800 rounded-xl px-2.5 py-1.5 text-emerald-400 font-bold">
                            <button type="button" @click="novoProd.variacoes.splice(idx, 1)" class="text-rose-400 font-bold px-2">✕</button>
                        </div>
                    </template>
                </div>

                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-extrabold py-3 rounded-xl text-sm shadow-lg">
                    Salvar no Cardápio
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Novo Cupom de Desconto (Etapa 2) -->
    <div x-cloak x-show="modalNovoCupom" class="fixed inset-0 z-50 bg-black/75 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="modalNovoCupom = false" class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-t-3xl sm:rounded-3xl p-5 space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="font-bold text-base text-white">🎟️ Criar Cupom Promocional</h3>
                <button @click="modalNovoCupom = false" class="text-slate-400">✕</button>
            </div>
            <form @submit.prevent="salvarNovoCupom()" class="space-y-3 text-xs">
                <div>
                    <label class="font-bold text-slate-400 block mb-1">Código do Cupom *</label>
                    <input type="text" x-model="novoCupom.codigo" required placeholder="Ex: SEXTOU15 ou VOLTE10" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-emerald-400 font-black uppercase">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Tipo de Desconto</label>
                        <select x-model="novoCupom.tipo" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                            <option value="percentual">Porcentagem (%)</option>
                            <option value="fixo_centavos">Valor Fixo (R$)</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-slate-400 block mb-1" x-text="novoCupom.tipo === 'percentual' ? 'Desconto (%)' : 'Desconto (R$)'"></label>
                        <input type="text" x-model="novoCupom.valor_input" required placeholder="10" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white font-bold">
                    </div>
                </div>
                <div>
                    <label class="font-bold text-slate-400 block mb-1">Pedido Mínimo p/ Usar (R$)</label>
                    <input type="text" x-model="novoCupom.minimo_reais" placeholder="30,00" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                </div>
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold py-3 rounded-xl text-sm">
                    Ativar Cupom Agora
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Detalhes Completos do Pedido & Endereço Google Maps (Etapa 2) -->
    <div x-cloak x-show="pedidoSelecionado" class="fixed inset-0 z-50 bg-black/80 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="pedidoSelecionado = null" class="bg-slate-900 border border-slate-800 w-full max-w-lg rounded-t-3xl sm:rounded-3xl p-5 space-y-4 max-h-[92vh] overflow-y-auto">
            <template x-if="pedidoSelecionado">
                <div class="space-y-4 text-xs">
                    <div class="flex justify-between items-start border-b border-slate-800 pb-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-black text-rose-400" x-text="'Pedido #' + pedidoSelecionado.codigo_publico"></span>
                                <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-200 font-bold uppercase text-[10px]" x-text="pedidoSelecionado.canal"></span>
                                <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold uppercase text-[10px]" x-text="pedidoSelecionado.status"></span>
                            </div>
                            <p class="text-slate-400 mt-0.5" x-text="'Recebido há ' + pedidoSelecionado.tempo_decorrido_minutos + ' minutos'"></p>
                        </div>
                        <button @click="pedidoSelecionado = null" class="text-slate-400 text-base font-bold">✕</button>
                    </div>

                    <!-- Dados do Cliente -->
                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 flex items-center justify-between gap-2">
                        <div>
                            <div class="font-bold text-white text-sm" x-text="pedidoSelecionado.cliente?.nome || 'Cliente Balcão'"></div>
                            <div class="text-slate-400" x-text="(pedidoSelecionado.cliente?.telefone || '') + ' • ' + (pedidoSelecionado.cliente?.total_pedidos_historico || 1) + 'º pedido na loja'"></div>
                        </div>
                        <template x-if="pedidoSelecionado.whatsapp_notificacao_url">
                            <a :href="pedidoSelecionado.whatsapp_notificacao_url" target="_blank" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-3 py-2 rounded-xl">
                                💬 WhatsApp
                            </a>
                        </template>
                    </div>

                    <!-- Endereço de Entrega + Link Google Maps -->
                    <template x-if="pedidoSelecionado.endereco && pedidoSelecionado.endereco.rua">
                        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-300">📍 Endereço de Entrega</span>
                                <a :href="'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(pedidoSelecionado.endereco.rua + ', ' + pedidoSelecionado.endereco.numero + ' - ' + pedidoSelecionado.endereco.bairro + ', ' + (pedidoSelecionado.endereco.cidade || ''))"
                                   target="_blank" class="text-sky-400 hover:underline font-bold">
                                    🗺️ Abrir no Google Maps ↗
                                </a>
                            </div>
                            <div class="text-slate-200 font-semibold" x-text="pedidoSelecionado.endereco.rua + ', ' + pedidoSelecionado.endereco.numero + (pedidoSelecionado.endereco.complemento ? ' (' + pedidoSelecionado.endereco.complemento + ')' : '')"></div>
                            <div class="text-slate-400" x-text="pedidoSelecionado.endereco.bairro + ' • ' + (pedidoSelecionado.endereco.cidade || '') + ' • CEP: ' + (pedidoSelecionado.endereco.cep || '')"></div>
                            <template x-if="pedidoSelecionado.endereco.referencia">
                                <div class="text-amber-300 font-bold" x-text="'Ref: ' + pedidoSelecionado.endereco.referencia"></div>
                            </template>
                        </div>
                    </template>

                    <!-- Itens e Adicionais -->
                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 space-y-2">
                        <div class="font-bold text-slate-300">🍔 Itens do Pedido</div>
                        <template x-for="it in pedidoSelecionado.itens" :key="it.id">
                            <div class="border-b border-slate-800/80 last:border-0 pb-2 last:pb-0">
                                <div class="flex justify-between font-bold text-white">
                                    <span x-text="it.qtd + 'x ' + it.nome + (it.variacao ? ' (' + it.variacao + ')' : '')"></span>
                                    <span x-text="formatarCentavos(it.total)"></span>
                                </div>
                                <template x-if="it.adicionais && it.adicionais.length > 0">
                                    <div class="text-slate-400 pl-3" x-text="it.adicionais.map(a => '+ ' + a.nome).join(', ')"></div>
                                </template>
                                <template x-if="it.observacao">
                                    <div class="text-amber-400 font-bold pl-3" x-text="'⚠️ Obs: ' + it.observacao"></div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- Resumo Financeiro do Pedido -->
                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 space-y-1.5">
                        <div class="flex justify-between text-slate-400"><span>Subtotal:</span><span x-text="formatarCentavos(pedidoSelecionado.subtotal_centavos)"></span></div>
                        <div class="flex justify-between text-slate-400" x-show="pedidoSelecionado.taxa_entrega_centavos > 0"><span>Taxa de Entrega:</span><span x-text="formatarCentavos(pedidoSelecionado.taxa_entrega_centavos)"></span></div>
                        <div class="flex justify-between text-emerald-400" x-show="pedidoSelecionado.desconto_centavos > 0"><span>Desconto Cupom:</span><span x-text="'- ' + formatarCentavos(pedidoSelecionado.desconto_centavos)"></span></div>
                        <div class="flex justify-between text-emerald-400" x-show="pedidoSelecionado.desconto_cashback_centavos > 0"><span>Cashback Fidelidade:</span><span x-text="'- ' + formatarCentavos(pedidoSelecionado.desconto_cashback_centavos)"></span></div>
                        <div class="flex justify-between text-white font-black text-sm pt-1.5 border-t border-slate-800">
                            <span x-text="'Total (' + (pedidoSelecionado.forma_pagamento || '').toUpperCase() + ')'"></span>
                            <span class="text-emerald-400" x-text="formatarCentavos(pedidoSelecionado.total_centavos)"></span>
                        </div>
                        <template x-if="pedidoSelecionado.troco_para">
                            <div class="text-amber-300 font-bold pt-1" x-text="'💵 Levar troco para: ' + formatarCentavos(pedidoSelecionado.troco_para)"></div>
                        </template>
                    </div>

                    <div class="flex gap-2">
                        <button @click="imprimirPedido(pedidoSelecionado.id, 'cozinha')" class="flex-1 bg-slate-800 hover:bg-slate-700 text-white font-bold py-2.5 rounded-xl">
                            🖨️ Cupom Cozinha 80mm
                        </button>
                        <button @click="imprimirPedido(pedidoSelecionado.id, 'entrega')" class="flex-1 bg-slate-800 hover:bg-slate-700 text-white font-bold py-2.5 rounded-xl">
                            🖨️ Cupom Caixa/Motoboy
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Modal: Calculadora de Divisão de Conta da Mesa (Etapa 3) -->
    <div x-cloak x-show="mesaDivisao" class="fixed inset-0 z-50 bg-black/80 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="mesaDivisao = null" class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-t-3xl sm:rounded-3xl p-5 space-y-4">
            <template x-if="mesaDivisao">
                <div class="space-y-4 text-xs">
                    <div class="flex justify-between items-center">
                        <h3 class="font-black text-base text-white" x-text="'🪑 Fechamento & Divisão — Mesa #' + mesaDivisao.numero"></h3>
                        <button @click="mesaDivisao = null" class="text-slate-400 text-base">✕</button>
                    </div>

                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 space-y-2">
                        <div class="flex justify-between text-slate-400">
                            <span>Consumo dos Pedidos da Mesa:</span>
                            <span class="font-bold text-white" x-text="formatarCentavos(totalConsumoMesaCentavos)"></span>
                        </div>
                        <label class="flex items-center justify-between cursor-pointer pt-1">
                            <span class="text-slate-300 font-bold">Incluir Taxa de Serviço Garçom (10%)</span>
                            <input type="checkbox" x-model="incluirTaxaServicoMesa" class="rounded bg-slate-900 border-slate-700 text-rose-600">
                        </label>
                        <div class="flex justify-between text-sm font-black text-emerald-400 pt-2 border-t border-slate-800">
                            <span>Total Final da Mesa:</span>
                            <span x-text="formatarCentavos(totalFinalMesaCentavos)"></span>
                        </div>
                    </div>

                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-white">Dividir entre quantas pessoas?</div>
                            <div class="text-slate-400">Cálculo automático por pessoa</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="pessoasDivisaoMesa = Math.max(1, pessoasDivisaoMesa - 1)" class="w-8 h-8 rounded-xl bg-slate-800 text-white font-black">-</button>
                            <span class="text-base font-black text-white w-6 text-center" x-text="pessoasDivisaoMesa"></span>
                            <button @click="pessoasDivisaoMesa++" class="w-8 h-8 rounded-xl bg-slate-800 text-white font-black">+</button>
                        </div>
                    </div>

                    <div class="bg-emerald-500/15 border border-emerald-500/30 rounded-2xl p-4 text-center space-y-1">
                        <span class="text-[11px] font-bold uppercase text-emerald-300">Valor por Pessoa (<span x-text="pessoasDivisaoMesa"></span>x)</span>
                        <div class="text-2xl font-black text-emerald-400" x-text="formatarCentavos(Math.ceil(totalFinalMesaCentavos / Math.max(1, pessoasDivisaoMesa)))"></div>
                    </div>

                    <button @click="mesaDivisao = null; toastMsg = 'Conferência da Mesa #' + mesaDivisao.numero + ' concluída!'" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-black py-3 rounded-xl text-sm">
                        ✓ Concluir Conferência da Mesa
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- Modal: Nova Categoria -->
    <div x-cloak x-show="modalNovaCategoria" class="fixed inset-0 z-50 bg-black/75 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="modalNovaCategoria = false" class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-t-3xl sm:rounded-3xl p-5 space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="font-bold text-base text-white">Nova Categoria do Cardápio</h3>
                <button @click="modalNovaCategoria = false" class="text-slate-400">✕</button>
            </div>
            <form @submit.prevent="salvarNovaCategoria()" class="space-y-3 text-xs">
                <div>
                    <label class="font-bold text-slate-400 block mb-1">Nome da Categoria *</label>
                    <input type="text" x-model="novaCat.nome" required placeholder="Ex: Sobremesas Artesanais" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                </div>
                <div>
                    <label class="font-bold text-slate-400 block mb-1">Descrição Curta</label>
                    <input type="text" x-model="novaCat.descricao" placeholder="Doces feitos na casa..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                </div>
                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-extrabold py-3 rounded-xl text-sm">
                    Criar Categoria
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Novo Entregador -->
    <div x-cloak x-show="modalNovoEntregador" class="fixed inset-0 z-50 bg-black/75 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="modalNovoEntregador = false" class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-t-3xl sm:rounded-3xl p-5 space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="font-bold text-base text-white">Cadastrar Entregador (Motoboy)</h3>
                <button @click="modalNovoEntregador = false" class="text-slate-400">✕</button>
            </div>
            <form @submit.prevent="salvarNovoEntregador()" class="space-y-3 text-xs">
                <div>
                    <label class="font-bold text-slate-400 block mb-1">Nome do Entregador *</label>
                    <input type="text" x-model="novoEnt.nome" required placeholder="Ex: João Motoboy" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                </div>
                <div>
                    <label class="font-bold text-slate-400 block mb-1">WhatsApp *</label>
                    <input type="text" x-model="novoEnt.telefone" required placeholder="31988887777" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Vínculo</label>
                        <select x-model="novoEnt.tipo" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                            <option value="proprio">Próprio / Fixo</option>
                            <option value="freelancer">Freelancer</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Taxa p/ Corrida (R$)</label>
                        <input type="text" x-model="novoEnt.valor_reais" required placeholder="7,00" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                    </div>
                </div>
                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-extrabold py-3 rounded-xl text-sm">
                    Salvar Entregador
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Nova Taxa de Entrega -->
    <div x-cloak x-show="modalNovaArea" class="fixed inset-0 z-50 bg-black/75 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="modalNovaArea = false" class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-t-3xl sm:rounded-3xl p-5 space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="font-bold text-base text-white">Nova Taxa de Entrega por Bairro</h3>
                <button @click="modalNovaArea = false" class="text-slate-400">✕</button>
            </div>
            <form @submit.prevent="salvarNovaArea()" class="space-y-3 text-xs">
                <div>
                    <label class="font-bold text-slate-400 block mb-1">Nome do Bairro *</label>
                    <input type="text" x-model="novaArea.nome_bairro" required placeholder="Ex: Santa Helena" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Taxa de Entrega (R$)</label>
                        <input type="text" x-model="novaArea.taxa_reais" required placeholder="6,00" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                    </div>
                    <div>
                        <label class="font-bold text-slate-400 block mb-1">Tempo Estimado (min)</label>
                        <input type="number" x-model.number="novaArea.tempo_estimado_min" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white">
                    </div>
                </div>
                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-extrabold py-3 rounded-xl text-sm">
                    Salvar Taxa de Bairro
                </button>
            </form>
        </div>
    </div>

    <!-- Modal: Lançar Pedido Rápido PDV Balcão -->
    <div x-cloak x-show="modalPdv" class="fixed inset-0 z-50 bg-black/75 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="modalPdv = false" class="bg-slate-900 border border-slate-800 w-full max-w-lg rounded-t-3xl sm:rounded-3xl p-5 space-y-4 max-h-[92vh] overflow-y-auto">
            <div class="flex justify-between items-center">
                <h3 class="font-black text-base text-white">⚡ Lançar Pedido Rápido (PDV Balcão / Telefone)</h3>
                <button @click="modalPdv = false" class="text-slate-400 font-bold">✕</button>
            </div>
            <div class="space-y-3 text-xs">
                <div class="grid grid-cols-2 gap-2.5">
                    <input type="text" x-model="pdv.nome" placeholder="Nome do Cliente *" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-white">
                    <input type="text" x-model="pdv.telefone" placeholder="WhatsApp *" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-white">
                </div>
                <div class="grid grid-cols-2 gap-2.5">
                    <select x-model="pdv.canal" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-white font-bold">
                        <option value="balcao">🛍️ Balcão / Retirada</option>
                        <option value="delivery">🛵 Delivery</option>
                    </select>
                    <select x-model="pdv.forma_pagamento" class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-white font-bold">
                        <option value="cartao_maquina">💳 Cartão Presencial</option>
                        <option value="dinheiro">💵 Dinheiro</option>
                        <option value="pix">⚡ PIX QR Code</option>
                    </select>
                </div>

                <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3 max-h-48 overflow-y-auto space-y-1.5">
                    <div class="text-[11px] font-bold uppercase text-slate-400 mb-1">Toque nos produtos para adicionar:</div>
                    <template x-for="p in produtos" :key="p.id">
                        <div @click="adicionarItemPdv(p)" class="flex justify-between items-center p-2 rounded-xl bg-slate-900 hover:bg-slate-800 cursor-pointer transition">
                            <span class="font-semibold text-white" x-text="p.nome"></span>
                            <span class="font-bold text-emerald-400" x-text="'+ ' + formatarCentavos(p.preco_efetivo_centavos)"></span>
                        </div>
                    </template>
                </div>

                <template x-if="pdv.itens.length > 0">
                    <div class="bg-slate-950 border border-slate-800 rounded-2xl p-3 space-y-1.5">
                        <template x-for="(it, i) in pdv.itens" :key="i">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-200 font-bold" x-text="it.qtd + 'x ' + it.nome"></span>
                                <button @click="pdv.itens.splice(i, 1)" class="text-rose-400 font-bold">✕</button>
                            </div>
                        </template>
                    </div>
                </template>

                <button @click="enviarPedidoPdv()" :disabled="pdv.itens.length === 0" class="w-full bg-rose-600 hover:bg-rose-500 disabled:opacity-40 text-white font-black py-3.5 rounded-xl text-sm">
                    Confirmar Pedido no Kanban
                </button>
            </div>
        </div>
    </div>

    <script>
        function painelApp() {
            return {
                autenticado: false,
                temSessaoSuperAdmin: false,
                moduloAtivo: 'pedidos',
                abaPedidoMobile: 'novo',
                somHabilitado: true,
                autoImprimir80mm: localStorage.getItem('pl_auto_print_80mm') === '1',
                onboardingOculto: localStorage.getItem('pl_onboarding_oculto') === '1',
                audioCtx: null,
                somInterval: null,
                toastMsg: '',
                buscaPedido: '',
                filtroCategoriaId: '',
                loja: null,
                configLoja: { nome: '', telefone_whatsapp: '', cor_primaria: '#DC2626', endereco: '', cidade: '', uf: '' },
                pedidos: [],
                pedidoSelecionado: null,
                produtos: [],
                categorias: [],
                cupons: [],
                chamadosGarcom: [],
                kdsTickets: [],
                estacaoKds: '',
                mesas: [],
                mesaDivisao: null,
                pessoasDivisaoMesa: 2,
                incluirTaxaServicoMesa: true,
                entregadores: [],
                areasEntrega: [],
                relatorio: {},
                mpStatus: { conectado: false, mp_conta_nome: '', mp_user_id: '', chave_pix: '', public_key: '' },
                salvandoMp: false,
                testandoPix: false,
                pixTesteResultado: null,
                formMp: { access_token: '', public_key: '', chave_pix: '' },
                whatsappStatus: {
                    instance: '',
                    connected: false,
                    state: 'disconnected',
                    phone: null,
                    qrBase64: null,
                    config: {
                        notificar_pedidos: true,
                        notificar_motoboy: true,
                        bot_ativo: true,
                        recuperar_carrinho: true,
                        recompra_um_clique: true,
                        ia_atendente_ativa: true,
                        alerta_atraso_ativo: true,
                        minutos_alerta_atraso: 35,
                        solicitar_avaliacao: true,
                        minutos_solicitar_avaliacao: 30,
                        google_maps_review_url: '',
                        winback_ativo: false,
                        winback_dias_inatividade: 30,
                        winback_cupom_codigo: 'VOLTEI10',
                        winback_desconto_tipo: 'percentual',
                        winback_desconto_valor: 10,
                        winback_validade_horas: 48,
                        winback_minimo_reais: 30,
                        winback_limite_diario: 30,
                        winback_mensagem: ''
                    }
                },
                whatsappCarregando: false,
                whatsappPolling: null,
                whatsappTelefoneTeste: '',
                whatsappMsgTeste: '',
                salvandoConfigWpp: false,
                subAbaWhatsapp: 'chat',
                conversasWhatsapp: [],
                filtroEstadoConversa: 'todos',
                buscaConversas: '',
                conversaSelecionada: null,
                mensagensConversa: [],
                mensagemChatInput: '',
                chatCarregando: false,
                chatEnviando: false,
                totalAguardandoHumano: 0,
                winbackPreview: {
                    total_clientes_base: 0,
                    total_inativos: 0,
                    dias_inatividade: 30,
                    cupom_codigo: 'VOLTEI10',
                    desconto_texto: '10% OFF',
                    validade_horas: 48,
                    exemplo_mensagem: '',
                    instancia_conectada: false
                },
                carregandoWinback: false,
                disparandoWinback: false,
                modalNovoProduto: false,
                modalNovaCategoria: false,
                modalNovoCupom: false,
                modalNovoEntregador: false,
                modalNovaArea: false,
                modalPdv: false,
                arquivoFotoProduto: null,
                novoProd: { id: null, categoria_id: '', nome: '', descricao: '', preco_reais: '', preco_promocional_reais: '', estacao_kds: 'chapa', imagem: '', variacoes: [] },
                novoCupom: { codigo: '', tipo: 'percentual', valor_input: '10', minimo_reais: '30,00' },
                novaCat: { nome: '', descricao: '' },
                novoEnt: { nome: '', telefone: '', tipo: 'proprio', valor_reais: '7,00' },
                novaArea: { nome_bairro: '', taxa_reais: '5,00', tempo_estimado_min: 30 },
                pdv: { nome: 'Cliente Balcão', telefone: '31999999999', canal: 'balcao', forma_pagamento: 'cartao_maquina', itens: [] },

                get pedidosFiltrados() {
                    if (!this.buscaPedido.trim()) return this.pedidos;
                    const q = this.buscaPedido.toLowerCase();
                    return this.pedidos.filter(p =>
                        (p.codigo_publico || '').toLowerCase().includes(q) ||
                        (p.cliente?.nome || '').toLowerCase().includes(q)
                    );
                },

                get colunas() {
                    const lista = this.pedidosFiltrados;
                    return {
                        novo: lista.filter(p => p.status === 'novo'),
                        em_preparo: lista.filter(p => p.status === 'em_preparo' || p.status === 'aceito'),
                        pronto: lista.filter(p => p.status === 'pronto'),
                        saiu_entrega: lista.filter(p => p.status === 'saiu_entrega'),
                        finalizado: lista.filter(p => ['entregue', 'retirado', 'servido'].includes(p.status)),
                    };
                },

                get volumeDiaCentavos() {
                    return this.pedidos.filter(p => p.status !== 'cancelado').reduce((acc, p) => acc + (p.total_centavos || 0), 0);
                },

                get produtosFiltrados() {
                    if (!this.filtroCategoriaId) return this.produtos;
                    return this.produtos.filter(p => Number(p.categoria_id) === Number(this.filtroCategoriaId));
                },

                get totalConsumoMesaCentavos() {
                    if (!this.mesaDivisao) return 0;
                    const pedidosMesa = this.pedidos.filter(p =>
                        p.status !== 'cancelado' &&
                        p.status !== 'entregue' &&
                        Number(p.mesa?.id) === Number(this.mesaDivisao.id)
                    );
                    const soma = pedidosMesa.reduce((acc, p) => acc + (p.subtotal_centavos || p.total_centavos || 0), 0);
                    return soma > 0 ? soma : 8990;
                },

                get totalFinalMesaCentavos() {
                    const base = this.totalConsumoMesaCentavos;
                    return this.incluirTaxaServicoMesa ? Math.round(base * 1.10) : base;
                },

                get whatsappConectado() {
                    return Boolean(this.whatsappStatus?.connected);
                },

                tituloModulo() {
                    const map = {
                        pedidos: 'Gestão de Pedidos em Tempo Real & PDV',
                        catalogo: 'Cardápio, Promoções, Cupons & Estoque Rápido',
                        kds: 'KDS — Produção por Estação na Cozinha',
                        mesas: 'Mesas, Comandas & Divisão de Conta',
                        entregas: 'Logística: Entregadores & Taxas de Frete',
                        crm: 'CRM, Fidelidade Cashback & Recuperação WhatsApp',
                        financeiro: 'Frente de Caixa, Relatórios & PIX Mercado Pago',
                        whatsapp: 'WhatsApp Integrado, Notificações & Bot Anti-Ansiedade',
                        config: 'Configurações da Loja & Identidade Visual'
                    };
                    return map[this.moduloAtivo] || 'Painel PedeLivre';
                },

                iniciar() {
                    const token = localStorage.getItem('pl_token');
                    if (!token) {
                        window.location.replace('/login');
                        return;
                    }
                    const u = JSON.parse(localStorage.getItem('pl_usuario') || '{}');
                    this.temSessaoSuperAdmin = Boolean(sessionStorage.getItem('pl_superadmin_token') || u.papel === 'super_admin');

                    fetch('/api/loja', { headers: this.headersAuth() })
                        .then(r => {
                            if (r.status === 401 || r.status === 403) {
                                localStorage.removeItem('pl_token');
                                localStorage.removeItem('pl_usuario');
                                window.location.replace('/login');
                                return null;
                            }
                            return r.ok ? r.json() : null;
                        })
                        .then(d => {
                            if (!d) return;
                            this.autenticado = true;
                            this.loja = d.data || d;
                            this.configLoja = {
                                nome: this.loja.nome || '',
                                telefone_whatsapp: this.loja.telefone_whatsapp || '',
                                cor_primaria: this.loja.cor_primaria || '#DC2626',
                                endereco: this.loja.endereco || '',
                                cidade: this.loja.cidade || '',
                                uf: this.loja.uf || ''
                            };
                            this.carregarPedidos();
                            this.carregarCatalogo();
                            this.carregarEntregas();
                            this.carregarChamadosGarcom();
                            this.carregarStatusMp();
                            this.carregarStatusWhatsapp();
                            this.carregarConversasWhatsapp(true);
                            if (new URLSearchParams(window.location.search).get('mp_oauth') === 'sucesso') {
                                this.toastMsg = 'Conta Mercado Pago conectada com sucesso via login!';
                                this.moduloAtivo = 'config';
                                window.history.replaceState({}, '', '/painel');
                            }
                            setInterval(() => {
                                this.carregarPedidos();
                                this.carregarChamadosGarcom();
                                this.carregarConversasWhatsapp(true);
                            }, 5000);
                        })
                        .catch(() => {
                            window.location.replace('/login');
                        });
                    this.iniciarAudio();
                },

                ocultarOnboarding() {
                    this.onboardingOculto = true;
                    localStorage.setItem('pl_onboarding_oculto', '1');
                },

                alternarAutoImpressao() {
                    this.autoImprimir80mm = !this.autoImprimir80mm;
                    localStorage.setItem('pl_auto_print_80mm', this.autoImprimir80mm ? '1' : '0');
                    this.toastMsg = this.autoImprimir80mm
                        ? 'Auto-impressão 80mm ativada ao aceitar pedidos!'
                        : 'Auto-impressão 80mm desativada.';
                },

                copiarLinkCardapio() {
                    const url = window.location.origin + '/p/' + (this.loja?.slug || 'brasa-artesanal');
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(url);
                    }
                    this.toastMsg = 'Link público copiado: ' + url;
                },

                abrirDetalhesPedido(pedido) {
                    this.pedidoSelecionado = pedido;
                },

                abrirDivisaoMesa(mesa) {
                    this.mesaDivisao = mesa;
                    this.pessoasDivisaoMesa = 2;
                    this.incluirTaxaServicoMesa = true;
                },

                toggleFullscreenKds() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(() => {});
                    } else if (document.exitFullscreen) {
                        document.exitFullscreen().catch(() => {});
                    }
                },

                voltarSuperAdmin() {
                    const admToken = sessionStorage.getItem('pl_superadmin_token');
                    if (admToken) {
                        localStorage.setItem('pl_token', admToken);
                        sessionStorage.removeItem('pl_superadmin_token');
                    }
                    window.location.href = '/admin';
                },

                sair() {
                    fetch('/api/auth/logout', { method: 'POST', headers: this.headersAuth() }).catch(() => {});
                    localStorage.removeItem('pl_token');
                    localStorage.removeItem('pl_usuario');
                    sessionStorage.removeItem('pl_superadmin_token');
                    window.location.replace('/login');
                },

                mudarModulo(mod) {
                    this.moduloAtivo = mod;
                    if (mod === 'catalogo') { this.carregarCatalogo(); this.carregarCupons(); }
                    if (mod === 'kds') this.carregarKds();
                    if (mod === 'mesas') { this.carregarMesas(); this.carregarChamadosGarcom(); }
                    if (mod === 'entregas') this.carregarEntregas();
                    if (mod === 'crm' || mod === 'financeiro') { this.carregarFinanceiro(); this.carregarCupons(); }
                    if (mod === 'config') this.carregarLoja();
                    if (mod === 'whatsapp') { this.carregarStatusWhatsapp(); this.carregarConversasWhatsapp(); }
                },

                atualizarTudo() {
                    this.carregarPedidos();
                    this.carregarChamadosGarcom();
                    if (this.moduloAtivo === 'catalogo') { this.carregarCatalogo(); this.carregarCupons(); }
                    if (this.moduloAtivo === 'kds') this.carregarKds();
                    if (this.moduloAtivo === 'mesas') this.carregarMesas();
                    if (this.moduloAtivo === 'entregas') this.carregarEntregas();
                    if (this.moduloAtivo === 'crm' || this.moduloAtivo === 'financeiro') this.carregarFinanceiro();
                },

                headersAuth() {
                    const token = localStorage.getItem('pl_token') || '';
                    return {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': token ? `Bearer ${token}` : ''
                    };
                },

                carregarChamadosGarcom() {
                    fetch('/api/mesas-chamados', { headers: this.headersAuth() })
                        .then(r => r.ok ? r.json() : null)
                        .then(d => {
                            if (d && Array.isArray(d.chamados)) {
                                this.chamadosGarcom = d.chamados;
                                this.verificarAlertaSonoro();
                            }
                        })
                        .catch(() => {});
                },

                atenderChamadoGarcom(mesaId) {
                    fetch(`/api/mesas/${mesaId}/atender-chamado`, {
                        method: 'POST',
                        headers: this.headersAuth()
                    }).then(() => {
                        this.chamadosGarcom = this.chamadosGarcom.filter(c => Number(c.mesa_id) !== Number(mesaId));
                        this.toastMsg = `Chamado da Mesa atendido!`;
                        this.verificarAlertaSonoro();
                    });
                },

                carregarCupons() {
                    fetch('/api/cupons', { headers: this.headersAuth() })
                        .then(r => r.ok ? r.json() : null)
                        .then(d => {
                            if (d) this.cupons = d.data || [];
                        })
                        .catch(() => {});
                },

                salvarNovoCupom() {
                    const isPct = this.novoCupom.tipo === 'percentual';
                    const valor = isPct
                        ? (parseInt(this.novoCupom.valor_input, 10) || 10)
                        : (Math.round(parseFloat(String(this.novoCupom.valor_input).replace(',', '.')) * 100) || 1000);
                    const minCentavos = Math.round(parseFloat(String(this.novoCupom.minimo_reais || '0').replace(',', '.')) * 100) || 0;

                    fetch('/api/cupons', {
                        method: 'POST',
                        headers: this.headersAuth(),
                        body: JSON.stringify({
                            codigo: this.novoCupom.codigo,
                            tipo: this.novoCupom.tipo,
                            valor: valor,
                            minimo_centavos: minCentavos
                        })
                    }).then(r => {
                        if (r.ok) {
                            this.modalNovoCupom = false;
                            this.novoCupom = { codigo: '', tipo: 'percentual', valor_input: '10', minimo_reais: '30,00' };
                            this.toastMsg = 'Cupom promocional ativado com sucesso!';
                            this.carregarCupons();
                        }
                    });
                },

                alternarCupom(id) {
                    fetch(`/api/cupons/${id}`, { method: 'PATCH', headers: this.headersAuth() })
                        .then(() => this.carregarCupons());
                },

                excluirCupom(id) {
                    fetch(`/api/cupons/${id}`, { method: 'DELETE', headers: this.headersAuth() })
                        .then(() => {
                            this.toastMsg = 'Cupom removido.';
                            this.carregarCupons();
                        });
                },

                carregarLoja() {
                    fetch('/api/loja', { headers: this.headersAuth() })
                        .then(r => r.ok ? r.json() : null)
                        .then(d => {
                            if (d) {
                                this.loja = d.data || d;
                                this.configLoja = {
                                    nome: this.loja.nome || 'Brasa & Sabor Artesanal',
                                    telefone_whatsapp: this.loja.telefone_whatsapp || '31987654321',
                                    cor_primaria: this.loja.cor_primaria || '#DC2626',
                                    endereco: this.loja.endereco || '',
                                    cidade: this.loja.cidade || 'Coronel Fabriciano',
                                    uf: this.loja.uf || 'MG'
                                };
                            }
                        });
                },

                salvarDadosLoja() {
                    fetch('/api/loja', {
                        method: 'PATCH',
                        headers: this.headersAuth(),
                        body: JSON.stringify(this.configLoja)
                    })
                    .then(r => r.json())
                    .then(() => {
                        this.toastMsg = 'Dados operacionais e identidade visual da loja atualizados!';
                        this.carregarLoja();
                    });
                },

                carregarPedidos() {
                    fetch('/api/painel/pedidos', { headers: this.headersAuth() })
                        .then(r => r.json())
                        .then(data => {
                            this.pedidos = data.data || [];
                            this.verificarAlertaSonoro();
                        })
                        .catch(() => {});
                },

                carregarCatalogo() {
                    fetch('/api/categorias', { headers: this.headersAuth() })
                        .then(r => r.json())
                        .then(d => {
                            this.categorias = d.data || [];
                            if (this.categorias.length > 0 && !this.novoProd.categoria_id) {
                                this.novoProd.categoria_id = this.categorias[0].id;
                            }
                        });
                    fetch('/api/produtos', { headers: this.headersAuth() })
                        .then(r => r.json())
                        .then(d => { this.produtos = d.data || []; });
                    this.carregarCupons();
                },

                abrirModalProduto(prod = null) {
                    this.arquivoFotoProduto = null;
                    if (prod) {
                        this.novoProd = {
                            id: prod.id,
                            categoria_id: prod.categoria_id,
                            nome: prod.nome,
                            descricao: prod.descricao || '',
                            preco_reais: ((prod.preco_centavos || prod.preco_efetivo_centavos) / 100).toFixed(2).replace('.', ','),
                            preco_promocional_reais: prod.preco_promocional_centavos ? (prod.preco_promocional_centavos / 100).toFixed(2).replace('.', ',') : '',
                            estacao_kds: prod.estacao_kds || 'chapa',
                            imagem: prod.imagem || '',
                            variacoes: (prod.variacoes || []).map(v => ({
                                nome: v.nome,
                                preco_reais: ((v.preco_centavos || 0) / 100).toFixed(2).replace('.', ',')
                            }))
                        };
                    } else {
                        this.novoProd = {
                            id: null,
                            categoria_id: this.categorias[0]?.id || '',
                            nome: '',
                            descricao: '',
                            preco_reais: '',
                            preco_promocional_reais: '',
                            estacao_kds: 'chapa',
                            imagem: '',
                            variacoes: []
                        };
                    }
                    this.modalNovoProduto = true;
                },

                alternarEstoqueProduto(prod) {
                    fetch(`/api/produtos/${prod.id}/pausar`, {
                        method: 'POST',
                        headers: this.headersAuth()
                    }).then(() => {
                        this.toastMsg = `Status de estoque de "${prod.nome}" atualizado!`;
                        this.carregarCatalogo();
                    });
                },

                salvarNovoProduto() {
                    const centavos = Math.round(parseFloat(String(this.novoProd.preco_reais).replace(',', '.')) * 100) || 1990;
                    const promoRaw = String(this.novoProd.preco_promocional_reais || '').trim();
                    const promoCentavos = promoRaw ? (Math.round(parseFloat(promoRaw.replace(',', '.')) * 100) || null) : null;
                    const variacoesPayload = (this.novoProd.variacoes || [])
                        .filter(v => (v.nome || '').trim() !== '')
                        .map((v, idx) => ({
                            nome: v.nome.trim(),
                            preco_centavos: Math.round(parseFloat(String(v.preco_reais || '0').replace(',', '.')) * 100) || centavos,
                            ordem: idx
                        }));

                    const url = this.novoProd.id ? `/api/produtos/${this.novoProd.id}` : '/api/produtos';
                    const method = this.novoProd.id ? 'PUT' : 'POST';

                    fetch(url, {
                        method: method,
                        headers: this.headersAuth(),
                        body: JSON.stringify({
                            categoria_id: Number(this.novoProd.categoria_id),
                            nome: this.novoProd.nome,
                            descricao: this.novoProd.descricao,
                            preco_centavos: centavos,
                            preco_promocional_centavos: promoCentavos,
                            estacao_kds: this.novoProd.estacao_kds || 'chapa',
                            imagem: this.novoProd.imagem || null,
                            variacoes: variacoesPayload,
                            ativo: true,
                            canais: ['delivery', 'retirada', 'mesa', 'balcao']
                        })
                    })
                    .then(r => r.ok ? r.json() : null)
                    .then(res => {
                        if (!res) return;
                        const prodId = this.novoProd.id || res.produto?.id || res.data?.id;
                        if (this.arquivoFotoProduto && prodId) {
                            const fd = new FormData();
                            fd.append('imagem', this.arquivoFotoProduto);
                            const token = localStorage.getItem('pl_token') || '';
                            fetch(`/api/produtos/${prodId}/imagem`, {
                                method: 'POST',
                                headers: { 'Accept': 'application/json', 'Authorization': token ? `Bearer ${token}` : '' },
                                body: fd
                            }).finally(() => {
                                this.modalNovoProduto = false;
                                this.toastMsg = 'Produto e foto WebP salvos no cardápio!';
                                this.carregarCatalogo();
                            });
                        } else {
                            this.modalNovoProduto = false;
                            this.toastMsg = 'Cardápio atualizado com sucesso!';
                            this.carregarCatalogo();
                        }
                    });
                },

                salvarNovaCategoria() {
                    fetch('/api/categorias', {
                        method: 'POST',
                        headers: this.headersAuth(),
                        body: JSON.stringify({
                            nome: this.novaCat.nome,
                            descricao: this.novaCat.descricao,
                            ativa: true,
                            canais: ['delivery', 'retirada', 'mesa', 'balcao']
                        })
                    }).then(r => {
                        if (r.ok) {
                            this.modalNovaCategoria = false;
                            this.novaCat = { nome: '', descricao: '' };
                            this.toastMsg = 'Nova categoria adicionada ao cardápio!';
                            this.carregarCatalogo();
                        }
                    });
                },

                carregarKds() {
                    const q = this.estacaoKds ? `?estacao=${this.estacaoKds}` : '';
                    fetch(`/api/kds/tickets${q}`, { headers: this.headersAuth() })
                        .then(r => r.json())
                        .then(d => { this.kdsTickets = d.tickets || []; });
                },

                avancarItemKds(ticket) {
                    const novo = ticket.status_cozinha === 'fila' ? 'preparando' : 'pronto';
                    fetch(`/api/kds/itens/${ticket.item_id}/status`, {
                        method: 'PATCH',
                        headers: this.headersAuth(),
                        body: JSON.stringify({ status_cozinha: novo })
                    }).then(() => this.carregarKds());
                },

                carregarMesas() {
                    fetch('/api/mesas', { headers: this.headersAuth() })
                        .then(r => r.json())
                        .then(d => { this.mesas = d.data || []; });
                },

                criarNovaMesa() {
                    const proxNum = this.mesas.length > 0 ? Math.max(...this.mesas.map(m => m.numero)) + 1 : 1;
                    fetch('/api/mesas', {
                        method: 'POST',
                        headers: this.headersAuth(),
                        body: JSON.stringify({ numero: proxNum, nome: 'Mesa ' + proxNum, capacidade: 4 })
                    }).then(() => {
                        this.toastMsg = `Mesa #${proxNum} criada com QR Code!`;
                        this.carregarMesas();
                    });
                },

                regenerarQrMesa(id) {
                    fetch(`/api/mesas/${id}/regenerar-qr`, {
                        method: 'POST',
                        headers: this.headersAuth()
                    }).then(() => {
                        this.toastMsg = 'Novo QR Code anti-fraude gerado para a mesa!';
                        this.carregarMesas();
                    });
                },

                carregarEntregas() {
                    fetch('/api/entregadores', { headers: this.headersAuth() })
                        .then(r => r.json())
                        .then(d => { this.entregadores = d.data || d || []; })
                        .catch(() => {});
                    fetch('/api/areas-entrega', { headers: this.headersAuth() })
                        .then(r => r.json())
                        .then(d => { this.areasEntrega = d.data || d || []; })
                        .catch(() => {});
                },

                salvarNovoEntregador() {
                    const centavos = Math.round(parseFloat(String(this.novoEnt.valor_reais).replace(',', '.')) * 100) || 700;
                    fetch('/api/entregadores', {
                        method: 'POST',
                        headers: this.headersAuth(),
                        body: JSON.stringify({
                            nome: this.novoEnt.nome,
                            telefone: this.novoEnt.telefone,
                            tipo: this.novoEnt.tipo,
                            valor_por_entrega_centavos: centavos,
                            ativo: true
                        })
                    }).then(r => {
                        if (r.ok) {
                            this.modalNovoEntregador = false;
                            this.novoEnt = { nome: '', telefone: '', tipo: 'proprio', valor_reais: '7,00' };
                            this.toastMsg = 'Entregador cadastrado!';
                            this.carregarEntregas();
                        }
                    });
                },

                salvarNovaArea() {
                    const centavos = Math.round(parseFloat(String(this.novaArea.taxa_reais).replace(',', '.')) * 100) || 500;
                    fetch('/api/areas-entrega', {
                        method: 'POST',
                        headers: this.headersAuth(),
                        body: JSON.stringify({
                            tipo: 'bairro',
                            nome_bairro: this.novaArea.nome_bairro,
                            taxa_centavos: centavos,
                            pedido_minimo_centavos: 2000,
                            tempo_estimado_min: this.novaArea.tempo_estimado_min || 30,
                            ativa: true
                        })
                    }).then(r => {
                        if (r.ok) {
                            this.modalNovaArea = false;
                            this.novaArea = { nome_bairro: '', taxa_reais: '5,00', tempo_estimado_min: 30 };
                            this.toastMsg = 'Nova taxa de entrega salva!';
                            this.carregarEntregas();
                        }
                    });
                },

                removerAreaEntrega(id) {
                    fetch(`/api/areas-entrega/${id}`, { method: 'DELETE', headers: this.headersAuth() })
                        .then(() => this.carregarEntregas());
                },

                atribuirMotoboy(pedidoId, entregadorId) {
                    if (!entregadorId) return;
                    fetch(`/api/painel/pedidos/${pedidoId}/atribuir-entregador`, {
                        method: 'POST',
                        headers: this.headersAuth(),
                        body: JSON.stringify({ entregador_id: Number(entregadorId) })
                    }).then(() => {
                        this.toastMsg = 'Motoboy vinculado ao pedido!';
                        this.carregarPedidos();
                    });
                },

                carregarFinanceiro() {
                    fetch('/api/relatorios/vendas', { headers: this.headersAuth() })
                        .then(r => r.json())
                        .then(d => { this.relatorio = d || {}; });
                    this.carregarStatusMp();
                },

                carregarStatusMp() {
                    fetch('/api/pagamentos/mercadopago/status', { headers: this.headersAuth() })
                        .then(r => r.ok ? r.json() : null)
                        .then(d => {
                            if (d) {
                                this.mpStatus = d;
                                if (d.chave_pix && !this.formMp.chave_pix) this.formMp.chave_pix = d.chave_pix;
                                if (d.public_key && !this.formMp.public_key) this.formMp.public_key = d.public_key;
                            }
                        })
                        .catch(() => {});
                },

                salvarTokenMercadoPago() {
                    if (!this.formMp.access_token.trim()) return;
                    this.salvandoMp = true;
                    fetch('/api/pagamentos/mercadopago/salvar-token', {
                        method: 'POST',
                        headers: this.headersAuth(),
                        body: JSON.stringify(this.formMp)
                    })
                    .then(async r => {
                        const d = await r.json();
                        this.salvandoMp = false;
                        if (r.ok) {
                            this.toastMsg = d.message || 'Conta Mercado Pago autorizada com sucesso!';
                            this.formMp.access_token = '';
                            this.carregarStatusMp();
                        } else {
                            alert(d.message || 'Erro ao validar o Access Token do Mercado Pago.');
                        }
                    })
                    .catch(() => {
                        this.salvandoMp = false;
                    });
                },

                desconectarMercadoPago() {
                    fetch('/api/pagamentos/mercadopago/desconectar', {
                        method: 'POST',
                        headers: this.headersAuth()
                    })
                    .then(r => r.json())
                    .then(d => {
                        this.pixTesteResultado = null;
                        this.toastMsg = d.message || 'Conta Mercado Pago desconectada.';
                        this.carregarStatusMp();
                    });
                },

                testarPixMercadoPago() {
                    this.testandoPix = true;
                    this.pixTesteResultado = null;
                    fetch('/api/pagamentos/mercadopago/testar-pix', {
                        method: 'POST',
                        headers: this.headersAuth()
                    })
                    .then(async r => {
                        const d = await r.json();
                        this.testandoPix = false;
                        if (r.ok) {
                            this.pixTesteResultado = d;
                            this.toastMsg = 'QR Code PIX real emitido com sucesso!';
                        } else {
                            alert(d.message || 'Erro ao emitir PIX de teste.');
                        }
                    })
                    .catch(() => {
                        this.testandoPix = false;
                    });
                },

                abrirCaixa() {
                    fetch('/api/caixa/abrir', {
                        method: 'POST',
                        headers: this.headersAuth(),
                        body: JSON.stringify({ saldo_inicial: 10000 })
                    }).then(() => {
                        this.toastMsg = 'Turno de Caixa aberto com R$ 100,00 de fundo de troco!';
                    });
                },

                conectarMercadoPago() {
                    fetch('/api/pagamentos/mercadopago/conectar', { headers: this.headersAuth() })
                        .then(r => r.json())
                        .then(d => {
                            if (d.oauth_url || d.authorization_url) {
                                window.location.href = d.oauth_url || d.authorization_url;
                            } else {
                                this.toastMsg = d.message || 'Informe seu Access Token (APP_USR-...) no campo abaixo para conectar sua conta.';
                            }
                        });
                },

                abrirModalPdv() {
                    if (this.produtos.length === 0) this.carregarCatalogo();
                    this.pdv.itens = [];
                    this.modalPdv = true;
                },

                adicionarItemPdv(prod) {
                    const ex = this.pdv.itens.find(i => i.produto_id === prod.id);
                    if (ex) ex.qtd++;
                    else this.pdv.itens.push({ produto_id: prod.id, nome: prod.nome, qtd: 1 });
                },

                enviarPedidoPdv() {
                    const slug = this.loja?.slug || 'brasa-artesanal';
                    fetch(`/api/p/${slug}/pedidos`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({
                            idempotency_key: 'pdv_' + Date.now(),
                            canal: this.pdv.canal,
                            cliente: { nome: this.pdv.nome || 'Cliente Balcão', telefone: this.pdv.telefone || '31999999999' },
                            endereco: this.pdv.canal === 'delivery' ? { cep: '35170-001', rua: 'Av. Central', numero: '100', bairro: 'Centro', cidade: 'Coronel Fabriciano', uf: 'MG' } : null,
                            itens: this.pdv.itens.map(i => ({ produto_id: i.produto_id, qtd: i.qtd })),
                            forma_pagamento: this.pdv.forma_pagamento
                        })
                    }).then(r => {
                        if (r.ok) {
                            this.modalPdv = false;
                            this.toastMsg = 'Pedido PDV lançado no Kanban!';
                            this.carregarPedidos();
                        }
                    });
                },

                proximoStatus(pedido) {
                    if (pedido.status === 'novo') return 'aceito';
                    if (pedido.status === 'aceito') return 'em_preparo';
                    if (pedido.status === 'em_preparo') return 'pronto';
                    if (pedido.status === 'pronto') {
                        if (pedido.canal === 'delivery') return 'saiu_entrega';
                        if (pedido.canal === 'retirada') return 'retirado';
                        if (pedido.canal === 'mesa') return 'servido';
                        return 'entregue';
                    }
                    if (pedido.status === 'saiu_entrega') return 'entregue';
                    return null;
                },

                rotuloProximoStatus(pedido) {
                    const prox = this.proximoStatus(pedido);
                    const map = {
                        aceito: '✓ Aceitar Pedido',
                        em_preparo: '🔥 Iniciar Preparo',
                        pronto: '✓ Marcar como Pronto',
                        saiu_entrega: '🛵 Despachar p/ Entrega',
                        retirado: '🛍️ Confirmar Retirada',
                        servido: '🍽️ Marcar Servido na Mesa',
                        entregue: '✓ Concluir Entrega'
                    };
                    return map[prox] || 'Avançar';
                },

                avancarPedido(pedido) {
                    const prox = this.proximoStatus(pedido);
                    if (!prox) return;
                    const eraNovo = pedido.status === 'novo';
                    fetch(`/api/painel/pedidos/${pedido.id}/status`, {
                        method: 'PATCH',
                        headers: this.headersAuth(),
                        body: JSON.stringify({ status: prox })
                    })
                    .then(r => r.json())
                    .then(() => {
                        if (eraNovo && this.autoImprimir80mm) {
                            this.imprimirPedido(pedido.id, 'cozinha');
                        }
                        this.carregarPedidos();
                    });
                },

                cancelarPedido(pedido) {
                    fetch(`/api/painel/pedidos/${pedido.id}/status`, {
                        method: 'PATCH',
                        headers: this.headersAuth(),
                        body: JSON.stringify({ status: 'cancelado', motivo: 'Cancelado pelo operador no painel' })
                    }).then(() => this.carregarPedidos());
                },

                imprimirPedido(pedidoId, tipo) {
                    const tk = encodeURIComponent(localStorage.getItem('pl_token') || '');
                    window.open(`/painel/pedidos/${pedidoId}/imprimir?tipo=${tipo}&token=${tk}`, '_blank', 'width=400,height=600');
                    fetch(`/api/painel/pedidos/${pedidoId}/imprimir`, {
                        method: 'POST',
                        headers: this.headersAuth(),
                        body: JSON.stringify({ tipo: tipo })
                    });
                },

                iniciarAudio() {
                    try {
                        const AudioContext = window.AudioContext || window.webkitAudioContext;
                        this.audioCtx = new AudioContext();
                        // Navegadores bloqueiam áudio até o 1º toque do usuário: destrava no primeiro clique/toque
                        const destravar = () => {
                            if (this.audioCtx && this.audioCtx.state === 'suspended') this.audioCtx.resume();
                        };
                        ['click', 'touchstart', 'keydown'].forEach(ev => document.addEventListener(ev, destravar, { passive: true }));
                    } catch (e) {}
                },

                // Toque de telefone/campainha de cozinha: "trrrrrring-trrrrrring" (2 toques de ~0,9s) + pausa
                tocarChime() {
                    if (!this.somHabilitado || !this.audioCtx) return;
                    const ctx = this.audioCtx;
                    if (ctx.state === 'suspended') ctx.resume();

                    const inicio = ctx.currentTime + 0.05;
                    const mestre = ctx.createGain();
                    mestre.gain.value = 0.9;
                    mestre.connect(ctx.destination);

                    // Cada "toque" = duas notas (1400 Hz + 1800 Hz, timbre de sino metálico) com tremolo rápido de martelo (~22 Hz)
                    const toque = (t0, duracao) => {
                        [1400, 1800, 2800].forEach((freq, i) => {
                            const osc = ctx.createOscillator();
                            const ganho = ctx.createGain();
                            const tremolo = ctx.createOscillator();
                            const tremoloGanho = ctx.createGain();

                            osc.type = i === 2 ? 'sine' : 'square';
                            osc.frequency.setValueAtTime(freq, t0);

                            tremolo.type = 'square';
                            tremolo.frequency.setValueAtTime(22, t0);
                            tremoloGanho.gain.setValueAtTime(i === 2 ? 0.05 : 0.22, t0);

                            ganho.gain.setValueAtTime(0.0001, t0);
                            ganho.gain.exponentialRampToValueAtTime(i === 2 ? 0.08 : 0.28, t0 + 0.02);
                            ganho.gain.setValueAtTime(i === 2 ? 0.08 : 0.28, t0 + duracao - 0.05);
                            ganho.gain.exponentialRampToValueAtTime(0.0001, t0 + duracao);

                            tremolo.connect(tremoloGanho);
                            tremoloGanho.connect(ganho.gain);
                            osc.connect(ganho);
                            ganho.connect(mestre);

                            osc.start(t0);
                            tremolo.start(t0);
                            osc.stop(t0 + duracao + 0.02);
                            tremolo.stop(t0 + duracao + 0.02);
                        });
                    };

                    toque(inicio, 0.85);
                    toque(inicio + 1.05, 0.85);

                    if (navigator.vibrate) navigator.vibrate([400, 150, 400]);
                },

                verificarAlertaSonoro() {
                    const temNovos = this.colunas.novo.length > 0 || this.chamadosGarcom.length > 0;
                    if (temNovos && !this.somInterval) {
                        this.tocarChime();
                        this.somInterval = setInterval(() => this.tocarChime(), 3200);
                    } else if (!temNovos && this.somInterval) {
                        clearInterval(this.somInterval);
                        this.somInterval = null;
                    }
                },

                alternarSom() {
                    this.somHabilitado = !this.somHabilitado;
                    if (this.somHabilitado) {
                        this.tocarChime();
                    }
                    if (!this.somHabilitado && this.somInterval) {
                        clearInterval(this.somInterval);
                        this.somInterval = null;
                    }
                },

                async carregarStatusWhatsapp() {
                    try {
                        const r = await fetch('/api/painel/whatsapp/status', { headers: this.headersAuth() });
                        if (r.ok) {
                            const d = await r.json();
                            this.whatsappStatus = {
                                ...this.whatsappStatus,
                                ...d,
                                config: {
                                    ...this.whatsappStatus.config,
                                    ...(d.config || {})
                                }
                            };
                            this.carregarPreviewWinback();
                        }
                    } catch (e) {
                        console.error('Erro ao consultar status do WhatsApp:', e);
                    }
                },

                async conectarWhatsapp() {
                    this.whatsappCarregando = true;
                    try {
                        const r = await fetch('/api/painel/whatsapp/conectar', {
                            method: 'POST',
                            headers: this.headersAuth()
                        });
                        const d = await r.json();
                        if (r.ok) {
                            this.whatsappStatus.qrBase64 = d.qrBase64;
                            this.whatsappStatus.state = d.state;
                            this.whatsappStatus.connected = d.connected;
                            this.whatsappStatus.phone = d.phone;
                            this.toastMsg = 'QR Code gerado! Aponte a câmera do WhatsApp para escanear.';

                            this.iniciarPollingWhatsapp();
                        } else {
                            this.toastMsg = 'Falha ao iniciar conexão: ' + (d.message || 'Erro');
                        }
                    } catch (e) {
                        this.toastMsg = 'Erro ao conectar WhatsApp. Tente novamente.';
                    } finally {
                        this.whatsappCarregando = false;
                    }
                },

                iniciarPollingWhatsapp() {
                    if (this.whatsappPolling) clearInterval(this.whatsappPolling);
                    this.whatsappPolling = setInterval(async () => {
                        await this.carregarStatusWhatsapp();
                        if (this.whatsappConectado) {
                            clearInterval(this.whatsappPolling);
                            this.whatsappPolling = null;
                            this.toastMsg = '🎉 WhatsApp conectado com sucesso!';
                        }
                    }, 3500);
                },

                async desconectarWhatsapp() {
                    if (!confirm('Deseja realmente desconectar o WhatsApp da loja?')) return;
                    this.whatsappCarregando = true;
                    try {
                        const r = await fetch('/api/painel/whatsapp/desconectar', {
                            method: 'POST',
                            headers: this.headersAuth()
                        });
                        const d = await r.json();
                        if (r.ok) {
                            this.whatsappStatus.connected = false;
                            this.whatsappStatus.phone = null;
                            this.whatsappStatus.qrBase64 = null;
                            this.whatsappStatus.state = 'disconnected';
                            if (this.whatsappPolling) {
                                clearInterval(this.whatsappPolling);
                                this.whatsappPolling = null;
                            }
                            this.toastMsg = 'WhatsApp desconectado com sucesso.';
                        } else {
                            this.toastMsg = d.message || 'Erro ao desconectar.';
                        }
                    } catch (e) {
                        this.toastMsg = 'Erro ao desconectar WhatsApp.';
                    } finally {
                        this.whatsappCarregando = false;
                    }
                },

                async salvarConfigWhatsapp() {
                    this.salvandoConfigWpp = true;
                    try {
                        const r = await fetch('/api/painel/whatsapp/config', {
                            method: 'POST',
                            headers: this.headersAuth(),
                            body: JSON.stringify(this.whatsappStatus.config)
                        });
                        if (r.ok) {
                            this.toastMsg = 'Automações do WhatsApp atualizadas com sucesso!';
                        }
                    } catch (e) {
                        this.toastMsg = 'Erro ao salvar configurações do WhatsApp.';
                    } finally {
                        this.salvandoConfigWpp = false;
                    }
                },

                async enviarTesteWhatsapp() {
                    if (!this.whatsappTelefoneTeste) {
                        alert('Por favor, informe seu número com DDD para o teste.');
                        return;
                    }
                    this.whatsappCarregando = true;
                    try {
                        const r = await fetch('/api/painel/whatsapp/teste', {
                            method: 'POST',
                            headers: this.headersAuth(),
                            body: JSON.stringify({
                                telefone: this.whatsappTelefoneTeste,
                                mensagem: this.whatsappMsgTeste || undefined
                            })
                        });
                        const d = await r.json();
                        if (d.success) {
                            this.toastMsg = '✅ ' + d.message;
                            this.whatsappMsgTeste = '';
                        } else {
                            alert(d.message || 'Falha ao enviar mensagem de teste.');
                        }
                    } catch (e) {
                        alert('Erro ao comunicar com o servidor.');
                    } finally {
                        this.whatsappCarregando = false;
                    }
                },

                async carregarPreviewWinback() {
                    this.carregandoWinback = true;
                    try {
                        const r = await fetch('/api/painel/whatsapp/campanhas/preview', { headers: this.headersAuth() });
                        if (r.ok) {
                            const d = await r.json();
                            if (d.success) {
                                this.winbackPreview = d;
                            }
                        }
                    } catch (e) {
                        console.error('Erro ao consultar preview do win-back:', e);
                    } finally {
                        this.carregandoWinback = false;
                    }
                },

                async dispararCampanhaWinback() {
                    if (!confirm('Deseja iniciar o disparo da campanha de reativação para os clientes inativos elegíveis? As mensagens serão enviadas com delay anti-ban de forma segura.')) return;
                    this.disparandoWinback = true;
                    try {
                        const r = await fetch('/api/painel/whatsapp/campanhas/disparar', {
                            method: 'POST',
                            headers: this.headersAuth()
                        });
                        const d = await r.json();
                        if (r.ok && d.success) {
                            this.toastMsg = `🚀 Campanha iniciada! ${d.enviados || 0} mensagens enviadas (Cupom: ${d.cupom || ''}).`;
                            await this.carregarPreviewWinback();
                            if (this.carregarConversasWhatsapp) {
                                this.carregarConversasWhatsapp(true);
                            }
                        } else {
                            alert(d.message || 'Não foi possível disparar a campanha.');
                        }
                    } catch (e) {
                        alert('Erro ao disparar campanha no servidor.');
                    } finally {
                        this.disparandoWinback = false;
                    }
                },

                mudarSubAbaWhatsapp(subAba) {
                    this.subAbaWhatsapp = subAba;
                    if (subAba === 'chat') {
                        this.carregarConversasWhatsapp();
                    } else if (subAba === 'config') {
                        this.carregarStatusWhatsapp();
                        this.carregarPreviewWinback();
                    }
                },

                async carregarConversasWhatsapp(silencioso = false) {
                    if (!silencioso) this.chatCarregando = true;
                    try {
                        let url = `/api/painel/whatsapp/conversas?estado=${this.filtroEstadoConversa}`;
                        if (this.buscaConversas) {
                            url += `&busca=${encodeURIComponent(this.buscaConversas)}`;
                        }
                        const r = await fetch(url, { headers: this.headersAuth() });
                        if (r.ok) {
                            const d = await r.json();
                            const anteriorHumano = this.totalAguardandoHumano;
                            this.conversasWhatsapp = d.conversas || [];
                            this.totalAguardandoHumano = d.total_aguardando_humano || 0;

                            // Alerta sonoro suave se houver novo chamado humano
                            if (this.totalAguardandoHumano > anteriorHumano && this.somHabilitado) {
                                this.tocarChime();
                            }

                            // Se houver uma conversa aberta, atualiza mensagens
                            if (this.conversaSelecionada) {
                                const atualizada = this.conversasWhatsapp.find(c => c.id === this.conversaSelecionada.id);
                                if (atualizada) {
                                    this.conversaSelecionada = { ...this.conversaSelecionada, ...atualizada };
                                }
                                if (silencioso) {
                                    this.carregarMensagensConversa(this.conversaSelecionada.id, true);
                                }
                            }
                        }
                    } catch (e) {
                        console.error('Erro ao carregar conversas do WhatsApp:', e);
                    } finally {
                        if (!silencioso) this.chatCarregando = false;
                    }
                },

                async selecionarConversa(conversa) {
                    this.conversaSelecionada = conversa;
                    await this.carregarMensagensConversa(conversa.id);
                },

                async carregarMensagensConversa(conversaId, silencioso = false) {
                    if (!silencioso) this.chatCarregando = true;
                    try {
                        const r = await fetch(`/api/painel/whatsapp/conversas/${conversaId}/mensagens`, {
                            headers: this.headersAuth()
                        });
                        if (r.ok) {
                            const d = await r.json();
                            if (d.conversa) {
                                this.conversaSelecionada = { ...this.conversaSelecionada, ...d.conversa };
                            }
                            const qtdAnterior = this.mensagensConversa.length;
                            this.mensagensConversa = d.mensagens || [];
                            if (this.mensagensConversa.length > qtdAnterior || !silencioso) {
                                this.$nextTick(() => this.rolarChatParaFinal());
                            }
                        }
                    } catch (e) {
                        console.error('Erro ao obter mensagens da conversa:', e);
                    } finally {
                        if (!silencioso) this.chatCarregando = false;
                    }
                },

                async enviarMensagemChat() {
                    const texto = (this.mensagemChatInput || '').trim();
                    if (!texto || !this.conversaSelecionada || this.chatEnviando) return;

                    this.chatEnviando = true;
                    try {
                        const r = await fetch(`/api/painel/whatsapp/conversas/${this.conversaSelecionada.id}/responder`, {
                            method: 'POST',
                            headers: this.headersAuth(),
                            body: JSON.stringify({ mensagem: texto })
                        });
                        const d = await r.json();
                        if (r.ok && d.success) {
                            this.mensagensConversa.push(d.mensagem);
                            this.mensagemChatInput = '';
                            if (this.conversaSelecionada) {
                                this.conversaSelecionada.estado = 'humano';
                            }
                            this.$nextTick(() => this.rolarChatParaFinal());
                            this.carregarConversasWhatsapp(true);
                        } else {
                            alert(d.message || 'Falha ao enviar mensagem pelo WhatsApp.');
                        }
                    } catch (e) {
                        alert('Erro ao enviar mensagem para o cliente.');
                    } finally {
                        this.chatEnviando = false;
                    }
                },

                async assumirConversaHumano(conversaId) {
                    this.chatEnviando = true;
                    try {
                        const r = await fetch(`/api/painel/whatsapp/conversas/${conversaId}/assumir`, {
                            method: 'POST',
                            headers: this.headersAuth(),
                            body: JSON.stringify({ avisar_cliente: false })
                        });
                        if (r.ok) {
                            if (this.conversaSelecionada) {
                                this.conversaSelecionada.estado = 'humano';
                            }
                            this.toastMsg = 'Você assumiu o atendimento da conversa.';
                            this.carregarConversasWhatsapp(true);
                        }
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.chatEnviando = false;
                    }
                },

                async devolverConversaBot(conversaId) {
                    if (!confirm('Deseja devolver esta conversa para o assistente automático?')) return;
                    this.chatEnviando = true;
                    try {
                        const r = await fetch(`/api/painel/whatsapp/conversas/${conversaId}/devolver-bot`, {
                            method: 'POST',
                            headers: this.headersAuth(),
                            body: JSON.stringify({ avisar_cliente: true })
                        });
                        if (r.ok) {
                            if (this.conversaSelecionada) {
                                this.conversaSelecionada.estado = 'ia_ativa';
                            }
                            this.toastMsg = 'Conversa devolvida para o robô!';
                            this.carregarMensagensConversa(conversaId, true);
                            this.carregarConversasWhatsapp(true);
                        }
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.chatEnviando = false;
                    }
                },

                usarRespostaRapida(texto) {
                    this.mensagemChatInput = texto;
                },

                rolarChatParaFinal() {
                    const el = this.$refs.chatMessagesBox;
                    if (el) {
                        el.scrollTop = el.scrollHeight;
                    }
                },

                formatarCentavos(centavos) {
                    return ((centavos || 0) / 100).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                }
            };
        }
    </script>
</body>
</html>
