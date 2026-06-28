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
Responde siempre la última pregunta del joven; no respondas preguntas anteriores ya tratadas ni cambies de tema.
Si el contexto contiene información relacionada con la pregunta, explícala aunque sea parcial. Solo di que no tienes información cuando el contexto no contenga nada relacionado con la pregunta, y en ese caso no inventes.
Incluye una pregunta breve de reflexión solo cuando aporte valor.
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
     * Construye el mensaje de usuario que se envía a Gemini. La pregunta actual
     * va primero y con instrucción imperativa para que el modelo la responda a
     * ella (y no a una pregunta anterior del hilo). Expuesto para que la vista
     * previa del panel pueda mostrar exactamente qué se enviará, sin revelar la
     * API key.
     *
     * @param  array<int,string>  $priorQuestions  Preguntas previas del joven, solo como hilo.
     */
    public function buildUserPrompt(string $question, string $contextText, array $priorQuestions = []): string
    {
        $context = trim($contextText) !== ''
            ? $contextText
            : 'No hay contexto autorizado disponible para esta pregunta.';

        $sections = [
            "Pregunta actual del joven (respóndela SOLO a ella; no respondas preguntas anteriores):\n{$question}",
        ];

        $prior = array_values(array_filter(array_map('trim', $priorQuestions), fn (string $q) => $q !== ''));
        if ($prior !== []) {
            $list = implode("\n", array_map(fn (string $q) => "- {$q}", $prior));
            $sections[] = "Preguntas anteriores del joven (solo para entender el hilo; NO las respondas de nuevo):\n{$list}";
        }

        $sections[] = "Contexto autorizado (es tu única fuente):\n{$context}";

        return implode("\n\n", $sections);
    }

    /**
     * @param  array<int,array{role:string,content:string}>  $history
     * @return array{ok:bool,answer:string,usage:array<string,int|null>,finish_reason:?string,status:int}
     */
    public function generate(string $question, string $contextText = '', array $history = []): array
    {
        // Solo conservamos las preguntas previas del joven (rol user) como hilo.
        // NUNCA reenviamos respuestas anteriores del asistente: arrastrar una
        // respuesta previa (sobre todo un "no tengo info") hacía que el modelo
        // respondiera la pregunta anterior en vez de la actual.
        $priorQuestions = [];
        foreach ($history as $turn) {
            if (($turn['role'] ?? '') !== 'user') {
                continue;
            }
            $text = trim((string) ($turn['content'] ?? ''));
            if ($text !== '') {
                $priorQuestions[] = $text;
            }
        }

        // Un único turno de usuario: la pregunta actual va primero y el hilo
        // previo queda embebido como contexto, no como turnos a "continuar".
        $contents = [[
            'role' => 'user',
            'parts' => [['text' => $this->buildUserPrompt($question, $contextText, $priorQuestions)]],
        ]];

        $response = Http::post(
            self::API_BASE."{$this->model}:generateContent?key={$this->apiKey}",
            [
                'systemInstruction' => ['parts' => [['text' => self::SYSTEM_PROMPT]]],
                'contents' => $contents,
                'generationConfig' => [
                    // gemini-2.5-flash es un modelo "thinking": su razonamiento
                    // interno también consume maxOutputTokens. El thinking aporta
                    // precisión (analiza la pregunta y el contexto antes de
                    // responder), así que lo mantenemos pero acotado. El tope de
                    // tokens es generoso para que, tras razonar, quede espacio de
                    // sobra para la respuesta visible y no se corte a media frase.
                    'maxOutputTokens' => 2048,
                    // Temperatura baja para máxima fidelidad al contexto y menos
                    // deriva de tema.
                    'temperature' => 0.35,
                    'thinkingConfig' => [
                        'thinkingBudget' => 512,
                    ],
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
