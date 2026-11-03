<?php

declare(strict_types=1);

use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Catalogo\Services\CardapioCacheService;
use App\Domain\Catalogo\Services\HorarioFuncionamentoService;
use App\Domain\Entregas\Models\AreaEntrega;
use App\Domain\Fidelidade\Models\Avaliacao;
use App\Domain\Pedidos\Models\Cupom;
use App\Domain\Mesas\Models\Mesa;
use App\Domain\Pedidos\Models\Pedido;
use App\Support\Tenancy\LojaContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('landing');
Route::view('/login', 'auth.login')->name('login');
Route::view('/cadastro', 'auth.cadastro')->name('cadastro');
Route::redirect('/painel', '/painel/pedidos');

Route::get('/p/{slug}', function (
    string $slug,
    Request $request,
    CardapioCacheService $cacheService,
    HorarioFuncionamentoService $horarioService,
    LojaContext $context
) {
    $loja = Loja::where('slug', $slug)->where('status', '!=', 'cancelada')->firstOrFail();
    $context->setLoja($loja);

    $canal = (string) $request->query('canal', 'delivery');
    if (!in_array($canal, ['delivery', 'retirada', 'mesa', 'balcao'], true)) {
        $canal = 'delivery';
    }

    $cardapio = $cacheService->getCardapio($loja->id, $canal);
    $lojaStatus = $horarioService->avaliarStatusLoja($loja, $canal);

    $cuponsAtivos = Cupom::where('loja_id', $loja->id)
        ->where('ativo', true)
        ->where(fn ($q) => $q->whereNull('validade')->orWhere('validade', '>', now()))
        ->orderByDesc('id')
        ->get(['codigo', 'tipo', 'valor', 'pedido_minimo', 'uso_unico_por_cliente']);

    $areasEntrega = AreaEntrega::where('loja_id', $loja->id)
        ->where('ativa', true)
        ->orderBy('taxa_centavos')
        ->get(['id', 'nome', 'tipo', 'bairro_nome', 'taxa_centavos', 'pedido_minimo', 'frete_gratis_acima']);

    $mediaAvaliacao = round((float) (Avaliacao::where('loja_id', $loja->id)->avg('nota') ?: 5.0), 1);

    return view('cardapio.index', [
        'loja' => $loja,
        'cardapio' => $cardapio,
        'lojaStatus' => $lojaStatus,
        'mesa' => null,
        'cuponsAtivos' => $cuponsAtivos,
        'areasEntrega' => $areasEntrega,
        'mediaAvaliacao' => $mediaAvaliacao,
    ]);
})->name('cardapio.publico');

// Table QR Code Mode (/p/{slug}/m/{token} and /p/{slug}/mesa/{token})
Route::get('/p/{slug}/{prefix}/{token}', function (
    string $slug,
    string $prefix,
    string $token,
    CardapioCacheService $cacheService,
    HorarioFuncionamentoService $horarioService,
    LojaContext $context
) {
    abort_unless(in_array($prefix, ['m', 'mesa'], true), 404);

    $loja = Loja::where('slug', $slug)->where('status', '!=', 'cancelada')->firstOrFail();
    $context->setLoja($loja);

    $mesa = Mesa::where('loja_id', $loja->id)->where('qr_token', $token)->firstOrFail();
    $cardapio = $cacheService->getCardapio($loja->id, 'mesa');
    $lojaStatus = $horarioService->avaliarStatusLoja($loja, 'mesa');

    $cuponsAtivos = Cupom::where('loja_id', $loja->id)
        ->where('ativo', true)
        ->where(fn ($q) => $q->whereNull('validade')->orWhere('validade', '>', now()))
        ->orderByDesc('id')
        ->get(['codigo', 'tipo', 'valor', 'pedido_minimo', 'uso_unico_por_cliente']);

    $areasEntrega = AreaEntrega::where('loja_id', $loja->id)
        ->where('ativa', true)
        ->orderBy('taxa_centavos')
        ->get(['id', 'nome', 'tipo', 'bairro_nome', 'taxa_centavos', 'pedido_minimo', 'frete_gratis_acima']);

    $mediaAvaliacao = round((float) (Avaliacao::where('loja_id', $loja->id)->avg('nota') ?: 5.0), 1);

    return view('cardapio.index', [
        'loja' => $loja,
        'cardapio' => $cardapio,
        'lojaStatus' => $lojaStatus,
        'mesa' => [
            'id' => $mesa->id,
            'numero' => $mesa->numero,
            'nome' => $mesa->nome,
            'qr_token' => $mesa->qr_token,
        ],
        'cuponsAtivos' => $cuponsAtivos,
        'areasEntrega' => $areasEntrega,
        'mediaAvaliacao' => $mediaAvaliacao,
    ]);
})->whereIn('prefix', ['m', 'mesa'])->name('cardapio.mesa');

// Real-time Public Order Tracking (/p/{slug}/pedidos/{codigo})
Route::get('/p/{slug}/pedidos/{codigo}', function (string $slug, string $codigo, Request $request, LojaContext $context) {
    $loja = Loja::where('slug', $slug)->where('status', '!=', 'cancelada')->firstOrFail();
    $context->setLoja($loja);

    $pedido = Pedido::where('loja_id', $loja->id)
        ->where('codigo_publico', $codigo)
        ->with(['itens', 'pagamento', 'mesa', 'eventos'])
        ->firstOrFail();

    $signature = (string) $request->query('t', '');
    $expectedSignature = hash_hmac('sha256', (string) $pedido->tracking_token_hash, (string) config('app.key'));
    abort_unless($signature !== '' && hash_equals($expectedSignature, $signature), 403, 'Link de rastreamento inválido ou expirado.');

    return view('cardapio.tracking', [
        'loja' => $loja,
        'pedido' => $pedido,
    ]);
})->name('cardapio.tracking');

Route::view('/painel/pedidos', 'painel.kanban')->name('painel.kanban');
Route::view('/admin', 'admin.painel')->name('admin.painel');
Route::redirect('/superadmin', '/admin');

Route::get('/painel/pedidos/{id}/imprimir', function (int $id, Request $request) {
    $rawToken = $request->bearerToken() ?: (string) $request->query('token', '');
    abort_if($rawToken === '', 403, 'Autenticação obrigatória para impressão.');

    $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($rawToken);
    $usuario = $accessToken?->tokenable;
    abort_if(!$usuario instanceof \App\Domain\Autenticacao\Models\Usuario || !$usuario->ativo, 403, 'Token inválido.');

    $pedido = Pedido::withoutGlobalScopes()->with(['itens', 'cliente', 'mesa', 'loja'])->findOrFail($id);
    abort_unless($usuario->isSuperAdmin() || (int) $usuario->loja_id === (int) $pedido->loja_id, 403, 'Acesso negado a este pedido.');

    $tipo = (string) $request->query('tipo', 'cliente');

    return view('impressao.recibo', [
        'pedido' => $pedido,
        'tipo' => $tipo,
    ]);
})->name('painel.pedidos.imprimir');

Route::get('/painel/caixa/{id}/imprimir', function (int $id, Request $request) {
    $rawToken = $request->bearerToken() ?: (string) $request->query('token', '');
    abort_if($rawToken === '', 403, 'Autenticação obrigatória para impressão.');

    $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($rawToken);
    $usuario = $accessToken?->tokenable;
    abort_if(!$usuario instanceof \App\Domain\Autenticacao\Models\Usuario || !$usuario->ativo, 403, 'Token inválido.');

    $sessao = \App\Domain\Operacao\Models\CaixaSessao::withoutGlobalScopes()->with(['usuario', 'movimentos'])->findOrFail($id);
    abort_unless($usuario->isSuperAdmin() || (int) $usuario->loja_id === (int) $sessao->loja_id, 403, 'Acesso negado.');

    $loja = \App\Domain\Assinaturas\Models\Loja::findOrFail($sessao->loja_id);

    $pedidos = Pedido::where('loja_id', $loja->id)
        ->where('created_at', '>=', $sessao->aberto_em)
        ->when($sessao->fechado_em, fn($q) => $q->where('created_at', '<=', $sessao->fechado_em))
        ->where('status', '!=', 'cancelado')
        ->get();

    $vendasDinheiro = (int) $pedidos->where('forma_pagamento', 'dinheiro')->sum('total');
    $vendasPix = (int) $pedidos->where('forma_pagamento', 'pix')->sum('total');
    $vendasCartao = (int) $pedidos->whereIn('forma_pagamento', ['cartao_maquina', 'cartao_online', 'voucher'])->sum('total');

    return view('impressao.caixa', [
        'sessao' => $sessao,
        'loja' => $loja,
        'vendasDinheiro' => $vendasDinheiro,
        'vendasPix' => $vendasPix,
        'vendasCartao' => $vendasCartao,
        'totalPedidos' => $pedidos->count(),
    ]);
})->name('painel.caixa.imprimir');

Route::get('/painel/entregadores/{id}/imprimir-acerto', function (int $id, Request $request) {
    $rawToken = $request->bearerToken() ?: (string) $request->query('token', '');
    abort_if($rawToken === '', 403, 'Autenticação obrigatória para impressão.');

    $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($rawToken);
    $usuario = $accessToken?->tokenable;
    abort_if(!$usuario instanceof \App\Domain\Autenticacao\Models\Usuario || !$usuario->ativo, 403, 'Token inválido.');

    $entregador = \App\Domain\Entregas\Models\Entregador::withoutGlobalScopes()->findOrFail($id);
    abort_unless($usuario->isSuperAdmin() || (int) $usuario->loja_id === (int) $entregador->loja_id, 403, 'Acesso negado.');

    $loja = \App\Domain\Assinaturas\Models\Loja::findOrFail($entregador->loja_id);

    $pedidosHoje = Pedido::where('loja_id', $loja->id)
        ->where('canal', 'delivery')
        ->where('entregador_id', $entregador->id)
        ->where('created_at', '>=', now()->startOfDay())
        ->where('status', '!=', 'cancelado')
        ->get();

    $concluidas = $pedidosHoje->whereIn('status', ['entregue', 'finalizado', 'retirado', 'servido']);
    $taxaUnit = (int) ($entregador->valor_por_entrega_centavos ?? 700);
    $totalTaxas = $concluidas->count() * $taxaUnit;
    $dinheiroColetado = (int) $concluidas->where('forma_pagamento', 'dinheiro')->sum('total');
    $saldoAcerto = $totalTaxas - $dinheiroColetado;

    return view('impressao.motoboy', [
        'entregador' => $entregador,
        'loja' => $loja,
        'concluidas' => $concluidas,
        'taxaUnit' => $taxaUnit,
        'totalTaxas' => $totalTaxas,
        'dinheiroColetado' => $dinheiroColetado,
        'saldoAcerto' => $saldoAcerto,
    ]);
})->name('painel.entregadores.imprimir');

Route::get('/status', fn () => response()->json([
    'status' => 'online',
    'app' => 'PedeLivre SaaS',
    'timestamp' => now()->toIso8601String(),
]));
Route::redirect('/termos', '/');
Route::redirect('/privacidade', '/');
