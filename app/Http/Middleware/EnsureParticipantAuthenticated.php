<?php

namespace App\Http\Middleware;

use App\Models\Participant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garantiza que exista una sesión válida de participante.
 *
 * El identificador del participante vive EXCLUSIVAMENTE en la sesión del
 * servidor (clave 'participant_id'). Nunca se lee desde el request, query
 * string ni del frontend. El participante resuelto queda disponible para los
 * controladores vía request->attributes('participant').
 */
class EnsureParticipantAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $participantId = $request->session()->get('participant_id');

        $participant = $participantId
            ? Participant::query()->where('is_active', true)->find($participantId)
            : null;

        if ($participant === null) {
            $request->session()->forget('participant_id');

            if ($request->expectsJson()) {
                abort(401, 'Tu sesión expiró. Vuelve a ingresar con tu código y PIN.');
            }

            return redirect()->route('participant.access.show');
        }

        $request->attributes->set('participant', $participant);

        return $next($request);
    }
}
