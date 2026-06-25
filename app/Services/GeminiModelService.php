<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiModelService
{
    private const API_BASE = 'https://generativelanguage.googleapis.com/v1beta/models/';

    public const SYSTEM_PROMPT = <<<'PROMPT'
Eres NicenoBot, un asistente de catequesis católica para adolescentes.

Responde únicamente usando el contexto autorizado entregado por el sistema.
No inventes citas bíblicas, números del Catecismo, frases de santos, documentos ni enseñanzas de la Iglesia.
No solicites datos personales.
No reemplaces a un sacerdote, catequista, adulto responsable, médico o profesional.
Usa lenguaje sencillo, cercano y respetuoso.
Responde normalmente entre 90 y 180 palabras.
No repitas la pregunta.
Incluye una pregunta breve de reflexión solo cuando aporte valor.
Si el contexto no alcanza, dilo claramente.
No generes fuentes: las fuentes son entregadas por el backend.
PROMPT;

    public function __construct(
        private readonly string $apiKey = '',
        private readonly string $model = 'gemini-2.5-flash',
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            apiKey: (string) config('services.nicenobot.api_key'),
            model: (string) config('services.nicenobot.model', 'gemini-2.5-flash'),
        );
    }

    /**
     * Construye el mensaje de usuario que se envía a Gemini (pregunta +
     * contexto autorizado). Expuesto para que la vista previa del panel pueda
     * mostrar exactamente qué se enviará, sin revelar la API key.
     */
    public function buildUserPrompt(string $question, string $contextText): string
    {
        $context = trim($contextText) !== ''
            ? $contextText
            : 'No hay contexto autorizado disponible para esta pregunta.';

        return "Contexto autorizado (es tu única fuente):\n{$context}\n\nPregunta del joven: {$question}";
    }

    /**
     * @param  array<int,array{role:string,content:string}>  $history
     * @return array{ok:bool,answer:string,usage:array<string,int|null>,finish_reason:?string,status:int}
     */
    public function generate(string $question, string $contextText = '', array $history = []): array
    {
        $contents = [];

        foreach ($history as $turn) {
            $role = ($turn['role'] ?? '') === 'assistant' ? 'model' : 'user';
            $text = trim((string) ($turn['content'] ?? ''));

            if ($text !== '') {
                $contents[] = ['role' => $role, 'parts' => [['text' => $text]]];
            }
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $this->buildUserPrompt($question, $contextText)]],
        ];

        $response = Http::post(
            self::API_BASE."{$this->model}:generateContent?key={$this->apiKey}",
            [
                'systemInstruction' => ['parts' => [['text' => self::SYSTEM_PROMPT]]],
                'contents' => $contents,
                'generationConfig' => [
                    'maxOutputTokens' => 400,
                    'temperature' => 0.6,
                ],
            ]
        );

        if ($response->failed()) {
            return [
                'ok' => false,
                'answer' => '',
                'usage' => [],
                'finish_reason' => null,
                'status' => $response->status(),
            ];
        }

        $usage = $response->json('usageMetadata') ?? [];

        return [
            'ok' => true,
            'answer' => (string) ($response->json('candidates.0.content.parts.0.text') ?? ''),
            'usage' => [
                'prompt_tokens' => $usage['promptTokenCount'] ?? null,
                'candidate_tokens' => $usage['candidatesTokenCount'] ?? null,
                'total_tokens' => $usage['totalTokenCount'] ?? null,
            ],
            'finish_reason' => $response->json('candidates.0.finishReason'),
            'status' => $response->status(),
        ];
    }
}
