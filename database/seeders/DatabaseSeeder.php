<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $role = Role::findOrCreate(config('nicenito.admin_role'));

        // Usuario administrador de desarrollo (admin@nicenito.test / password).
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@nicenito.test'],
            [
                'name' => 'Admin NicenoBot',
                'password' => Hash::make('password'),
            ],
        );

        $admin->assignRole($role);

        $this->call([
            NicenoBotContentSeeder::class,
            ParticipantSeeder::class,
        ]);
    }
}
