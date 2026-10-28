<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustProxies;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Aplikasi desktop menjalankan Laravel di belakang proxy HTTPS lokal (127.0.0.1)
        $middleware->trustProxies(at: '127.0.0.1');

        // Pengguna yang sudah login dan membuka /login diarahkan ke dashboard sesuai perannya.
        // Tanpa ini Laravel mengarah ke '/', yang kembali ke /login (redirect berputar).
        $middleware->redirectUsersTo(fn ($request) => route(
            $request->user()?->role === 'admin' ? 'admin.dashboard' : 'kasir.dashboard'
        ));

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'event.selected' => \App\Http\Middleware\EnsureEventSelected::class,
            // 'check.event'   => \App\Http\Middleware\CheckEventActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            return redirect()->route('login')->withErrors(['error' => 'Sesi habis, silakan login kembali.']);
        });
    })->create();
