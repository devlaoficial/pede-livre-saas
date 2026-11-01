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
