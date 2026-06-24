<?php

namespace Tests\Feature;

use App\Models\NicenitoQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruneNicenitoQuestionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_anonymizes_old_questions_but_keeps_metrics(): void
    {
        config(['nicenito.question_retention_days' => 90]);

        $old = NicenitoQuestion::factory()->create([
            'question' => 'Pregunta antigua sensible',
            'answer' => 'Respuesta antigua',
            'detected_category' => 'Oración',
            'used_gemini' => true,
        ]);
        // Envejecemos el registro sin tocar los timestamps automáticamente.
        NicenitoQuestion::query()->whereKey($old->id)->update(['created_at' => now()->subDays(200)]);

        $recent = NicenitoQuestion::factory()->create([
            'question' => 'Pregunta reciente',
            'answer' => 'Respuesta reciente',
        ]);

        $this->artisan('nicenito:prune-questions')->assertSuccessful();

        $old->refresh();
        $this->assertNull($old->question);
        $this->assertNull($old->answer);
        // Métricas agregadas se conservan.
        $this->assertSame('Oración', $old->detected_category);
        $this->assertTrue($old->used_gemini);

        // La pregunta reciente no se toca.
        $recent->refresh();
        $this->assertSame('Pregunta reciente', $recent->question);
    }
}
