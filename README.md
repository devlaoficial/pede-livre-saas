# 🍔 PedeLivre OS — Plataforma SaaS Multi-Tenant para Restaurantes, Delivery, QR Code de Mesa & KDS (0% Comissão)

O **PedeLivre OS** é um ecossistema SaaS completo de gestão e vendas diretas para restaurantes, hamburguerias, pizzarias, bares e dark kitchens. Projetado com arquitetura **Multi-Tenant**, **PIX Não-Custodial (0% de comissão)** direto na conta Mercado Pago do lojista e interfaces auto-adaptativas independentes para **Celular (Mobile-First)** e **Computador (Widescreen)**.

---

## 🚀 Principais Diferenciais e Módulos

### 1. 🛒 Cardápio Digital de Alta Conversão ("iFood-Killer UX")
- **Carrossel de Destaques & Promoções (`"🔥 Destaques & Mais Pedidos"`):** exibição automática de produtos em oferta com cálculo de desconto (`-X% OFF`).
- **Barra Progressiva de Frete Grátis:** gatilho visual dinâmico na sacola lateral (PC) e no checkout mobile incentivando o aumento do pedido.
- **Order Bump Inteligente no Checkout (`"⚡ Que tal adicionar para acompanhar?"`):** sugestão de bebidas, acompanhamentos e sobremesas com adição em **1 toque** antes do fechamento.
- **Pizzas Meio a Meio (1 a 4 sabores):** cálculo configurável pelo maior valor ou média proporcional dos sabores.
- **Identidade Visual Dinâmica (`Whitelabel`):** cores (`--cor-loja`), logotipo, capa e link público próprio (`/p/{slug}`).
- **Checkout em 3 Etapas + Busca Automática de CEP (ViaCEP):** cálculo de frete por bairro ou raio KM, validação de cupons e pagamento via **QR Code PIX Banco Central** com atualização em tempo real.
- **Rastreamento do Pedido Ao Vivo (`/p/{slug}/pedidos/{codigo}`):** linha do tempo de eventos protegida por assinatura criptográfica HMAC, alerta vibratório no celular do cliente e botão direto para o WhatsApp da loja.

### 2. 📋 Painel do Lojista 2.0 (Kanban 5 Colunas no PC + App Bar no Celular)
- **Quadro Kanban em Tempo Real & PDV Balcão:** gestão visual das etapas (*Novos*, *Em Preparo*, *Prontos p/ Despacho*, *Em Rota de Entrega* e *Concluídos*), alerta sonoro contínuo e lançamento rápido de pedidos de balcão/telefone.
- **Impressão Térmica 80mm (ESC/POS):** layout dedicado para impressoras térmicas de 80mm (vias de Cozinha e Caixa/Motoboy) com opção de **Auto-Impressão automática ao aceitar o pedido**.
- **Modal Completo de Detalhes do Pedido:** visualização de observações destacadas, histórico de pedidos do cliente, resumo financeiro detalhado, troco e link direto **"🗺️ Abrir no Google Maps ↗"**.
- **Catálogo Avançado & Estoque em 1 Toque:**
  - Preço Promocional (*"De / Por"*).
  - Upload seguro de fotos com conversão automática para **WebP** (`SafeImageUploadService`).
  - Variações dinâmicas de tamanho/opção (*Pequeno, Médio, Grande*, etc.) e grupos de adicionais (*Mín/Máx*).
  - Pausa instantânea de itens esgotados com invalidação imediata de cache Redis.
- **Gestor de Cupons Promocionais:** criação e ativação/pausa de cupons percentuais (`%`) ou fixos (`R$`) com regras de pedido mínimo e limite de uso.

### 3. 🪑 Salão Inteligente, Comandas & KDS Cozinha Modo TV
- **QR Code de Mesa Anti-Fraude:** cada mesa possui um token criptográfico único de 32 caracteres (`qr_token`) que pode ser regenerado a qualquer momento pelo lojista.
- **Alerta Ao Vivo `"🔔 Mesa #X Chamando Garçom"`:** quando o cliente aciona o botão *Chamar Garçom* no celular, o painel do restaurante dispara alerta sonoro e exibe uma faixa de atendimento imediato em 1 clique.
- **Calculadora de Divisão de Conta por Pessoa:** fechamento rápido de mesa com opção de taxa de serviço de 10% do garçom e divisão exata pelo número de pessoas.
- **KDS (Kitchen Display System) por Estação + Modo TV Tela Cheia:** separação automática dos itens para **Chapa**, **Forno**, **Fritadeira**, **Bar** e **Geral**, com cronômetro SLA de atraso e botão Fullscreen para monitores na cozinha.

### 4. 👥 Motor de CRM, Cashback & Recuperação WhatsApp (Win-Back)
- **Cashback Automático:** crédito configurável na carteira do cliente a cada pedido concluído para fidelização na próxima compra.
- **Ranking de Clientes & Disparo 1-Clique no WhatsApp:** identificação automática de clientes fiéis e clientes inativos há mais de 30 dias (*Win-Back*), gerando link direto de recuperação no WhatsApp com cupom personalizado.
- **Mapa de Calor de Horários de Pico:** gráfico visual de concentração de pedidos por faixa de horário para dimensionamento de cozinha e motoboys.

### 5. 👑 Painel Master do Dono do SaaS (`/admin`)
- **Métricas Globais de Receita Recorrente:** acompanhamento de **MRR**, **ARR**, **Ticket Médio SaaS**, **GMV Transacionado** e distribuição por planos (*Delivery*, *Presencial* e *Premium*).
- **Provisionamento Instantâneo de Lojas:** criação de novas lojas já populadas com categorias iniciais, produtos exemplo, áreas de entrega, `Mesa #1` e cupom `BEMVINDO10`.
- **Impersonação Auditada & Régua de Inadimplência:** suporte técnico seguro com retorno à sessão Master e execução automatizada da régua de cobrança (`D+1` a `D+15`).

---

## 🔒 Arquitetura de Segurança & Conformidade LGPD

- **Isolamento Multi-Tenant Estrito (`BelongsToLoja` & `LojaScope`):** todas as consultas e gravações Eloquent são escopadas automaticamente pelo `loja_id` do tenant autenticado.
- **Controle de Acesso Baseado em Papéis (RBAC):** separação rigorosa entre `super_admin` / `suporte` (exclusivos da plataforma SaaS) e papéis operacionais da loja (`dono_loja`, `gerente`, `caixa`, `cozinha`, `garcom`).
- **Criptografia de Dados Sensíveis (PII) + Blind Index:** telefones, e-mails e tokens OAuth são criptografados em repouso (`AES-256-GCM`) e indexados para busca exata via `HMAC-SHA256` (`BlindIndexService`).
- **Assinatura Criptográfica em Rotas Públicas:** o rastreamento de pedidos exige token HMAC válido (`?t=`), prevenindo enumeração de pedidos de terceiros (IDOR).
- **Portal de Conformidade LGPD (`/api/lgpd/*`):** exportação estruturada e anonimização irreversível de dados pessoais sob solicitação do titular.

---

## 🛠️ Stack Tecnológica

- **Backend:** PHP 8.3+ / Laravel 11 (Arquitetura Modular orientada a Domínios em `app/Domain/*`)
- **Banco de Dados:** MySQL 8.0+ / MariaDB (valores monetários armazenados integralmente em **centavos `BIGINT/INT`** para precisão financeira absoluta)
- **Cache, Sessões & Filas:** Redis (`predis/predis`)
- **Frontend:** Blade + Alpine.js 3 + Tailwind CSS (PWA Instalável via `/manifest.json`)
- **Integrações:** Mercado Pago OAuth2 & PIX API (Não-Custodial), Meta WhatsApp Cloud API, ViaCEP

---

## 📂 Estrutura de Domínios (`app/Domain`)

```text
app/
├── Domain/
│   ├── Assinaturas/     # Planos SaaS, Lojas (Tenants), Faturas e Painel Super Admin
│   ├── Autenticacao/    # Login, Cadastro Self-Service, 2FA, RBAC e Impersonação Auditada
│   ├── Bot/             # Webhook Oficial Meta WhatsApp Cloud API
│   ├── Catalogo/        # Categorias, Produtos, Variações, Adicionais, Combos, Upload WebP e Cache
│   ├── Clientes/        # Clientes Criptografados, Endereços e Portal LGPD
│   ├── Entregas/        # Áreas de Entrega (Bairro/Raio KM), Cálculo de Frete e Motoboys
│   ├── Fidelidade/      # Motor de Cashback, Cupons e Relatórios Analíticos / CRM
│   ├── Mesas/           # Mesas com QR Code Anti-Fraude, Chamado de Garçom e Comandas
│   ├── Operacao/        # Horários de Funcionamento, Exceções e Frente de Caixa
│   ├── Pagamentos/      # OAuth2 Mercado Pago e Processamento de Webhooks PIX
│   └── Pedidos/         # Checkout 3 Etapas, Máquina de Estados, Kanban, KDS e Impressão 80mm
└── Support/
    ├── Security/        # BlindIndexService (HMAC-SHA256) e SafeImageUploadService (WebP)
    └── Tenancy/         # BelongsToLoja, LojaScope, LojaContext e Middlewares Multi-Tenant
```

---

## ⚙️ Instalação e Configuração Local

### 1. Clonar o repositório e instalar dependências
```bash
git clone https://github.com/devlaoficial/pede-livre-saas.git
cd pede-livre-saas
composer install
```

### 2. Configurar variáveis de ambiente
```bash
cp .env.example .env
php artisan key:generate
```
Configure no arquivo `.env` as credenciais do banco de dados (`DB_*`), Redis (`REDIS_*`) e a chave de indexação cega (`BLIND_INDEX_KEY`, gerada com `php -r "echo bin2hex(random_bytes(32));"`).

### 3. Executar migrações e popular dados iniciais
```bash
php artisan migrate --seed
php artisan storage:link
```

### 4. Iniciar o servidor de desenvolvimento
```bash
php artisan serve
```

---

## 🧪 Testes Automatizados

O projeto conta com suíte de testes cobrindo isolamento Multi-Tenant, cálculo de pizzas meio a meio, máquina de estados de pedidos, webhooks PIX idempotentes e régua de inadimplência:

```bash
php artisan test
```

---

## 📄 Licença

Propriedade intelectual de **DevLa Oficial / PedeLivre Tecnologia**. Todos os direitos reservados.
