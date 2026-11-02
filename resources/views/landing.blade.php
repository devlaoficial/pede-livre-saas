<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b0f19">
    <link rel="manifest" href="/manifest.json">
    <title>PedeLivre — Venda pelo seu canal. Fique com 100% do lucro.</title>
    <meta name="description" content="Cardápio digital com PIX na hora, PDV de balcão, KDS para a cozinha e CRM com WhatsApp. Sem comissão e sem taxas intermediárias.">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#0b0f19] text-slate-100 antialiased font-sans selection:bg-[#f43f5e] selection:text-white"
      x-data="{
          faturamentoMensal: 30000,
          taxaApp: 23,
          faqAberta: 1,
          menuMobile: false,

          get perdaMensalHoje() {
              return Math.round((this.faturamentoMensal * this.taxaApp) / 100);
          },

          get custoPedelivreMensal() {
              return 149.90;
          },

          get economiaAnualEstimada() {
              const economiaMensal = this.perdaMensalHoje - this.custoPedelivreMensal;
              return Math.max(0, Math.round(economiaMensal * 12));
          },

          formatarMoeda(val) {
              return Number(val).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
          }
      }">

    <!-- ==================== NAVBAR (DESIGN CLAUDE FIEL) ==================== -->
    <header class="sticky top-0 z-50 bg-[#0b0f19]/90 backdrop-blur-md border-b border-[#171e30]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 py-3.5 flex items-center justify-between">
            <!-- Logo -->
            <a href="/" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-[#e11d48] flex items-center justify-center text-white font-black text-lg shadow-md shadow-rose-600/30 group-hover:scale-105 transition">
                    P
                </div>
                <span class="text-xl font-black text-white tracking-tight">Pede<span class="text-[#f43f5e]">Livre</span></span>
            </a>

            <!-- Links de Navegação -->
            <nav class="hidden md:flex items-center gap-7 text-xs font-bold text-slate-300">
                <a href="#recursos" class="hover:text-white transition">Recursos</a>
                <a href="#calculadora" class="hover:text-white transition">Economia</a>
                <a href="#planos" class="hover:text-white transition">Planos</a>
                <a href="#faq" class="hover:text-white transition">Dúvidas</a>
            </nav>

            <!-- Ações Direita -->
            <div class="hidden sm:flex items-center gap-3">
                <a href="/login" class="text-xs font-bold text-slate-300 hover:text-white px-3 py-2 rounded-xl transition">
                    Entrar
                </a>
                <a href="/cadastro?plano=premium" class="bg-[#e11d48] hover:bg-[#be123c] text-white text-xs font-black px-4 py-2.5 rounded-xl shadow-lg shadow-rose-600/25 transition active:scale-95">
                    Criar loja grátis
                </a>
            </div>

            <!-- Botão Menu Mobile -->
            <button @click="menuMobile = !menuMobile" class="sm:hidden p-2 rounded-xl bg-[#141928] border border-[#1e2638] text-slate-300">
                <span x-text="menuMobile ? '✕' : '☰'" class="font-bold"></span>
            </button>
        </div>

        <!-- Drawer Mobile -->
        <div x-cloak x-show="menuMobile" class="sm:hidden bg-[#0d1220] border-b border-[#1b2336] p-4 space-y-3">
            <div class="flex flex-col gap-2 text-xs font-bold text-slate-300">
                <a href="#recursos" @click="menuMobile = false" class="p-2 hover:bg-[#151c2e] rounded-lg">Recursos</a>
                <a href="#calculadora" @click="menuMobile = false" class="p-2 hover:bg-[#151c2e] rounded-lg">Economia</a>
                <a href="#planos" @click="menuMobile = false" class="p-2 hover:bg-[#151c2e] rounded-lg">Planos</a>
                <a href="#faq" @click="menuMobile = false" class="p-2 hover:bg-[#151c2e] rounded-lg">Dúvidas</a>
            </div>
            <div class="pt-2 border-t border-[#1b2336] flex gap-2">
                <a href="/login" class="flex-1 text-center py-2.5 bg-[#151c2e] rounded-xl text-xs font-bold text-white">Entrar</a>
                <a href="/cadastro?plano=premium" class="flex-1 text-center py-2.5 bg-[#e11d48] rounded-xl text-xs font-black text-white">Criar loja</a>
            </div>
        </div>
    </header>

    <!-- ==================== HERO SECTION (Fiel Imagem 4) ==================== -->
    <section class="relative pt-12 pb-16 lg:pt-20 lg:pb-24 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-12 items-center">
            
            <!-- Texto do Hero -->
            <div class="lg:col-span-7 space-y-6">
                <!-- Pill Notificação -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#151c2e] border border-[#212b44] text-slate-300 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-[#f43f5e]"></span>
                    <span>0% comissão · Sem taxas por venda</span>
                </div>

                <!-- Título Hero -->
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.08]">
                    Venda pelo seu canal. <br>
                    <span class="text-[#f43f5e]">Fique com 100% do lucro.</span>
                </h1>

                <!-- Subtítulo -->
                <p class="text-sm sm:text-base text-slate-400 max-w-xl leading-relaxed">
                    Cardápio digital com PIX na hora, PDV de balcão, KDS para a cozinha e CRM com WhatsApp. Tudo integrado, sem mensalidades escondidas e sem intermediários.
                </p>

                <!-- Botões de Ação -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                    <a href="/cadastro?plano=premium" 
                       class="bg-[#e11d48] hover:bg-[#be123c] text-white font-black px-6 py-3.5 rounded-2xl text-xs sm:text-sm text-center shadow-xl shadow-rose-600/30 transition active:scale-95">
                        Criar minha loja grátis
                    </a>
                    <a href="/p/brasa-artesanal" 
                       class="bg-[#121624] hover:bg-[#181d30] text-slate-200 border border-[#1e273c] font-bold px-6 py-3.5 rounded-2xl text-xs sm:text-sm text-center transition">
                        Ver cardápio de exemplo
                    </a>
                </div>

                <!-- Micro Copy -->
                <div class="flex items-center gap-2 text-xs text-slate-500 pt-2">
                    <span>14 dias grátis</span>
                    <span>·</span>
                    <span>Sem cartão de crédito</span>
                    <span>·</span>
                    <span>Cancele quando quiser</span>
                </div>
            </div>

            <!-- Card Mockup de Demonstração (Fiel Imagem 4) -->
            <div class="lg:col-span-5">
                <div class="bg-[#121624] border border-[#1e2638] rounded-3xl p-5 sm:p-6 shadow-2xl space-y-4 relative">
                    <!-- Topo Card Mockup -->
                    <div class="flex items-center justify-between border-b border-[#1e2638] pb-3">
                        <span class="text-xs font-bold text-white">Pedido confirmado</span>
                        <span class="bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 text-[10px] font-black px-2.5 py-0.5 rounded-md">
                            PIX direto
                        </span>
                    </div>

                    <!-- Lista de Itens -->
                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between items-center text-slate-300">
                            <span>1x Smash Burger Especial</span>
                            <span class="font-bold text-white">R$ 38,90</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-300">
                            <span>1x Batata Rústica Trufada</span>
                            <span class="font-bold text-white">R$ 18,90</span>
                        </div>
                        <div class="flex justify-between items-center text-slate-300">
                            <span>1x Coca-Cola Zero</span>
                            <span class="font-bold text-white">R$ 6,00</span>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="pt-3 border-t border-[#1e2638] flex justify-between items-baseline">
                        <span class="font-bold text-xs text-slate-400">Total:</span>
                        <span class="text-xl font-black text-white">R$ 63,80</span>
                    </div>

                    <!-- Notificação PIX Recebido -->
                    <div class="bg-[#102920] border border-emerald-500/30 rounded-2xl p-3 flex items-center gap-2.5 text-xs text-emerald-400 font-bold">
                        <span class="w-5 h-5 rounded-full bg-emerald-500 text-slate-950 flex items-center justify-center font-black text-[11px] shrink-0">✓</span>
                        <span>PIX recebido na sua conta em 3s</span>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ==================== BARRA DE CONFIANÇA / 4 STATS (Fiel Imagem 4) ==================== -->
    <section class="border-y border-[#171e30] bg-[#090d16]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div>
                <div class="text-2xl sm:text-3xl font-black text-white">0%</div>
                <div class="text-xs text-slate-400 mt-1 font-medium">Taxa sobre pedidos</div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-black text-white">PIX na hora</div>
                <div class="text-xs text-slate-400 mt-1 font-medium">Sem esperar 30 dias</div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-black text-white">Celular e PC</div>
                <div class="text-xs text-slate-400 mt-1 font-medium">Sem maquininha cara</div>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-black text-white">14 dias</div>
                <div class="text-xs text-slate-400 mt-1 font-medium">Grátis para testar</div>
            </div>
        </div>
    </section>

    <!-- ==================== CALCULADORA INTERATIVA DE ECONOMIA (Fiel Imagem 4) ==================== -->
    <section id="calculadora" class="py-16 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-12 gap-10 items-center">
                
                <!-- Esquerda: Sliders e Descrição -->
                <div class="lg:col-span-6 space-y-5">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#f43f5e]">CALCULADORA</span>
                    <h2 class="text-2xl sm:text-4xl font-black text-white leading-tight">
                        Quanto dinheiro volta para o seu bolso?
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                        Veja quanto você economiza ao substituir as taxas abusivas dos marketplaces (12% a 27%) pelo PedeLivre com taxa ZERO.
                    </p>

                    <!-- Slider 1: Faturamento Mensal -->
                    <div class="space-y-2 pt-2">
                        <div class="flex justify-between text-xs">
                            <span class="font-bold text-slate-300">Faturamento mensal no delivery:</span>
                            <span class="font-black text-white text-sm" x-text="formatarMoeda(faturamentoMensal)"></span>
                        </div>
                        <input type="range" min="5000" max="150000" step="5000" x-model.number="faturamentoMensal"
                            class="w-full accent-[#f43f5e] cursor-pointer">
                    </div>

                    <!-- Slider 2: Taxa dos Apps -->
                    <div class="space-y-2 pt-1">
                        <div class="flex justify-between text-xs">
                            <span class="font-bold text-slate-300">Taxa média cobrada pelos apps:</span>
                            <span class="font-black text-[#f43f5e] text-sm" x-text="taxaApp + '%'"></span>
                        </div>
                        <input type="range" min="10" max="30" step="1" x-model.number="taxaApp"
                            class="w-full accent-[#f43f5e] cursor-pointer">
                    </div>

                    <div class="pt-3">
                        <a href="/cadastro?plano=premium" class="inline-block bg-[#e11d48] hover:bg-[#be123c] text-white font-black px-6 py-3.5 rounded-2xl text-xs sm:text-sm shadow-xl shadow-rose-600/30 transition">
                            Começar a economizar agora
                        </a>
                    </div>
                </div>

                <!-- Direita: Card Resumo da Economia (Fiel Imagem 4) -->
                <div class="lg:col-span-6">
                    <div class="bg-[#121624] border border-[#1e2638] rounded-3xl p-6 sm:p-8 space-y-4 shadow-2xl">
                        <span class="text-xs font-bold text-slate-400 block">Com o PedeLivre você fica com 100%</span>
                        
                        <div class="space-y-2.5 text-xs sm:text-sm">
                            <div class="flex justify-between text-slate-300">
                                <span>Faturamento bruto:</span>
                                <span class="font-bold text-white" x-text="formatarMoeda(faturamentoMensal)"></span>
                            </div>
                            <div class="flex justify-between text-[#f43f5e]">
                                <span>O que você perde hoje em taxas:</span>
                                <span class="font-extrabold" x-text="'- ' + formatarMoeda(perdaMensalHoje) + ' / mês'"></span>
                            </div>
                            <div class="flex justify-between text-slate-400">
                                <span>Custo do PedeLivre:</span>
                                <span class="font-bold text-white">R$ 149,90 / mês</span>
                            </div>
                        </div>

                        <div class="border-t border-[#1e2638] pt-4 space-y-1">
                            <span class="text-xs font-bold text-slate-400 block">Economia estimada em 1 ano:</span>
                            <div class="text-3xl sm:text-4xl font-black text-[#10b981]" x-text="formatarMoeda(economiaAnualEstimada)"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ==================== GRID DE RECURSOS / TUDO INCLUSO (Fiel Imagem 4) ==================== -->
    <section id="recursos" class="py-16 bg-[#090d16] border-t border-[#171e30]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            <div class="text-center max-w-2xl mx-auto space-y-2">
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#f43f5e]">TUDO INCLUSO</span>
                <h2 class="text-2xl sm:text-4xl font-black text-white">
                    Tudo para vender e operar, sem depender de ninguém
                </h2>
                <p class="text-xs sm:text-sm text-slate-400">
                    Do cardápio ao motoboy, tudo que seu delivery ou restaurante precisa em uma única plataforma.
                </p>
            </div>

            <!-- Grid 3x2 (Fiel Imagem 4) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                
                <!-- Recurso 1 -->
                <div class="bg-[#121624] border border-[#1e2638] rounded-2xl p-5 space-y-3 shadow-md hover:border-slate-500 transition">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-base">
                        🍔
                    </div>
                    <h3 class="font-black text-sm text-white">Cardápio digital com PIX</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Cardápio próprio com link curto, fotos, adicionais, QR code de mesa e PIX com confirmação em segundos.
                    </p>
                </div>

                <!-- Recurso 2 -->
                <div class="bg-[#121624] border border-[#1e2638] rounded-2xl p-5 space-y-3 shadow-md hover:border-slate-500 transition">
                    <div class="w-9 h-9 rounded-xl bg-rose-500/20 text-[#f43f5e] flex items-center justify-center font-bold text-base">
                        📋
                    </div>
                    <h3 class="font-black text-sm text-white">Pedidos no painel</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Kanban em tempo real com alertas sonoros, filtros por canal e controle total de novos a entregues.
                    </p>
                </div>

                <!-- Recurso 3 -->
                <div class="bg-[#121624] border border-[#1e2638] rounded-2xl p-5 space-y-3 shadow-md hover:border-slate-500 transition">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-base">
                        ⚡
                    </div>
                    <h3 class="font-black text-sm text-white">PDV de balcão</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Lance pedidos presenciais no balcão, comanda de mesa ou telefone com atalhos de teclado rápidos.
                    </p>
                </div>

                <!-- Recurso 4 -->
                <div class="bg-[#121624] border border-[#1e2638] rounded-2xl p-5 space-y-3 shadow-md hover:border-slate-500 transition">
                    <div class="w-9 h-9 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center font-bold text-base">
                        👨‍🍳
                    </div>
                    <h3 class="font-black text-sm text-white">KDS da cozinha</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Separação por estações (chapa, forno, fritadeira, bar), cronômetro SLA e controle de atrasos.
                    </p>
                </div>

                <!-- Recurso 5 -->
                <div class="bg-[#121624] border border-[#1e2638] rounded-2xl p-5 space-y-3 shadow-md hover:border-slate-500 transition">
                    <div class="w-9 h-9 rounded-xl bg-orange-500/20 text-orange-400 flex items-center justify-center font-bold text-base">
                        🛵
                    </div>
                    <h3 class="font-black text-sm text-white">Rota de entrega</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Gestão de motoboys próprios, taxa dinâmica por bairro e link de rastreio em tempo real para o cliente.
                    </p>
                </div>

                <!-- Recurso 6 -->
                <div class="bg-[#121624] border border-[#1e2638] rounded-2xl p-5 space-y-3 shadow-md hover:border-slate-500 transition">
                    <div class="w-9 h-9 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold text-base">
                        💬
                    </div>
                    <h3 class="font-black text-sm text-white">CRM e WhatsApp</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Base de clientes com histórico de compras, ticket médio e mensagens automáticas no WhatsApp.
                    </p>
                </div>

            </div>

        </div>
    </section>

    <!-- ==================== SUA LOJA NO AR EM TRÊS PASSOS (Fiel Imagem 4) ==================== -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            
            <div class="text-center max-w-xl mx-auto space-y-2">
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#f43f5e]">PASSO A PASSO</span>
                <h2 class="text-2xl sm:text-3xl font-black text-white">
                    Sua loja no ar em três passos
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-[#121624] border border-[#1e2638] rounded-2xl p-5 space-y-2.5">
                    <div class="w-7 h-7 rounded-lg bg-rose-500/20 text-[#f43f5e] font-black text-xs flex items-center justify-center">1</div>
                    <h3 class="font-black text-sm text-white">Crie sua loja</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Cadastre seus produtos, fotos e dados do PIX em menos de 10 minutos.</p>
                </div>

                <div class="bg-[#121624] border border-[#1e2638] rounded-2xl p-5 space-y-2.5">
                    <div class="w-7 h-7 rounded-lg bg-rose-500/20 text-[#f43f5e] font-black text-xs flex items-center justify-center">2</div>
                    <h3 class="font-black text-sm text-white">Compartilhe o link</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Cole no Instagram, WhatsApp, TikTok e espalhe QR Codes nas mesas.</p>
                </div>

                <div class="bg-[#121624] border border-[#1e2638] rounded-2xl p-5 space-y-2.5">
                    <div class="w-7 h-7 rounded-lg bg-rose-500/20 text-[#f43f5e] font-black text-xs flex items-center justify-center">3</div>
                    <h3 class="font-black text-sm text-white">Receba e gerencie</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">Os pedidos caem direto no painel e o dinheiro vai na hora para sua conta.</p>
                </div>
            </div>

        </div>
    </section>

    <!-- ==================== TABELA COMPARATIVA (Fiel Imagem 4) ==================== -->
    <section class="py-16 bg-[#090d16] border-t border-[#171e30]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="text-center space-y-2">
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#f43f5e]">COMPARATIVO</span>
                <h2 class="text-2xl sm:text-3xl font-black text-white">Por que o PedeLivre?</h2>
            </div>

            <div class="bg-[#121624] border border-[#1e2638] rounded-2xl overflow-hidden shadow-xl text-xs">
                <div class="grid grid-cols-3 bg-[#151c2d] p-3.5 font-bold text-slate-400 border-b border-[#1e2638]">
                    <span>Recurso</span>
                    <span class="text-[#f43f5e] font-black">PedeLivre</span>
                    <span>Outros Marketplaces</span>
                </div>

                <div class="divide-y divide-[#1e2638]">
                    <div class="grid grid-cols-3 p-3.5 items-center">
                        <span class="text-slate-300">Taxa sobre pedidos</span>
                        <span class="font-black text-emerald-400">0%</span>
                        <span class="text-slate-400">12% a 27%</span>
                    </div>
                    <div class="grid grid-cols-3 p-3.5 items-center">
                        <span class="text-slate-300">Recebimento do PIX</span>
                        <span class="font-black text-emerald-400">Na hora na sua conta</span>
                        <span class="text-slate-400">Até 30 dias</span>
                    </div>
                    <div class="grid grid-cols-3 p-3.5 items-center">
                        <span class="text-slate-300">PIX Dinâmico com baixa</span>
                        <span class="font-black text-emerald-400">Incluso</span>
                        <span class="text-slate-400">Não disponível</span>
                    </div>
                    <div class="grid grid-cols-3 p-3.5 items-center">
                        <span class="text-slate-300">Base de clientes</span>
                        <span class="font-black text-emerald-400">100% sua</span>
                        <span class="text-slate-400">Do marketplace</span>
                    </div>
                    <div class="grid grid-cols-3 p-3.5 items-center">
                        <span class="text-slate-300">Canal próprio</span>
                        <span class="font-black text-emerald-400">Seu link, sua marca</span>
                        <span class="text-slate-400">Concorre na mesma tela</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== PLANOS E PREÇOS (Fiel Imagem 4) ==================== -->
    <section id="planos" class="py-16 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <div class="text-center space-y-2">
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#f43f5e]">PLANOS</span>
                <h2 class="text-2xl sm:text-4xl font-black text-white">Preço simples. Sem taxas escondidas.</h2>
                <p class="text-xs sm:text-sm text-slate-400">Escolha o plano ideal para a sua operação. Cancele quando quiser.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
                
                <!-- Plano 1: Delivery -->
                <div class="bg-[#121624] border border-[#1e2638] rounded-3xl p-6 flex flex-col justify-between space-y-5 shadow-lg">
                    <div class="space-y-4">
                        <div>
                            <h3 class="font-black text-base text-white">Delivery</h3>
                            <p class="text-xs text-slate-400 mt-1">Ideal para quem trabalha focado em entregas e retirada.</p>
                        </div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black text-white">R$ 99,90</span>
                            <span class="text-xs text-slate-500">/ mês</span>
                        </div>
                        <div class="space-y-2 pt-2 text-xs text-slate-300">
                            <div class="flex items-center gap-2"><span>✓</span><span>Cardápio digital ilimitado</span></div>
                            <div class="flex items-center gap-2"><span>✓</span><span>PIX direto sem intermediários</span></div>
                            <div class="flex items-center gap-2"><span>✓</span><span>Painel Kanban em tempo real</span></div>
                            <div class="flex items-center gap-2"><span>✓</span><span>Botão de WhatsApp</span></div>
                        </div>
                    </div>
                    <a href="/cadastro?plano=delivery" class="w-full bg-[#182033] hover:bg-[#202a44] text-white font-bold py-3 rounded-xl text-xs text-center transition">
                        Testar 14 dias grátis
                    </a>
                </div>

                <!-- Plano 2: Presencial -->
                <div class="bg-[#121624] border border-[#1e2638] rounded-3xl p-6 flex flex-col justify-between space-y-5 shadow-lg">
                    <div class="space-y-4">
                        <div>
                            <h3 class="font-black text-base text-white">Presencial</h3>
                            <p class="text-xs text-slate-400 mt-1">Para bares e restaurantes com atendimento no salão e balcão.</p>
                        </div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black text-white">R$ 149,90</span>
                            <span class="text-xs text-slate-500">/ mês</span>
                        </div>
                        <div class="space-y-2 pt-2 text-xs text-slate-300">
                            <div class="flex items-center gap-2"><span>✓</span><span>Tudo do plano Delivery</span></div>
                            <div class="flex items-center gap-2"><span>✓</span><span>PDV balcão com atalhos F2</span></div>
                            <div class="flex items-center gap-2"><span>✓</span><span>QR Code e comandas por mesa</span></div>
                            <div class="flex items-center gap-2"><span>✓</span><span>KDS de cozinha por estação</span></div>
                            <div class="flex items-center gap-2"><span>✓</span><span>Fechamento de caixa</span></div>
                        </div>
                    </div>
                    <a href="/cadastro?plano=presencial" class="w-full bg-[#182033] hover:bg-[#202a44] text-white font-bold py-3 rounded-xl text-xs text-center transition">
                        Testar 14 dias grátis
                    </a>
                </div>

                <!-- Plano 3: Premium Total (Destaque Fiel Imagem 4) -->
                <div class="bg-[#121624] border-2 border-[#f43f5e] rounded-3xl p-6 flex flex-col justify-between space-y-5 shadow-2xl relative">
                    <span class="absolute -top-3 right-6 bg-[#f43f5e] text-white text-[10px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                        MAIS POPULAR
                    </span>
                    <div class="space-y-4">
                        <div>
                            <h3 class="font-black text-base text-white">Premium Total</h3>
                            <p class="text-xs text-slate-400 mt-1">Operação completa delivery, salão, motoboys e marketing.</p>
                        </div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black text-white">R$ 249,90</span>
                            <span class="text-xs text-slate-500">/ mês</span>
                        </div>
                        <div class="space-y-2 pt-2 text-xs text-slate-300">
                            <div class="flex items-center gap-2"><span class="text-[#f43f5e] font-bold">✓</span><span>Tudo do Presencial</span></div>
                            <div class="flex items-center gap-2"><span class="text-[#f43f5e] font-bold">✓</span><span>Gestão e acerto de motoboys</span></div>
                            <div class="flex items-center gap-2"><span class="text-[#f43f5e] font-bold">✓</span><span>CRM e fidelidade de clientes</span></div>
                            <div class="flex items-center gap-2"><span class="text-[#f43f5e] font-bold">✓</span><span>Automações WhatsApp</span></div>
                            <div class="flex items-center gap-2"><span class="text-[#f43f5e] font-bold">✓</span><span>Múltiplos operadores e caixas</span></div>
                            <div class="flex items-center gap-2"><span class="text-[#f43f5e] font-bold">✓</span><span>Suporte prioritário VIP</span></div>
                        </div>
                    </div>
                    <a href="/cadastro?plano=premium" class="w-full bg-[#e11d48] hover:bg-[#be123c] text-white font-black py-3 rounded-xl text-xs text-center transition shadow-lg shadow-rose-600/30">
                        Testar 14 dias grátis
                    </a>
                </div>

            </div>
        </div>
    </section>

    <!-- ==================== FAQ ACCORDION (Fiel Imagem 4) ==================== -->
    <section id="faq" class="py-16 bg-[#090d16] border-t border-[#171e30]">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="text-center space-y-2">
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-[#f43f5e]">DÚVIDAS</span>
                <h2 class="text-2xl sm:text-3xl font-black text-white">Perguntas frequentes</h2>
            </div>

            <div class="space-y-3">
                <!-- FAQ 1 -->
                <div class="bg-[#121624] border border-[#1e2638] rounded-2xl p-4 cursor-pointer" @click="faqAberta = (faqAberta === 1 ? null : 1)">
                    <div class="flex justify-between items-center text-xs sm:text-sm font-bold text-white">
                        <span>Cobram taxa sobre minhas vendas?</span>
                        <span x-text="faqAberta === 1 ? '▲' : '▼'" class="text-xs text-slate-500"></span>
                    </div>
                    <div x-show="faqAberta === 1" class="text-xs text-slate-400 pt-2 leading-relaxed">
                        Não! Taxa ZERO sobre suas vendas. Você só paga a mensalidade fixa do plano escolhido e fica com 100% do valor dos seus pedidos.
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="bg-[#121624] border border-[#1e2638] rounded-2xl p-4 cursor-pointer" @click="faqAberta = (faqAberta === 2 ? null : 2)">
                    <div class="flex justify-between items-center text-xs sm:text-sm font-bold text-white">
                        <span>O dinheiro do PIX cai onde?</span>
                        <span x-text="faqAberta === 2 ? '▲' : '▼'" class="text-xs text-slate-500"></span>
                    </div>
                    <div x-show="faqAberta === 2" class="text-xs text-slate-400 pt-2 leading-relaxed">
                        Direto na sua conta bancária via Mercado Pago em segundos. Não retemos seu dinheiro em nenhum momento.
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="bg-[#121624] border border-[#1e2638] rounded-2xl p-4 cursor-pointer" @click="faqAberta = (faqAberta === 3 ? null : 3)">
                    <div class="flex justify-between items-center text-xs sm:text-sm font-bold text-white">
                        <span>Preciso de computador ou posso usar no celular?</span>
                        <span x-text="faqAberta === 3 ? '▲' : '▼'" class="text-xs text-slate-500"></span>
                    </div>
                    <div x-show="faqAberta === 3" class="text-xs text-slate-400 pt-2 leading-relaxed">
                        Você pode gerenciar tudo 100% pelo celular, tablet ou computador. A interface é totalmente adaptada e responsiva.
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="bg-[#121624] border border-[#1e2638] rounded-2xl p-4 cursor-pointer" @click="faqAberta = (faqAberta === 4 ? null : 4)">
                    <div class="flex justify-between items-center text-xs sm:text-sm font-bold text-white">
                        <span>Posso cancelar quando quiser?</span>
                        <span x-text="faqAberta === 4 ? '▲' : '▼'" class="text-xs text-slate-500"></span>
                    </div>
                    <div x-show="faqAberta === 4" class="text-xs text-slate-400 pt-2 leading-relaxed">
                        Sim, não há fidelidade nem multas. Você pode testar por 14 dias grátis sem colocar cartão de crédito e cancelar a qualquer hora.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== BANNER FINAL CTA VERMELHO (Fiel Imagem 4) ==================== -->
    <section class="py-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-r from-[#e11d48] to-[#9f1239] rounded-3xl p-8 sm:p-12 text-center space-y-4 shadow-2xl">
                <h2 class="text-2xl sm:text-4xl font-black text-white leading-tight">
                    Pare de dar de 12% a 27% das suas vendas
                </h2>
                <p class="text-xs sm:text-sm text-rose-100 max-w-lg mx-auto">
                    Crie sua loja em 2 minutos e comece a lucrar mais hoje.
                </p>
                <div class="pt-2">
                    <a href="/cadastro?plano=premium" class="inline-block bg-white hover:bg-slate-100 text-[#be123c] font-black px-8 py-3.5 rounded-2xl text-xs sm:text-sm shadow-xl transition active:scale-95">
                        Criar minha loja grátis
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== FOOTER ==================== -->
    <footer class="border-t border-[#171e30] bg-[#090d16] py-8 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-[#e11d48] text-white font-black text-xs flex items-center justify-center">P</span>
                <span class="font-bold text-slate-300">PedeLivre</span>
                <span>© 2026 — Todos os direitos reservados</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="/login" class="hover:text-slate-300">Área do Lojista</a>
                <a href="/cadastro" class="hover:text-slate-300">Planos</a>
                <a href="#faq" class="hover:text-slate-300">Perguntas Frequentes</a>
            </div>
        </div>
    </footer>

</body>
</html>
