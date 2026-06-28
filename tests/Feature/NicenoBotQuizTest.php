<?php

namespace Tests\Feature;

use App\Models\NicenoBotContent;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NicenoBotQuizTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate(config('nicenito.admin_role')));

        return $user;
    }

    private function weeklyWithQuiz(array $quiz): NicenoBotContent
    {
        return NicenoBotContent::factory()->weekly()->create(['quiz_questions' => $quiz]);
    }

    // --- Autoría en el panel ------------------------------------------------

    public function test_admin_can_store_weekly_quiz_questions_from_text(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/nicenito/contenidos', [
                'type' => 'weekly',
                'action' => 'draft',
                'title' => 'Semana con quiz',
                'summary' => 'Resumen.',
                'content' => 'Contenido.',
                'gospel_reference' => 'Mateo 10, 26-33',
                'starts_at' => NicenoBotContent::now()->format('Y-m-d H:i'),
                'ends_at' => NicenoBotContent::now()->addDays(6)->format('Y-m-d H:i'),
                'quiz_questions_text' => "¿Qué propone Jesús frente al miedo? :: Confiar en Dios | Esconderse | Resignarse :: 0\n¿Dios cuida de cada persona? :: Sí | No :: 0",
            ])
            ->assertRedirect();

        $content = NicenoBotContent::query()->where('title', 'Semana con quiz')->firstOrFail();

        $this->assertCount(2, $content->quiz_questions);
        $this->assertSame('¿Qué propone Jesús frente al miedo?', $content->quiz_questions[0]['question']);
        $this->assertSame(['Confiar en Dios', 'Esconderse', 'Resignarse'], $content->quiz_questions[0]['options']);
        $this->assertSame(0, $content->quiz_questions[0]['correct']);
    }

    public function test_correct_index_out_of_range_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/nicenito/contenidos', [
                'type' => 'weekly',
                'action' => 'draft',
                'title' => 'Quiz inválido',
                'summary' => 'Resumen.',
                'content' => 'Contenido.',
                'gospel_reference' => 'Mateo 10, 26-33',
                'starts_at' => NicenoBotContent::now()->format('Y-m-d H:i'),
                'ends_at' => NicenoBotContent::now()->addDays(6)->format('Y-m-d H:i'),
                'quiz_questions_text' => '¿Pregunta? :: A | B :: 5',
            ])
            ->assertSessionHasErrors('quiz_questions.0.correct');
    }

    // --- Disparador en el chat ----------------------------------------------

    public function test_window_limit_returns_quiz_without_correct_answer(): void
    {
        $participant = Participant::factory()->create();
        $this->weeklyWithQuiz([
            ['question' => '¿Qué propone Jesús?', 'options' => ['Confiar', 'Huir'], 'correct' => 0],
        ]);

        $windowKey = 'nicenito-q-window:'.$participant->id;
        for ($i = 0; $i < config('nicenito.participant.questions_per_window'); $i++) {
            RateLimiter::hit($windowKey, 900);
        }

        $this->withSession(['participant_id' => $participant->id])
            ->postJson(route('chatbot.chat'), ['message' => 'Hola'])
            ->assertStatus(429)
            ->assertJsonPath('reason', 'rate_window')
            ->assertJsonPath('quiz.questions.0.question', '¿Qué propone Jesús?')
            ->assertJsonPath('quiz.questions.0.options.0', 'Confiar')
            // La alternativa correcta NUNCA viaja al navegador.
            ->assertJsonMissingPath('quiz.questions.0.correct');
    }

    public function test_cooldown_limit_has_no_quiz(): void
    {
        $participant = Participant::factory()->create();
        $this->weeklyWithQuiz([
            ['question' => 'Q', 'options' => ['a', 'b'], 'correct' => 0],
        ]);

        RateLimiter::hit('nicenito-q-cooldown:'.$participant->id, 8);

        $this->withSession(['participant_id' => $participant->id])
            ->postJson(route('chatbot.chat'), ['message' => 'Hola'])
            ->assertStatus(429)
            ->assertJsonPath('reason', 'cooldown')
            ->assertJsonMissingPath('quiz');
    }

    // --- Envío del quiz ------------------------------------------------------

    public function test_quiz_submission_grades_and_stores_attempt(): void
    {
        $participant = Participant::factory()->create();
        $weekly = $this->weeklyWithQuiz([
            ['question' => 'Q1', 'options' => ['a', 'b'], 'correct' => 0],
            ['question' => 'Q2', 'options' => ['x', 'y', 'z'], 'correct' => 2],
        ]);

        $this->withSession(['participant_id' => $participant->id])
            ->postJson(route('chatbot.quiz'), ['answers' => [0, 1]])
            ->assertOk()
            ->assertJsonPath('score', 1)
            ->assertJsonPath('total', 2)
            ->assertJsonPath('results.0.is_correct', true)
            ->assertJsonPath('results.1.is_correct', false)
            ->assertJsonPath('results.1.correct_index', 2);

        $this->assertDatabaseHas('nicenito_quiz_attempts', [
            'participant_id' => $participant->id,
            'nicenito_content_id' => $weekly->id,
            'score' => 1,
            'total' => 2,
        ]);
    }

    public function test_quiz_requires_participant_session(): void
    {
        $this->weeklyWithQuiz([
            ['question' => 'Q', 'options' => ['a', 'b'], 'correct' => 0],
        ]);

        $this->postJson(route('chatbot.quiz'), ['answers' => [0]])->assertStatus(401);
    }
}
