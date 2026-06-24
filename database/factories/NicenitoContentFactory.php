<?php

namespace Database\Factories;

use App\Enums\NicenitoContentStatus;
use App\Enums\NicenitoContentType;
use App\Models\NicenitoContent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NicenitoContent>
 */
class NicenitoContentFactory extends Factory
{
    protected $model = NicenitoContent::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(4);

        return [
            'type' => NicenitoContentType::Fixed,
            'status' => NicenitoContentStatus::Published,
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
            'type' => NicenitoContentType::Weekly,
            'category' => null,
            'gospel_reference' => 'Mateo 10, 26-33',
            'starts_at' => NicenitoContent::now()->subDay(),
            'ends_at' => NicenitoContent::now()->addDays(6),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => NicenitoContentStatus::Draft]);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => NicenitoContentStatus::Archived]);
    }
}
