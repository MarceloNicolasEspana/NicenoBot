<?php

namespace Database\Factories;

use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Participant>
 */
class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    public function definition(): array
    {
        $name = $this->faker->name();

        return [
            'full_name' => $name,
            'display_name' => $this->faker->firstName().' '.strtoupper($this->faker->randomLetter()).'.',
            'group_name' => 'Confirmación 2026',
            'access_code' => Participant::generateAccessCode(),
            'pin_hash' => Hash::make('123456'),
            'is_active' => true,
            'must_change_pin' => false,
            'last_login_at' => now(),
            'privacy_notice_accepted_at' => now(),
        ];
    }

    public function mustChangePin(): static
    {
        return $this->state(fn () => ['must_change_pin' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function privacyPending(): static
    {
        return $this->state(fn () => ['privacy_notice_accepted_at' => null]);
    }
}
