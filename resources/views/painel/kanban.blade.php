<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-[#0b0f19] text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="theme-color" content="#0b0f19">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/manifest.json">
    <title>Painel do Lojista — PedeLivre</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body x-cloak x-show="autenticado" class="h-full flex flex-col lg:flex-row antialiased font-sans overflow-hidden bg-[#0b0f19] text-slate-100" x-data="painelApp()" x-init="iniciar()">

    {{-- 1. Barra Lateral de Navegação (Desktop) --}}
    @include('painel.partials.sidebar')

    {{-- 2. Área Central de Conteúdo --}}
    <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden">
        {{-- Header Superior & Avisos em Tempo Real --}}
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

    {{-- 3. Barra de Navegação Inferior (Mobile) --}}
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

    {{-- 5. Lógica Reativa Alpine.js (State & APIs) --}}
    @include('painel.partials.scripts')

</body>
</html>
