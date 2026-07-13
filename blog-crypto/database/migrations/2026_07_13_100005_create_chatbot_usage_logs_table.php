<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_usage_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('session_id')
                ->nullable()
                ->constrained('chatbot_sessions')
                ->nullOnDelete();

            $table->foreignId('message_id')
                ->nullable()
                ->constrained('chatbot_messages')
                ->nullOnDelete();

            $table->string('model', 100)->nullable();

            $table->unsignedInteger('input_tokens')
                ->default(0);

            $table->unsignedInteger('output_tokens')
                ->default(0);

            $table->unsignedInteger('total_tokens')
                ->default(0);

            $table->unsignedInteger('latency_ms')
                ->nullable();

            $table->json('tool_calls')
                ->nullable();

            $table->json('retrieved_documents')
                ->nullable();

            $table->string('status', 20)
                ->default('completed');

            $table->text('error_message')
                ->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('model');
            $table->index(['session_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_usage_logs');
    }
};