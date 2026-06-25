<?php

namespace Tests\Feature;

use App\Models\NicenoBotContent;
use App\Services\NicenoBotContentContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NicenoBotContentContextTest extends TestCase
{
    use RefreshDatabase;

    private function service(): NicenoBotContentContextService
    {
        return app(NicenoBotContentContextService::class);
    }

    public function test_active_published_weekly_is_retrieved(): void
    {
        $weekly = NicenoBotContent::factory()->weekly()->create([
            'title' => 'No tengan miedo',
            'tags' => ['miedo'],
        ]);

        $context = $this->service()->build('Tengo miedo de algo');

        $this->assertNotNull($context['weekly_content']);
        $this->assertSame($weekly->id, $context['weekly_content']->id);
    }

    public function test_expired_weekly_is_not_used(): void
    {
        NicenoBotContent::factory()->weekly()->create([
            'starts_at' => NicenoBotContent::now()->subDays(20),
            'ends_at' => NicenoBotContent::now()->subDays(13),
        ]);

        $context = $this->service()->build('Tengo miedo de algo');

        $this->assertNull($context['weekly_content']);
    }

    public function test_trinity_question_retrieves_the_right_fixed_contents(): void
    {
        $trinity = NicenoBotContent::factory()->create([
            'title' => 'La Santísima Trinidad',
            'category' => 'Jesús y Trinidad',
            'tags' => ['trinidad', 'padre', 'hijo', 'espiritu santo'],
            'faq' => [['question' => '¿Por qué Jesús es Dios si es el Hijo?', 'answer' => 'Porque comparte la naturaleza divina del Padre.']],
        ]);

        // Contenido fijo irrelevante que NO debe recuperarse.
        NicenoBotContent::factory()->create([
            'title' => 'La oración personal',
            'category' => 'Oración',
            'tags' => ['oracion', 'rezar'],
        ]);

        $context = $this->service()->build('¿Por qué Jesús es Dios si Jesús es el Hijo?');

        $titles = $context['fixed_contents']->pluck('title')->all();
        $this->assertContains('La Santísima Trinidad', $titles);
        $this->assertNotContains('La oración personal', $titles);
    }

    public function test_generic_word_alone_does_not_pull_irrelevant_content(): void
    {
        NicenoBotContent::factory()->create([
            'title' => 'Los sacramentos',
            'category' => 'Sacramentos',
            'tags' => ['sacramentos', 'bautismo'],
            'summary' => 'Signos del amor de Dios.',
            'content' => 'Texto sin la palabra genérica.',
        ]);

        // Solo contiene palabras genéricas (stopwords): no debería recuperar nada.
        $context = $this->service()->build('Dios fe Jesús');

        $this->assertTrue($context['fixed_contents']->isEmpty());
    }
}
