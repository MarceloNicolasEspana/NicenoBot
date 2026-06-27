<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Login mínimo basado en el guard de sesión que trae Laravel. No se instaló
 * Breeze/Jetstream para no agregar paquetes; solo cubre el acceso al panel.
 */
class LoginController extends Controller
{
    public function show(): Response
    {
        // Sin caché: el formulario siempre se sirve con un token CSRF vigente,
        // evitando el 419 al enviarlo desde una página cacheada o el botón Atrás.
        return response()
            ->view('auth.login')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no son válidas.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.nicenito.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
