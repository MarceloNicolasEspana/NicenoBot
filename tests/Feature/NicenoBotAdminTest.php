<?php

namespace Tests\Feature;

use App\Enums\NicenoBotContentStatus;
use App\Models\NicenoBotContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NicenoBotAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create(['email' => 'admin@nicenito.test']);
        $user->assignRole(Role::findOrCreate(config('nicenito.admin_role')));

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/nicenito/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_user_without_role_is_forbidden(): void
    {
        $user = User::factory()->create(['email' => 'random@example.com']);

        $this->actingAs($user)->get('/admin/nicenito/dashboard')->assertForbidden();
    }

    public function test_allowed_admin_can_open_dashboard(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/nicenito/dashboard')
            ->assertOk();
    }

    public function test_admin_can_create_fixed_content(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/nicenito/contenidos', [
                'type' => 'fixed',
                'action' => 'publish',
                'title' => 'La Santísima Trinidad',
                'category' => 'Jesús y Trinidad',
                'summary' => 'Un solo Dios en tres Personas.',
                'content' => 'Explicación de la Trinidad.',
                'tags_text' => 'trinidad, padre, hijo',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('nicenito_contents', [
            'title' => 'La Santísima Trinidad',
            'type' => 'fixed',
            'status' => 'published',
        ]);
    }

    public function test_overlapping_published_weeks_are_rejected(): void
    {
        NicenoBotContent::factory()->weekly()->create([
            'starts_at' => NicenoBotContent::now()->startOfDay(),
            'ends_at' => NicenoBotContent::now()->addDays(7)->endOfDay(),
            'status' => NicenoBotContentStatus::Published,
        ]);

        $this->actingAs($this->admin())
            ->post('/admin/nicenito/contenidos', [
                'type' => 'weekly',
                'action' => 'publish',
                'title' => 'Otra semana',
                'summary' => 'Resumen.',
                'content' => 'Contenido.',
                'gospel_reference' => 'Lucas 1, 1-4',
                'starts_at' => NicenoBotContent::now()->addDays(2)->format('Y-m-d H:i'),
                'ends_at' => NicenoBotContent::now()->addDays(9)->format('Y-m-d H:i'),
            ])
            ->assertSessionHasErrors('starts_at');

        $this->assertDatabaseMissing('nicenito_contents', ['title' => 'Otra semana']);
    }
}
