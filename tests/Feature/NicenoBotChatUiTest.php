<?php

namespace Tests\Feature;

use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cubre la conmutación de interfaz (Vue / legacy) y la seguridad de los datos
 * de bootstrap entregados a la interfaz Vue.
 */
class NicenoBotChatUiTest extends TestCase
{
    use RefreshDatabase;

    private function participant(array $attributes = []): Participant
    {
        return Participant::factory()->create($attributes);
    }

    public function test_vue_ui_renders_mount_point_by_default(): void
    {
        config(['nicenito.chat_ui' => 'vue']);

        $this->withSession(['participant_id' => $this->participant()->id])
            ->get('/chatbot-catequesis')
            ->assertOk()
            ->assertSee('id="nicenito-app"', false)
            ->assertDontSee('id="catequesis-chat"', false);
    }

    public function test_legacy_ui_can_be_restored_with_flag(): void
    {
        config(['nicenito.chat_ui' => 'legacy']);

        $this->withSession(['participant_id' => $this->participant()->id])
            ->get('/chatbot-catequesis')
            ->assertOk()
            ->assertSee('id="catequesis-chat"', false)
            ->assertDontSee('id="nicenito-app"', false);
    }

    public function test_vue_bootstrap_exposes_safe_display_name(): void
    {
        config(['nicenito.chat_ui' => 'vue']);

        // Usamos valores ASCII: @json escapa Unicode (í -> í), así que las
        // aserciones de subcadena deben evitar acentos.
        $participant = $this->participant([
            'display_name' => 'Martincito',
            'full_name' => 'Martincito SecretoApellido',
            'access_code' => 'NCE-ZZZZ',
        ]);

        $response = $this->withSession(['participant_id' => $participant->id])
            ->get('/chatbot-catequesis')
            ->assertOk();

        // El nombre visible seguro sí aparece...
        $response->assertSee('Martincito', false);
        // ...pero nunca el nombre completo ni el código de acceso.
        $response->assertDontSee('SecretoApellido', false);
        $response->assertDontSee('NCE-ZZZZ', false);
    }

    public function test_vue_bootstrap_includes_suggested_questions_and_limit(): void
    {
        config([
            'nicenito.chat_ui' => 'vue',
            'nicenito.suggested_questions' => ['¿Qué es la gracia?'],
        ]);

        $this->withSession(['participant_id' => $this->participant()->id])
            ->get('/chatbot-catequesis')
            ->assertOk()
            ->assertSee('gracia', false)
            ->assertSee('maxLength', false);
    }
}
