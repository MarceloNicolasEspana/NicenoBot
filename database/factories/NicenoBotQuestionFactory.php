<?php

namespace Database\Factories;

use App\Enums\FollowUpStatus;
use App\Models\NicenoBotQuestion;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NicenoBotQuestion>
 */
class NicenoBotQuestionFactory extends Factory
{
    protected $model = NicenoBotQuestion::class;

    public function definition(): array
    {
        return [
            'participant_id' => Participant::factory(),
            'weekly_content_id' => null,
            'question' => $this->faker->sentence().'?',
            'answer' => $this->faker->paragraph(),
            'sources' => [],
            'detected_category' => null,
            'used_gemini' => true,
            'has_weekly_content' => false,
            'fixed_contents_count' => 0,
            'needs_human_guidance' => false,
            'follow_up_status' => FollowUpStatus::None,
            'follow_up_notes' => null,
            'answered_at' => now(),
        ];
    }
}
