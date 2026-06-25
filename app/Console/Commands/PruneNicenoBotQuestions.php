<?php

namespace App\Console\Commands;

use App\Models\NicenoBotQuestion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Anonimiza preguntas antiguas: elimina los textos sensibles (pregunta,
 * respuesta, fuentes, notas) conservando solo métricas agregadas no
 * identificables (categoría, uso de Gemini, conteos, fechas, seguimiento).
 *
 * Programación (no se activa sola). En routes/console.php:
 *   Schedule::command('nicenito:prune-questions')->daily();
 * y correr el scheduler del sistema:  php artisan schedule:work
 */
class PruneNicenoBotQuestions extends Command
{
    protected $signature = 'nicenito:prune-questions {--days= : Sobrescribe los días de retención}';

    protected $description = 'Anonimiza el contenido sensible de preguntas más antiguas que el período de retención.';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?? config('nicenito.question_retention_days', 90));
        $cutoff = now()->subDays($days);

        $count = NicenoBotQuestion::query()
            ->where('created_at', '<', $cutoff)
            ->where(function ($q) {
                $q->whereNotNull('question')
                    ->orWhereNotNull('answer')
                    ->orWhereNotNull('sources')
                    ->orWhereNotNull('follow_up_notes');
            })
            ->update([
                'question' => null,
                'answer' => null,
                'sources' => null,
                'follow_up_notes' => null,
            ]);

        // Privacidad: solo registramos el conteo, nunca contenido.
        Log::info('niceno.prune', ['retention_days' => $days, 'anonymized' => $count]);
        $this->info("Preguntas anonimizadas: {$count} (retención: {$days} días).");

        return self::SUCCESS;
    }
}
