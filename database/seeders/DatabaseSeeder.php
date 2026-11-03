<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Assinaturas\Models\Plano;
use App\Domain\Autenticacao\Models\Usuario;
use App\Support\Security\BlindIndexService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $blindIndex = new BlindIndexService((string) config('app.blind_index_key', 'pilot-blind-index-key-secret-32-bytes!'));

        // 1. Seed the 3 Official SaaS Subscription Plans (idempotent via updateOrCreate)
        Plano::updateOrCreate(
            ['nome' => 'delivery'],
            [
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
            ]
        );

        Plano::updateOrCreate(
            ['nome' => 'presencial'],
            [
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
            ]
        );

        Plano::updateOrCreate(
            ['nome' => 'premium'],
            [
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
            ]
        );

        // 2. Seed Master Super Admin (SaaS Owner)
        $superAdminEmail = (string) env('SUPER_ADMIN_EMAIL', 'admin@pedelivre.com.br');
        $superAdminPass = (string) env('SUPER_ADMIN_PASSWORD', 'AdminMaster2026!');
        $superAdminBidx = $blindIndex->generateForEmail($superAdminEmail);

        if (!Usuario::withoutGlobalScopes()->where('email_bidx', $superAdminBidx)->exists()) {
            Usuario::withoutGlobalScopes()->create([
                'loja_id' => null,
                'nome' => 'Administrador Master — PedeLivre SaaS',
                'email' => $superAdminEmail,
                'email_bidx' => $superAdminBidx,
                'telefone' => '31999990000',
                'senha_hash' => Hash::make($superAdminPass),
                'papel' => 'super_admin',
                'ativo' => true,
            ]);
        }
    }
}
