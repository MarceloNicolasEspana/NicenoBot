<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiModelService
{
    private const API_BASE = 'https://generativelanguage.googleapis.com/v1beta/models/';

    private const SYSTEM_PROMPT = 'Eres Nicenito, un asistente de catequesis de la fe cristiana católica. Respondes con claridad, calidez y profundidad espiritual. Tus respuestas son apropiadas para jóvenes en formación religiosa. No reemplazas al catequista ni al sacerdote. Sé breve y directo: responde en no más de 80 palabras. Cuando sea relevante, incluye al final una referencia bíblica concreta en una sola línea.';

    public function __construct(
        private readonly string $apiKey = '',
        private readonly string $model = 'gemini-2.5-flash',
    ) {
    }

    public static function fromConfig(): self
    {
        return new self(
            apiKey: config('services.nicenobot.api_key'),
            model: config('services.nicenobot.model', 'gemini-2.5-flash'),
        );
    }

    public function ask(string $prompt): string
    {
        Log::info('gemini.request', [
            'model'         => $this->model,
            'prompt_length' => mb_strlen($prompt),
        ]);

        $response = Http::post(
            self::API_BASE . "{$this->model}:generateContent?key={$this->apiKey}",
            [
                'systemInstruction' => [
                    'parts' => [['text' => self::SYSTEM_PROMPT]],
                ],
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 200,
                    'temperature'     => 0.7,
                ],
            ]
        );

        if ($response->failed()) {
            Log::error('gemini.error', [
                'status' => $response->status(),
                'reason' => $response->json('error.message') ?? $response->body(),
            ]);

            return 'En este momento no puedo responder. Consultá con tu catequista o sacerdote.';
        }

        $usage = $response->json('usageMetadata') ?? [];

        Log::info('gemini.response', [
            'prompt_tokens'    => $usage['promptTokenCount']     ?? null,
            'candidate_tokens' => $usage['candidatesTokenCount'] ?? null,
            'total_tokens'     => $usage['totalTokenCount']      ?? null,
            'finish_reason'    => $response->json('candidates.0.finishReason'),
        ]);

        return $response->json('candidates.0.content.parts.0.text')
            ?? 'No pude generar una respuesta. Intentá reformular tu pregunta.';
    }
}
