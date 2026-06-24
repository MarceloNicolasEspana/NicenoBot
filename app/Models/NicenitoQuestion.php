<?php

namespace App\Models;

use App\Enums\FollowUpStatus;
use Database\Factories\NicenitoQuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $participant_id
 * @property int|null $weekly_content_id
 * @property string|null $question
 * @property string|null $answer
 * @property array|null $sources
 * @property string|null $detected_category
 * @property bool $used_gemini
 * @property bool $has_weekly_content
 * @property int $fixed_contents_count
 * @property bool $needs_human_guidance
 * @property FollowUpStatus $follow_up_status
 */
class NicenitoQuestion extends Model
{
    /** @use HasFactory<NicenitoQuestionFactory> */
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'weekly_content_id',
        'question',
        'answer',
        'sources',
        'detected_category',
        'used_gemini',
        'has_weekly_content',
        'fixed_contents_count',
        'needs_human_guidance',
        'follow_up_status',
        'follow_up_notes',
        'follow_up_by',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'sources' => 'array',
            'used_gemini' => 'boolean',
            'has_weekly_content' => 'boolean',
            'fixed_contents_count' => 'integer',
            'needs_human_guidance' => 'boolean',
            'follow_up_status' => FollowUpStatus::class,
            'answered_at' => 'datetime',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function weeklyContent(): BelongsTo
    {
        return $this->belongsTo(NicenitoContent::class, 'weekly_content_id');
    }

    public function followUpBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'follow_up_by');
    }
}
