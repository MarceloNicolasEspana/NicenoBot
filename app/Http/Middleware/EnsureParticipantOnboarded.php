<?php

namespace App\Http\Middleware;

use App\Models\Participant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exige que el participante haya completado el "onboarding" antes de usar el
 * chatbot: cambiar el PIN temporal y aceptar el aviso de privacidad.
 *
 * Debe ejecutarse DESPUÉS de EnsureParticipantAuthenticated.
 */
class EnsureParticipantOnboarded
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Participant $participant */
        $participant = $request->attributes->get('participant');

        if ($participant->must_change_pin) {
            if ($request->expectsJson()) {
                abort(403, 'Debes cambiar tu PIN antes de continuar.');
            }

            return redirect()->route('participant.pin.show');
        }

        if (! $participant->hasAcceptedPrivacy()) {
            if ($request->expectsJson()) {
                abort(403, 'Debes aceptar el aviso de privacidad antes de continuar.');
            }

            return redirect()->route('participant.privacy.show');
        }

        return $next($request);
    }
}
