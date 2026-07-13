<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('session_id')
                ->constrained('chatbot_sessions')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('role', 20)->index();
            $table->longText('content');

            /*
             * sources: danh sách Blog/News được dùng để trả lời.
             * metadata: intent, tool calls, model, lỗi phụ trợ...
             */
            $table->json('sources')->nullable();
            $table->json('metadata')->nullable();

            $table->string('openai_response_id')->nullable()->index();
            $table->string('status', 20)->default('completed')->index();

            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->unsignedInteger('total_tokens')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();

            $table->timestamps();

            $table->index(['session_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_messages');
    }
};