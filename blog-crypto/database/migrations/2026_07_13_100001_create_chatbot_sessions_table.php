<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->char('guest_token_hash', 64)
                ->nullable()
                ->index();

            $table->string('title')->nullable();
            $table->longText('summary')->nullable();

            $table->string('openai_previous_response_id')
                ->nullable()
                ->index();

            $table->string('status', 20)
                ->default('active')
                ->index();

            $table->timestamp('last_activity_at')
                ->nullable()
                ->index();

            $table->timestamp('context_compacted_at')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_sessions');
    }
};