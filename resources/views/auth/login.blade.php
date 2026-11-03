<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-[#06080f] text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#06080f">
    <title>Entrar no Painel — PedeLivre OS</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #06080f;
        }
    </style>
</head>
<body class="min-h-full antialiased selection:bg-rose-500 selection:text-white relative overflow-x-hidden flex flex-col justify-center" x-data="loginApp()" x-init="initLogin()">

    <!-- Luzes de Fundo Suaves -->
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden">
        <div class="absolute -top-32 -left-32 w-[480px] h-[480px] bg-rose-600/15 rounded-full blur-[130px]"></div>
        <div class="absolute bottom-0 right-0 w-[450px] h-[450px] bg-amber-500/10 rounded-full blur-[130px]"></div>
    </div>

    <div class="relative z-10 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 bg-[#0b0f1c]/90 backdrop-blur-2xl border border-white/[0.08] rounded-[32px] shadow-2xl overflow-hidden">
            
            <!-- ==================== COLUNA ESQUERDA: PAINEL EXECUTIVO DA MARCA (Desktop) ==================== -->
            <div class="hidden lg:flex lg:col-span-5 bg-gradient-to-br from-[#12182b] via-[#0d1221] to-[#090c16] p-10 border-r border-white/[0.07] flex-col justify-between relative overflow-hidden">
                <div class="absolute -right-20 -top-20 w-64 h-64 bg-rose-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="space-y-8 relative z-10">
                    <!-- Logo -->
                    <a href="/" class="inline-flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-rose-500 via-rose-600 to-amber-500 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-rose-600/30">
                            P
                        </div>
                        <div>
                            <span class="text-xl font-black tracking-tight text-white">Pede<span class="text-rose-500">Livre</span></span>
                            <span class="block text-[10px] font-bold uppercase tracking-widest text-amber-400">Central de Operações</span>
                        </div>
                    </a>

                    <div class="space-y-3">
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-extrabold uppercase tracking-wider px-3 py-1 rounded-full bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Ambiente de Produção Seguro
                        </span>
                        <h2 class="text-2xl font-black text-white leading-snug">
                            Gerencie pedidos, cozinha, caixa e WhatsApp em uma única tela.
                        </h2>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Acesse seu painel operacional em tempo real com alertas sonoros, emissão de PIX sem comissão e controle completo da sua loja.
                        </p>
                    </div>

                    <!-- Mini Destaques Operacionais -->
                    <div class="space-y-3 pt-2">
                        <div class="bg-white/[0.03] border border-white/[0.06] rounded-2xl p-3.5 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-rose-500/15 text-rose-400 flex items-center justify-center font-bold text-base shrink-0">📋</div>
                            <div>
                                <div class="text-xs font-extrabold text-white">Kanban &amp; KDS em Tempo Real</div>
                                <div class="text-[11px] text-slate-400">Sincronização instantânea entre salão, balcão e cozinha</div>
                            </div>
                        </div>

                        <div class="bg-white/[0.03] border border-white/[0.06] rounded-2xl p-3.5 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-500/15 text-emerald-400 flex items-center justify-center font-bold text-base shrink-0">⚡</div>
                            <div>
                                <div class="text-xs font-extrabold text-white">PIX 0% Taxa Direto na Conta</div>
                                <div class="text-[11px] text-slate-400">Chave PIX Direta BCB ou Mercado Pago Dinâmico</div>
                            </div>
                        </div>

                        <div class="bg-white/[0.03] border border-white/[0.06] rounded-2xl p-3.5 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-amber-500/15 text-amber-400 flex items-center justify-center font-bold text-base shrink-0">💬</div>
                            <div>
                                <div class="text-xs font-extrabold text-white">Atendimento WhatsApp IA</div>
                                <div class="text-[11px] text-slate-400">Avisos automáticos de status e campanhas Win-Back</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rodapé de Segurança -->
                <div class="pt-6 border-t border-white/[0.07] flex items-center justify-between text-[11px] text-slate-400 relative z-10">
                    <span class="flex items-center gap-1.5 font-semibold text-slate-300">
                        <span>🔒</span> Criptografia AES-256-GCM
                    </span>
                    <span>Isolamento Multi-Tenant</span>
                </div>
            </div>

            <!-- ==================== COLUNA DIREITA: FORMULÁRIO DE AUTENTICAÇÃO ==================== -->
            <div class="lg:col-span-7 p-6 sm:p-10 lg:p-12 flex flex-col justify-center">
                
                <!-- Topo Mobile -->
                <div class="lg:hidden flex items-center justify-between mb-6 pb-4 border-b border-white/[0.07]">
                    <a href="/" class="inline-flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-rose-500 to-amber-500 flex items-center justify-center text-white font-black text-sm">P</div>
                        <span class="text-lg font-black text-white">Pede<span class="text-rose-500">Livre</span></span>
                    </a>
                    <span class="text-[10px] font-bold uppercase px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/25">🔒 Acesso Seguro</span>
                </div>

                <div class="max-w-md w-full mx-auto space-y-6">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight" x-text="etapa2fa ? 'Verificação em 2 Etapas' : 'Bem-vindo de volta'"></h1>
                        <p class="mt-1.5 text-xs sm:text-sm text-slate-400" x-text="etapa2fa ? 'Digite o código de 6 dígitos do seu aplicativo autenticador.' : 'Informe suas credenciais para acessar o painel da sua loja.'"></p>
                    </div>

                    <!-- Alerta de Erro -->
                    <template x-if="erro">
                        <div class="bg-rose-950/70 border border-rose-500/40 text-rose-200 text-xs font-semibold rounded-2xl p-4 flex items-start gap-2.5 shadow-lg">
                            <span class="text-rose-400 text-sm shrink-0">⚠️</span>
                            <span class="flex-1" x-text="erro"></span>
                            <button type="button" @click="erro = ''" class="text-rose-400 hover:text-white font-bold">✕</button>
                        </div>
                    </template>

                    <!-- FORMULÁRIO 1: E-MAIL E SENHA -->
                    <template x-if="!etapa2fa">
                        <form @submit.prevent="entrar()" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">E-mail de Acesso</label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-3.5 text-slate-500 text-sm">✉️</span>
                                    <input type="email" x-model="email" required placeholder="voce@seurestaurante.com.br" autocomplete="username"
                                        class="w-full bg-[#06080f] border border-white/[0.1] focus:border-rose-500 rounded-2xl pl-10 pr-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none transition">
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">Sua Senha</label>
                                    <button type="button" @click="mostrarSenha = !mostrarSenha" class="text-[11px] font-bold text-slate-400 hover:text-rose-400 transition">
                                        <span x-text="mostrarSenha ? '🙈 Ocultar senha' : '👁️ Mostrar senha'"></span>
                                    </button>
                                </div>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-3.5 text-slate-500 text-sm">🔑</span>
                                    <input :type="mostrarSenha ? 'text' : 'password'" x-model="senha" required placeholder="••••••••" autocomplete="current-password"
                                        class="w-full bg-[#06080f] border border-white/[0.1] focus:border-rose-500 rounded-2xl pl-10 pr-12 py-3 text-sm text-white placeholder-slate-500 focus:outline-none transition">
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <label class="inline-flex items-center gap-2 text-xs text-slate-300 cursor-pointer select-none">
                                    <input type="checkbox" x-model="lembrarEmail" class="rounded bg-[#06080f] border-white/[0.15] text-rose-600 focus:ring-rose-500">
                                    <span>Lembrar meu e-mail neste dispositivo</span>
                                </label>
                            </div>

                            <button type="submit" :disabled="carregando"
                                class="w-full bg-gradient-to-r from-rose-600 to-rose-500 hover:from-rose-500 hover:to-rose-400 disabled:opacity-50 text-white font-black py-3.5 rounded-2xl text-sm shadow-xl shadow-rose-600/30 transition active:scale-98 flex items-center justify-center gap-2">
                                <template x-if="carregando">
                                    <span class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                </template>
                                <span x-text="carregando ? 'Autenticando com segurança...' : 'Acessar Painel Operacional →'"></span>
                            </button>
                        </form>
                    </template>

                    <!-- FORMULÁRIO 2: VERIFICAÇÃO 2FA (SE ATIVO NA CONTA) -->
                    <template x-if="etapa2fa">
                        <form @submit.prevent="verificar2fa()" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">Código Autenticador (6 dígitos)</label>
                                <input type="text" maxlength="6" x-model="codigo2fa" required placeholder="000000"
                                    class="w-full bg-[#06080f] border border-white/[0.1] focus:border-rose-500 rounded-2xl px-4 py-3.5 text-center font-mono text-xl tracking-[0.4em] font-black text-emerald-400 focus:outline-none">
                            </div>
                            <div class="flex gap-2">
                                <button type="button" @click="etapa2fa = false; tempToken = ''"
                                    class="w-1/3 bg-white/[0.06] hover:bg-white/[0.1] text-slate-300 font-bold py-3.5 rounded-2xl text-xs transition">
                                    Voltar
                                </button>
                                <button type="submit" :disabled="carregando"
                                    class="w-2/3 bg-emerald-600 hover:bg-emerald-500 text-white font-black py-3.5 rounded-2xl text-sm shadow-xl transition">
                                    <span x-text="carregando ? 'Verificando...' : 'Confirmar Código →'"></span>
                                </button>
                            </div>
                        </form>
                    </template>

                    <!-- Links Inferiores -->
                    <div class="pt-5 border-t border-white/[0.08] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
                        <a href="/" class="hover:text-white font-semibold transition">← Voltar para a página inicial</a>
                        <a href="/cadastro?plano=premium" class="text-rose-400 hover:text-rose-300 font-extrabold transition">
                            Ainda não tem conta? Testar 14 dias grátis →
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <script>
        function loginApp() {
            return {
                email: '',
                senha: '',
                mostrarSenha: false,
                lembrarEmail: true,
                carregando: false,
                erro: '',
                etapa2fa: false,
                tempToken: '',
                codigo2fa: '',

                initLogin() {
                    try {
                        const savedEmail = localStorage.getItem('pl_saved_email');
                        if (savedEmail) {
                            this.email = savedEmail;
                        }
                    } catch (e) {}
                },

                entrar() {
                    this.carregando = true;
                    this.erro = '';

                    try {
                        if (this.lembrarEmail && this.email.trim()) {
                            localStorage.setItem('pl_saved_email', this.email.trim());
                        } else {
                            localStorage.removeItem('pl_saved_email');
                        }
                    } catch (e) {}

                    fetch('/api/auth/login', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({
                            email: this.email.trim(),
                            senha: this.senha,
                            device_name: 'painel-web'
                        })
                    })
                    .then(async r => {
                        const data = await r.json();
                        if (!r.ok) throw new Error(data.message || 'E-mail ou senha incorretos.');

                        if (data.requires_2fa && data.temp_token) {
                            this.tempToken = data.temp_token;
                            this.etapa2fa = true;
                            return;
                        }

                        this.finalizarSessao(data);
                    })
                    .catch(e => {
                        this.erro = e.message;
                    })
                    .finally(() => {
                        this.carregando = false;
                    });
                },

                verificar2fa() {
                    if (!this.codigo2fa.trim()) return;
                    this.carregando = true;
                    this.erro = '';

                    fetch('/api/auth/2fa/verificar', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'Authorization': `Bearer ${this.tempToken}`
                        },
                        body: JSON.stringify({
                            code: this.codigo2fa.trim(),
                            device_name: 'painel-web'
                        })
                    })
                    .then(async r => {
                        const data = await r.json();
                        if (!r.ok) throw new Error(data.message || 'Código 2FA inválido ou expirado.');
                        this.finalizarSessao(data);
                    })
                    .catch(e => {
                        this.erro = e.message;
                    })
                    .finally(() => {
                        this.carregando = false;
                    });
                },

                finalizarSessao(data) {
                    localStorage.setItem('pl_token', data.access_token || data.token);
                    if (data.usuario) {
                        localStorage.setItem('pl_usuario', JSON.stringify(data.usuario));
                    }
                    const papel = data.usuario?.papel || '';
                    if (papel === 'super_admin' || papel === 'suporte') {
                        window.location.href = '/admin';
                    } else {
                        window.location.href = '/painel/pedidos';
                    }
                }
            };
        }
    </script>
</body>
</html>
