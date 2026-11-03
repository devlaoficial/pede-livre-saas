<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth bg-[#06080f] text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#06080f">
    <link rel="manifest" href="/manifest.json">
    <title>PedeLivre — Plataforma Completa de Delivery, PDV, KDS e Cardápio Digital (0% Comissão)</title>
    <meta name="description" content="Venda pelo seu próprio canal com 0% de comissão. Cardápio digital de alta conversão, PIX na hora, PDV de balcão, KDS de cozinha, QR Code de mesa e WhatsApp IA.">

    <!-- Google Fonts: Inter & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        html, body { font-family: 'Inter', system-ui, -apple-system, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; text-rendering: optimizeLegibility; font-synthesis: none; }
        .font-extrabold { font-weight: 700; }
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #06080f;
        }
        .font-mono-code {
            font-family: 'Inter', monospace;
        }
        .bg-grid-pattern {
            background-image: radial-gradient(rgba(255, 255, 255, 0.06) 1px, transparent 1px);
            background-size: 28px 28px;
        }
        input[type="range"] {
            -webkit-appearance: none;
            appearance: none;
            height: 8px;
            border-radius: 999px;
            background: #1a2236;
            outline: none;
        }
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #f43f5e;
            border: 3px solid #ffffff;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(244, 63, 94, 0.55);
        }
    </style>
</head>
<body class="bg-[#06080f] text-slate-100 antialiased selection:bg-rose-500 selection:text-white overflow-x-hidden"
      x-data="{
          abaPreviewHero: 'cardapio',
          faturamentoMensal: 35000,
          taxaApp: 23,
          planoSimulador: 'presencial',
          faqAberta: 1,
          menuMobile: false,

          get perdaMensalHoje() {
              return Math.round((this.faturamentoMensal * this.taxaApp) / 100);
          },

          get custoPedelivreMensal() {
              if (this.planoSimulador === 'delivery') return 99.90;
              if (this.planoSimulador === 'premium') return 249.90;
              return 149.90;
          },

          get economiaMensalLiquida() {
              return Math.max(0, this.perdaMensalHoje - this.custoPedelivreMensal);
          },

          get economiaAnualEstimada() {
              return Math.max(0, Math.round(this.economiaMensalLiquida * 12));
          },

          formatarMoeda(val) {
              return Number(val || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
          }
      }">

    <!-- EFEITOS DE LUZ AMBIENTE NO TOPO -->
    <div class="fixed inset-x-0 top-0 pointer-events-none z-0 overflow-hidden h-[650px]">
        <div class="absolute -top-40 left-1/4 w-[520px] h-[520px] bg-rose-600/15 rounded-full blur-[130px]"></div>
        <div class="absolute top-10 right-1/4 w-[440px] h-[440px] bg-amber-500/10 rounded-full blur-[130px]"></div>
    </div>

    <!-- ==================== 1. NAVBAR GLASSMORPHISM ==================== -->
    <header class="sticky top-0 z-50 bg-[#06080f]/85 backdrop-blur-xl border-b border-white/[0.07]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Marca -->
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-rose-500 via-rose-600 to-amber-500 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-rose-600/30 group-hover:scale-105 transition duration-300">
                    P
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xl font-bold text-white tracking-tight">Pede<span class="text-rose-500">Livre</span></span>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full bg-rose-500/15 text-rose-400 border border-rose-500/25">OS</span>
                    </div>
                    <span class="block text-[10px] text-slate-400 font-medium -mt-0.5">0% Comissão · Gestão Completa</span>
                </div>
            </a>

            <!-- Navegação Desktop -->
            <nav class="hidden lg:flex items-center gap-8 text-xs font-bold text-slate-300">
                <a href="#ecossistema" class="hover:text-white transition">Ecossistema</a>
                <a href="#simulador" class="hover:text-white transition">Calculadora de Lucro</a>
                <a href="#comparativo" class="hover:text-white transition">Comparativo</a>
                <a href="#planos" class="hover:text-white transition">Planos e Preços</a>
                <a href="#faq" class="hover:text-white transition">Dúvidas</a>
            </nav>

            <!-- Botões de Ação -->
            <div class="hidden sm:flex items-center gap-3">
                <a href="/login" class="text-xs font-bold text-slate-200 hover:text-white bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.08] px-4 py-2.5 rounded-xl transition">
                    Entrar no Painel
                </a>
                <a href="/cadastro?plano=premium" class="bg-gradient-to-r from-rose-600 to-rose-500 hover:from-rose-500 hover:to-rose-400 text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow-lg shadow-rose-600/30 transition active:scale-95 flex items-center gap-1.5">
                    <span>Criar Loja Grátis</span>
                    <span>→</span>
                </a>
            </div>

            <!-- Botão Menu Mobile -->
            <button @click="menuMobile = !menuMobile" class="sm:hidden p-2.5 rounded-xl bg-white/[0.05] border border-white/[0.08] text-slate-200">
                <span x-text="menuMobile ? '' : ''" class="font-bold text-sm"></span>
            </button>
        </div>

        <!-- Menu Mobile -->
        <div x-cloak x-show="menuMobile" class="sm:hidden bg-[#0b0f1c] border-b border-white/[0.08] px-4 py-5 space-y-4">
            <div class="flex flex-col gap-2 text-xs font-bold text-slate-300">
                <a href="#ecossistema" @click="menuMobile = false" class="p-2.5 hover:bg-white/[0.05] rounded-xl">Ecossistema Completo</a>
                <a href="#simulador" @click="menuMobile = false" class="p-2.5 hover:bg-white/[0.05] rounded-xl">Calculadora de Economia</a>
                <a href="#comparativo" @click="menuMobile = false" class="p-2.5 hover:bg-white/[0.05] rounded-xl">Comparativo de Mercado</a>
                <a href="#planos" @click="menuMobile = false" class="p-2.5 hover:bg-white/[0.05] rounded-xl">Planos e Preços</a>
                <a href="#faq" @click="menuMobile = false" class="p-2.5 hover:bg-white/[0.05] rounded-xl">Perguntas Frequentes</a>
            </div>
            <div class="pt-3 border-t border-white/[0.08] grid grid-cols-2 gap-2.5">
                <a href="/login" class="text-center py-3 bg-white/[0.06] border border-white/[0.08] rounded-xl text-xs font-bold text-white">Entrar</a>
                <a href="/cadastro?plano=premium" class="text-center py-3 bg-rose-600 rounded-xl text-xs font-bold text-white shadow-lg shadow-rose-600/30">Testar 14 Dias</a>
            </div>
        </div>
    </header>

    <!-- ==================== 2. HERO SECTION COM VITRINE INTERATIVA ==================== -->
    <section class="relative z-10 pt-12 pb-20 lg:pt-20 lg:pb-28 bg-grid-pattern border-b border-white/[0.06]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-12 lg:gap-10 items-center">
            
            <!-- Coluna Esquerda: Copywriting de Alta Conversão -->
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-rose-500/10 border border-rose-500/25 text-rose-300 text-xs font-bold shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                    <span class="w-2 h-2 rounded-full bg-rose-500 -ml-4"></span>
                    <span>0% de comissão sobre vendas · Dinheiro na hora via PIX</span>
                </div>

                <h1 class="text-3xl sm:text-5xl lg:text-[56px] font-bold text-white tracking-tight leading-[1.08]">
                    Transforme seu restaurante em uma <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-400 via-rose-500 to-amber-400">máquina de vendas diretas.</span>
                </h1>

                <p class="text-sm sm:text-base lg:text-lg text-slate-300/90 max-w-2xl leading-relaxed font-normal">
                    Pare de entregar até 27% do seu faturamento para aplicativos intermediários. Tenha seu próprio <strong class="text-white font-bold">Cardápio Digital com Order Bump</strong>, <strong class="text-white font-bold">PIX Instantâneo</strong>, <strong class="text-white font-bold">PDV de Balcão</strong>, <strong class="text-white font-bold">KDS para Cozinha</strong>, <strong class="text-white font-bold">QR Code de Mesa</strong> e <strong class="text-white font-bold">Atendimento IA no WhatsApp</strong>.
                </p>

                <!-- CTAs Principais -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-2">
                    <a href="/cadastro?plano=premium" 
                       class="bg-gradient-to-r from-rose-600 via-rose-500 to-amber-500 hover:brightness-110 text-white font-bold px-7 py-4 rounded-2xl text-sm text-center shadow-2xl shadow-rose-600/30 transition active:scale-95 flex items-center justify-center gap-2">
                        <span>Criar Minha Loja Grátis (14 Dias)</span>
                    </a>
                    <a href="#simulador" 
                       class="bg-[#101626] hover:bg-[#161e33] text-slate-200 border border-white/[0.1] font-bold px-6 py-4 rounded-2xl text-sm text-center transition flex items-center justify-center gap-2">
                        <span>Simular Minha Economia</span>
                    </a>
                </div>

                <!-- Selos de Garantia Operacional -->
                <div class="pt-3 grid grid-cols-3 gap-3 max-w-lg border-t border-white/[0.08]">
                    <div class="flex items-center gap-2 text-xs text-slate-300">
                        <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-[11px]"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>
                        <span>Sem cartão no teste</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-300">
                        <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-[11px]"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>
                        <span>Pronto em 5 minutos</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-300">
                        <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-[11px]"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>
                        <span>Sem fidelidade</span>
                    </div>
                </div>
            </div>

            <!-- Coluna Direita: Showcase Interativo em 3 Abas (Cardápio PIX / Kanban KDS / WhatsApp IA) -->
            <div class="lg:col-span-5">
                <div class="bg-[#0d1220]/90 backdrop-blur-xl border border-white/[0.1] rounded-3xl p-4 sm:p-5 shadow-2xl space-y-4 relative">
                    <!-- Seletor de Telas do Sistema -->
                    <div class="grid grid-cols-3 gap-1.5 bg-[#06080f] p-1.5 rounded-2xl border border-white/[0.07] text-[11px] font-extrabold">
                        <button type="button" @click="abaPreviewHero = 'cardapio'"
                            class="py-2 px-2 rounded-xl transition flex items-center justify-center gap-1"
                            :class="abaPreviewHero === 'cardapio' ? 'bg-rose-600 text-white shadow-md' : 'text-slate-400 hover:text-white'">
                            <span><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg></span> Cardápio PIX
                        </button>
                        <button type="button" @click="abaPreviewHero = 'kanban'"
                            class="py-2 px-2 rounded-xl transition flex items-center justify-center gap-1"
                            :class="abaPreviewHero === 'kanban' ? 'bg-rose-600 text-white shadow-md' : 'text-slate-400 hover:text-white'">
                            <span><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path></svg></span> Kanban &amp; KDS
                        </button>
                        <button type="button" @click="abaPreviewHero = 'whatsapp'"
                            class="py-2 px-2 rounded-xl transition flex items-center justify-center gap-1"
                            :class="abaPreviewHero === 'whatsapp' ? 'bg-rose-600 text-white shadow-md' : 'text-slate-400 hover:text-white'">
                            <span><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg></span> Robô IA
                        </button>
                    </div>

                    <!-- ABA 1: PREVIEW CARDÁPIO & PIX INSTANTÂNEO -->
                    <div x-show="abaPreviewHero === 'cardapio'" class="space-y-3.5 text-xs">
                        <div class="flex items-center justify-between bg-[#131a2e] border border-white/[0.07] rounded-2xl p-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-rose-600 flex items-center justify-center font-bold text-white text-sm">B</div>
                                <div>
                                    <div class="font-bold text-white text-xs">Pedido #4829 Confirmado</div>
                                    <div class="text-[11px] text-slate-400">Delivery · Tempo médio: 30–40 min</div>
                                </div>
                            </div>
                            <span class="bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold px-2.5 py-1 rounded-full">
                                PIX Aprovado
                            </span>
                        </div>

                        <div class="bg-[#090d18] border border-white/[0.06] rounded-2xl p-3.5 space-y-2.5">
                            <div class="flex justify-between items-center text-slate-200">
                                <div>
                                    <span class="font-bold text-white">2x Smash Burger Duplo Bacon</span>
                                    <span class="block text-[10px] text-slate-400">+ Cheddar Inglês, + Maionese Defumada</span>
                                </div>
                                <span class="font-bold text-white">R$ 74,80</span>
                            </div>
                            <div class="flex justify-between items-center text-slate-200 pt-2 border-t border-white/[0.05]">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[10px] bg-amber-500/20 text-amber-300 px-1.5 py-0.5 rounded font-bold">Order Bump</span>
                                    <span class="font-semibold text-white">2x Coca-Cola Lata 350ml</span>
                                </div>
                                <span class="font-bold text-emerald-400">+ R$ 14,00</span>
                            </div>
                            <div class="pt-2.5 border-t border-white/[0.08] flex justify-between items-baseline">
                                <span class="text-slate-400 font-bold">Valor creditado na sua conta:</span>
                                <span class="text-lg font-bold text-emerald-400">R$ 88,80 (100%)</span>
                            </div>
                        </div>

                        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-2xl p-3 flex items-center justify-between gap-2 text-emerald-300 font-bold">
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-emerald-500 text-slate-950 flex items-center justify-center font-bold text-[11px]"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>
                                <span>Comissão cobrada pelo PedeLivre:</span>
                            </div>
                            <span class="font-bold text-white bg-emerald-600 px-2.5 py-0.5 rounded-lg text-xs">R$ 0,00</span>
                        </div>
                    </div>

                    <!-- ABA 2: PREVIEW KANBAN & KDS POR ESTAÇÃO -->
                    <div x-cloak x-show="abaPreviewHero === 'kanban'" class="space-y-3 text-xs">
                        <div class="grid grid-cols-2 gap-2.5">
                            <div class="bg-[#090d18] border border-rose-500/30 rounded-2xl p-3 space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-white">#4830 · MESA 04</span>
                                    <span class="text-[10px] bg-rose-500/20 text-rose-300 px-2 py-0.5 rounded font-bold">Novo · 1 min</span>
                                </div>
                                <div class="text-[11px] text-slate-300 space-y-1">
                                    <div>1x Pizza G Meio a Meio <span class="text-amber-400">(Forno)</span></div>
                                    <div>2x Suco Natural Laranja <span class="text-sky-400">(Bar)</span></div>
                                </div>
                                <div class="bg-rose-600 text-white text-center py-1.5 rounded-xl font-bold text-[11px]">
                                    Aceitar e Imprimir 80mm
                                </div>
                            </div>

                            <div class="bg-[#090d18] border border-amber-500/30 rounded-2xl p-3 space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-white">#4828 · DELIVERY</span>
                                    <span class="text-[10px] bg-amber-500/20 text-amber-300 px-2 py-0.5 rounded font-bold">Preparo · 8 min</span>
                                </div>
                                <div class="text-[11px] text-slate-300 space-y-1">
                                    <div class="line-through text-slate-500">2x Burger Artesanal (Chapa)</div>
                                    <div>⏳ 1x Batata Frita G (Fritadeira)</div>
                                </div>
                                <div class="bg-amber-500 text-slate-950 text-center py-1.5 rounded-xl font-bold text-[11px]">
                                    Despachar c/ Motoboy
                                </div>
                            </div>
                        </div>

                        <div class="bg-[#131a2e] border border-white/[0.07] rounded-2xl p-3 flex items-center justify-between text-[11px]">
                            <span class="text-slate-300 font-semibold">Impressão Térmica 80mm Automática</span>
                            <span class="text-emerald-400 font-bold">● KDS Sincronizado</span>
                        </div>
                    </div>

                    <!-- ABA 3: PREVIEW ROBÔ WHATSAPP IA & CASHBACK -->
                    <div x-cloak x-show="abaPreviewHero === 'whatsapp'" class="space-y-2.5 text-xs">
                        <div class="bg-[#090d18] border border-white/[0.07] rounded-2xl p-3.5 space-y-2.5">
                            <div class="bg-[#162038] text-slate-200 p-2.5 rounded-2xl rounded-tl-sm max-w-[85%] text-[11px]">
                                Olá! Vocês estão abertos? Qual o tempo de entrega para o Centro?
                            </div>
                            <div class="bg-emerald-950/80 border border-emerald-500/30 text-emerald-100 p-2.5 rounded-2xl rounded-tr-sm ml-auto max-w-[90%] text-[11px] space-y-1">
                                <div class="font-bold text-emerald-400">Assistente IA da Loja</div>
                                <p>Olá! Estamos abertos sim! Para o bairro <strong>Centro</strong> nossa entrega leva cerca de <strong>30 min</strong> (Taxa R$ 5,00 ou Grátis acima de R$ 80). Faça seu pedido com 5% de Cashback no link abaixo:</p>
                            </div>
                        </div>
                        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-2xl p-2.5 px-3.5 flex items-center justify-between text-[11px] text-emerald-300 font-bold">
                            <span>Notificações automáticas a cada mudança de status</span>
                            <span>24h/7d</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ==================== 3. BARRA EXECUTIVA DE MÉTRICAS ==================== -->
    <section class="bg-[#090d18] border-b border-white/[0.06]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid grid-cols-2 lg:grid-cols-4 gap-6 text-center">
            <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/[0.05]">
                <div class="text-2xl sm:text-3xl font-bold text-emerald-400">0% Taxa</div>
                <div class="text-xs text-slate-400 mt-1 font-medium">Nenhuma comissão sobre seus pedidos</div>
            </div>
            <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/[0.05]">
                <div class="text-2xl sm:text-3xl font-bold text-white">PIX em 3s</div>
                <div class="text-xs text-slate-400 mt-1 font-medium">Mercado Pago ou Chave PIX Direta BCB</div>
            </div>
            <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/[0.05]">
                <div class="text-2xl sm:text-3xl font-bold text-amber-400">4 Canais</div>
                <div class="text-xs text-slate-400 mt-1 font-medium">Delivery, Retirada, Balcão F2 e Mesa QR</div>
            </div>
            <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/[0.05]">
                <div class="text-2xl sm:text-3xl font-bold text-rose-400">+28% Ticket</div>
                <div class="text-xs text-slate-400 mt-1 font-medium">Com Order Bump, Cupons e Cashback</div>
            </div>
        </div>
    </section>

    <!-- ==================== 4. ECOSSISTEMA COMPLETO (6 PILARES) ==================== -->
    <section id="ecossistema" class="py-20 lg:py-28 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-14">
            <div class="text-center max-w-3xl mx-auto space-y-3">
                <span class="text-[11px] font-bold uppercase tracking-widest text-rose-500 bg-rose-500/10 border border-rose-500/20 px-3 py-1 rounded-full">Ecossistema Completo</span>
                <h2 class="text-2xl sm:text-4xl font-bold text-white tracking-tight">
                    Tudo que os líderes do mercado oferecem, integrado em uma única plataforma
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                    Substitua múltiplos sistemas fragmentados por uma operação unificada que atende do pedido no celular até o fechamento de caixa e acerto dos motoboys.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Pilar 1 -->
                <div class="bg-[#0d1220] border border-white/[0.08] hover:border-rose-500/40 rounded-3xl p-6 space-y-4 transition group shadow-xl">
                    <div class="flex items-center justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-400 flex items-center justify-center text-xl"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg></div>
                        <span class="text-[10px] font-bold uppercase px-2.5 py-1 rounded-full bg-emerald-500/15 text-emerald-400">Alta Conversão</span>
                    </div>
                    <h3 class="font-bold text-base text-white group-hover:text-rose-400 transition">Cardápio Digital com Order Bump &amp; PIX</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Vitrine automática de ofertas ("De/Por"), sugestão inteligente de bebidas no checkout (Order Bump), barra de progresso para frete grátis, fotos WebP ultrarrápidas e pagamento PIX instantâneo.
                    </p>
                </div>

                <!-- Pilar 2 -->
                <div class="bg-[#0d1220] border border-white/[0.08] hover:border-amber-500/40 rounded-3xl p-6 space-y-4 transition group shadow-xl">
                    <div class="flex items-center justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center text-xl"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg></div>
                        <span class="text-[10px] font-bold uppercase px-2.5 py-1 rounded-full bg-amber-500/15 text-amber-300">Atalho F2</span>
                    </div>
                    <h3 class="font-bold text-base text-white group-hover:text-amber-400 transition">PDV de Balcão &amp; Frente de Caixa</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Lance pedidos presenciais ou por telefone em segundos usando o atalho F2, calcule troco automaticamente e controle abertura de caixa, sangrias, suprimentos e conferência de gaveta.
                    </p>
                </div>

                <!-- Pilar 3 -->
                <div class="bg-[#0d1220] border border-white/[0.08] hover:border-sky-500/40 rounded-3xl p-6 space-y-4 transition group shadow-xl">
                    <div class="flex items-center justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-sky-500/15 border border-sky-500/30 text-sky-400 flex items-center justify-center text-xl"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path></svg></div>
                        <span class="text-[10px] font-bold uppercase px-2.5 py-1 rounded-full bg-sky-500/15 text-sky-300">Modo Smart TV</span>
                    </div>
                    <h3 class="font-bold text-base text-white group-hover:text-sky-400 transition">Kanban Sonoro &amp; KDS por Estação</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Campainha sonora contínua para novos pedidos, impressão térmica 80mm automática e tela KDS separada por estação (Chapa, Forno, Fritadeira, Bar e Sobremesa) com alerta de atraso.
                    </p>
                </div>

                <!-- Pilar 4 -->
                <div class="bg-[#0d1220] border border-white/[0.08] hover:border-purple-500/40 rounded-3xl p-6 space-y-4 transition group shadow-xl">
                    <div class="flex items-center justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-purple-500/15 border border-purple-500/30 text-purple-400 flex items-center justify-center text-xl"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg></div>
                        <span class="text-[10px] font-bold uppercase px-2.5 py-1 rounded-full bg-purple-500/15 text-purple-300">Autoatendimento</span>
                    </div>
                    <h3 class="font-bold text-base text-white group-hover:text-purple-400 transition">QR Code de Mesa &amp; Divisão de Conta</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        QR Code individual anti-fraude para cada mesa do salão. O cliente pede direto pelo celular, chama o garçom em 1 clique com alerta sonoro no caixa e você divide a conta por pessoa facilmente.
                    </p>
                </div>

                <!-- Pilar 5 -->
                <div class="bg-[#0d1220] border border-white/[0.08] hover:border-emerald-500/40 rounded-3xl p-6 space-y-4 transition group shadow-xl">
                    <div class="flex items-center justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-xl"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg></div>
                        <span class="text-[10px] font-bold uppercase px-2.5 py-1 rounded-full bg-emerald-500/15 text-emerald-300">Logística Completa</span>
                    </div>
                    <h3 class="font-bold text-base text-white group-hover:text-emerald-400 transition">Frete Inteligente &amp; Acerto de Motoboys</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Configure taxas de entrega por Bairro ou Raio de Distância (KM), despache pedidos com link Google Maps para o motoboy e feche o acerto financeiro da noite com extrato em 1 clique.
                    </p>
                </div>

                <!-- Pilar 6 -->
                <div class="bg-[#0d1220] border border-white/[0.08] hover:border-rose-500/40 rounded-3xl p-6 space-y-4 transition group shadow-xl">
                    <div class="flex items-center justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-400 flex items-center justify-center text-xl"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg></div>
                        <span class="text-[10px] font-bold uppercase px-2.5 py-1 rounded-full bg-rose-500/15 text-rose-300">Retenção &amp; IA</span>
                    </div>
                    <h3 class="font-bold text-base text-white group-hover:text-rose-400 transition">WhatsApp IA, Cashback &amp; Win-Back</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Conecte seu WhatsApp via QR Code para avisar o cliente em cada etapa do pedido, responder dúvidas com Inteligência Artificial e reativar clientes sumidos com cupom automático.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== 5. SIMULADOR INTERATIVO DE ECONOMIA ==================== -->
    <section id="simulador" class="py-20 bg-[#090d18] border-y border-white/[0.06]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-12 gap-12 items-center">
                
                <!-- Controles da Calculadora -->
                <div class="lg:col-span-6 space-y-6">
                    <span class="text-[11px] font-bold uppercase tracking-widest text-emerald-400 bg-emerald-500/10 border border-emerald-500/25 px-3 py-1 rounded-full">Simulador Financeiro</span>
                    <h2 class="text-2xl sm:text-4xl font-bold text-white leading-tight">
                        Descubra quanto lucro volta para o caixa do seu restaurante
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                        Ajuste os controles abaixo com o volume mensal do seu delivery e compare o custo de comissões dos aplicativos com a mensalidade fixa do PedeLivre.
                    </p>

                    <!-- Slider 1: Faturamento -->
                    <div class="bg-[#0d1323] border border-white/[0.07] rounded-2xl p-4 space-y-3">
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-bold text-slate-300">Seu faturamento mensal em pedidos:</span>
                            <span class="font-bold text-white text-base bg-rose-500/15 border border-rose-500/30 px-3 py-1 rounded-xl" x-text="formatarMoeda(faturamentoMensal)"></span>
                        </div>
                        <input type="range" min="5000" max="200000" step="5000" x-model.number="faturamentoMensal" class="w-full">
                        <div class="flex justify-between text-[10px] text-slate-500 font-bold">
                            <span>R$ 5.000/mês</span>
                            <span>R$ 100.000/mês</span>
                            <span>R$ 200.000/mês</span>
                        </div>
                    </div>

                    <!-- Slider 2: Taxa Marketplace -->
                    <div class="bg-[#0d1323] border border-white/[0.07] rounded-2xl p-4 space-y-3">
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-bold text-slate-300">Comissão média cobrada pelos apps:</span>
                            <span class="font-bold text-rose-400 text-base bg-rose-500/15 border border-rose-500/30 px-3 py-1 rounded-xl" x-text="taxaApp + '%'"></span>
                        </div>
                        <input type="range" min="12" max="28" step="1" x-model.number="taxaApp" class="w-full">
                        <div class="flex justify-between text-[10px] text-slate-500 font-bold">
                            <span>12% (Plano Básico)</span>
                            <span>20% (Média)</span>
                            <span>27%+ (Entrega Parceira)</span>
                        </div>
                    </div>

                    <!-- Seletor de Plano para Comparação -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 block">Plano PedeLivre para comparar:</label>
                        <div class="grid grid-cols-3 gap-2 text-xs">
                            <button type="button" @click="planoSimulador = 'delivery'"
                                class="py-2.5 px-3 rounded-xl font-bold border transition"
                                :class="planoSimulador === 'delivery' ? 'bg-rose-600 border-rose-500 text-white' : 'bg-[#0d1323] border-white/[0.08] text-slate-400 hover:text-white'">
                                Delivery (R$ 99,90)
                            </button>
                            <button type="button" @click="planoSimulador = 'presencial'"
                                class="py-2.5 px-3 rounded-xl font-bold border transition"
                                :class="planoSimulador === 'presencial' ? 'bg-rose-600 border-rose-500 text-white' : 'bg-[#0d1323] border-white/[0.08] text-slate-400 hover:text-white'">
                                Presencial (R$ 149,90)
                            </button>
                            <button type="button" @click="planoSimulador = 'premium'"
                                class="py-2.5 px-3 rounded-xl font-bold border transition"
                                :class="planoSimulador === 'premium' ? 'bg-rose-600 border-rose-500 text-white' : 'bg-[#0d1323] border-white/[0.08] text-slate-400 hover:text-white'">
                                Premium (R$ 249,90)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Resultado Financeiro -->
                <div class="lg:col-span-6">
                    <div class="bg-gradient-to-br from-[#11182b] to-[#0b101d] border border-white/[0.1] rounded-3xl p-6 sm:p-8 space-y-6 shadow-2xl relative overflow-hidden">
                        <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

                        <div class="flex items-center justify-between border-b border-white/[0.08] pb-4">
                            <div>
                                <span class="text-xs font-bold text-slate-400 block">Resultado da Simulação</span>
                                <h3 class="text-lg font-bold text-white">Seu Comparativo de Rentabilidade</h3>
                            </div>
                            <span class="bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 text-xs font-bold px-3 py-1 rounded-full">0% Comissão</span>
                        </div>

                        <div class="space-y-3 text-xs sm:text-sm">
                            <div class="flex justify-between items-center p-3 rounded-xl bg-rose-500/10 border border-rose-500/20">
                                <span class="text-rose-200 font-medium">Comissão paga aos apps por mês:</span>
                                <span class="font-bold text-rose-400 text-base" x-text="'- ' + formatarMoeda(perdaMensalHoje)"></span>
                            </div>

                            <div class="flex justify-between items-center p-3 rounded-xl bg-white/[0.03] border border-white/[0.06]">
                                <span class="text-slate-300 font-medium">Mensalidade fixa no PedeLivre:</span>
                                <span class="font-bold text-white text-base" x-text="formatarMoeda(custoPedelivreMensal)"></span>
                            </div>

                            <div class="flex justify-between items-center p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/25">
                                <span class="text-emerald-200 font-bold">Lucro extra todo mês no seu caixa:</span>
                                <span class="font-bold text-emerald-400 text-base" x-text="'+ ' + formatarMoeda(economiaMensalLiquida)"></span>
                            </div>
                        </div>

                        <div class="bg-[#06080f] border border-emerald-500/30 rounded-2xl p-5 text-center space-y-1">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Economia Acumulada em 12 Meses</span>
                            <div class="text-3xl sm:text-5xl font-bold text-emerald-400 tracking-tight" x-text="formatarMoeda(economiaAnualEstimada)"></div>
                            <p class="text-[11px] text-slate-400 pt-1">Valor que permanece 100% no caixa do seu restaurante.</p>
                        </div>

                        <a :href="'/cadastro?plano=' + planoSimulador" class="w-full bg-gradient-to-r from-emerald-500 to-emerald-600 hover:brightness-110 text-slate-950 font-bold py-4 rounded-2xl text-sm shadow-xl transition flex items-center justify-center gap-2">
                            <span>Começar Meu Teste Grátis de 14 Dias</span>
                            <span>→</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ==================== 6. TABELA COMPARATIVA ==================== -->
    <section id="comparativo" class="py-20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <div class="text-center space-y-2">
                <span class="text-[11px] font-bold uppercase tracking-widest text-rose-500">Comparativo Direto</span>
                <h2 class="text-2xl sm:text-4xl font-bold text-white">Por que migrar sua operação para o PedeLivre?</h2>
            </div>

            <div class="bg-[#0d1220] border border-white/[0.08] rounded-3xl overflow-hidden shadow-2xl text-xs sm:text-sm">
                <div class="grid grid-cols-3 bg-[#131a2e] p-4 sm:p-5 font-bold text-slate-300 border-b border-white/[0.08]">
                    <span>Critério Operacional</span>
                    <span class="text-rose-400">PedeLivre OS</span>
                    <span class="text-slate-400">Apps de Marketplace</span>
                </div>

                <div class="divide-y divide-white/[0.06]">
                    <div class="grid grid-cols-3 p-4 items-center">
                        <span class="text-slate-300 font-semibold">Comissão por pedido</span>
                        <span class="font-bold text-emerald-400">0% (Isento para sempre)</span>
                        <span class="text-rose-400 font-semibold">12% a 27% por pedido</span>
                    </div>
                    <div class="grid grid-cols-3 p-4 items-center">
                        <span class="text-slate-300 font-semibold">Repasse do pagamento PIX</span>
                        <span class="font-bold text-emerald-400">Na hora (100% na sua conta)</span>
                        <span class="text-slate-400">Retido por 7 a 30 dias</span>
                    </div>
                    <div class="grid grid-cols-3 p-4 items-center">
                        <span class="text-slate-300 font-semibold">Order Bump + Cashback</span>
                        <span class="font-bold text-emerald-400">Incluso nativamente</span>
                        <span class="text-slate-400">Limitado ou cobrado à parte</span>
                    </div>
                    <div class="grid grid-cols-3 p-4 items-center">
                        <span class="text-slate-300 font-semibold">PDV Balcão + KDS + Mesas QR</span>
                        <span class="font-bold text-emerald-400">Tudo no mesmo painel</span>
                        <span class="text-slate-400">Exige contratar outro sistema</span>
                    </div>
                    <div class="grid grid-cols-3 p-4 items-center">
                        <span class="text-slate-300 font-semibold">Propriedade da base de clientes</span>
                        <span class="font-bold text-emerald-400">100% sua (com WhatsApp e CRM)</span>
                        <span class="text-slate-400">Bloqueada pelo aplicativo</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== 7. PLANOS E PREÇOS ==================== -->
    <section id="planos" class="py-20 bg-[#090d18] border-t border-white/[0.06]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="text-center max-w-2xl mx-auto space-y-3">
                <span class="text-[11px] font-bold uppercase tracking-widest text-rose-500 bg-rose-500/10 border border-rose-500/20 px-3 py-1 rounded-full">Planos Transparentes</span>
                <h2 class="text-2xl sm:text-4xl font-bold text-white">Mensalidade fixa. Lucro 100% seu.</h2>
                <p class="text-xs sm:text-sm text-slate-400">Teste qualquer plano por 14 dias grátis sem informar cartão de crédito.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
                <!-- Plano 1: Delivery -->
                <div class="bg-[#0d1220] border border-white/[0.08] rounded-3xl p-7 flex flex-col justify-between space-y-6 shadow-xl">
                    <div class="space-y-4">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Essencial</span>
                            <h3 class="font-bold text-xl text-white mt-0.5">Plano Delivery</h3>
                            <p class="text-xs text-slate-400 mt-1">Ideal para dark kitchens, hamburguerias e operações focadas em entrega e retirada.</p>
                        </div>
                        <div class="flex items-baseline gap-1 pt-2 border-t border-white/[0.07]">
                            <span class="text-3xl sm:text-4xl font-bold text-white">R$ 99,90</span>
                            <span class="text-xs text-slate-400">/ mês</span>
                        </div>
                        <div class="space-y-2.5 pt-2 text-xs text-slate-300">
                            <div class="flex items-center gap-2"><span class="text-emerald-400 font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span><span>Cardápio digital ilimitado com Order Bump</span></div>
                            <div class="flex items-center gap-2"><span class="text-emerald-400 font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span><span>PIX Mercado Pago ou Chave PIX Direta (0% taxa)</span></div>
                            <div class="flex items-center gap-2"><span class="text-emerald-400 font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span><span>Painel Kanban em tempo real com alerta sonoro</span></div>
                            <div class="flex items-center gap-2"><span class="text-emerald-400 font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span><span>Taxas de entrega por Bairro ou Raio KM</span></div>
                            <div class="flex items-center gap-2"><span class="text-emerald-400 font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span><span>Cupons de desconto e link de rastreio ao vivo</span></div>
                        </div>
                    </div>
                    <a href="/cadastro?plano=delivery" class="w-full bg-white/[0.06] hover:bg-white/[0.12] border border-white/[0.1] text-white font-bold py-3.5 rounded-2xl text-xs text-center transition">
                        Começar 14 Dias Grátis →
                    </a>
                </div>

                <!-- Plano 2: Presencial -->
                <div class="bg-[#0d1220] border border-white/[0.08] rounded-3xl p-7 flex flex-col justify-between space-y-6 shadow-xl">
                    <div class="space-y-4">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400">Salão + Delivery</span>
                            <h3 class="font-bold text-xl text-white mt-0.5">Plano Presencial</h3>
                            <p class="text-xs text-slate-400 mt-1">Para restaurantes, pizzarias e bares com atendimento em mesas, balcão e delivery.</p>
                        </div>
                        <div class="flex items-baseline gap-1 pt-2 border-t border-white/[0.07]">
                            <span class="text-3xl sm:text-4xl font-bold text-white">R$ 149,90</span>
                            <span class="text-xs text-slate-400">/ mês</span>
                        </div>
                        <div class="space-y-2.5 pt-2 text-xs text-slate-300">
                            <div class="flex items-center gap-2"><span class="text-emerald-400 font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span><span><strong>Tudo do plano Delivery</strong> +</span></div>
                            <div class="flex items-center gap-2"><span class="text-emerald-400 font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span><span>PDV de Balcão rápido com atalho F2</span></div>
                            <div class="flex items-center gap-2"><span class="text-emerald-400 font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span><span>QR Code por Mesa, Comandas e Chamar Garçom</span></div>
                            <div class="flex items-center gap-2"><span class="text-emerald-400 font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span><span>KDS de Cozinha por Estação (Modo TV)</span></div>
                            <div class="flex items-center gap-2"><span class="text-emerald-400 font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span><span>Controle de Frente de Caixa, Sangria e Turnos</span></div>
                        </div>
                    </div>
                    <a href="/cadastro?plano=presencial" class="w-full bg-white/[0.06] hover:bg-white/[0.12] border border-white/[0.1] text-white font-bold py-3.5 rounded-2xl text-xs text-center transition">
                        Começar 14 Dias Grátis →
                    </a>
                </div>

                <!-- Plano 3: Premium Total -->
                <div class="bg-gradient-to-b from-[#171326] to-[#0d1220] border-2 border-rose-500 rounded-3xl p-7 flex flex-col justify-between space-y-6 shadow-2xl shadow-rose-600/20 relative">
                    <span class="absolute -top-3.5 right-6 bg-gradient-to-r from-rose-600 to-amber-500 text-white text-[10px] font-bold px-3.5 py-1 rounded-full uppercase tracking-wider shadow-lg">
                        Mais Escolhido
                    </span>
                    <div class="space-y-4">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-400">Automação Total</span>
                            <h3 class="font-bold text-xl text-white mt-0.5">Plano Premium Total</h3>
                            <p class="text-xs text-slate-300 mt-1">Operação completa com WhatsApp Inteligência Artificial, CRM Cashback e Motoboys.</p>
                        </div>
                        <div class="flex items-baseline gap-1 pt-2 border-t border-white/[0.1]">
                            <span class="text-3xl sm:text-4xl font-bold text-white">R$ 249,90</span>
                            <span class="text-xs text-slate-400">/ mês</span>
                        </div>
                        <div class="space-y-2.5 pt-2 text-xs text-slate-200">
                            <div class="flex items-center gap-2"><span class="text-rose-400 font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span><span><strong>Tudo dos planos Delivery e Presencial</strong> +</span></div>
                            <div class="flex items-center gap-2"><span class="text-rose-400 font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span><span>Robô de Atendimento IA no WhatsApp (Evolution)</span></div>
                            <div class="flex items-center gap-2"><span class="text-rose-400 font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span><span>Campanhas Automáticas Win-Back (Clientes Inativos)</span></div>
                            <div class="flex items-center gap-2"><span class="text-rose-400 font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span><span>Fidelidade com Cashback Automático por Cliente</span></div>
                            <div class="flex items-center gap-2"><span class="text-rose-400 font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span><span>Gestão e Fechamento Diário de Motoboys</span></div>
                            <div class="flex items-center gap-2"><span class="text-rose-400 font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span><span>Múltiplos Operadores e Suporte Prioritário VIP</span></div>
                        </div>
                    </div>
                    <a href="/cadastro?plano=premium" class="w-full bg-gradient-to-r from-rose-600 to-rose-500 hover:from-rose-500 hover:to-rose-400 text-white font-bold py-4 rounded-2xl text-xs text-center transition shadow-xl shadow-rose-600/30">
                        Testar Premium Total por 14 Dias Grátis →
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== 8. FAQ ACCORDION ==================== -->
    <section id="faq" class="py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="text-center space-y-2">
                <span class="text-[11px] font-bold uppercase tracking-widest text-rose-500">Tire Suas Dúvidas</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-white">Perguntas Frequentes</h2>
            </div>

            <div class="space-y-3">
                <div class="bg-[#0d1220] border border-white/[0.08] rounded-2xl p-5 cursor-pointer transition hover:border-white/[0.15]" @click="faqAberta = (faqAberta === 1 ? null : 1)">
                    <div class="flex justify-between items-center text-xs sm:text-sm font-bold text-white">
                        <span>O PedeLivre cobra alguma porcentagem sobre os meus pedidos?</span>
                        <span x-text="faqAberta === 1 ? '−' : '+'" class="text-lg font-bold text-rose-400"></span>
                    </div>
                    <div x-show="faqAberta === 1" class="text-xs sm:text-sm text-slate-400 pt-3 leading-relaxed">
                        Não! Nossa comissão sobre vendas é <strong>0%</strong>. Se o seu restaurante vender R$ 10.000 ou R$ 150.000 no mês, você paga apenas o valor fixo da sua assinatura mensal e fica com 100% do lucro dos seus pedidos.
                    </div>
                </div>

                <div class="bg-[#0d1220] border border-white/[0.08] rounded-2xl p-5 cursor-pointer transition hover:border-white/[0.15]" @click="faqAberta = (faqAberta === 2 ? null : 2)">
                    <div class="flex justify-between items-center text-xs sm:text-sm font-bold text-white">
                        <span>Como recebo os pagamentos PIX dos meus clientes?</span>
                        <span x-text="faqAberta === 2 ? '−' : '+'" class="text-lg font-bold text-rose-400"></span>
                    </div>
                    <div x-show="faqAberta === 2" class="text-xs sm:text-sm text-slate-400 pt-3 leading-relaxed">
                        Você tem duas opções integradas no painel: pode informar sua <strong>Chave PIX Principal</strong> (CNPJ, CPF, E-mail, Celular ou Aleatória de qualquer banco) para gerar QR Code oficial do Banco Central direto para sua conta, ou conectar seu <strong>Mercado Pago</strong> para baixa automática via Webhook em segundos.
                    </div>
                </div>

                <div class="bg-[#0d1220] border border-white/[0.08] rounded-2xl p-5 cursor-pointer transition hover:border-white/[0.15]" @click="faqAberta = (faqAberta === 3 ? null : 3)">
                    <div class="flex justify-between items-center text-xs sm:text-sm font-bold text-white">
                        <span>Funciona em impressora térmica 80mm e celular?</span>
                        <span x-text="faqAberta === 3 ? '−' : '+'" class="text-lg font-bold text-rose-400"></span>
                    </div>
                    <div x-show="faqAberta === 3" class="text-xs sm:text-sm text-slate-400 pt-3 leading-relaxed">
                        Sim! O painel do lojista roda perfeitamente no celular, tablet ou computador, e possui formatação nativa para impressoras térmicas de 80mm (comanda de cozinha, recibo de entrega, fechamento de caixa e acerto de motoboys).
                    </div>
                </div>

                <div class="bg-[#0d1220] border border-white/[0.08] rounded-2xl p-5 cursor-pointer transition hover:border-white/[0.15]" @click="faqAberta = (faqAberta === 4 ? null : 4)">
                    <div class="flex justify-between items-center text-xs sm:text-sm font-bold text-white">
                        <span>Preciso cadastrar cartão de crédito para testar os 14 dias grátis?</span>
                        <span x-text="faqAberta === 4 ? '−' : '+'" class="text-lg font-bold text-rose-400"></span>
                    </div>
                    <div x-show="faqAberta === 4" class="text-xs sm:text-sm text-slate-400 pt-3 leading-relaxed">
                        Não! Você cria sua loja agora mesmo sem cartão de crédito, configura seu cardápio e já pode receber pedidos reais durante os 14 dias de avaliação gratuita.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== 9. CTA FINAL ==================== -->
    <section class="py-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-r from-rose-600 via-rose-500 to-amber-500 rounded-3xl p-8 sm:p-12 text-center space-y-5 shadow-2xl relative overflow-hidden">
                <h2 class="text-2xl sm:text-4xl font-bold text-white leading-tight">
                    Coloque o cardápio da sua loja no ar agora mesmo
                </h2>
                <p class="text-xs sm:text-base text-white/90 max-w-xl mx-auto font-medium">
                    Leva menos de 2 minutos. Sem taxa de adesão, sem comissão sobre vendas e com 14 dias gratuitos.
                </p>
                <div class="pt-2 flex flex-col sm:flex-row justify-center gap-3">
                    <a href="/cadastro?plano=premium" class="inline-block bg-slate-950 hover:bg-slate-900 text-white font-bold px-8 py-4 rounded-2xl text-xs sm:text-sm shadow-2xl transition active:scale-95">
                        Criar Minha Loja Grátis Agora
                    </a>
                    <a href="/login" class="inline-block bg-white/15 hover:bg-white/25 text-white border border-white/30 font-bold px-7 py-4 rounded-2xl text-xs sm:text-sm transition">
                        Já sou cliente · Entrar
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== 10. FOOTER ==================== -->
    <footer class="border-t border-white/[0.07] bg-[#05070c] py-10 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-gradient-to-br from-rose-600 to-amber-500 text-white font-bold text-xs flex items-center justify-center">P</span>
                <span class="font-bold text-slate-200">PedeLivre OS</span>
                <span>© 2026 — Plataforma Multi-Tenant de Delivery &amp; Operação Gastronômica</span>
            </div>
            <div class="flex items-center gap-6 font-semibold">
                <a href="/login" class="hover:text-white transition">Login do Lojista</a>
                <a href="/cadastro?plano=premium" class="hover:text-white transition">Criar Conta</a>
                <a href="#planos" class="hover:text-white transition">Planos</a>
                <a href="#faq" class="hover:text-white transition">FAQ</a>
            </div>
        </div>
    </footer>

</body>
</html>
