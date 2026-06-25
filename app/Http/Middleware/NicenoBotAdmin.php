<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protege el panel /admin/nicenito.
 *
 * La autorización se basa en roles de spatie/laravel-permission: solo usuarios
 * autenticados con el rol configurado en config('nicenito.admin_role') pueden
 * continuar. Si no hay sesión, se redirige al login.
 */
class NicenoBotAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $role = config('nicenito.admin_role');

        abort_unless(Auth::user()->hasRole($role), 403, 'No tienes acceso al panel de NicenoBot.');

        return $next($request);
    }
}
