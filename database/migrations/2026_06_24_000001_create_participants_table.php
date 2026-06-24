<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('display_name')->nullable();
            $table->string('group_name')->nullable()->index();
            $table->string('access_code')->unique();
            $table->string('pin_hash');
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('must_change_pin')->default(true);
            $table->dateTime('last_login_at')->nullable();
            $table->dateTime('privacy_notice_accepted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
