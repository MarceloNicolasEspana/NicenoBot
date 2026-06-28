<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Intento de quiz de un participante sobre el contenido semanal. Se guarda para
 * que el catequista pueda revisar qué respondió cada joven.
 *
 * @property int $id
 * @property int $participant_id
 * @property int $nicenito_content_id
 * @property array $answers
 * @property int $score
 * @property int $total
 */
class NicenoBotQuizAttempt extends Model
{
    protected $table = 'nicenito_quiz_attempts';

    protected $fillable = [
        'participant_id',
        'nicenito_content_id',
        'answers',
        'score',
        'total',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(NicenoBotContent::class, 'nicenito_content_id');
    }
}
