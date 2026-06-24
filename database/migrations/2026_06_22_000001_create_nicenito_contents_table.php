<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nicenito_contents', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['weekly', 'fixed'])->index();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->index();
            $table->string('category')->nullable()->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('gospel_reference')->nullable();
            $table->json('biblical_references')->nullable();
            $table->json('catechism_references')->nullable();
            $table->text('summary');
            $table->longText('content');
            $table->json('key_ideas')->nullable();
            $table->json('faq')->nullable();
            $table->json('reflection_questions')->nullable();
            $table->json('tags')->nullable();
            $table->dateTime('starts_at')->nullable()->index();
            $table->dateTime('ends_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nicenito_contents');
    }
};
