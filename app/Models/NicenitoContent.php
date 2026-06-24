<?php

namespace App\Models;

use App\Enums\NicenitoContentStatus;
use App\Enums\NicenitoContentType;
use Carbon\CarbonInterface;
use Database\Factories\NicenitoContentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property NicenitoContentType $type
 * @property NicenitoContentStatus $status
 * @property string|null $category
 * @property string $title
 * @property string $slug
 * @property string|null $gospel_reference
 * @property array|null $biblical_references
 * @property array|null $catechism_references
 * @property string $summary
 * @property string $content
 * @property array|null $key_ideas
 * @property array|null $faq
 * @property array|null $reflection_questions
 * @property array|null $tags
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 */
class NicenitoContent extends Model
{
    /** @use HasFactory<NicenitoContentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'status',
        'category',
        'title',
        'slug',
        'gospel_reference',
        'biblical_references',
        'catechism_references',
        'summary',
        'content',
        'key_ideas',
        'faq',
        'reflection_questions',
        'tags',
        'starts_at',
        'ends_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => NicenitoContentType::class,
            'status' => NicenitoContentStatus::class,
            'biblical_references' => 'array',
            'catechism_references' => 'array',
            'key_ideas' => 'array',
            'faq' => 'array',
            'reflection_questions' => 'array',
            'tags' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ---------------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------------

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', NicenitoContentStatus::Published);
    }

    public function scopeWeekly(Builder $query): Builder
    {
        return $query->where('type', NicenitoContentType::Weekly);
    }

    public function scopeFixed(Builder $query): Builder
    {
        return $query->where('type', NicenitoContentType::Fixed);
    }

    /**
     * Contenido semanal vigente: publicado y con la fecha actual dentro del
     * rango [starts_at, ends_at], evaluado en la zona horaria configurada.
     */
    public function scopeActiveWeekly(Builder $query, ?CarbonInterface $now = null): Builder
    {
        $now ??= self::now();

        return $query->weekly()
            ->published()
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now);
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    public static function now(): Carbon
    {
        return Carbon::now(config('nicenito.timezone'));
    }

    public function isActiveWeekly(?CarbonInterface $now = null): bool
    {
        if ($this->type !== NicenitoContentType::Weekly) {
            return false;
        }

        if ($this->status !== NicenitoContentStatus::Published) {
            return false;
        }

        if ($this->starts_at === null || $this->ends_at === null) {
            return false;
        }

        $now ??= self::now();

        return $now->betweenIncluded($this->starts_at, $this->ends_at);
    }

    public function isPublished(): bool
    {
        return $this->status === NicenitoContentStatus::Published;
    }

    /**
     * ¿Existe ya un contenido semanal PUBLICADO cuyo rango de fechas se solape
     * con [$start, $end]? Usado tanto por la validación del formulario como por
     * la acción rápida de "publicar".
     */
    public static function hasPublishedWeeklyOverlap(
        CarbonInterface $start,
        CarbonInterface $end,
        ?int $ignoreId = null,
    ): bool {
        return self::query()
            ->weekly()
            ->published()
            ->when($ignoreId, fn (Builder $q) => $q->whereKeyNot($ignoreId))
            ->where('starts_at', '<=', $end)
            ->where('ends_at', '>=', $start)
            ->exists();
    }
}
