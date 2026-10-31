<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar no Painel — PedeLivre</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-full flex flex-col justify-center py-10 px-4 sm:px-6 lg:px-8 font-sans antialiased" x-data="loginApp()">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <a href="/" class="inline-flex items-center gap-3">
            <span class="text-2xl font-black tracking-tight text-white">Pede<span class="text-rose-500">Livre</span></span>
            <span class="text-[10px] uppercase tracking-widest bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-2.5 py-1 rounded-full font-bold">Acesso Seguro</span>
        </a>
        <h1 class="mt-6 text-2xl font-extrabold text-white">Acesse sua operação</h1>
        <p class="mt-2 text-sm text-slate-400">Entre com seu e-mail e senha cadastrados</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-slate-900 py-8 px-6 shadow-2xl rounded-3xl border border-slate-800 sm:px-10 space-y-6">
            <template x-if="erro">
                <div class="bg-rose-950/60 border border-rose-500/40 text-rose-300 text-xs font-semibold rounded-xl p-3.5" x-text="erro"></div>
            </template>

            <form @submit.prevent="entrar()" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">E-mail</label>
                    <input type="email" x-model="email" required placeholder="seu@email.com.br" autocomplete="username" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-rose-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Senha</label>
                    <input type="password" x-model="senha" required placeholder="••••••••" autocomplete="current-password" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-rose-500">
                </div>

                <button type="submit" :disabled="carregando" class="w-full bg-rose-600 hover:bg-rose-500 disabled:opacity-50 text-white font-extrabold py-3.5 rounded-xl text-sm shadow-lg shadow-rose-600/30 transition">
                    <span x-text="carregando ? 'Autenticando...' : 'Entrar no Painel →'"></span>
                </button>
            </form>

            <div class="pt-4 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
                <a href="/" class="hover:text-white font-semibold">← Voltar ao Início</a>
                <a href="/cadastro?plano=premium" class="text-rose-400 hover:underline font-bold">Criar nova loja (14 dias grátis) →</a>
            </div>
        </div>
    </div>

    <script>
        function loginApp() {
            return {
                email: '',
                senha: '',
                carregando: false,
                erro: '',
                entrar() {
                    this.carregando = true;
                    this.erro = '';
                    fetch('/api/auth/login', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ email: this.email, senha: this.senha, device_name: 'painel-web' })
                    })
                    .then(async r => {
                        const data = await r.json();
                        if (!r.ok) throw new Error(data.message || 'E-mail ou senha incorretos.');
                        localStorage.setItem('pl_token', data.access_token || data.token);
                        if (data.usuario) localStorage.setItem('pl_usuario', JSON.stringify(data.usuario));
                        const papel = data.usuario?.papel || '';
                        if (papel === 'super_admin' || papel === 'suporte') {
                            window.location.href = '/admin';
                        } else {
                            window.location.href = '/painel/pedidos';
                        }
                    })
                    .catch(e => { this.erro = e.message; })
                    .finally(() => { this.carregando = false; });
                }
            };
        }
    </script>
</body>
</html>
