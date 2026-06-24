<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccessController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('participant_id')) {
            return redirect()->route('chatbot.show');
        }

        return view('participant.access');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'access_code' => ['required', 'string', 'max:20'],
            'pin' => ['required', 'string', 'max:12'],
        ]);

        $throttleKey = 'participant-login:'.$request->ip();
        $config = config('nicenito.participant');

        if (RateLimiter::tooManyAttempts($throttleKey, $config['login_max_attempts'])) {
            throw ValidationException::withMessages([
                'access_code' => 'Has realizado varios intentos. Espera unos minutos antes de volver a probar.',
            ]);
        }

        $participant = Participant::query()
            ->where('access_code', Str::upper(trim($credentials['access_code'])))
            ->where('is_active', true)
            ->first();

        // Mensaje genérico: nunca revelamos si el código existe o no.
        if ($participant === null || ! $participant->checkPin($credentials['pin'])) {
            RateLimiter::hit($throttleKey, $config['login_window_minutes'] * 60);

            throw ValidationException::withMessages([
                'access_code' => 'Revisa tu código y PIN e inténtalo nuevamente.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        // Regeneramos la sesión ANTES de asociar el participante.
        $request->session()->regenerate();
        $request->session()->put('participant_id', $participant->id);

        $participant->forceFill(['last_login_at' => now()])->save();

        if ($participant->must_change_pin) {
            return redirect()->route('participant.pin.show');
        }

        if (! $participant->hasAcceptedPrivacy()) {
            return redirect()->route('participant.privacy.show');
        }

        return redirect()->route('chatbot.show');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('participant_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('participant.access.show');
    }
}
