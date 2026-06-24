<?php

use App\Http\Middleware\EnsureParticipantAuthenticated;
use App\Http\Middleware\EnsureParticipantOnboarded;
use App\Http\Middleware\NicenitoAdmin;
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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'nicenito.admin' => NicenitoAdmin::class,
            'participant.auth' => EnsureParticipantAuthenticated::class,
            'participant.onboarded' => EnsureParticipantOnboarded::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
