<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Assinaturas\Models\Plano;
use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Catalogo\Models\Categoria;
use App\Domain\Catalogo\Models\Combo;
use App\Domain\Catalogo\Models\ComboItem;
use App\Domain\Catalogo\Models\GrupoAdicional;
use App\Domain\Catalogo\Models\OpcaoAdicional;
use App\Domain\Catalogo\Models\Produto;
use App\Domain\Catalogo\Models\ProdutoVariacao;
use App\Domain\Entregas\Models\AreaEntrega;
use App\Domain\Entregas\Models\Entregador;
use App\Domain\Mesas\Models\Mesa;
use App\Domain\Operacao\Models\Horario;
use App\Domain\Pedidos\Models\Cupom;
use App\Support\Security\BlindIndexService;
use App\Support\Tenancy\LojaContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $blindIndex = new BlindIndexService((string) config('app.blind_index_key', 'pilot-blind-index-key-secret-32-bytes!'));

        // 1. Seed the 3 Subscription Plans with exact feature flags
        $planoDelivery = Plano::create([
            'nome' => 'delivery',
            'preco_centavos' => 9990,
            'limite_usuarios' => 2,
            'recursos_json' => [
                'cardapio_online' => true,
                'canal_delivery_retirada' => true,
                'canal_mesa_balcao' => false,
                'frete_bairro_raio' => true,
                'gestao_entregadores' => true,
                'kds' => 'basic',
                'comandas_divisao' => false,
                'pix_automatico' => true,
                'cupons' => true,
                'estoque_financeiro' => false,
                'crm_cashback' => false,
                'bot_whatsapp' => false,
            ],
        ]);

        $planoPresencial = Plano::create([
            'nome' => 'presencial',
            'preco_centavos' => 14990,
            'limite_usuarios' => 5,
            'recursos_json' => [
                'cardapio_online' => true,
                'canal_delivery_retirada' => false,
                'canal_mesa_balcao' => true,
                'frete_bairro_raio' => false,
                'gestao_entregadores' => false,
                'kds' => 'full',
                'comandas_divisao' => true,
                'pix_automatico' => true,
                'cupons' => true,
                'estoque_financeiro' => 'basic',
                'crm_cashback' => false,
                'bot_whatsapp' => false,
            ],
        ]);

        $planoPremium = Plano::create([
            'nome' => 'premium',
            'preco_centavos' => 24990,
            'limite_usuarios' => 9999,
            'recursos_json' => [
                'cardapio_online' => true,
                'canal_delivery_retirada' => true,
                'canal_mesa_balcao' => true,
                'frete_bairro_raio' => true,
                'gestao_entregadores' => true,
                'kds' => 'full',
                'comandas_divisao' => true,
                'pix_automatico' => true,
                'cupons' => true,
                'estoque_financeiro' => 'full',
                'crm_cashback' => true,
                'bot_whatsapp' => true,
            ],
        ]);

        // 2. Seed Pilot Store: "Brasa & Sabor Artesanal"
        $rawCnpj = '42.123.456/0001-89';
        $loja = Loja::create([
            'slug' => 'brasa-artesanal',
            'nome' => 'Brasa & Sabor Artesanal',
            'cnpj_cpf' => $rawCnpj,
            'cnpj_cpf_bidx' => $blindIndex->generateForDocument($rawCnpj),
            'telefone_whatsapp' => '31987654321',
            'logo' => 'lojas/brasa-artesanal/logo.webp',
            'capa' => 'lojas/brasa-artesanal/capa.webp',
            'cor_primaria' => '#DC2626',
            'endereco' => 'Av. Alberto Carazzai, 1200',
            'cidade' => 'Coronel Fabriciano',
            'uf' => 'MG',
            'lat' => -19.5186,
            'lng' => -42.6306,
            'plano_id' => $planoPremium->id,
            'status' => 'ativa',
            'config_json' => [
                'timezone' => 'America/Sao_Paulo',
                'estrategia_pizza_meio_a_meio' => 'maior_preco',
                'tempo_medio_preparo_min' => 35,
                'aceita_pedidos' => true,
            ],
        ]);

        app(LojaContext::class)->setLoja($loja);

        // 3. Seed Super Admin (SaaS Owner) & Pilot Store Owner
        $superAdminEmail = 'admin@pedelivre.com.br';
        Usuario::withoutGlobalScopes()->create([
            'loja_id' => null,
            'nome' => 'CEO — Dono do SaaS PedeLivre',
            'email' => $superAdminEmail,
            'email_bidx' => $blindIndex->generateForEmail($superAdminEmail),
            'telefone' => '31999990000',
            'senha_hash' => Hash::make('AdminMaster2026!'),
            'papel' => 'super_admin',
            'ativo' => true,
        ]);

        $rawEmail = 'contato@brasaesabor.com.br';
        Usuario::create([
            'loja_id' => $loja->id,
            'nome' => 'Rodrigo Alvarenga (Dono)',
            'email' => $rawEmail,
            'email_bidx' => $blindIndex->generateForEmail($rawEmail),
            'telefone' => '31987654321',
            'senha_hash' => Hash::make('BrasaMaster2026!'),
            'papel' => 'dono_loja',
            'ativo' => true,
        ]);

        // 4. Seed Operating Hours (Multi-shifts: Lunch & Dinner for delivery and mesa)
        for ($dia = 0; $dia <= 6; $dia++) {
            if ($dia === 1) {
                continue;
            }

            Horario::create([
                'loja_id' => $loja->id,
                'canal' => 'delivery',
                'dia_semana' => $dia,
                'abre' => '11:00:00',
                'fecha' => '14:30:00',
            ]);
            Horario::create([
                'loja_id' => $loja->id,
                'canal' => 'mesa',
                'dia_semana' => $dia,
                'abre' => '11:00:00',
                'fecha' => '14:30:00',
            ]);

            $fechaJantar = ($dia === 5 || $dia === 6) ? '00:30:00' : '23:30:00';
            Horario::create([
                'loja_id' => $loja->id,
                'canal' => 'delivery',
                'dia_semana' => $dia,
                'abre' => '18:30:00',
                'fecha' => $fechaJantar,
            ]);
            Horario::create([
                'loja_id' => $loja->id,
                'canal' => 'mesa',
                'dia_semana' => $dia,
                'abre' => '18:30:00',
                'fecha' => $fechaJantar,
            ]);
        }

        // 5. Seed Delivery Zones (Coronel Fabriciano, MG)
        AreaEntrega::create([
            'loja_id' => $loja->id,
            'tipo' => 'raio',
            'raio_km_de' => 0.0,
            'raio_km_ate' => 3.0,
            'taxa_centavos' => 500, // R$ 5,00
            'pedido_minimo_centavos' => 0,
            'tempo_estimado_min' => 30,
            'frete_gratis_acima_centavos' => 8000, // Grátis acima de R$ 80
            'ativa' => true,
        ]);
        AreaEntrega::create([
            'loja_id' => $loja->id,
            'tipo' => 'raio',
            'raio_km_de' => 3.0,
            'raio_km_ate' => 25.0,
            'taxa_centavos' => 900, // R$ 9,00
            'pedido_minimo_centavos' => 0,
            'tempo_estimado_min' => 45,
            'frete_gratis_acima_centavos' => 12000,
            'ativa' => true,
        ]);
        AreaEntrega::create([
            'loja_id' => $loja->id,
            'tipo' => 'bairro',
            'nome_bairro' => 'Centro',
            'taxa_centavos' => 400, // R$ 4,00
            'pedido_minimo_centavos' => 0,
            'tempo_estimado_min' => 25,
            'ativa' => true,
        ]);

        // 6. Seed Tables (Mesas 1 to 10 with 32-char cryptographically random tokens)
        for ($m = 1; $m <= 10; $m++) {
            Mesa::create([
                'loja_id' => $loja->id,
                'numero' => $m,
                'nome' => $m <= 4 ? "Varanda 0{$m}" : "Salão Nobre 0{$m}",
                'qr_token' => Str::random(32),
                'capacidade' => $m % 2 === 0 ? 4 : 2,
                'status' => 'livre',
            ]);
        }

        // 7. Seed Couriers (Entregadores)
        $telEntregador1 = '31988881111';
        Entregador::create([
            'loja_id' => $loja->id,
            'nome' => 'Carlos "Relâmpago" Silva',
            'telefone' => $telEntregador1,
            'telefone_bidx' => $blindIndex->generateForPhone($telEntregador1),
            'tipo' => 'proprio',
            'valor_por_entrega_centavos' => 700,
            'ativo' => true,
        ]);

        $telEntregador2 = '31988882222';
        Entregador::create([
            'loja_id' => $loja->id,
            'nome' => 'Marcos "Entrega Express"',
            'telefone' => $telEntregador2,
            'telefone_bidx' => $blindIndex->generateForPhone($telEntregador2),
            'tipo' => 'freelancer',
            'valor_por_entrega_centavos' => 850,
            'ativo' => true,
        ]);

        // 8. Seed Welcome Coupon
        Cupom::create([
            'loja_id' => $loja->id,
            'codigo' => 'BEMVINDO10',
            'tipo' => 'percentual',
            'valor' => 10,
            'minimo_centavos' => 3000,
            'validade' => now()->addYear(),
            'limite_uso_total' => 1000,
            'limite_por_cliente' => 3,
            'usos' => 0,
            'canais' => ['delivery', 'retirada', 'mesa', 'balcao'],
            'ativo' => true,
        ]);

        $todosCanais = ['delivery', 'retirada', 'mesa', 'balcao'];

        // 9. Seed Categories
        $catBurgers = Categoria::create([
            'loja_id' => $loja->id,
            'nome' => 'Hambúrgueres Artesanais',
            'descricao' => 'Carnes nobres grelhadas no fogo forte com pão brioche amanteigado',
            'ordem' => 1,
            'ativa' => true,
            'canais' => $todosCanais,
        ]);

        $catPizzas = Categoria::create([
            'loja_id' => $loja->id,
            'nome' => 'Pizzas Especiais',
            'descricao' => 'Massa de fermentação natural de 48h assada em forno a lenha',
            'ordem' => 2,
            'ativa' => true,
            'canais' => $todosCanais,
        ]);

        $catAcomp = Categoria::create([
            'loja_id' => $loja->id,
            'nome' => 'Acompanhamentos & Porções',
            'descricao' => 'Porções generosas e crocantes para petiscar e compartilhar',
            'ordem' => 3,
            'ativa' => true,
            'canais' => $todosCanais,
        ]);

        $catBebidas = Categoria::create([
            'loja_id' => $loja->id,
            'nome' => 'Bebidas Geladas',
            'descricao' => 'Refrigerantes, sucos naturais feitos na hora e cervejas long neck',
            'ordem' => 4,
            'ativa' => true,
            'canais' => $todosCanais,
        ]);

        $catCombos = Categoria::create([
            'loja_id' => $loja->id,
            'nome' => 'Combos Promocionais',
            'descricao' => 'Economize pedindo nossos combos completos com bebida e fritas',
            'ordem' => 5,
            'ativa' => true,
            'canais' => $todosCanais,
        ]);

        // 10. Addon Groups
        $grpPonto = GrupoAdicional::create([
            'loja_id' => $loja->id,
            'nome' => 'Ponto da Carne',
            'minimo' => 1,
            'maximo' => 1,
            'obrigatorio' => true,
            'tipo' => 'escolha_unica',
        ]);
        OpcaoAdicional::create(['loja_id' => $loja->id, 'grupo_id' => $grpPonto->id, 'nome' => 'Ao Ponto para Mal (Suculento)', 'preco_centavos' => 0]);
        OpcaoAdicional::create(['loja_id' => $loja->id, 'grupo_id' => $grpPonto->id, 'nome' => 'Ao Ponto da Casa (Recomendado)', 'preco_centavos' => 0]);
        OpcaoAdicional::create(['loja_id' => $loja->id, 'grupo_id' => $grpPonto->id, 'nome' => 'Bem Passado', 'preco_centavos' => 0]);

        $grpTurbinar = GrupoAdicional::create([
            'loja_id' => $loja->id,
            'nome' => 'Turbine seu Hambúrguer',
            'minimo' => 0,
            'maximo' => 5,
            'obrigatorio' => false,
            'tipo' => 'adicional',
        ]);
        OpcaoAdicional::create(['loja_id' => $loja->id, 'grupo_id' => $grpTurbinar->id, 'nome' => 'Bacon Crocante Extra', 'preco_centavos' => 500]);
        OpcaoAdicional::create(['loja_id' => $loja->id, 'grupo_id' => $grpTurbinar->id, 'nome' => 'Queijo Cheddar Inglês Duplo', 'preco_centavos' => 600]);
        OpcaoAdicional::create(['loja_id' => $loja->id, 'grupo_id' => $grpTurbinar->id, 'nome' => 'Cebola Caramelizada no Melado', 'preco_centavos' => 400]);
        OpcaoAdicional::create(['loja_id' => $loja->id, 'grupo_id' => $grpTurbinar->id, 'nome' => 'Burger Angus Extra 180g', 'preco_centavos' => 1400]);

        $grpSaboresPizza = GrupoAdicional::create([
            'loja_id' => $loja->id,
            'nome' => 'Escolha os Sabores (1 ou 2 Sabores)',
            'minimo' => 1,
            'maximo' => 2,
            'obrigatorio' => true,
            'tipo' => 'sabor_meio_a_meio',
        ]);
        OpcaoAdicional::create(['loja_id' => $loja->id, 'grupo_id' => $grpSaboresPizza->id, 'nome' => '1/2 Napolitana Tradicional', 'preco_centavos' => 0]);
        OpcaoAdicional::create(['loja_id' => $loja->id, 'grupo_id' => $grpSaboresPizza->id, 'nome' => '1/2 Calabresa Defumada com Cebola Roxa', 'preco_centavos' => 0]);
        OpcaoAdicional::create(['loja_id' => $loja->id, 'grupo_id' => $grpSaboresPizza->id, 'nome' => '1/2 Frango com Catupiry Original', 'preco_centavos' => 400]);
        OpcaoAdicional::create(['loja_id' => $loja->id, 'grupo_id' => $grpSaboresPizza->id, 'nome' => '1/2 Quatro Queijos Especial', 'preco_centavos' => 600]);

        // 11. Products - Burgers
        $burger1 = Produto::create([
            'loja_id' => $loja->id,
            'categoria_id' => $catBurgers->id,
            'nome' => 'Brasa Bacon Burger Especial',
            'descricao' => 'Pão brioche selado na manteiga, blend angus 180g na brasa, fatias de bacon crocante, cheddar inglês e maionese defumada.',
            'preco_centavos' => 3890,
            'preco_promocional_centavos' => 3490,
            'imagem' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=600&q=80',
            'ativo' => true,
            'esgotado' => false,
            'controla_estoque' => true,
            'estoque_qtd' => 50,
            'canais' => $todosCanais,
            'ordem' => 1,
            'tempo_preparo_min' => 20,
            'estacao_kds' => 'chapa',
            'alergenos' => ['gluten', 'lactose'],
        ]);
        $burger1->gruposAdicionais()->attach([$grpPonto->id => ['ordem' => 1], $grpTurbinar->id => ['ordem' => 2]]);

        $burger2 = Produto::create([
            'loja_id' => $loja->id,
            'categoria_id' => $catBurgers->id,
            'nome' => 'Duplo Smash Cheddar & Cebola Crispy',
            'descricao' => 'Dois smash burgers angus 100g com crosta caramelizada, quádruplo cheddar cremoso, cebola crispy dourada e pão australiano.',
            'preco_centavos' => 4490,
            'preco_promocional_centavos' => 3990,
            'imagem' => 'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?auto=format&fit=crop&w=600&q=80',
            'ativo' => true,
            'esgotado' => false,
            'canais' => $todosCanais,
            'ordem' => 2,
            'tempo_preparo_min' => 20,
            'estacao_kds' => 'chapa',
        ]);
        $burger2->gruposAdicionais()->attach([$grpTurbinar->id => ['ordem' => 1]]);

        $burger3 = Produto::create([
            'loja_id' => $loja->id,
            'categoria_id' => $catBurgers->id,
            'nome' => 'Costela BBQ Defumada 200g',
            'descricao' => 'Hambúrguer de costela bovina 200g assado no fogo forte, queijo prato derretido, onion rings crocantes e barbecue artesanal.',
            'preco_centavos' => 4290,
            'imagem' => 'https://images.unsplash.com/photo-1553979459-d2229ba7433b?auto=format&fit=crop&w=600&q=80',
            'ativo' => true,
            'esgotado' => false,
            'canais' => $todosCanais,
            'ordem' => 3,
            'tempo_preparo_min' => 25,
            'estacao_kds' => 'chapa',
        ]);
        $burger3->gruposAdicionais()->attach([$grpPonto->id => ['ordem' => 1], $grpTurbinar->id => ['ordem' => 2]]);

        Produto::create([
            'loja_id' => $loja->id,
            'categoria_id' => $catBurgers->id,
            'nome' => 'Chicken Crispy Supreme',
            'descricao' => 'Sobrecoxa de frango marinada e empanada super crocante, maionese de alho assado, picles artesanal e alface americana no brioche.',
            'preco_centavos' => 3290,
            'preco_promocional_centavos' => 2990,
            'imagem' => 'https://images.unsplash.com/photo-1606755962773-d324e0a13086?auto=format&fit=crop&w=600&q=80',
            'ativo' => true,
            'esgotado' => false,
            'canais' => $todosCanais,
            'ordem' => 4,
            'tempo_preparo_min' => 18,
            'estacao_kds' => 'fritadeira',
        ]);

        // 12. Products - Pizzas
        $pizza1 = Produto::create([
            'loja_id' => $loja->id,
            'categoria_id' => $catPizzas->id,
            'nome' => 'Pizza Tradicional Napolitana',
            'descricao' => 'Massa italiana crocante de 48h de fermentação, molho de tomate San Marzano, muçarela especial e manjericão fresco.',
            'preco_centavos' => 5990,
            'imagem' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?auto=format&fit=crop&w=600&q=80',
            'ativo' => true,
            'esgotado' => false,
            'canais' => $todosCanais,
            'ordem' => 1,
            'tempo_preparo_min' => 25,
            'estacao_kds' => 'forno',
        ]);
        ProdutoVariacao::create(['loja_id' => $loja->id, 'produto_id' => $pizza1->id, 'nome' => 'Média (6 Fatias)', 'preco_centavos' => 5990, 'ordem' => 1]);
        ProdutoVariacao::create(['loja_id' => $loja->id, 'produto_id' => $pizza1->id, 'nome' => 'Grande (8 Fatias)', 'preco_centavos' => 7490, 'ordem' => 2]);
        $pizza1->gruposAdicionais()->attach([$grpSaboresPizza->id => ['ordem' => 1]]);

        $pizza2 = Produto::create([
            'loja_id' => $loja->id,
            'categoria_id' => $catPizzas->id,
            'nome' => 'Pizza Calabresa Artesanal & Catupiry',
            'descricao' => 'Calabresa defumada fatiada fina, borda recheada gratuita, cebola roxa, azeitonas pretas e autêntico Catupiry gratinado.',
            'preco_centavos' => 6490,
            'imagem' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=600&q=80',
            'ativo' => true,
            'esgotado' => false,
            'canais' => $todosCanais,
            'ordem' => 2,
            'tempo_preparo_min' => 25,
            'estacao_kds' => 'forno',
        ]);
        ProdutoVariacao::create(['loja_id' => $loja->id, 'produto_id' => $pizza2->id, 'nome' => 'Média (6 Fatias)', 'preco_centavos' => 6490, 'ordem' => 1]);
        ProdutoVariacao::create(['loja_id' => $loja->id, 'produto_id' => $pizza2->id, 'nome' => 'Grande (8 Fatias)', 'preco_centavos' => 7990, 'ordem' => 2]);
        $pizza2->gruposAdicionais()->attach([$grpSaboresPizza->id => ['ordem' => 1]]);

        // 13. Products - Acompanhamentos & Porções
        Produto::create([
            'loja_id' => $loja->id,
            'categoria_id' => $catAcomp->id,
            'nome' => 'Batata Rústica com Cheddar & Bacon (400g)',
            'descricao' => 'Batatas corte rústico com casca e alecrim, cobertas com muito creme de cheddar inglês e farofa de bacon defumado.',
            'preco_centavos' => 2990,
            'preco_promocional_centavos' => 2690,
            'imagem' => 'https://images.unsplash.com/photo-1576107232684-1279f3908594?auto=format&fit=crop&w=600&q=80',
            'ativo' => true,
            'esgotado' => false,
            'canais' => $todosCanais,
            'ordem' => 1,
            'tempo_preparo_min' => 15,
            'estacao_kds' => 'fritadeira',
        ]);

        Produto::create([
            'loja_id' => $loja->id,
            'categoria_id' => $catAcomp->id,
            'nome' => 'Onion Rings Empanadas na Cerveja',
            'descricao' => 'Porção generosa de anéis de cebola dourados e super crocantes, acompanha potinho de molho barbecue da casa.',
            'preco_centavos' => 2290,
            'imagem' => 'https://images.unsplash.com/photo-1639024471283-03518883512d?auto=format&fit=crop&w=600&q=80',
            'ativo' => true,
            'esgotado' => false,
            'canais' => $todosCanais,
            'ordem' => 2,
            'tempo_preparo_min' => 12,
            'estacao_kds' => 'fritadeira',
        ]);

        // 14. Products - Bebidas Geladas
        Produto::create([
            'loja_id' => $loja->id,
            'categoria_id' => $catBebidas->id,
            'nome' => 'Coca-Cola Original Lata 350ml',
            'descricao' => 'Refrigerante Coca-Cola tradicional lata 350ml trincando de gelada.',
            'preco_centavos' => 690,
            'imagem' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?auto=format&fit=crop&w=600&q=80',
            'ativo' => true,
            'esgotado' => false,
            'canais' => $todosCanais,
            'ordem' => 1,
            'tempo_preparo_min' => 2,
            'estacao_kds' => 'bar',
        ]);

        Produto::create([
            'loja_id' => $loja->id,
            'categoria_id' => $catBebidas->id,
            'nome' => 'Suco Natural de Laranja 500ml',
            'descricao' => 'Suco 100% fruta espremido na hora com laranjas selecionadas, sem conservantes.',
            'preco_centavos' => 1290,
            'imagem' => 'https://images.unsplash.com/photo-1613478223719-2ab802602423?auto=format&fit=crop&w=600&q=80',
            'ativo' => true,
            'esgotado' => false,
            'canais' => $todosCanais,
            'ordem' => 2,
            'tempo_preparo_min' => 5,
            'estacao_kds' => 'bar',
        ]);

        Produto::create([
            'loja_id' => $loja->id,
            'categoria_id' => $catBebidas->id,
            'nome' => 'Heineken Long Neck 330ml',
            'descricao' => 'Cerveja Premium Puro Malte Heineken 330ml estupidamente gelada.',
            'preco_centavos' => 1390,
            'imagem' => 'https://images.unsplash.com/photo-1608270586620-248524c67de9?auto=format&fit=crop&w=600&q=80',
            'ativo' => true,
            'esgotado' => false,
            'canais' => $todosCanais,
            'ordem' => 3,
            'tempo_preparo_min' => 2,
            'estacao_kds' => 'bar',
        ]);

        // 15. Products - Combos Promocionais
        Produto::create([
            'loja_id' => $loja->id,
            'categoria_id' => $catCombos->id,
            'nome' => 'Combo Individual Brasa (Burger + Fritas + Lata)',
            'descricao' => '1 Brasa Bacon Burger Especial 180g + Porção de Batata Rústica + 1 Coca-Cola Lata 350ml gelada.',
            'preco_centavos' => 5690,
            'preco_promocional_centavos' => 4990,
            'imagem' => 'https://images.unsplash.com/photo-1594212699903-ec8a3eca50f5?auto=format&fit=crop&w=600&q=80',
            'ativo' => true,
            'esgotado' => false,
            'canais' => $todosCanais,
            'ordem' => 1,
            'tempo_preparo_min' => 20,
            'estacao_kds' => 'chapa',
        ]);

        Produto::create([
            'loja_id' => $loja->id,
            'categoria_id' => $catCombos->id,
            'nome' => 'Combo Casal Completo (2 Burgers + Batata + 2 Latas)',
            'descricao' => '2 Brasa Bacon Burger Especial 180g + 1 Batata Rústica Grande com Cheddar e Bacon + 2 Coca-Cola Lata 350ml.',
            'preco_centavos' => 10490,
            'preco_promocional_centavos' => 8990,
            'imagem' => 'https://images.unsplash.com/photo-1561758033-d89a9ad46330?auto=format&fit=crop&w=600&q=80',
            'ativo' => true,
            'esgotado' => false,
            'canais' => $todosCanais,
            'ordem' => 2,
            'tempo_preparo_min' => 25,
            'estacao_kds' => 'chapa',
        ]);

        // Combo Record
        $combo = Combo::create([
            'loja_id' => $loja->id,
            'nome' => 'Combo Brasa Individual',
            'descricao' => '1 Burger Especial + Acompanhamento',
            'preco_centavos' => 4990,
            'ativo' => true,
        ]);
        ComboItem::create(['combo_id' => $combo->id, 'produto_id' => $burger1->id, 'qtd' => 1]);
    }
}
