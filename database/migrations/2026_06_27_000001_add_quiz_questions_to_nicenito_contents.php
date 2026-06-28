<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nicenito_contents', function (Blueprint $table) {
            // Hasta 4 preguntas de opción múltiple por contenido:
            // [{ question, options: [...], correct: int }]
            $table->json('quiz_questions')->nullable()->after('reflection_questions');
        });
    }

    public function down(): void
    {
        Schema::table('nicenito_contents', function (Blueprint $table) {
            $table->dropColumn('quiz_questions');
        });
    }
};
