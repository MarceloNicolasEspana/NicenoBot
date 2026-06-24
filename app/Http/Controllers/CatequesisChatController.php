<?php

namespace App\Http\Controllers;

use App\Models\NicenitoQuestion;
use App\Models\Participant;
use App\Services\CatequesisChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class CatequesisChatController extends Controller
{
    public function __construct(
        private readonly CatequesisChatService $catequesisChatService,
    ) {}

    public function show(): View
    {
        return view('catequesis.chatbot');
    }

    public function chat(Request $request): JsonResponse
    {
        /** @var Participant $participant */
        // El participante SIEMPRE proviene de la sesión (vía middleware), nunca
        // del request: ignoramos cualquier participant_id que envíe el frontend.
        $participant = $request->attributes->get('participant');

        if ($limit = $this->rateLimit($participant)) {
            return $limit;
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
            'history' => ['sometimes', 'array', 'max:10'],
            'history.*.role' => ['required_with:history', 'string', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:2000'],
        ], [
            'message.required' => 'Escribe una pregunta para continuar.',
            'message.max' => 'Tu mensaje no puede superar los 500 caracteres.',
        ]);

        $startedAt = microtime(true);

        $result = $this->catequesisChatService->respond(
            $validated['message'],
            $validated['history'] ?? [],
        );

        $meta = $result['meta'];

        $this->storeQuestion($participant, $validated['message'], $result, $meta);

        // Privacidad: solo métricas, nunca el texto de la pregunta ni la respuesta.
        Log::info('niceno.request', [
            'participant_id' => $participant->id,
            'message_length' => mb_strlen($validated['message']),
            'detected_category' => $meta['detected_category'],
            'used_gemini' => $meta['used_gemini'],
            'latency_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'needs_human_guidance' => $result['needs_human_guidance'],
        ]);

        // Devolvemos exactamente el contrato del frontend (sin 'meta').
        return response()->json(Arr::except($result, 'meta'));
    }

    /**
     * Límites por participante: una pregunta cada N segundos y un máximo por
     * ventana. Devuelve una respuesta 429 amable si se exceden.
     */
    private function rateLimit(Participant $participant): ?JsonResponse
    {
        $config = config('nicenito.participant');
        $windowKey = 'nicenito-q-window:'.$participant->id;
        $cooldownKey = 'nicenito-q-cooldown:'.$participant->id;

        if (RateLimiter::tooManyAttempts($windowKey, $config['questions_per_window'])) {
            return response()->json([
                'message' => 'Has hecho varias preguntas seguidas. Tómate un momento y vuelve en unos minutos.',
            ], 429);
        }

        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            return response()->json([
                'message' => 'Espera unos segundos antes de enviar otra pregunta.',
            ], 429);
        }

        RateLimiter::hit($cooldownKey, $config['question_cooldown_seconds']);
        RateLimiter::hit($windowKey, $config['questions_window_minutes'] * 60);

        return null;
    }

    /**
     * @param  array<string,mixed>  $result
     * @param  array<string,mixed>  $meta
     */
    private function storeQuestion(Participant $participant, string $message, array $result, array $meta): void
    {
        NicenitoQuestion::create([
            'participant_id' => $participant->id,
            'weekly_content_id' => $meta['weekly_content_id'],
            'question' => $message,
            'answer' => $result['answer'],
            'sources' => $result['sources'],
            'detected_category' => $meta['detected_category'],
            'used_gemini' => $meta['used_gemini'],
            'has_weekly_content' => $meta['has_weekly_content'],
            'fixed_contents_count' => $meta['fixed_contents_count'],
            'needs_human_guidance' => $result['needs_human_guidance'],
            'answered_at' => now(),
        ]);
    }
}
