<?php

namespace Tests\Feature;

use App\Models\NicenoBotQuestion;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NicenoBotParticipantAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate(config('nicenito.admin_role')));

        return $user;
    }

    public function test_admin_can_view_participants_and_questions(): void
    {
        Participant::factory()->has(NicenoBotQuestion::factory()->count(2), 'questions')->create();

        $admin = $this->admin();
        $this->actingAs($admin)->get('/admin/nicenito/participantes')->assertOk();
        $this->actingAs($admin)->get('/admin/nicenito/preguntas')->assertOk();
    }

    public function test_admin_can_create_participant_and_gets_credentials(): void
    {
        $response = $this->actingAs($this->admin())
            ->post('/admin/nicenito/participantes', [
                'full_name' => 'Ana Soto',
                'display_name' => 'Ana S.',
                'group_name' => 'Confirmación 2026',
                'is_active' => '1',
            ]);

        $participant = Participant::query()->where('full_name', 'Ana Soto')->first();
        $this->assertNotNull($participant);
        $this->assertTrue($participant->must_change_pin);
        $this->assertStringStartsWith('NCE-', $participant->access_code);
        $response->assertRedirect(route('admin.nicenito.participantes.credentials', $participant));
        $response->assertSessionHas('temp_pin');
    }

    public function test_unauthorized_user_cannot_view_questions(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/nicenito/preguntas')->assertForbidden();
    }

    public function test_admin_can_mark_follow_up(): void
    {
        $question = NicenoBotQuestion::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.nicenito.preguntas.follow-up', $question), [
                'follow_up_status' => 'catechist_follow_up',
                'follow_up_notes' => 'Conversar en la próxima sesión.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('nicenito_questions', [
            'id' => $question->id,
            'follow_up_status' => 'catechist_follow_up',
        ]);
    }
}
