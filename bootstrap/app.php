<?php

declare(strict_types=1);

use App\Support\Monitoring\SystemMonitorService;
use App\Support\Tenancy\IdentificarTenantMiddleware;
use App\Support\Tenancy\ModoLeituraInadimplenciaMiddleware;
use App\Support\Tenancy\VerificarRecursoPlanoMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['prefix' => 'api', 'middleware' => ['auth:sanctum']],
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant' => IdentificarTenantMiddleware::class,
            'recurso.plano' => VerificarRecursoPlanoMiddleware::class,
            'modo.leitura' => ModoLeituraInadimplenciaMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (Throwable $e): void {
            try {
                app(SystemMonitorService::class)->reportarExcecaoCritica($e, request());
            } catch (Throwable) {
                // Evita falha em cascata caso o container ainda não esteja pronto
            }
        });
    })->create();
