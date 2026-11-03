<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Assinaturas\Console\ProcessarInadimplenciaCommand;
use App\Domain\Assinaturas\Models\Loja;
use App\Domain\Assinaturas\Services\AssinaturaPlataformaService;
use App\Domain\Assinaturas\Services\ReguaInadimplenciaService;
use App\Domain\Autenticacao\Models\Usuario;
use App\Domain\Bot\Commands\DispararWinBackCommand;
use App\Domain\Bot\Services\WhatsAppMetaCloudService;
use App\Domain\Catalogo\Models\Categoria;
use App\Domain\Catalogo\Models\Combo;
use App\Domain\Catalogo\Models\GrupoAdicional;
use App\Domain\Catalogo\Models\OpcaoAdicional;
use App\Domain\Catalogo\Models\Produto;
use App\Domain\Catalogo\Models\ProdutoVariacao;
use App\Domain\Catalogo\Observers\CatalogoCacheObserver;
use App\Domain\Catalogo\Policies\CategoriaPolicy;
use App\Domain\Catalogo\Policies\ComboPolicy;
use App\Domain\Catalogo\Policies\GrupoAdicionalPolicy;
use App\Domain\Catalogo\Policies\ProdutoPolicy;
use App\Domain\Catalogo\Services\CardapioCacheService;
use App\Domain\Entregas\Models\AreaEntrega;
use App\Domain\Entregas\Models\Entregador;
use App\Domain\Entregas\Policies\AreaEntregaPolicy;
use App\Domain\Entregas\Policies\EntregadorPolicy;
use App\Domain\Fidelidade\Commands\SolicitarAvaliacoesCommand;
use App\Domain\Fidelidade\Services\CashbackService;
use App\Domain\Mesas\Models\Mesa;
use App\Domain\Mesas\Policies\MesaPolicy;
use App\Domain\Mesas\Services\ComandaService;
use App\Domain\Operacao\Models\ExcecaoHorario;
use App\Domain\Operacao\Models\Horario;
use App\Domain\Operacao\Policies\ExcecaoHorarioPolicy;
use App\Domain\Operacao\Policies\HorarioPolicy;
use App\Domain\Pagamentos\Services\MercadoPagoOAuthService;
use App\Domain\Pagamentos\Services\MercadoPagoPixService;
use App\Domain\Pedidos\Commands\RecuperarCarrinhosCommand;
use App\Domain\Pedidos\Commands\VerificarPedidosAtrasadosCommand;
use App\Domain\Pedidos\Services\CaixaService;
use App\Domain\Pedidos\Services\ImpressaoEscPosService;
use App\Policies\LojaPolicy;
use App\Policies\UsuarioPolicy;
use App\Support\Security\BlindIndexService;
use App\Support\Security\SafeImageUploadService;
use App\Support\Security\SensitiveDataRedactor;
use App\Support\Security\WebhookSignatureValidator;
use App\Support\Tenancy\LojaContext;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LojaContext::class, fn () => new LojaContext());
        $this->app->singleton(BlindIndexService::class, fn () => new BlindIndexService());
        $this->app->singleton(SensitiveDataRedactor::class, fn () => new SensitiveDataRedactor());
        $this->app->singleton(SafeImageUploadService::class, fn () => new SafeImageUploadService());
        $this->app->singleton(CardapioCacheService::class, fn () => new CardapioCacheService());
        $this->app->singleton(ImpressaoEscPosService::class, fn () => new ImpressaoEscPosService());
        $this->app->singleton(WebhookSignatureValidator::class, fn () => new WebhookSignatureValidator());
        $this->app->singleton(MercadoPagoOAuthService::class, fn () => new MercadoPagoOAuthService());
        $this->app->singleton(MercadoPagoPixService::class, fn () => new MercadoPagoPixService());
        $this->app->singleton(AssinaturaPlataformaService::class, fn () => new AssinaturaPlataformaService());
        $this->app->singleton(ReguaInadimplenciaService::class, fn () => new ReguaInadimplenciaService());
        $this->app->singleton(ComandaService::class, fn ($app) => new ComandaService($app->make(SensitiveDataRedactor::class)));
        $this->app->singleton(CaixaService::class, fn () => new CaixaService());
        $this->app->singleton(CashbackService::class, fn () => new CashbackService());
        $this->app->singleton(WhatsAppMetaCloudService::class, fn ($app) => new WhatsAppMetaCloudService($app->make(BlindIndexService::class)));
    }

    public function boot(): void
    {
        // 1. RBAC Policies
        Gate::policy(Loja::class, LojaPolicy::class);
        Gate::policy(Usuario::class, UsuarioPolicy::class);
        Gate::policy(Categoria::class, CategoriaPolicy::class);
        Gate::policy(Produto::class, ProdutoPolicy::class);
        Gate::policy(GrupoAdicional::class, GrupoAdicionalPolicy::class);
        Gate::policy(Combo::class, ComboPolicy::class);
        Gate::policy(Horario::class, HorarioPolicy::class);
        Gate::policy(ExcecaoHorario::class, ExcecaoHorarioPolicy::class);
        Gate::policy(AreaEntrega::class, AreaEntregaPolicy::class);
        Gate::policy(Entregador::class, EntregadorPolicy::class);
        Gate::policy(Mesa::class, MesaPolicy::class);

        // 2. Cache Invalidation Observers
        Categoria::observe(CatalogoCacheObserver::class);
        Produto::observe(CatalogoCacheObserver::class);
        ProdutoVariacao::observe(CatalogoCacheObserver::class);
        GrupoAdicional::observe(CatalogoCacheObserver::class);
        OpcaoAdicional::observe(CatalogoCacheObserver::class);
        Combo::observe(CatalogoCacheObserver::class);

        // 3. Custom Domain CLI Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                ProcessarInadimplenciaCommand::class,
                RecuperarCarrinhosCommand::class,
                VerificarPedidosAtrasadosCommand::class,
                SolicitarAvaliacoesCommand::class,
                DispararWinBackCommand::class,
            ]);
        }
    }
}
