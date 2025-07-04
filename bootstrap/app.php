<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\Authenticate; // ✅ Middleware auth kustom kamu
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Tidak perlu middleware('api') lagi di sini, sudah otomatis
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ✅ Tambahkan HandleCors ke grup API
        $middleware->appendToGroup('api', HandleCors::class);

        // ✅ Daftarkan alias middleware
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'auth' => Authenticate::class, // Gantikan bawaan Laravel
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
