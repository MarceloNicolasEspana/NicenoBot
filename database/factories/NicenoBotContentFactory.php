<?php

namespace Database\Factories;

use App\Enums\NicenoBotContentStatus;
use App\Enums\NicenoBotContentType;
use App\Models\NicenoBotContent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NicenoBotContent>
 */
class NicenoBotContentFactory extends Factory
{
    protected $model = NicenoBotContent::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(4);

        return [
            'type' => NicenoBotContentType::Fixed,
            'status' => NicenoBotContentStatus::Published,
            'category' => $this->faker->randomElement(config('nicenito.categories')),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(5),
            'gospel_reference' => null,
            'biblical_references' => [],
            'catechism_references' => [],
            'summary' => $this->faker->paragraph(),
            'content' => $this->faker->paragraphs(3, true),
            'key_ideas' => [$this->faker->sentence(), $this->faker->sentence()],
            'faq' => [],
            'reflection_questions' => [],
            'tags' => [],
            'starts_at' => null,
            'ends_at' => null,
            'created_by' => null,
        ];
    }

    public function weekly(): static
    {
        return $this->state(fn () => [
            'type' => NicenoBotContentType::Weekly,
            'category' => null,
            'gospel_reference' => 'Mateo 10, 26-33',
            'starts_at' => NicenoBotContent::now()->subDay(),
            'ends_at' => NicenoBotContent::now()->addDays(6),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => NicenoBotContentStatus::Draft]);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => NicenoBotContentStatus::Archived]);
    }
}
