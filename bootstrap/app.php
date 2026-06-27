<?php

use App\Http\Middleware\EnsureParticipantAuthenticated;
use App\Http\Middleware\EnsureParticipantOnboarded;
use App\Http\Middleware\NicenoBotAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'nicenito.admin' => NicenoBotAdmin::class,
            'participant.auth' => EnsureParticipantAuthenticated::class,
            'participant.onboarded' => EnsureParticipantOnboarded::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // CSRF expirado (error 419): en vez de la pantalla "Page Expired",
        // devolvemos al usuario al formulario con un token nuevo, conservando
        // los datos (sin secretos) y un aviso claro. Así un envío con token
        // viejo (página cacheada, botón Atrás o sesión rotada) se resuelve al
        // reintentar en lugar de bloquear el acceso.
        //
        // Nota: Laravel convierte TokenMismatchException en HttpException(419)
        // antes de los render callbacks, por eso interceptamos el 419.
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($e->getStatusCode() !== 419 && ! ($e->getPrevious() instanceof TokenMismatchException)) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'La página expiró por seguridad. Recárgala e inténtalo de nuevo.',
                ], 419);
            }

            return redirect()
                ->back()
                ->withInput($request->except('password', 'pin', 'pin_confirmation', '_token'))
                ->withErrors([
                    'email' => 'Tu sesión expiró por seguridad. Vuelve a intentarlo.',
                ]);
        });
    })->create();
