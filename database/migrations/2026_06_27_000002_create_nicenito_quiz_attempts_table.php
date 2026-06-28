<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nicenito_quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->foreignId('nicenito_content_id')->constrained('nicenito_contents')->cascadeOnDelete();
            // [{ question_index, selected_index, is_correct }]
            $table->json('answers');
            $table->unsignedSmallInteger('score')->default(0);
            $table->unsignedSmallInteger('total')->default(0);
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index(['participant_id', 'nicenito_content_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nicenito_quiz_attempts');
    }
};
