<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-[#06080f] text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#06080f">
    <title>Criar Restaurante Grátis (14 Dias) — PedeLivre OS</title>

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
    </style>
</head>
<body class="min-h-full antialiased selection:bg-rose-500 selection:text-white relative overflow-x-hidden py-6 sm:py-10 px-4 sm:px-6 lg:px-8" x-data="cadastroApp()">

    <!-- Luzes de Fundo Suaves -->
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden">
        <div class="absolute -top-32 left-1/3 w-[520px] h-[520px] bg-rose-600/15 rounded-full blur-[135px]"></div>
        <div class="absolute bottom-0 right-10 w-[440px] h-[440px] bg-emerald-500/10 rounded-full blur-[135px]"></div>
    </div>

    <div class="relative z-10 max-w-6xl mx-auto">
        <!-- Barra Superior de Navegação Rápida -->
        <div class="flex items-center justify-between mb-6">
            <a href="/" class="inline-flex items-center gap-3 group">
                <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-rose-500 via-rose-600 to-amber-500 flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-rose-600/30">
                    P
                </div>
                <div>
                    <span class="text-lg font-bold tracking-tight text-white">Pede<span class="text-rose-500">Livre</span></span>
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-rose-500/15 text-rose-400 border border-rose-500/30 ml-1">14 Dias Grátis</span>
                </div>
            </a>
            <a href="/login" class="text-xs font-bold text-slate-300 hover:text-white bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.08] px-4 py-2 rounded-xl transition">
                Já tenho conta · Entrar →
            </a>
        </div>

        <!-- Card Principal Split-Screen -->
        <div class="grid grid-cols-1 lg:grid-cols-12 bg-[#0b0f1c]/90 backdrop-blur-2xl border border-white/[0.08] rounded-[32px] shadow-2xl overflow-hidden">
            
            <!-- ==================== COLUNA ESQUERDA: PREVIEW AO VIVO DA LOJA & PLANO (5 Colunas) ==================== -->
            <div class="lg:col-span-5 bg-gradient-to-br from-[#12182b] via-[#0d1221] to-[#080b14] p-6 sm:p-8 lg:p-10 border-b lg:border-b-0 lg:border-r border-white/[0.07] flex flex-col justify-between space-y-6">
                <div class="space-y-6">
                    <div>
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-extrabold uppercase tracking-wider px-3 py-1 rounded-full bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                            Ativação Instantânea · R$ 0,00 Hoje
                        </span>
                        <h2 class="mt-3 text-xl sm:text-2xl font-bold text-white leading-snug">
                            Seu restaurante pronto para vender em menos de 2 minutos.
                        </h2>
                        <p class="mt-1 text-xs text-slate-400 leading-relaxed">
                            Veja abaixo como ficará o link oficial do seu cardápio digital assim que você concluir o cadastro:
                        </p>
                    </div>

                    <!-- Card Preview ao Vivo do Cardápio do Cliente -->
                    <div class="bg-[#070a12] border border-white/[0.1] rounded-3xl p-4 space-y-3.5 shadow-2xl">
                        <div class="flex items-center justify-between border-b border-white/[0.07] pb-2.5">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            </div>
                            <div class="font-mono-code text-[11px] text-emerald-400 bg-emerald-500/10 border border-emerald-500/25 px-2.5 py-0.5 rounded-lg truncate max-w-[230px]">
                                <span class="text-slate-400">/p/</span><span x-text="form.slug || 'seu-restaurante'"></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-rose-600 to-amber-500 flex items-center justify-center font-bold text-xl text-white shrink-0 shadow-lg"
                                 x-text="(form.nome_loja || 'R').trim().charAt(0).toUpperCase()"></div>
                            <div class="min-w-0 flex-1">
                                <div class="font-bold text-white text-sm truncate" x-text="form.nome_loja || 'Nome do Seu Restaurante'"></div>
                                <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-400 mt-0.5">
                                    <span class="text-emerald-400 font-bold">● Aberto agora</span>
                                    <span>•</span>
                                    <span x-text="(form.cidade || 'Sua Cidade') + '/' + (form.uf || 'MG').toUpperCase()"></span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2 pt-1 text-center text-[10px]">
                            <div class="bg-white/[0.03] border border-white/[0.06] rounded-xl p-2">
                                <span class="block font-bold text-emerald-400">0% Taxa</span>
                                <span class="text-slate-400">Comissão</span>
                            </div>
                            <div class="bg-white/[0.03] border border-white/[0.06] rounded-xl p-2">
                                <span class="block font-bold text-white">PIX Direto</span>
                                <span class="text-slate-400">Na sua conta</span>
                            </div>
                            <div class="bg-white/[0.03] border border-white/[0.06] rounded-xl p-2">
                                <span class="block font-bold text-amber-400">14 Dias</span>
                                <span class="text-slate-400">Teste Grátis</span>
                            </div>
                        </div>
                    </div>

                    <!-- Resumo do Plano Selecionado -->
                    <div class="bg-white/[0.03] border border-white/[0.07] rounded-2xl p-4 space-y-2.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-white uppercase tracking-wider" x-text="detalhesPlanoSelecionado.titulo"></span>
                            <span class="font-bold text-rose-400" x-text="detalhesPlanoSelecionado.preco + '/mês após 14 dias'"></span>
                        </div>
                        <p class="text-[11px] text-slate-400" x-text="detalhesPlanoSelecionado.resumo"></p>
                        <div class="space-y-1.5 pt-1 border-t border-white/[0.06] text-[11px] text-slate-300">
                            <template x-for="(item, idx) in detalhesPlanoSelecionado.itens" :key="idx">
                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-400 font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>
                                    <span x-text="item"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Rodapé de Garantias -->
                <div class="pt-4 border-t border-white/[0.07] flex items-center justify-between text-[11px] text-slate-400">
                    <span>Sem cobrança hoje</span>
                    <span>Cancele em 1 clique</span>
                </div>
            </div>

            <!-- ==================== COLUNA DIREITA: FORMULÁRIO DE CADASTRO (7 Colunas) ==================== -->
            <div class="lg:col-span-7 p-6 sm:p-8 lg:p-10">
                <div class="space-y-5">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Criar Minha Loja Grátis</h1>
                        <p class="text-xs sm:text-sm text-slate-400 mt-1">Preencha os dados abaixo para liberar seu cardápio digital e painel de pedidos imediatamente.</p>
                    </div>

                    <!-- Alerta de Erro -->
                    <template x-if="erro">
                        <div class="bg-rose-950/70 border border-rose-500/40 text-rose-200 text-xs font-semibold rounded-2xl p-4 flex items-start gap-2.5 shadow-lg">
                            <span class="text-rose-400 text-sm shrink-0"><svg class="w-4 h-4 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></span>
                            <span class="flex-1" x-text="erro"></span>
                            <button type="button" @click="erro = ''" class="text-rose-400 hover:text-white font-bold"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                        </div>
                    </template>

                    <form @submit.prevent="cadastrar()" class="space-y-5 text-xs">
                        <!-- 1. SELETOR VISUAL DE PLANO (3 Cards Clicáveis) -->
                        <div class="space-y-2">
                            <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-300">1. Escolha seu Plano (14 dias grátis em qualquer opção)</label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                <div @click="form.plano = 'delivery'"
                                     class="p-3 rounded-2xl border cursor-pointer transition select-none"
                                     :class="form.plano === 'delivery' ? 'bg-rose-500/15 border-rose-500 text-white shadow-md' : 'bg-[#06080f] border-white/[0.08] text-slate-400 hover:border-white/[0.2]'">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-xs text-white">Delivery</span>
                                        <span class="w-4 h-4 rounded-full border flex items-center justify-center text-[10px]"
                                              :class="form.plano === 'delivery' ? 'bg-rose-500 border-rose-500 text-white font-bold' : 'border-slate-600'">
                                            <span x-show="form.plano === 'delivery'"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>
                                        </span>
                                    </div>
                                    <div class="font-bold text-sm text-emerald-400 mt-1">R$ 99,90<span class="text-[10px] text-slate-400 font-normal">/mês</span></div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Entrega, Retirada &amp; PIX</div>
                                </div>

                                <div @click="form.plano = 'presencial'"
                                     class="p-3 rounded-2xl border cursor-pointer transition select-none"
                                     :class="form.plano === 'presencial' ? 'bg-rose-500/15 border-rose-500 text-white shadow-md' : 'bg-[#06080f] border-white/[0.08] text-slate-400 hover:border-white/[0.2]'">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-xs text-white">Presencial</span>
                                        <span class="w-4 h-4 rounded-full border flex items-center justify-center text-[10px]"
                                              :class="form.plano === 'presencial' ? 'bg-rose-500 border-rose-500 text-white font-bold' : 'border-slate-600'">
                                            <span x-show="form.plano === 'presencial'"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>
                                        </span>
                                    </div>
                                    <div class="font-bold text-sm text-emerald-400 mt-1">R$ 149,90<span class="text-[10px] text-slate-400 font-normal">/mês</span></div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">+ PDV, Mesas QR &amp; KDS</div>
                                </div>

                                <div @click="form.plano = 'premium'"
                                     class="p-3 rounded-2xl border cursor-pointer transition select-none relative"
                                     :class="form.plano === 'premium' ? 'bg-rose-500/15 border-rose-500 text-white shadow-md' : 'bg-[#06080f] border-white/[0.08] text-slate-400 hover:border-white/[0.2]'">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-xs text-white">Premium Total</span>
                                        <span class="w-4 h-4 rounded-full border flex items-center justify-center text-[10px]"
                                              :class="form.plano === 'premium' ? 'bg-rose-500 border-rose-500 text-white font-bold' : 'border-slate-600'">
                                            <span x-show="form.plano === 'premium'"><svg class="w-3.5 h-3.5 shrink-0 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>
                                        </span>
                                    </div>
                                    <div class="font-bold text-sm text-emerald-400 mt-1">R$ 249,90<span class="text-[10px] text-slate-400 font-normal">/mês</span></div>
                                    <div class="text-[10px] text-rose-300 font-bold mt-0.5">+ WhatsApp IA &amp; CRM</div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. DADOS DO RESTAURANTE -->
                        <div class="space-y-3 pt-2 border-t border-white/[0.07]">
                            <span class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-300">2. Dados do Restaurante</span>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-400 mb-1">Nome Fantasia da Loja *</label>
                                    <input type="text" x-model="form.nome_loja" @input="gerarSlug()" required placeholder="Ex: Pizzaria Don Vito"
                                        class="w-full bg-[#06080f] border border-white/[0.1] focus:border-rose-500 rounded-xl px-3.5 py-2.5 text-xs text-white font-bold focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-400 mb-1">Link do Cardápio (/p/slug) *</label>
                                    <div class="flex items-center bg-[#06080f] border border-white/[0.1] focus-within:border-rose-500 rounded-xl overflow-hidden">
                                        <span class="pl-3 pr-1 py-2.5 text-slate-500 font-mono-code text-[11px] select-none">/p/</span>
                                        <input type="text" x-model="form.slug" required placeholder="pizzaria-don-vito"
                                            class="w-full bg-transparent pr-3 py-2.5 text-xs text-emerald-400 font-mono-code font-bold focus:outline-none">
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-400 mb-1">CNPJ ou CPF *</label>
                                    <input type="text" x-model="form.cnpj_cpf" @input="mascararDocumento()" required placeholder="00.000.000/0001-00" maxlength="18"
                                        class="w-full bg-[#06080f] border border-white/[0.1] focus:border-rose-500 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-400 mb-1">WhatsApp de Atendimento *</label>
                                    <input type="tel" x-model="form.telefone" @input="mascararTelefone()" required placeholder="(31) 99999-8888" maxlength="15"
                                        class="w-full bg-[#06080f] border border-white/[0.1] focus:border-rose-500 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none">
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-3">
                                <div class="col-span-2">
                                    <label class="block text-[11px] font-bold text-slate-400 mb-1">Cidade *</label>
                                    <input type="text" x-model="form.cidade" required placeholder="Ex: Coronel Fabriciano"
                                        class="w-full bg-[#06080f] border border-white/[0.1] focus:border-rose-500 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-400 mb-1">UF *</label>
                                    <input type="text" x-model="form.uf" maxlength="2" required placeholder="MG"
                                        class="w-full bg-[#06080f] border border-white/[0.1] focus:border-rose-500 rounded-xl px-3.5 py-2.5 text-xs text-white uppercase font-bold focus:outline-none">
                                </div>
                            </div>
                        </div>

                        <!-- 3. DADOS DO PROPRIETÁRIO / ACESSO -->
                        <div class="space-y-3 pt-2 border-t border-white/[0.07]">
                            <span class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-300">3. Seus Dados de Acesso ao Painel</span>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-400 mb-1">Seu Nome Completo *</label>
                                    <input type="text" x-model="form.nome_dono" required placeholder="Ex: João Silva"
                                        class="w-full bg-[#06080f] border border-white/[0.1] focus:border-rose-500 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-400 mb-1">Seu E-mail de Login *</label>
                                    <input type="email" x-model="form.email" required placeholder="voce@restaurante.com.br"
                                        class="w-full bg-[#06080f] border border-white/[0.1] focus:border-rose-500 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-[11px] font-bold text-slate-400">Crie uma Senha Forte (Mín. 8 caracteres) *</label>
                                    <button type="button" @click="mostrarSenha = !mostrarSenha" class="text-[11px] font-bold text-slate-400 hover:text-rose-400">
                                        <span x-text="mostrarSenha ? 'Ocultar' : 'Mostrar'"></span>
                                    </button>
                                </div>
                                <input :type="mostrarSenha ? 'text' : 'password'" x-model="form.senha" required minlength="8" placeholder="Mínimo de 8 caracteres"
                                    class="w-full bg-[#06080f] border border-white/[0.1] focus:border-rose-500 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none">
                                
                                <!-- Barra Indicadora de Força da Senha -->
                                <div class="mt-2 flex items-center gap-2" x-show="form.senha.length > 0">
                                    <div class="flex-1 grid grid-cols-4 gap-1.5 h-1.5">
                                        <div class="rounded-full transition-all" :class="forcaSenha >= 1 ? 'bg-rose-500' : 'bg-slate-800'"></div>
                                        <div class="rounded-full transition-all" :class="forcaSenha >= 2 ? 'bg-amber-500' : 'bg-slate-800'"></div>
                                        <div class="rounded-full transition-all" :class="forcaSenha >= 3 ? 'bg-emerald-400' : 'bg-slate-800'"></div>
                                        <div class="rounded-full transition-all" :class="forcaSenha >= 4 ? 'bg-emerald-500' : 'bg-slate-800'"></div>
                                    </div>
                                    <span class="text-[10px] font-bold" :class="forcaSenha >= 3 ? 'text-emerald-400' : 'text-amber-400'" x-text="rotuloForcaSenha"></span>
                                </div>
                            </div>
                        </div>

                        <button type="submit" :disabled="carregando"
                            class="w-full bg-gradient-to-r from-rose-600 via-rose-500 to-amber-500 hover:brightness-110 disabled:opacity-50 text-white font-bold py-4 rounded-2xl text-sm shadow-2xl shadow-rose-600/30 transition active:scale-98 flex items-center justify-center gap-2">
                            <template x-if="carregando">
                                <span class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            </template>
                            <span x-text="carregando ? 'Configurando seu restaurante...' : 'Criar Minha Loja e Acessar o Painel Agora →'"></span>
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        function cadastroApp() {
            const params = new URLSearchParams(window.location.search);
            const planoUrl = params.get('plano');
            return {
                carregando: false,
                mostrarSenha: false,
                erro: '',
                form: {
                    plano: ['delivery', 'presencial', 'premium'].includes(planoUrl) ? planoUrl : 'premium',
                    nome_loja: '',
                    slug: '',
                    cnpj_cpf: '',
                    telefone: '',
                    cidade: 'Coronel Fabriciano',
                    uf: 'MG',
                    nome_dono: '',
                    email: '',
                    senha: ''
                },

                get detalhesPlanoSelecionado() {
                    const map = {
                        delivery: {
                            titulo: 'Plano Delivery',
                            preco: 'R$ 99,90',
                            resumo: 'Ideal para operações de entrega e retirada no balcão com 0% de comissão.',
                            itens: [
                                'Cardápio Digital com Order Bump e Cupons',
                                'PIX Instantâneo (Chave Direta ou Mercado Pago)',
                                'Painel Kanban em tempo real com alerta sonoro',
                                'Taxas de entrega por Bairro ou Raio KM'
                            ]
                        },
                        presencial: {
                            titulo: 'Plano Presencial',
                            preco: 'R$ 149,90',
                            resumo: 'Completo para salão, mesas, balcão, cozinha e delivery.',
                            itens: [
                                'Tudo do Plano Delivery incluso',
                                'PDV de Balcão rápido com atalho F2',
                                'QR Code de Mesa, Comandas e Chamar Garçom',
                                'KDS de Cozinha por Estação + Frente de Caixa'
                            ]
                        },
                        premium: {
                            titulo: 'Plano Premium Total',
                            preco: 'R$ 249,90',
                            resumo: 'Automação máxima com Inteligência Artificial no WhatsApp, Cashback e Motoboys.',
                            itens: [
                                'Tudo dos Planos Delivery e Presencial incluso',
                                'Robô de Atendimento IA no WhatsApp 24h',
                                'Campanhas Win-Back e Fidelidade Cashback',
                                'Acerto Diário de Motoboys e Múltiplos Operadores'
                            ]
                        }
                    };
                    return map[this.form.plano] || map.premium;
                },

                get forcaSenha() {
                    const s = this.form.senha || '';
                    if (s.length === 0) return 0;
                    if (s.length < 8) return 1;
                    let score = 2;
                    if (/[0-9]/.test(s) && /[a-zA-Z]/.test(s)) score++;
                    if (/[^a-zA-Z0-9]/.test(s) || s.length >= 10) score++;
                    return Math.min(4, score);
                },

                get rotuloForcaSenha() {
                    const map = { 0: '', 1: 'Muito curta (mín. 8)', 2: 'Razoável', 3: 'Boa', 4: 'Excelente' };
                    return map[this.forcaSenha] || '';
                },

                gerarSlug() {
                    this.form.slug = this.form.nome_loja
                        .toLowerCase()
                        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/(^-|-$)/g, '');
                },

                mascararDocumento() {
                    let v = (this.form.cnpj_cpf || '').replace(/\D/g, '').slice(0, 14);
                    if (v.length <= 11) {
                        v = v.replace(/(\d{3})(\d)/, '$1.$2')
                             .replace(/(\d{3})(\d)/, '$1.$2')
                             .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                    } else {
                        v = v.replace(/^(\d{2})(\d)/, '$1.$2')
                             .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
                             .replace(/\.(\d{3})(\d)/, '.$1/$2')
                             .replace(/(\d{4})(\d)/, '$1-$2');
                    }
                    this.form.cnpj_cpf = v;
                },

                mascararTelefone() {
                    let v = (this.form.telefone || '').replace(/\D/g, '').slice(0, 11);
                    if (v.length > 2) {
                        v = '(' + v.slice(0, 2) + ') ' + v.slice(2);
                    }
                    if (v.length > 10) {
                        v = v.slice(0, 10) + '-' + v.slice(10);
                    }
                    this.form.telefone = v;
                },

                cadastrar() {
                    this.carregando = true;
                    this.erro = '';
                    fetch('/api/auth/cadastro', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify(this.form)
                    })
                    .then(async r => {
                        const data = await r.json();
                        if (!r.ok) throw new Error(data.message || 'Não foi possível concluir o cadastro. Verifique os dados informados.');
                        localStorage.setItem('pl_token', data.access_token || data.token);
                        if (data.usuario) {
                            localStorage.setItem('pl_usuario', JSON.stringify(data.usuario));
                        }
                        window.location.href = '/painel/pedidos';
                    })
                    .catch(e => {
                        this.erro = e.message;
                    })
                    .finally(() => {
                        this.carregando = false;
                    });
                }
            };
        }
    </script>
</body>
</html>
