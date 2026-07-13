<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_knowledge_documents', function (Blueprint $table) {
            $table->id();

            /*
             * source_type dùng giá trị "blog" hoặc "news".
             * source_id là ID thật trong blog_posts/news.
             */
            $table->string('source_type', 20);
            $table->unsignedBigInteger('source_id');

            $table->string('title');
            $table->string('slug')->nullable();
            $table->string('public_url', 1000);

            /*
             * SHA-256 của nội dung đã chuẩn hóa.
             * Nếu hash không đổi thì không upload/index lại.
             */
            $table->char('content_hash', 64)->index();

            $table->string('openai_file_id')->nullable()->unique();
            $table->string('vector_store_file_id')->nullable()->index();

            $table->string('status', 20)->default('pending')->index();
            $table->json('metadata')->nullable();
            $table->text('last_error')->nullable();

            $table->timestamp('indexed_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['source_type', 'source_id'],
                'ai_knowledge_source_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_knowledge_documents');
    }
};