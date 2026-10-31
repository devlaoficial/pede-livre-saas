<?php

declare(strict_types=1);

use App\Domain\Assinaturas\Controllers\AssinaturaController;
use App\Domain\Assinaturas\Controllers\LojaController;
use App\Domain\Assinaturas\Controllers\SuperAdminController;
use App\Domain\Autenticacao\Controllers\ImpersonationController;
use App\Domain\Autenticacao\Controllers\LoginController;
use App\Domain\Autenticacao\Controllers\TwoFactorController;
use App\Domain\Autenticacao\Controllers\UsuarioController;
use App\Domain\Bot\Controllers\WhatsAppWebhookController;
use App\Domain\Catalogo\Controllers\CategoriaController;
use App\Domain\Catalogo\Controllers\ComboController;
use App\Domain\Catalogo\Controllers\EstoqueRapidoController;
use App\Domain\Catalogo\Controllers\GrupoAdicionalController;
use App\Domain\Catalogo\Controllers\ImportacaoCatalogoController;
use App\Domain\Catalogo\Controllers\ProdutoController;
use App\Domain\Catalogo\Controllers\PublicCardapioController;
use App\Domain\Catalogo\Controllers\ReordenarCatalogoController;
use App\Domain\Catalogo\Controllers\UploadProdutoImagemController;
use App\Domain\Clientes\Controllers\LgpdController;
use App\Domain\Entregas\Controllers\AreaEntregaController;
use App\Domain\Entregas\Controllers\EntregadorController;
use App\Domain\Fidelidade\Controllers\RelatorioVendasController;
use App\Domain\Mesas\Controllers\ComandaController;
use App\Domain\Mesas\Controllers\MesaAdminController;
use App\Domain\Mesas\Controllers\MesaController;
use App\Domain\Operacao\Controllers\CaixaController;
use App\Domain\Operacao\Controllers\ExcecaoHorarioController;
use App\Domain\Operacao\Controllers\HorarioController;
use App\Domain\Pagamentos\Controllers\MercadoPagoOAuthController;
use App\Domain\Pagamentos\Controllers\MercadoPagoWebhookController;
use App\Domain\Pedidos\Controllers\CheckoutController;
use App\Domain\Pedidos\Controllers\ImpressaoFilaController;
use App\Domain\Pedidos\Controllers\KdsController;
use App\Domain\Pedidos\Controllers\PedidoPainelController;
use App\Support\Tenancy\IdentificarTenantMiddleware;
use App\Support\Tenancy\ModoLeituraInadimplenciaMiddleware;
use App\Support\Tenancy\VerificarRecursoPlanoMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - PedeLivre SaaS (Phases 1 to 11 Complete Master Blueprint)
|--------------------------------------------------------------------------
*/

// 1. Public Authentication (Rate Limited: 5 attempts / minute)
Route::prefix('auth')->group(function (): void {
    Route::post('login', [LoginController::class, 'login'])->middleware('throttle:5,1');
    Route::post('cadastro', [LoginController::class, 'cadastrar'])->middleware('throttle:5,1');
    Route::post('2fa/verificar', [TwoFactorController::class, 'verify'])->middleware('auth:sanctum');
});

// 2. Cryptographically Verified External Webhooks & OAuth Callback (Mercado Pago & Meta Cloud API)
Route::get('webhooks/mercadopago', fn () => response()->json(['status' => 'ok', 'webhook' => 'mercadopago']));
Route::post('webhooks/mercadopago', [MercadoPagoWebhookController::class, 'processar']);
Route::get('pagamentos/mercadopago/callback', [MercadoPagoOAuthController::class, 'callback']);
Route::post('webhooks/whatsapp', [WhatsAppWebhookController::class, 'processar']);

// 3. Public Menu, Table QR & 3-Step Checkout API (Tenant-isolated)
Route::prefix('p/{slug}')
    ->middleware(IdentificarTenantMiddleware::class)
    ->group(function (): void {
        Route::get('/', [PublicCardapioController::class, 'loja']);
        Route::get('cardapio', [PublicCardapioController::class, 'cardapio']);

        // Table QR Mode
        Route::get('mesa/{token}', [MesaController::class, 'identificarPorToken']);
        Route::post('mesa/{token}/chamar', [MesaController::class, 'chamarGarcom']);

        // 3-Step Public Checkout
        Route::post('frete', [CheckoutController::class, 'calcularFrete']);
        Route::post('cupom/validar', [CheckoutController::class, 'validarCupom']);
        Route::post('pedidos', [CheckoutController::class, 'criarPedido']);
        Route::get('pedidos/{codigo}', [CheckoutController::class, 'acompanharPedido']);
    });

// 4. Authenticated Merchant Panel, Staff & Super Admin API
Route::middleware([
    'auth:sanctum',
    IdentificarTenantMiddleware::class,
    ModoLeituraInadimplenciaMiddleware::class,
])->group(function (): void {
    // Auth & Logout
    Route::post('auth/logout', [LoginController::class, 'logout']);

    // Super Admin Dashboard & Audited Impersonation (Phase 1 & Phase 9)
    Route::prefix('admin')->group(function (): void {
        Route::get('metricas', [SuperAdminController::class, 'metricas']);
        Route::get('lojas', [SuperAdminController::class, 'listarLojas']);
        Route::post('lojas', [SuperAdminController::class, 'criarLoja']);
        Route::patch('lojas/{id}', [SuperAdminController::class, 'atualizarLoja']);
        Route::patch('planos/{id}', [SuperAdminController::class, 'atualizarPlano']);
        Route::post('regua-inadimplencia', [SuperAdminController::class, 'processarReguaInadimplencia']);
        Route::post('lojas/{id}/impersonar', [ImpersonationController::class, 'impersonar'])
            ->middleware('throttle:10,1');
    });

    // SaaS Subscriptions, Plan Upgrades & 3-Click Self-Service Cancellation (Phase 9)
    // Note: ModoLeituraInadimplenciaMiddleware explicitly allows /api/assinatura* and /api/faturas*
    Route::prefix('assinatura')->group(function (): void {
        Route::post('alterar-plano', [AssinaturaController::class, 'alterarPlano']);
        Route::post('cancelar', [AssinaturaController::class, 'cancelar']);
    });
    Route::post('faturas/{id}/pagar', [AssinaturaController::class, 'pagarFatura']);

    // Store Profile & Settings
    Route::get('loja', [LojaController::class, 'show']);
    Route::patch('loja', [LojaController::class, 'update']);

    // Staff Users Management
    Route::apiResource('usuarios', UsuarioController::class);

    // Catalog Drag-and-Drop & Fast Stock (Phase 3)
    Route::patch('categorias/reordenar', [ReordenarCatalogoController::class, 'reordenarCategorias']);
    Route::patch('produtos/reordenar', [ReordenarCatalogoController::class, 'reordenarProdutos']);
    Route::post('produtos/{id}/pausar', [EstoqueRapidoController::class, 'pausarProduto']);
    Route::post('opcoes-adicionais/{id}/pausar', [EstoqueRapidoController::class, 'pausarOpcaoAdicional']);
    Route::post('produtos/{id}/imagem', [UploadProdutoImagemController::class, 'upload']);
    Route::post('catalogo/importar', [ImportacaoCatalogoController::class, 'importar']);

    // Catalog CRUD (Phase 2 & 3)
    Route::apiResource('categorias', CategoriaController::class);
    Route::apiResource('produtos', ProdutoController::class);
    Route::apiResource('grupos-adicionais', GrupoAdicionalController::class);
    Route::apiResource('combos', ComboController::class);

    // Operations: Operating Hours & Exceptions (Phase 4)
    Route::get('horarios', [HorarioController::class, 'index']);
    Route::post('horarios/salvar-turnos', [HorarioController::class, 'salvarTurnos']);
    Route::delete('horarios/{id}', [HorarioController::class, 'destroy']);
    Route::apiResource('excecoes-horario', ExcecaoHorarioController::class)->only(['index', 'store', 'destroy']);

    // Logistics: Delivery Zones & Couriers
    Route::apiResource('areas-entrega', AreaEntregaController::class);
    Route::apiResource('entregadores', EntregadorController::class);

    // On-Premise Operations: Tables, Open Tabs (Comandas) & Bill Splitting (Phase 4 & Phase 10)
    Route::get('mesas-chamados', [MesaAdminController::class, 'chamadosAtivos']);
    Route::post('mesas/{id}/atender-chamado', [MesaAdminController::class, 'atenderChamado']);
    Route::middleware(VerificarRecursoPlanoMiddleware::class . ':canal_mesa_balcao')->group(function (): void {
        Route::apiResource('mesas', MesaAdminController::class);
        Route::post('mesas/{id}/regenerar-qr', [MesaAdminController::class, 'regenerarQr']);

        Route::post('comandas/abrir', [ComandaController::class, 'abrir']);
        Route::get('comandas/{id}/dividir', [ComandaController::class, 'dividir']);
        Route::post('comandas/transferir-item', [ComandaController::class, 'transferirItem']);
        Route::post('comandas/{id}/fechar', [ComandaController::class, 'fechar']);
    });

    // Kitchen Display System (KDS by Station - Phase 10)
    Route::prefix('kds')->group(function (): void {
        Route::get('tickets', [KdsController::class, 'tickets']);
        Route::patch('itens/{itemId}/status', [KdsController::class, 'atualizarStatusItem']);
    });

    // Cash Register (Caixa - Phase 10)
    Route::prefix('caixa')->group(function (): void {
        Route::post('abrir', [CaixaController::class, 'abrir']);
        Route::post('movimento', [CaixaController::class, 'movimento']);
        Route::post('fechar', [CaixaController::class, 'fechar']);
    });

    // Mercado Pago OAuth2 Connect, Direct Token Authorization & Status (Phase 8)
    Route::prefix('pagamentos/mercadopago')->group(function (): void {
        Route::get('conectar', [MercadoPagoOAuthController::class, 'conectar']);
        Route::post('salvar-token', [MercadoPagoOAuthController::class, 'salvarTokenManual']);
        Route::post('testar-pix', [MercadoPagoOAuthController::class, 'testarPix']);
        Route::post('desconectar', [MercadoPagoOAuthController::class, 'desconectar']);
        Route::get('status', [MercadoPagoOAuthController::class, 'status']);
    });

    // Tier 2: 80mm ESC/POS Thermal Print Queue & Agent Endpoints (Phase 7)
    Route::prefix('impressao')->group(function (): void {
        Route::get('fila', [ImpressaoFilaController::class, 'obterFila']);
        Route::post('{jobId}/confirmar', [ImpressaoFilaController::class, 'confirmarImpressao']);
        Route::post('{jobId}/falha', [ImpressaoFilaController::class, 'registrarFalha']);
    });
    Route::post('painel/pedidos/{id}/imprimir', [ImpressaoFilaController::class, 'imprimirManualmente']);

    // Real-Time Merchant Order Panel (Kanban & State Machine - Phase 6)
    Route::prefix('painel')->group(function (): void {
        Route::get('pedidos', [PedidoPainelController::class, 'index']);
        Route::get('pedidos/{id}', [PedidoPainelController::class, 'show']);
        Route::patch('pedidos/{id}/status', [PedidoPainelController::class, 'atualizarStatus']);
        Route::post('pedidos/{id}/atribuir-entregador', [PedidoPainelController::class, 'atribuirEntregador']);
    });

    // Analytics Reports, Coupons & LGPD Compliance Portal (Phase 11)
    Route::get('relatorios/vendas', [RelatorioVendasController::class, 'vendas']);
    Route::get('cupons', [RelatorioVendasController::class, 'listarCupons']);
    Route::post('cupons', [RelatorioVendasController::class, 'criarCupom']);
    Route::patch('cupons/{id}', [RelatorioVendasController::class, 'alternarCupom']);
    Route::delete('cupons/{id}', [RelatorioVendasController::class, 'excluirCupom']);
    Route::get('lgpd/exportar', [LgpdController::class, 'exportar']);
    Route::delete('lgpd/excluir', [LgpdController::class, 'anonimizar']);
});
