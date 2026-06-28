<?php

namespace Tests\Feature;

use App\Services\GeminiModelService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Verifica la coherencia del turno enviado a Gemini: la pregunta actual debe ir
 * primero y con instrucción imperativa, y las respuestas anteriores del
 * asistente NO deben reenviarse (evita responder la pregunta anterior).
 */
class GeminiPromptTest extends TestCase
{
    private function fakeOk(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => 'Respuesta.']]],
                    'finishReason' => 'STOP',
                ]],
                'usageMetadata' => ['totalTokenCount' => 10],
            ]),
        ]);
    }

    private function sentPromptText(): string
    {
        $text = '';
        Http::assertSent(function ($request) use (&$text) {
            $text = $request->data()['contents'][0]['parts'][0]['text'] ?? '';

            return true;
        });

        return $text;
    }

    public function test_current_question_is_prioritized_and_assistant_history_is_dropped(): void
    {
        $this->fakeOk();

        $service = new GeminiModelService('test-key', 'gemini-2.5-flash');
        $service->generate(
            '¿Qué significa la frase sobre las tinieblas y las azoteas?',
            'CONTEXTO SEMANAL Mateo 10',
            [
                ['role' => 'user', 'content' => 'Pregunta anterior sobre pajarillos'],
                ['role' => 'assistant', 'content' => 'No tengo esa información en este momento.'],
            ],
        );

        $prompt = $this->sentPromptText();

        // La pregunta actual va primero y marcada como tal.
        $this->assertStringContainsString('Pregunta actual del joven', $prompt);
        $this->assertStringContainsString('tinieblas y las azoteas', $prompt);

        // La pregunta previa del joven se conserva como hilo...
        $this->assertStringContainsString('pajarillos', $prompt);
        // ...pero la respuesta de fallback del asistente NUNCA se reenvía.
        $this->assertStringNotContainsString('No tengo esa información', $prompt);
    }

    public function test_request_sends_a_single_user_turn(): void
    {
        $this->fakeOk();

        $service = new GeminiModelService('test-key', 'gemini-2.5-flash');
        $service->generate('¿Qué es la fe?', 'CONTEXTO', [
            ['role' => 'user', 'content' => 'Antes pregunté otra cosa'],
            ['role' => 'assistant', 'content' => 'Respuesta vieja'],
        ]);

        Http::assertSent(function ($request) {
            $contents = $request->data()['contents'];
            $this->assertCount(1, $contents);
            $this->assertSame('user', $contents[0]['role']);

            return true;
        });
    }
}
