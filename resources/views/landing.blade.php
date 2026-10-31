<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#020617">
    <link rel="manifest" href="/manifest.json">
    <title>PedeLivre — Sistema Completo para Restaurantes, Delivery, QR Mesa e KDS com 0% de Comissão</title>
    <meta name="description" content="Venda pelo seu canal próprio com 0% de comissão, PIX direto na sua conta Mercado Pago, QR Code de Mesa, KDS por estação e Gestão Completa no Celular ou Computador.">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 antialiased font-sans selection:bg-rose-600 selection:text-white"
      x-data="{
          pedidosMes: 600,
          ticketMedio: 65,
          taxaMarketplace: 23,
          abaEcossistema: 'cardapio',
          menuMobile: false,
          faqAberta: 1
      }">

    <!-- Navbar Principal Responsiva -->
    <header class="sticky top-0 z-50 bg-slate-950/90 backdrop-blur-md border-b border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-rose-600 to-rose-800 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-rose-600/30 group-hover:scale-105 transition">
                    P
                </div>
                <div>
                    <span class="text-xl font-black tracking-tight text-white">Pede<span class="text-rose-500">Livre</span></span>
                    <span class="block text-[10px] uppercase tracking-widest text-emerald-400 font-bold">0% Comissão • PIX Direto</span>
                </div>
            </a>

            <nav class="hidden lg:flex items-center gap-7 text-sm font-semibold text-slate-300">
                <a href="#ecossistema" class="hover:text-white transition">Telas PC &amp; Mobile</a>
                <a href="#economia" class="hover:text-white transition">Calculadora de Lucro</a>
                <a href="#recursos" class="hover:text-white transition">Recursos</a>
                <a href="#planos" class="hover:text-white transition">Planos</a>
                <a href="#faq" class="hover:text-white transition">Dúvidas</a>
            </nav>

            <div class="hidden sm:flex items-center gap-2.5">
                <a href="/login" class="text-xs sm:text-sm font-bold text-slate-300 hover:text-white px-3.5 py-2.5 rounded-xl hover:bg-slate-900 transition">Entrar</a>
                <a href="/cadastro?plano=premium" class="bg-rose-600 hover:bg-rose-500 text-white text-xs sm:text-sm font-black px-5 py-2.5 rounded-xl shadow-lg shadow-rose-600/30 transition">
                    Criar Minha Loja Grátis →
                </a>
            </div>

            <!-- Botão Menu Mobile -->
            <button @click="menuMobile = !menuMobile" class="sm:hidden p-2.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-200">
                <span x-text="menuMobile ? '✕' : '☰'" class="font-black text-base"></span>
            </button>
        </div>

        <!-- Drawer Mobile -->
        <div x-cloak x-show="menuMobile" class="sm:hidden bg-slate-900 border-b border-slate-800 px-4 py-4 space-y-3">
            <div class="grid grid-cols-2 gap-2">
                <a href="/p/brasa-artesanal" class="bg-slate-950 border border-slate-800 p-3 rounded-xl text-xs font-bold text-emerald-400 text-center">🍔 Ver Cardápio</a>
                <a href="/login" class="bg-slate-950 border border-slate-800 p-3 rounded-xl text-xs font-bold text-white text-center">🔑 Área do Lojista</a>
            </div>
            <a href="/cadastro?plano=premium" class="block w-full bg-rose-600 text-white font-black py-3 rounded-xl text-center text-sm">
                Testar 14 Dias Grátis →
            </a>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative overflow-hidden pt-12 pb-20 lg:pt-20 lg:pb-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-6 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold">
                    <span>⚡ PIX Direto na Sua Conta Mercado Pago • 100% Adaptado p/ Celular e PC</span>
                </div>

                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-white leading-[1.08]">
                    Demita as taxas de 27%. <span class="bg-gradient-to-r from-rose-500 via-rose-400 to-amber-400 bg-clip-text text-transparent">O lucro do seu restaurante é 100% seu.</span>
                </h1>

                <p class="text-base sm:text-lg text-slate-300 leading-relaxed">
                    Tenha seu próprio Cardápio Digital com PIX instantâneo, Kanban de Pedidos, PDV Balcão, QR Code de Mesa e KDS de Cozinha. Funciona perfeitamente tanto no celular do empreendedor quanto no computador do caixa.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    <a href="/cadastro?plano=premium" class="bg-rose-600 hover:bg-rose-500 text-white font-black px-6 py-4 rounded-2xl text-center shadow-xl shadow-rose-600/30 transition text-sm">
                        Criar Meu Cardápio Agora →
                    </a>
                    <a href="/p/brasa-artesanal" class="bg-slate-900 hover:bg-slate-800 text-emerald-400 border border-emerald-500/30 font-extrabold px-6 py-4 rounded-2xl text-center transition text-sm">
                        🍔 Ver Cardápio Exemplo
                    </a>
                </div>

                <div class="pt-4 border-t border-slate-800/80 flex flex-wrap items-center gap-4 text-xs text-slate-400">
                    <span>✓ 14 dias grátis sem cartão</span>
                    <span>•</span>
                    <span>✓ Cancelamento em 1 clique</span>
                    <span>•</span>
                    <a href="/login" class="text-white hover:text-rose-400 font-bold underline">Já é cliente? Acessar Painel →</a>
                </div>
            </div>

            <!-- Showcase Visual Interativo no Hero -->
            <div class="lg:col-span-6 space-y-4">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-4 sm:p-6 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-bold text-slate-400 ml-2">Operação Completa para Restaurantes</span>
                        </div>
                        <span class="text-[11px] font-black text-emerald-400 bg-emerald-500/10 px-2.5 py-0.5 rounded-full">0% Comissão</span>
                    </div>

                    <div class="relative rounded-2xl overflow-hidden border border-slate-800">
                        <img src="/assets/img/hero-mockup.jpg" alt="Ecossistema PedeLivre" class="w-full h-64 sm:h-80 object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/30 to-transparent flex flex-col justify-end p-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <a href="/p/brasa-artesanal" class="bg-white/95 hover:bg-white text-slate-950 p-3 rounded-2xl text-xs font-black text-center shadow-lg transition">
                                    🍔 Ver Cardápio Digital
                                </a>
                                <a href="/cadastro?plano=premium" class="bg-rose-600 hover:bg-rose-500 text-white p-3 rounded-2xl text-xs font-black text-center shadow-lg transition">
                                    🚀 Testar 14 Dias Grátis
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Seção Interativa: Navegue pelos 4 Módulos do Empreendedor (PC & Mobile) -->
    <section id="ecossistema" class="py-20 bg-slate-900/50 border-y border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <div class="text-center max-w-3xl mx-auto space-y-3">
                <span class="text-xs font-extrabold uppercase tracking-widest text-rose-500">Auto-Adaptação Inteligente PC &amp; Mobile</span>
                <h2 class="text-2xl sm:text-4xl font-black text-white">Qualquer empreendedor opera pelo celular ou computador</h2>
                <p class="text-sm sm:text-base text-slate-400">Telas desenhadas separadamente para uso com uma mão no smartphone ou em monitores widescreen no balcão.</p>
            </div>

            <!-- Seletor de Abas das Telas -->
            <div class="flex flex-wrap justify-center gap-2">
                <button @click="abaEcossistema = 'cardapio'"
                    class="px-5 py-3 rounded-2xl text-xs sm:text-sm font-black transition"
                    :class="abaEcossistema === 'cardapio' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'bg-slate-900 text-slate-400 border border-slate-800 hover:text-white'">
                    🍔 1. Cardápio Digital &amp; PIX
                </button>
                <button @click="abaEcossistema = 'lojista'"
                    class="px-5 py-3 rounded-2xl text-xs sm:text-sm font-black transition"
                    :class="abaEcossistema === 'lojista' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'bg-slate-900 text-slate-400 border border-slate-800 hover:text-white'">
                    📋 2. Kanban de Pedidos &amp; PDV
                </button>
                <button @click="abaEcossistema = 'salao'"
                    class="px-5 py-3 rounded-2xl text-xs sm:text-sm font-black transition"
                    :class="abaEcossistema === 'salao' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'bg-slate-900 text-slate-400 border border-slate-800 hover:text-white'">
                    🪑 3. QR Code de Mesa &amp; Salão
                </button>
                <button @click="abaEcossistema = 'kds'"
                    class="px-5 py-3 rounded-2xl text-xs sm:text-sm font-black transition"
                    :class="abaEcossistema === 'kds' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/25' : 'bg-slate-900 text-slate-400 border border-slate-800 hover:text-white'">
                    🍳 4. KDS Cozinha &amp; Entregadores
                </button>
            </div>

            <!-- Conteúdo da Aba Selecionada -->
            <div class="bg-slate-950 border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-2xl">
                <div x-show="abaEcossistema === 'cardapio'" class="grid lg:grid-cols-12 gap-8 items-center">
                    <div class="lg:col-span-7 space-y-4">
                        <span class="text-xs font-black uppercase text-emerald-400">Conversão Máxima no Celular e no PC</span>
                        <h3 class="text-2xl sm:text-3xl font-black text-white">Cardápio Digital com Sacola Inteligente, Cupom e PIX Banco Central</h3>
                        <p class="text-sm text-slate-300 leading-relaxed">
                            O cliente abre o link da sua loja no WhatsApp ou Instagram, escolhe hambúrgueres, pizzas meio a meio ou combos, aplica cupom de desconto (<code class="text-rose-400 font-bold">BEMVINDO10</code>), calcula o frete automaticamente pelo CEP e paga via QR Code PIX do Mercado Pago com aprovação em segundos.
                        </p>
                        <div class="pt-2">
                            <a href="/p/brasa-artesanal" class="inline-flex bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black px-6 py-3.5 rounded-xl text-xs sm:text-sm transition">
                                Ver Cardápio Exemplo Ao Vivo →
                            </a>
                        </div>
                    </div>
                    <div class="lg:col-span-5 bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-3 text-xs">
                        <div class="font-bold text-white text-sm">Destaques do Cardápio:</div>
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">✓ Card Flutuante da Loja sem sobreposição de texto</div>
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">✓ Sacola Lateral Fixa no PC + Barra Inferior no Celular</div>
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">✓ Busca de CEP automática (ViaCEP) + Cálculo de Frete</div>
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">✓ Rastreamento do pedido em tempo real pelo cliente</div>
                    </div>
                </div>

                <div x-cloak x-show="abaEcossistema === 'lojista'" class="grid lg:grid-cols-12 gap-8 items-center">
                    <div class="lg:col-span-7 space-y-4">
                        <span class="text-xs font-black uppercase text-rose-400">7 Módulos Integrados para o Empreendedor</span>
                        <h3 class="text-2xl sm:text-3xl font-black text-white">Painel do Lojista: Kanban 5 Colunas no PC + Barra de Abas no Celular</h3>
                        <p class="text-sm text-slate-300 leading-relaxed">
                            Gerencie pedidos ao vivo com alerta sonoro, lance pedidos rápidos no PDV Balcão, edite produtos e categorias do cardápio, pause itens esgotados em 1 toque, cadastre motoboys e taxas de entrega por bairro e acompanhe seu faturamento.
                        </p>
                        <div class="pt-2">
                            <a href="/cadastro?plano=premium" class="inline-flex bg-rose-600 hover:bg-rose-500 text-white font-black px-6 py-3.5 rounded-xl text-xs sm:text-sm transition">
                                Criar Minha Loja e Acessar o Painel →
                            </a>
                        </div>
                    </div>
                    <div class="lg:col-span-5 bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-3 text-xs">
                        <div class="font-bold text-white text-sm">Módulos do Painel do Lojista:</div>
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">📋 Pedidos Ao Vivo + Lançamento Rápido PDV Balcão</div>
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">🍔 Cardápio, Categorias &amp; Pausa de Estoque em 1 toque</div>
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">🛵 Gestão de Entregadores (Motoboys) &amp; Taxas por Bairro</div>
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">🖨️ Impressão Térmica 80mm para Cozinha e Entrega</div>
                    </div>
                </div>

                <div x-cloak x-show="abaEcossistema === 'salao'" class="grid lg:grid-cols-12 gap-8 items-center">
                    <div class="lg:col-span-7 space-y-4">
                        <span class="text-xs font-black uppercase text-amber-400">Atendimento Presencial &amp; Salão</span>
                        <h3 class="text-2xl sm:text-3xl font-black text-white">Plaquinhas QR Code de Mesa Anti-Fraude &amp; Divisão de Conta</h3>
                        <p class="text-sm text-slate-300 leading-relaxed">
                            Cada mesa possui um QR Code único com token criptográfico de 32 caracteres. O cliente escaneia, pede direto da mesa ou chama o garçom em 1 toque, agilizando o giro de salão.
                        </p>
                        <div class="pt-2">
                            <a href="/cadastro?plano=presencial" class="inline-flex bg-amber-500 hover:bg-amber-400 text-slate-950 font-black px-6 py-3.5 rounded-xl text-xs sm:text-sm transition">
                                Testar QR Code de Mesa Grátis →
                            </a>
                        </div>
                    </div>
                    <div class="lg:col-span-5">
                        <img src="/assets/img/qr-mesa-pizza.jpg" alt="QR Code na Mesa" class="rounded-2xl border border-slate-800 w-full h-64 object-cover">
                    </div>
                </div>

                <div x-cloak x-show="abaEcossistema === 'kds'" class="grid lg:grid-cols-12 gap-8 items-center">
                    <div class="lg:col-span-7 space-y-4">
                        <span class="text-xs font-black uppercase text-amber-400">Produção na Cozinha &amp; Despacho</span>
                        <h3 class="text-2xl sm:text-3xl font-black text-white">KDS por Estação (Chapa, Forno, Bar) e Gestão de Motoboys</h3>
                        <p class="text-sm text-slate-300 leading-relaxed">
                            Elimine comandas de papel perdidas: assim que o pedido é aceito, os itens aparecem automaticamente separados para a Chapa, Forno, Fritadeira e Bar, com alerta de tempo de preparo e despacho em 1 clique para o motoboy.
                        </p>
                        <div class="pt-2">
                            <a href="/cadastro?plano=premium" class="inline-flex bg-amber-500 hover:bg-amber-400 text-slate-950 font-black px-6 py-3.5 rounded-xl text-xs sm:text-sm transition">
                                Começar Teste Grátis de 14 Dias →
                            </a>
                        </div>
                    </div>
                    <div class="lg:col-span-5 bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-3 text-xs">
                        <div class="font-bold text-white text-sm">Produtividade Operacional:</div>
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">🍳 Separação Automática por Estação (Chapa, Forno, Bar)</div>
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">⏱️ Cronômetro de SLA e Alerta de Atraso na Cozinha</div>
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">🛵 Vinculação de Entregadores e Taxas por Corrida</div>
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800">💵 Controle de Frente de Caixa e Relatórios de Horário de Pico</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Calculadora Interativa de Economia -->
    <section id="economia" class="py-20 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <span class="text-xs font-extrabold uppercase tracking-widest text-rose-500">Simulador Financeiro</span>
            <h2 class="text-2xl sm:text-4xl font-black text-white mt-2">Quanto dinheiro volta para o seu bolso?</h2>
            <p class="text-sm sm:text-base text-slate-400 mt-2">Compare as taxas de até 27% dos marketplaces com a mensalidade fixa e 0% de comissão do PedeLivre.</p>
        </div>

        <div class="bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-800 grid md:grid-cols-12 gap-8 items-center shadow-2xl">
            <div class="md:col-span-7 space-y-6">
                <div>
                    <div class="flex justify-between text-sm font-bold mb-2">
                        <span class="text-slate-300">Pedidos por mês</span>
                        <span class="text-rose-400 font-black" x-text="pedidosMes + ' pedidos/mês'"></span>
                    </div>
                    <input type="range" min="100" max="3000" step="50" x-model="pedidosMes" class="w-full accent-rose-600 bg-slate-800 h-2 rounded-lg cursor-pointer">
                </div>

                <div>
                    <div class="flex justify-between text-sm font-bold mb-2">
                        <span class="text-slate-300">Ticket Médio por Pedido (R$)</span>
                        <span class="text-rose-400 font-black" x-text="'R$ ' + ticketMedio"></span>
                    </div>
                    <input type="range" min="25" max="200" step="5" x-model="ticketMedio" class="w-full accent-rose-600 bg-slate-800 h-2 rounded-lg cursor-pointer">
                </div>

                <div>
                    <div class="flex justify-between text-sm font-bold mb-2">
                        <span class="text-slate-300">Comissão cobrada pelo App (%)</span>
                        <span class="text-rose-400 font-black" x-text="taxaMarketplace + '%'"></span>
                    </div>
                    <input type="range" min="12" max="27" step="1" x-model="taxaMarketplace" class="w-full accent-rose-600 bg-slate-800 h-2 rounded-lg cursor-pointer">
                </div>
            </div>

            <div class="md:col-span-5 bg-slate-950 p-6 rounded-2xl border border-emerald-500/30 text-center space-y-4">
                <span class="text-xs uppercase tracking-widest font-bold text-slate-400">Sua Economia Anual Estimada</span>
                <div class="text-3xl sm:text-5xl font-black text-emerald-400" x-text="(((pedidosMes * ticketMedio * (taxaMarketplace / 100)) - 249.90) * 12).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL', maximumFractionDigits: 0 })"></div>
                <p class="text-xs text-slate-400">Dinheiro líquido economizado por ano usando o Plano Premium com 0% de comissão.</p>
                <a href="/cadastro?plano=premium" class="block w-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black py-3.5 rounded-xl text-sm transition">
                    Começar Agora com 0% de Taxa →
                </a>
            </div>
        </div>
    </section>

    <!-- Comparativo Direto de Mercado (Etapa 5) -->
    <section id="recursos" class="py-16 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12 space-y-2">
            <span class="text-xs font-extrabold uppercase tracking-widest text-emerald-400">Vantagem Competitiva Real</span>
            <h2 class="text-2xl sm:text-4xl font-black text-white">Por que o PedeLivre está anos à frente?</h2>
            <p class="text-sm sm:text-base text-slate-400">Compare lado a lado o que você recebe no PedeLivre contra marketplaces e sistemas antigos.</p>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-slate-950 border-b border-slate-800 text-slate-400">
                            <th class="p-4 sm:p-5 font-bold">Recurso / Tecnologia</th>
                            <th class="p-4 sm:p-5 font-black text-white bg-rose-600/15 border-x border-rose-500/30 text-center">🚀 PedeLivre OS</th>
                            <th class="p-4 sm:p-5 font-bold text-center">Apps de Marketplace</th>
                            <th class="p-4 sm:p-5 font-bold text-center">Cardápios Antigos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80 text-slate-300">
                        <tr>
                            <td class="p-4 sm:p-5 font-bold text-white">Comissão sobre cada venda</td>
                            <td class="p-4 sm:p-5 text-center font-black text-emerald-400 bg-rose-600/5 border-x border-rose-500/20">0% (Lucro 100% seu)</td>
                            <td class="p-4 sm:p-5 text-center text-rose-400 font-bold">12% a 27% por pedido</td>
                            <td class="p-4 sm:p-5 text-center text-slate-400">0% a 3%</td>
                        </tr>
                        <tr>
                            <td class="p-4 sm:p-5 font-bold text-white">Recebimento do PIX</td>
                            <td class="p-4 sm:p-5 text-center font-black text-emerald-400 bg-rose-600/5 border-x border-rose-500/20">⚡ Na hora (Direto na sua conta)</td>
                            <td class="p-4 sm:p-5 text-center text-slate-400">Repasse em 7 a 30 dias</td>
                            <td class="p-4 sm:p-5 text-center text-slate-400">Comprovante manual no WhatsApp</td>
                        </tr>
                        <tr>
                            <td class="p-4 sm:p-5 font-bold text-white">Order Bump &amp; Barra de Frete Grátis</td>
                            <td class="p-4 sm:p-5 text-center font-black text-emerald-400 bg-rose-600/5 border-x border-rose-500/20">✓ Nativo (+22% Ticket Médio)</td>
                            <td class="p-4 sm:p-5 text-center text-slate-400">✓ Exibe concorrentes junto</td>
                            <td class="p-4 sm:p-5 text-center text-rose-400">✕ Não possui</td>
                        </tr>
                        <tr>
                            <td class="p-4 sm:p-5 font-bold text-white">QR Code de Mesa Anti-Fraude + Chamar Garçom</td>
                            <td class="p-4 sm:p-5 text-center font-black text-emerald-400 bg-rose-600/5 border-x border-rose-500/20">✓ Incluso com Alerta Sonoro</td>
                            <td class="p-4 sm:p-5 text-center text-rose-400">✕ Focado só em entrega</td>
                            <td class="p-4 sm:p-5 text-center text-slate-400">Cobrado à parte</td>
                        </tr>
                        <tr>
                            <td class="p-4 sm:p-5 font-bold text-white">CRM Cashback + Recuperação 1-Clique WhatsApp</td>
                            <td class="p-4 sm:p-5 text-center font-black text-emerald-400 bg-rose-600/5 border-x border-rose-500/20">✓ Base de clientes 100% sua</td>
                            <td class="p-4 sm:p-5 text-center text-rose-400">✕ Oculta telefone do cliente</td>
                            <td class="p-4 sm:p-5 text-center text-rose-400">✕ Sem automação Win-Back</td>
                        </tr>
                        <tr>
                            <td class="p-4 sm:p-5 font-bold text-white">KDS Cozinha Modo TV + Auto-Impressão 80mm</td>
                            <td class="p-4 sm:p-5 text-center font-black text-emerald-400 bg-rose-600/5 border-x border-rose-500/20">✓ Por estação (Chapa, Forno, Bar)</td>
                            <td class="p-4 sm:p-5 text-center text-rose-400">✕ Não separa por estação</td>
                            <td class="p-4 sm:p-5 text-center text-slate-400">Apenas impressão simples</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Tabela de Planos Transparente -->
    <section id="planos" class="py-20 bg-slate-900/50 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <h2 class="text-2xl sm:text-4xl font-black text-white">Planos Simples. Zero Taxas Escondidas.</h2>
                <p class="text-sm sm:text-base text-slate-400 mt-2">Teste grátis por 14 dias. Sem fidelidade e com cancelamento em 1 clique direto no painel.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Plano Delivery -->
                <div class="bg-slate-950 rounded-3xl p-8 border border-slate-800 flex flex-col justify-between">
                    <div class="space-y-4">
                        <h3 class="text-xl font-bold text-white">Plano Delivery</h3>
                        <p class="text-xs text-slate-400">Ideal para dark kitchens, hamburguerias e operações focadas em entrega e retirada.</p>
                        <div class="pt-2">
                            <span class="text-4xl font-black text-white">R$ 99,90</span>
                            <span class="text-slate-400 text-sm">/mês</span>
                        </div>
                        <ul class="space-y-3 pt-6 text-sm text-slate-300 border-t border-slate-800">
                            <li>✓ Cardápio Digital com link próprio</li>
                            <li>✓ Canais Delivery &amp; Retirada</li>
                            <li>✓ Frete por Bairro ou Raio KM</li>
                            <li>✓ Gestão de entregadores (Motoboys)</li>
                            <li>✓ PIX Automático Mercado Pago (0% taxa)</li>
                            <li>✓ Cupons de desconto e KDS Básico</li>
                            <li>✓ Até 2 contas de usuários</li>
                        </ul>
                    </div>
                    <a href="/cadastro?plano=delivery" class="mt-8 block w-full bg-slate-800 hover:bg-slate-700 text-white font-bold py-3.5 rounded-xl text-center text-sm transition">
                        Começar 14 Dias Grátis
                    </a>
                </div>

                <!-- Plano Presencial -->
                <div class="bg-slate-950 rounded-3xl p-8 border border-slate-800 flex flex-col justify-between">
                    <div class="space-y-4">
                        <h3 class="text-xl font-bold text-white">Plano Presencial</h3>
                        <p class="text-xs text-slate-400">Para restaurantes, bares e pizzarias com atendimento em mesas e balcão.</p>
                        <div class="pt-2">
                            <span class="text-4xl font-black text-white">R$ 149,90</span>
                            <span class="text-slate-400 text-sm">/mês</span>
                        </div>
                        <ul class="space-y-3 pt-6 text-sm text-slate-300 border-t border-slate-800">
                            <li>✓ Cardápio Digital &amp; QR Code de Mesa</li>
                            <li>✓ Comandas abertas &amp; Divisão de conta</li>
                            <li>✓ Botão Chamar Garçom em tempo real</li>
                            <li>✓ KDS Completo por estação (Chapa, Forno, Bar)</li>
                            <li>✓ Frente de Caixa e PDV Balcão</li>
                            <li>✓ PIX Automático Mercado Pago</li>
                            <li>✓ Até 5 contas de usuários</li>
                        </ul>
                    </div>
                    <a href="/cadastro?plano=presencial" class="mt-8 block w-full bg-slate-800 hover:bg-slate-700 text-white font-bold py-3.5 rounded-xl text-center text-sm transition">
                        Começar 14 Dias Grátis
                    </a>
                </div>

                <!-- Plano Premium -->
                <div class="bg-gradient-to-b from-rose-950/40 to-slate-950 rounded-3xl p-8 border-2 border-rose-500 relative flex flex-col justify-between shadow-2xl shadow-rose-950/40">
                    <span class="absolute -top-3.5 right-6 bg-rose-600 text-white text-[11px] font-black uppercase tracking-wider px-3 py-1 rounded-full">Mais Escolhido</span>
                    <div class="space-y-4">
                        <h3 class="text-xl font-bold text-white">Plano Premium Total</h3>
                        <p class="text-xs text-slate-300">Operação completa Delivery + Salão + CRM Cashback + Bot WhatsApp com IA.</p>
                        <div class="pt-2">
                            <span class="text-4xl font-black text-white">R$ 249,90</span>
                            <span class="text-slate-400 text-sm">/mês</span>
                        </div>
                        <ul class="space-y-3 pt-6 text-sm text-slate-200 border-t border-rose-500/30">
                            <li>✓ <strong>Tudo do Delivery + Presencial</strong></li>
                            <li>✓ <strong>Robô WhatsApp Oficial Meta Cloud API</strong></li>
                            <li>✓ <strong>Motor de CRM, Cashback &amp; Fidelidade</strong></li>
                            <li>✓ Estoque unificado com pausa em 1 toque</li>
                            <li>✓ KDS Completo por estação + Impressão 80mm</li>
                            <li>✓ <strong>Usuários e Garçons Ilimitados</strong></li>
                        </ul>
                    </div>
                    <a href="/cadastro?plano=premium" class="mt-8 block w-full bg-rose-600 hover:bg-rose-500 text-white font-black py-3.5 rounded-xl text-center text-sm shadow-lg shadow-rose-600/30 transition">
                        Testar Premium 14 Dias Grátis →
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Accordion -->
    <section id="faq" class="py-20 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div class="text-center space-y-2">
            <h2 class="text-2xl sm:text-3xl font-black text-white">Perguntas Frequentes</h2>
            <p class="text-xs sm:text-sm text-slate-400">Tudo o que você precisa saber sobre o funcionamento do PedeLivre</p>
        </div>

        <div class="space-y-3">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                <button @click="faqAberta = faqAberta === 1 ? null : 1" class="w-full p-4 text-left font-bold text-sm text-white flex justify-between items-center">
                    <span>1. O dinheiro do PIX cai direto na minha conta ou passa pelo PedeLivre?</span>
                    <span x-text="faqAberta === 1 ? '−' : '+'" class="text-rose-500 font-black text-lg"></span>
                </button>
                <div x-show="faqAberta === 1" class="px-4 pb-4 text-xs sm:text-sm text-slate-300 leading-relaxed">
                    Cai 100% direto na sua conta Mercado Pago em segundos! Nossa arquitetura é não-custodial: nós geramos o QR Code oficial do Banco Central em nome da sua loja e confirmamos o pagamento automaticamente via Webhook criptografado.
                </div>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                <button @click="faqAberta = faqAberta === 2 ? null : 2" class="w-full p-4 text-left font-bold text-sm text-white flex justify-between items-center">
                    <span>2. Consigo gerenciar meu restaurante apenas pelo celular?</span>
                    <span x-text="faqAberta === 2 ? '−' : '+'" class="text-rose-500 font-black text-lg"></span>
                </button>
                <div x-show="faqAberta === 2" class="px-4 pb-4 text-xs sm:text-sm text-slate-300 leading-relaxed">
                    Sim! O PedeLivre possui layouts independentes e auto-adaptativos: no celular você conta com uma barra inferior estilo aplicativo e botões grandes para o polegar; no computador você tem o quadro Kanban de 5 colunas lado a lado e barra lateral completa.
                </div>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                <button @click="faqAberta = faqAberta === 3 ? null : 3" class="w-full p-4 text-left font-bold text-sm text-white flex justify-between items-center">
                    <span>3. Preciso instalar algum programa para usar?</span>
                    <span x-text="faqAberta === 3 ? '−' : '+'" class="text-rose-500 font-black text-lg"></span>
                </button>
                <div x-show="faqAberta === 3" class="px-4 pb-4 text-xs sm:text-sm text-slate-300 leading-relaxed">
                    Não! Tanto o cardápio do cliente quanto o Painel do Lojista e o KDS da cozinha rodam direto no navegador com atualização em tempo real e suporte a impressoras térmicas 80mm.
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-slate-950 border-t border-slate-900 py-12 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row justify-between items-center gap-4">
            <p>© 2026 PedeLivre Tecnologia Ltda. Criptografia AES-256-GCM e Conformidade LGPD.</p>
            <div class="flex flex-wrap justify-center gap-5">
                <a href="/p/brasa-artesanal" class="hover:text-slate-300">Cardápio Exemplo</a>
                <a href="/login" class="hover:text-slate-300">Área do Lojista</a>
                <a href="/cadastro?plano=premium" class="hover:text-slate-300">Criar Conta Grátis</a>
                <a href="/status" class="hover:text-slate-300">Status do Sistema</a>
            </div>
        </div>
    </footer>
</body>
</html>
