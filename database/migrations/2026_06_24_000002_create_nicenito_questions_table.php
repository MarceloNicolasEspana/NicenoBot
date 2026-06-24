<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nicenito_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->foreignId('weekly_content_id')->nullable()->constrained('nicenito_contents')->nullOnDelete();
            $table->text('question')->nullable();
            $table->text('answer')->nullable();
            $table->json('sources')->nullable();
            $table->string('detected_category')->nullable()->index();
            $table->boolean('used_gemini')->default(false);
            $table->boolean('has_weekly_content')->default(false);
            $table->unsignedInteger('fixed_contents_count')->default(0);
            $table->boolean('needs_human_guidance')->default(false)->index();
            $table->enum('follow_up_status', ['none', 'review', 'catechist_follow_up', 'resolved'])
                ->default('none')->index();
            $table->text('follow_up_notes')->nullable();
            $table->foreignId('follow_up_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('answered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nicenito_questions');
    }
};
