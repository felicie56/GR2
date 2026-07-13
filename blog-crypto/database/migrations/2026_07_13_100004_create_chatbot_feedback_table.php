<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_feedback', function (Blueprint $table) {
            $table->id();

            $table->foreignId('message_id')
                ->constrained('chatbot_messages')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('rating', 20);
            $table->text('reason')->nullable();

            $table->timestamps();

            $table->index('rating');
            $table->index(['message_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_feedback');
    }
};