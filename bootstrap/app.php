<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    // konfigurasi file routing aplikasi
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    // registrasi alias dan handler middleware
    ->withMiddleware(function (Middleware $middleware): void {
        // alias middleware untuk route guard
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'voter.auth' => \App\Http\Middleware\VoterAuthenticate::class,
            'voter.guest' => \App\Http\Middleware\RedirectIfVoterAuthenticated::class,
            'admin.guest' => \App\Http\Middleware\RedirectIfAdminAuthenticated::class,
        ]);

        // redirect user yang belum login sesuai konteks url
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return null;
            }
            if ($request->is('admin*') || $request->is('events*') || $request->is('dashboard*')) {
                return route('admin.login');
            }
            return route('voter.login');
        });
    })
    // paksa format response exception json untuk route api
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
