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
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');

            $table->foreignId('blog_post_id')
                  ->constrained('blog_posts')
                  ->onDelete('cascade');

            // like / love / etc. Tạm thời dùng varchar đơn giản
            $table->string('type')->default('like');

            $table->timestamps();

            // tránh 1 user like 1 bài nhiều lần (tuỳ bạn)
            $table->unique(['user_id', 'blog_post_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
