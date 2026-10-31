<?php

declare(strict_types=1);

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
        // Exception handling
    })->create();
