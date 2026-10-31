<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Criar Restaurante Grátis (14 Dias) — PedeLivre</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-full flex flex-col justify-center py-10 px-4 sm:px-6 lg:px-8 font-sans antialiased" x-data="cadastroApp()">
    <div class="sm:mx-auto sm:w-full sm:max-w-lg text-center">
        <a href="/" class="inline-flex items-center gap-2">
            <span class="text-2xl font-black tracking-tight text-white">Pede<span class="text-rose-500">Livre</span></span>
            <span class="text-[10px] uppercase tracking-widest bg-rose-500/15 text-rose-400 border border-rose-500/30 px-2.5 py-1 rounded-full font-bold">14 Dias Grátis</span>
        </a>
        <h1 class="mt-4 text-2xl font-extrabold text-white">Coloque seu cardápio no ar agora</h1>
        <p class="mt-1 text-xs text-slate-400">0% de comissão • PIX direto na sua conta Mercado Pago • Sem cartão de crédito</p>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-lg">
        <div class="bg-slate-900 py-7 px-6 shadow-2xl rounded-3xl border border-slate-800 sm:px-10 space-y-5">
            <template x-if="erro">
                <div class="bg-rose-950/60 border border-rose-500/40 text-rose-300 text-xs font-semibold rounded-xl p-3.5" x-text="erro"></div>
            </template>

            <form @submit.prevent="cadastrar()" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Plano Escolhido</label>
                    <select x-model="form.plano" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white">
                        <option value="delivery">Plano Delivery — R$ 99,90/mês (após 14 dias)</option>
                        <option value="presencial">Plano Presencial — R$ 149,90/mês (após 14 dias)</option>
                        <option value="premium">Plano Premium Total — R$ 249,90/mês (após 14 dias)</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Nome do Restaurante</label>
                        <input type="text" x-model="form.nome_loja" @input="gerarSlug()" required placeholder="Ex: Pizzaria Don Vito" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Link do Cardápio (slug)</label>
                        <input type="text" x-model="form.slug" required placeholder="pizzaria-don-vito" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-emerald-400 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">CNPJ ou CPF</label>
                        <input type="text" x-model="form.cnpj_cpf" required placeholder="00.000.000/0001-00" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">WhatsApp da Loja</label>
                        <input type="text" x-model="form.telefone" required placeholder="31999998888" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-800">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Seu Nome</label>
                        <input type="text" x-model="form.nome_dono" required placeholder="João Silva" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Seu E-mail de Acesso</label>
                        <input type="email" x-model="form.email" required placeholder="voce@restaurante.com.br" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Crie sua Senha</label>
                    <input type="password" x-model="form.senha" required minlength="8" placeholder="Mínimo 8 caracteres" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-sm text-white">
                </div>

                <button type="submit" :disabled="carregando" class="w-full bg-rose-600 hover:bg-rose-500 disabled:opacity-50 text-white font-extrabold py-3.5 rounded-xl text-sm shadow-lg shadow-rose-600/30 transition">
                    <span x-text="carregando ? 'Criando seu restaurante...' : 'Criar Meu Cardápio Grátis →'"></span>
                </button>
            </form>

            <div class="text-center text-xs text-slate-400 pt-2 border-t border-slate-800 flex justify-between items-center">
                <a href="/login" class="hover:text-white font-semibold">Já tenho conta (Entrar)</a>
                <a href="/" class="text-slate-400 hover:text-white font-semibold">← Voltar ao Início</a>
            </div>
        </div>
    </div>

    <script>
        function cadastroApp() {
            const params = new URLSearchParams(window.location.search);
            return {
                carregando: false,
                erro: '',
                form: {
                    plano: params.get('plano') || 'premium',
                    nome_loja: '',
                    slug: '',
                    cnpj_cpf: '',
                    telefone: '',
                    nome_dono: '',
                    email: '',
                    senha: ''
                },
                gerarSlug() {
                    this.form.slug = this.form.nome_loja
                        .toLowerCase()
                        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/(^-|-$)/g, '');
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
                        if (!r.ok) throw new Error(data.message || 'Não foi possível concluir o cadastro.');
                        localStorage.setItem('pl_token', data.access_token || data.token);
                        window.location.href = '/painel/pedidos';
                    })
                    .catch(e => { this.erro = e.message; })
                    .finally(() => { this.carregando = false; });
                }
            };
        }
    </script>
</body>
</html>
