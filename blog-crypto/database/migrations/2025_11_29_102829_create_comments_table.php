<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();

            // người viết comment
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');

            // comment cho blog_post (có thể null nếu là comment tin tức)
            $table->foreignId('blog_post_id')
                  ->nullable()
                  ->constrained('blog_posts')
                  ->onDelete('cascade');

            // comment cho news (có thể null nếu là comment blog)
            $table->foreignId('news_id')
                  ->nullable()
                  ->constrained('news')
                  ->onDelete('cascade');

            $table->text('content');

            $table->timestamps();

            // nếu muốn, có thể thêm index
            $table->index(['blog_post_id']);
            $table->index(['news_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
