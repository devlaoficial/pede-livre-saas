<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-[#060911] text-slate-100 dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#060911">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PedeLivre OS">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-touch-icon.png">
    <title>Painel do Lojista — PedeLivre OS</title>

    <!-- Tipografia Premium: Plus Jakarta Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', '-apple-system', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        };
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(15, 23, 42, 0.4); border-radius: 9999px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(71, 85, 105, 0.5); border-radius: 9999px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(244, 63, 94, 0.65); }
        .pb-safe { padding-bottom: max(0.5rem, env(safe-area-inset-bottom)); }
        .pt-safe { padding-top: env(safe-area-inset-top); }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(244, 63, 94, 0.45); }
            50% { box-shadow: 0 0 0 8px rgba(244, 63, 94, 0); }
        }
        .pulse-rose-glow { animation: pulseGlow 2s infinite; }
    </style>
</head>
<body x-cloak x-show="autenticado"
      class="h-full flex flex-col lg:flex-row antialiased font-sans overflow-hidden bg-[#060911] text-slate-100 selection:bg-rose-500 selection:text-white"
      x-data="painelApp()"
      x-init="iniciar()">

    <!-- Ambient Glow sutil de fundo -->
    <div class="pointer-events-none fixed inset-0 overflow-hidden z-0">
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-rose-600/[0.06] rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 right-0 w-96 h-96 bg-sky-500/[0.04] rounded-full blur-3xl"></div>
    </div>

    {{-- 1. Barra Lateral de Navegação (Desktop) --}}
    @include('painel.partials.sidebar')

    {{-- 2. Área Central de Conteúdo --}}
    <div class="relative z-10 flex-1 flex flex-col min-w-0 h-full overflow-hidden">
        {{-- Header Superior, Controles Operacionais & Avisos em Tempo Real --}}
        @include('painel.partials.header')

        {{-- 9 Módulos Operacionais da Loja --}}
        @include('painel.modulos.pedidos')
        @include('painel.modulos.catalogo')
        @include('painel.modulos.kds')
        @include('painel.modulos.mesas')
        @include('painel.modulos.entregas')
        @include('painel.modulos.financeiro')
        @include('painel.modulos.crm')
        @include('painel.modulos.config')
        @include('painel.modulos.whatsapp')
    </div>

    {{-- 3. Barra de Navegação Inferior + Gaveta Operacional Completa (Mobile / Tablet PWA) --}}
    @include('painel.partials.bottom-nav')

    {{-- 4. Modais do Sistema --}}
    @include('painel.modals.modal-novo-produto')
    @include('painel.modals.modal-novo-cupom')
    @include('painel.modals.modal-detalhe-pedido')
    @include('painel.modals.modal-divisao-mesa')
    @include('painel.modals.modal-nova-categoria')
    @include('painel.modals.modal-novo-entregador')
    @include('painel.modals.modal-nova-area')
    @include('painel.modals.modal-pdv')
    @include('painel.modals.modal-abrir-caixa')
    @include('painel.modals.modal-movimento-caixa')
    @include('painel.modals.modal-fechar-caixa')

    {{-- Modal Guia de Instalação PWA (iOS / Android / Desktop) --}}
    <div x-cloak x-show="modalInstrucoesPwa"
         class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="modalInstrucoesPwa = false"
             class="bg-[#0d1322] border border-slate-800/90 w-full max-w-md rounded-t-3xl sm:rounded-3xl p-6 space-y-5 shadow-2xl">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <img src="/icons/icon-192.png" alt="PedeLivre OS" class="w-12 h-12 rounded-2xl shadow-lg shadow-rose-600/20">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-widest text-rose-400">Aplicativo do Lojista</span>
                        <h3 class="text-base font-black text-white">Instalar PedeLivre OS</h3>
                    </div>
                </div>
                <button @click="modalInstrucoesPwa = false" class="text-slate-400 hover:text-white text-sm font-bold p-1">✕</button>
            </div>

            <p class="text-xs text-slate-300 leading-relaxed">
                Instale o aplicativo oficial do painel no seu celular, tablet ou computador para receber <strong>alertas sonoros em tela cheia</strong>, manter a <strong>tela sempre acesa na cozinha</strong> e abrir o PDV em 1 toque.
            </p>

            <div class="space-y-2.5 text-xs">
                <div class="bg-[#070b14] border border-slate-800/80 rounded-2xl p-3.5 space-y-1.5">
                    <div class="font-extrabold text-white flex items-center gap-2">
                        <span>🍎</span> No iPhone ou iPad (Safari):
                    </div>
                    <ol class="list-decimal list-inside text-slate-300 space-y-1 text-[11px]">
                        <li>Toque no ícone <strong>Compartilhar</strong> (quadrado com seta para cima ⬆️) na barra do Safari.</li>
                        <li>Role para baixo e toque em <strong>"Adicionar à Tela de Início"</strong>.</li>
                        <li>Confirme em <strong>Adicionar</strong> no canto superior direito.</li>
                    </ol>
                </div>

                <div class="bg-[#070b14] border border-slate-800/80 rounded-2xl p-3.5 space-y-1.5">
                    <div class="font-extrabold text-white flex items-center gap-2">
                        <span>🤖</span> No Android ou Computador (Chrome / Edge):
                    </div>
                    <ol class="list-decimal list-inside text-slate-300 space-y-1 text-[11px]">
                        <li>Toque nos <strong>3 pontinhos (⋮)</strong> do navegador ou no ícone de instalação na barra de endereço.</li>
                        <li>Selecione <strong>"Instalar aplicativo"</strong> ou <strong>"Adicionar à tela inicial"</strong>.</li>
                    </ol>
                </div>
            </div>

            <div class="flex gap-2">
                <button @click="ativarNotificacoesNativas()"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold py-2.5 rounded-xl text-xs transition shadow-lg shadow-emerald-600/20">
                    🔔 Ativar Notificações
                </button>
                <button @click="modalInstrucoesPwa = false"
                        class="px-5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold py-2.5 rounded-xl text-xs transition">
                    Entendi
                </button>
            </div>
        </div>
    </div>

    {{-- 5. Lógica Reativa Alpine.js (State, PWA, WakeLock, Service Worker & APIs) --}}
    @include('painel.partials.scripts')

</body>
</html>
