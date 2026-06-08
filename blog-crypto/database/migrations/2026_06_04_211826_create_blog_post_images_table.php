<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('blog_post_images')) {
            return;
        }

        Schema::create('blog_post_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_id')
                ->constrained('blog_posts')
                ->cascadeOnDelete();

            $table->string('image_path', 1000);
            $table->string('original_name')->nullable();
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['blog_post_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_images');
    }
};