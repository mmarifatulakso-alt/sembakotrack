<?php

use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Security headers dipasang GLOBAL: berlaku untuk semua response.
        $middleware->append(SecurityHeaders::class);

        // Alias middleware RBAC: dipakai sebagai ->middleware('role:admin')
        $middleware->alias([
            'role' => EnsureRole::class,
        ]);

        // Jika di belakang reverse proxy/load balancer HTTPS, aktifkan:
        // $middleware->trustProxies(at: ['IP_PROXY_ANDA']);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();