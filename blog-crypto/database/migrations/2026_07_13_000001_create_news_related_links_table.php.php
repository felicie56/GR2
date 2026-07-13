<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_related_links', function (Blueprint $table) {
            $table->id();

            $table->foreignId('news_id')
                ->constrained('news')
                ->cascadeOnDelete();

            $table->foreignId('related_news_id')
                ->constrained('news')
                ->cascadeOnDelete();

            $table->decimal('score', 8, 2)->default(0);
            $table->unsignedTinyInteger('display_order')->default(1);
            $table->unsignedSmallInteger('paragraph_index')->nullable();
            $table->json('matched_keywords')->nullable();
            $table->string('reason', 500)->nullable();
            $table->timestamps();

            $table->unique(
                ['news_id', 'related_news_id'],
                'news_related_links_unique'
            );

            $table->index(
                ['news_id', 'display_order'],
                'news_related_links_display_index'
            );

            $table->index(
                ['related_news_id', 'score'],
                'news_related_links_reverse_index'
            );
        });

        Schema::table('news', function (Blueprint $table) {
            if (! Schema::hasColumn('news', 'related_links_generated_at')) {
                $table->timestamp('related_links_generated_at')
                    ->nullable()
                    ->after('fetched_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            if (Schema::hasColumn('news', 'related_links_generated_at')) {
                $table->dropColumn('related_links_generated_at');
            }
        });

        Schema::dropIfExists('news_related_links');
    }
};