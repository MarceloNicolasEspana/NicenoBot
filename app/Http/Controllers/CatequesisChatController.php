<?php

namespace App\Http\Controllers;

use App\Models\NicenoBotContent;
use App\Models\NicenoBotQuestion;
use App\Models\NicenoBotQuizAttempt;
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

    public function show(Request $request): View
    {
        /** @var Participant $participant */
        // El participante SIEMPRE proviene de la sesión (vía middleware).
        $participant = $request->attributes->get('participant');

        // Datos mínimos y seguros para arrancar la interfaz Vue. Nunca se
        // exponen nombre completo, PIN, access_code, IDs internos ni prompts.
        $bootstrap = [
            'displayName' => $participant->safeName(),
            'endpoint' => route('chatbot.chat'),
            'quizUrl' => route('chatbot.quiz'),
            'accessUrl' => route('participant.access.show'),
            'logoutUrl' => route('participant.logout'),
            'maxLength' => 500,
            'suggestedQuestions' => array_values(config('nicenito.suggested_questions', [])),
            'avatarBasePath' => '/images/nicenito/clean/',
            'brandName' => 'NicenoBot',
        ];

        return view('catequesis.chatbot', [
            'chatUi' => config('nicenito.chat_ui'),
            'bootstrap' => $bootstrap,
        ]);
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
            // Límite de ventana: aprovechamos la pausa para ofrecer el quiz del
            // contenido semanal (si existe). El front lo muestra en un modal.
            return response()->json(array_filter([
                'message' => 'Has hecho varias preguntas seguidas. Tómate un momento y vuelve en unos minutos.',
                'reason' => 'rate_window',
                'quiz' => $this->activeWeeklyQuiz(),
            ], fn ($value) => $value !== null), 429);
        }

        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            return response()->json([
                'message' => 'Espera unos segundos antes de enviar otra pregunta.',
                'reason' => 'cooldown',
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
        NicenoBotQuestion::create([
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

    /**
     * Recibe las respuestas del quiz, corrige contra el contenido semanal
     * vigente del SERVIDOR (ignora cualquier dato manipulado del cliente),
     * guarda el intento para el catequista y devuelve el resultado.
     */
    public function quiz(Request $request): JsonResponse
    {
        /** @var Participant $participant */
        $participant = $request->attributes->get('participant');

        $weekly = NicenoBotContent::query()->activeWeekly()->first();

        if (! $weekly || blank($weekly->quiz_questions)) {
            return response()->json(['message' => 'No hay un quiz disponible en este momento.'], 422);
        }

        $questions = array_values($weekly->quiz_questions);

        $validated = $request->validate([
            'answers' => ['required', 'array', 'max:'.count($questions)],
            'answers.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $results = [];
        $score = 0;

        foreach ($questions as $i => $question) {
            $selected = $validated['answers'][$i] ?? null;
            $correct = (int) ($question['correct'] ?? 0);
            $isCorrect = $selected !== null && (int) $selected === $correct;

            if ($isCorrect) {
                $score++;
            }

            $results[] = [
                'question_index' => $i,
                'selected_index' => $selected,
                'correct_index' => $correct,
                'is_correct' => $isCorrect,
            ];
        }

        $total = count($questions);

        NicenoBotQuizAttempt::create([
            'participant_id' => $participant->id,
            'nicenito_content_id' => $weekly->id,
            'answers' => array_map(fn ($r) => Arr::only($r, ['question_index', 'selected_index', 'is_correct']), $results),
            'score' => $score,
            'total' => $total,
            'completed_at' => now(),
        ]);

        return response()->json([
            'results' => $results,
            'score' => $score,
            'total' => $total,
        ]);
    }

    /**
     * Quiz del contenido semanal vigente, SIN la alternativa correcta (esa solo
     * vive en el servidor; la corrección se hace en quiz()).
     *
     * @return array<string,mixed>|null
     */
    private function activeWeeklyQuiz(): ?array
    {
        $weekly = NicenoBotContent::query()->activeWeekly()->first();

        if (! $weekly || blank($weekly->quiz_questions)) {
            return null;
        }

        return [
            'content_id' => $weekly->id,
            'title' => $weekly->title,
            'questions' => collect(array_values($weekly->quiz_questions))
                ->map(fn (array $q, int $i) => [
                    'index' => $i,
                    'question' => $q['question'] ?? '',
                    'options' => array_values($q['options'] ?? []),
                ])
                ->all(),
        ];
    }
}
