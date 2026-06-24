<?php

namespace Tests\Feature;

use App\Models\NicenitoContent;
use App\Models\NicenitoQuestion;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CatequesisChatTest extends TestCase
{
    use RefreshDatabase;

    private function participant(): Participant
    {
        return Participant::factory()->create();
    }

    private function fakeGemini(string $text = 'Respuesta de prueba de Nicenito.'): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => $text]]],
                    'finishReason' => 'STOP',
                ]],
                'usageMetadata' => ['promptTokenCount' => 100, 'candidatesTokenCount' => 50, 'totalTokenCount' => 150],
            ]),
        ]);
    }

    public function test_chatbot_page_requires_participant_session(): void
    {
        $this->get('/chatbot-catequesis')->assertRedirect(route('participant.access.show'));
    }

    public function test_chatbot_page_is_accessible_with_participant_session(): void
    {
        $this->withSession(['participant_id' => $this->participant()->id])
            ->get('/chatbot-catequesis')
            ->assertOk()
            ->assertSee('Preg&uacute;ntale a Nicenito', false);
    }

    public function test_chat_endpoint_validates_message_length(): void
    {
        $this->withSession(['participant_id' => $this->participant()->id])
            ->postJson(route('chatbot.chat'), ['message' => str_repeat('a', 501)])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['message']);
    }

    public function test_greeting_does_not_call_gemini(): void
    {
        Http::fake();

        $this->withSession(['participant_id' => $this->participant()->id])
            ->postJson(route('chatbot.chat'), ['message' => 'Hola Nicenito'])
            ->assertOk()
            ->assertJsonPath('nicenito_state', 'respondiendo')
            ->assertJsonMissingPath('meta');

        Http::assertNothingSent();
    }

    public function test_authenticated_chat_stores_question_for_session_participant(): void
    {
        $this->fakeGemini();

        $participant = $this->participant();
        NicenitoContent::factory()->weekly()->create([
            'gospel_reference' => 'Mateo 10, 26-33',
            'biblical_references' => ['Mateo 10, 26-33'],
            'tags' => ['miedo'],
        ]);

        $this->withSession(['participant_id' => $participant->id])
            ->postJson(route('chatbot.chat'), ['message' => 'Tengo miedo, ¿qué hago?'])
            ->assertOk()
            ->assertJsonPath('sources.0.type', 'Evangelio');

        $this->assertDatabaseHas('nicenito_questions', [
            'participant_id' => $participant->id,
            'used_gemini' => true,
        ]);
    }

    public function test_frontend_cannot_assign_participant_id(): void
    {
        $this->fakeGemini();

        $sessionParticipant = $this->participant();
        $otherParticipant = $this->participant();

        $this->withSession(['participant_id' => $sessionParticipant->id])
            ->postJson(route('chatbot.chat'), [
                'message' => 'Hola',
                'participant_id' => $otherParticipant->id,
            ])
            ->assertOk();

        $question = NicenitoQuestion::query()->latest('id')->first();
        $this->assertSame($sessionParticipant->id, $question->participant_id);
    }

    public function test_question_rate_limit_blocks_rapid_requests(): void
    {
        $this->fakeGemini();
        $participant = $this->participant();

        $this->withSession(['participant_id' => $participant->id])
            ->postJson(route('chatbot.chat'), ['message' => 'Primera pregunta sobre la fe'])
            ->assertOk();

        // Segunda pregunta inmediata: bloqueada por el enfriamiento.
        $this->withSession(['participant_id' => $participant->id])
            ->postJson(route('chatbot.chat'), ['message' => 'Segunda pregunta inmediata'])
            ->assertStatus(429);
    }
}
