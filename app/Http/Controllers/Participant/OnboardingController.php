<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Pasos previos al chatbot: cambio de PIN temporal y aceptación del aviso de
 * privacidad. Requiere sesión de participante (participant.auth), pero NO el
 * middleware de onboarding, para no provocar bucles de redirección.
 */
class OnboardingController extends Controller
{
    public function showPin(Request $request): View
    {
        return view('participant.change-pin');
    }

    public function updatePin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pin' => ['required', 'string', 'digits:6', 'confirmed'],
        ], [
            'pin.digits' => 'El PIN debe tener exactamente 6 dígitos.',
            'pin.confirmed' => 'La confirmación del PIN no coincide.',
        ]);

        /** @var Participant $participant */
        $participant = $request->attributes->get('participant');

        $participant->setPin($validated['pin']);
        $participant->must_change_pin = false;
        $participant->last_login_at = now();
        $participant->save();

        if (! $participant->hasAcceptedPrivacy()) {
            return redirect()->route('participant.privacy.show');
        }

        return redirect()->route('chatbot.show');
    }

    public function showPrivacy(Request $request): View
    {
        return view('participant.privacy');
    }

    public function acceptPrivacy(Request $request): RedirectResponse
    {
        $request->validate([
            'accept' => ['accepted'],
        ], [
            'accept.accepted' => 'Debes confirmar que entiendes el aviso para continuar.',
        ]);

        /** @var Participant $participant */
        $participant = $request->attributes->get('participant');

        if (! $participant->hasAcceptedPrivacy()) {
            $participant->forceFill(['privacy_notice_accepted_at' => now()])->save();
        }

        return redirect()->route('chatbot.show');
    }
}
