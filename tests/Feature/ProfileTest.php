<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create(['password' => Hash::make('secret-current')]);
        $user->assignRole(Role::findOrCreate(config('nicenito.admin_role')));

        return $user;
    }

    public function test_admin_can_open_profile(): void
    {
        $this->actingAs($this->admin())->get(route('admin.nicenito.perfil.edit'))->assertOk();
    }

    public function test_admin_can_update_name_and_email(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
            ->put(route('admin.nicenito.perfil.update'), [
                'name' => 'Nuevo Nombre',
                'email' => 'nuevo@dominio.cl',
            ])
            ->assertRedirect(route('admin.nicenito.perfil.edit'));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Nuevo Nombre', 'email' => 'nuevo@dominio.cl']);
    }

    public function test_password_change_requires_correct_current_password(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
            ->put(route('admin.nicenito.perfil.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'current_password' => 'wrong',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertSessionHasErrors('current_password');
    }

    public function test_password_change_succeeds_with_correct_current_password(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
            ->put(route('admin.nicenito.perfil.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'current_password' => 'secret-current',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }
}
