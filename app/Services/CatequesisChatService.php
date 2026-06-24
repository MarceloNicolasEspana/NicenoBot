<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Orquesta la respuesta de Nicenito.
 *
 * Flujo (Fase 1):
 *  1. Saludos / despedidas simples -> respuesta local breve (no llama a Gemini).
 *  2. Preguntas catequéticas -> recupera contexto autorizado (semanal + fijo),
 *     se lo entrega a Gemini para redactar y adjunta las fuentes verificadas
 *     por el backend.
 *  3. Sin contexto suficiente -> respuesta prudente, sin inventar doctrina,
 *     recomendando hablar con un catequista o sacerdote (no llama a Gemini).
 */
class CatequesisChatService
{
    private const GREETINGS = ['hola', 'buenos dias', 'buenas tardes', 'buenas noches', 'saludos', 'hey', 'que tal'];

    private const FAREWELLS = ['adios', 'chao', 'hasta luego', 'hasta pronto', 'nos vemos', 'gracias por todo', 'me despido'];

    public function __construct(
        private readonly NicenitoContentContextService $context,
        private readonly GeminiModelService $gemini,
    ) {}

    /**
     * Devuelve el contrato público del chat más una clave 'meta' (no se envía
     * al frontend) con datos para registrar la pregunta en el panel.
     *
     * @param  array<int,array{role:string,content:string}>  $history
     * @return array{answer:string,sources:array<int,array<string,string>>,reflection:?string,nicenito_state:string,needs_human_guidance:bool,meta:array<string,mixed>}
     */
    public function respond(string $message, array $history = []): array
    {
        $normalized = $this->normalize($message);

        if ($this->matches($normalized, self::FAREWELLS)) {
            $this->log('farewell', false, null);

            return $this->reply(
                'Me alegra haber conversado contigo. Que Dios te bendiga y te acompañe. Vuelve cuando quieras seguir aprendiendo.',
                state: 'finalizando',
                meta: $this->meta('farewell', false),
            );
        }

        if ($this->matches($normalized, self::GREETINGS)) {
            $this->log('greeting', false, null);

            return $this->reply(
                'Hola, soy Nicenito. ¿Sobre qué te gustaría conversar hoy: el Evangelio, la oración, los sacramentos o alguna duda de la fe?',
                state: 'respondiendo',
                meta: $this->meta('greeting', false),
            );
        }

        $context = $this->context->build($message);

        // Sin contenido autorizado: respondemos con prudencia y no gastamos tokens.
        if ($context['weekly_content'] === null && $context['fixed_contents']->isEmpty()) {
            $this->log('insufficient_content', false, $context);

            return $this->reply(
                'Todavía no tengo contenido suficiente para responder eso con seguridad. Te recomiendo conversarlo con tu catequista, sacerdote o un adulto de confianza, que podrán acompañarte mejor.',
                state: 'explicando',
                needsHuman: true,
                meta: $this->meta('insufficient_content', false, $context),
            );
        }

        $result = $this->gemini->generate($message, $context['context_text'], $this->trimHistory($history));

        if (! $result['ok'] || trim($result['answer']) === '') {
            $this->log('gemini_error', true, $context, $result);

            return $this->reply(
                'En este momento no puedo generar una respuesta. Intenta de nuevo en unos minutos o conversa tu duda con tu catequista o sacerdote.',
                state: 'explicando',
                needsHuman: true,
                meta: $this->meta('gemini_error', false, $context),
            );
        }

        $this->log('answer', true, $context, $result);

        return $this->reply(
            $result['answer'],
            sources: $context['sources'],
            state: 'explicando',
            meta: $this->meta('answer', true, $context),
        );
    }

    /**
     * Metadatos para el registro de la pregunta (no forman parte del contrato
     * que recibe el frontend).
     *
     * @param  array<string,mixed>|null  $context
     * @return array<string,mixed>
     */
    private function meta(string $intent, bool $usedGemini, ?array $context = null): array
    {
        $weekly = $context['weekly_content'] ?? null;
        $fixed = $context['fixed_contents'] ?? collect();

        return [
            'intent' => $intent,
            'used_gemini' => $usedGemini,
            'has_weekly_content' => $weekly !== null,
            'weekly_content_id' => $weekly?->id,
            'fixed_contents_count' => $fixed->count(),
            'detected_category' => $fixed->first()->category ?? ($weekly->category ?? null),
        ];
    }

    /**
     * @param  array<int,array<string,string>>  $sources
     * @param  array<string,mixed>  $meta
     * @return array{answer:string,sources:array<int,array<string,string>>,reflection:?string,nicenito_state:string,needs_human_guidance:bool,meta:array<string,mixed>}
     */
    private function reply(string $answer, array $sources = [], string $state = 'respondiendo', bool $needsHuman = false, array $meta = []): array
    {
        return [
            'answer' => trim($answer),
            'sources' => $sources,
            'reflection' => null,
            'nicenito_state' => $state,
            'needs_human_guidance' => $needsHuman,
            'meta' => $meta,
        ];
    }

    /**
     * Reduce el historial a los últimos N mensajes útiles definidos en config.
     *
     * @param  array<int,array{role:string,content:string}>  $history
     * @return array<int,array{role:string,content:string}>
     */
    private function trimHistory(array $history): array
    {
        $max = (int) config('nicenito.context.history_messages', 2);

        return array_slice(array_values($history), -$max);
    }

    /**
     * Logging respetuoso de la privacidad: nunca el texto completo de la
     * pregunta ni el contexto en producción. Solo métricas.
     *
     * @param  array<string,mixed>|null  $context
     * @param  array<string,mixed>|null  $gemini
     */
    private function log(string $intent, bool $usedGemini, ?array $context, ?array $gemini = null): void
    {
        $payload = [
            'intent' => $intent,
            'used_gemini' => $usedGemini,
            'had_weekly' => $context !== null && $context['weekly_content'] !== null,
            'fixed_count' => $context !== null ? $context['fixed_contents']->count() : 0,
            'tokens' => $gemini['usage'] ?? null,
            'finish_reason' => $gemini['finish_reason'] ?? null,
            'error_status' => ($gemini !== null && ! ($gemini['ok'] ?? true)) ? $gemini['status'] : null,
        ];

        if (config('nicenito.detailed_logging')) {
            $payload['confidence'] = $context['confidence'] ?? null;
            $payload['weekly_title'] = $context['weekly_content']->title ?? null;
            $payload['fixed_titles'] = $context !== null
                ? $context['fixed_contents']->pluck('title')->all()
                : [];
        }

        Log::info('niceno.turn', $payload);
    }

    private function matches(string $normalized, array $phrases): bool
    {
        // Saludos/despedidas: mensajes cortos que contienen la expresión.
        if (Str::wordCount($normalized) > 6) {
            return false;
        }

        foreach ($phrases as $phrase) {
            if (str_contains($normalized, $phrase)) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^\pL\pN\s]+/u', ' ')
            ->squish()
            ->value();
    }
}
