<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            if (! Schema::hasColumn('blog_posts', 'reviewed_by')) {
                $table->foreignId('reviewed_by')
                    ->nullable()
                    ->after('status')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('blog_posts', 'reviewed_at')) {
                $table->timestamp('reviewed_at')
                    ->nullable()
                    ->after('reviewed_by');
            }

            if (! Schema::hasColumn('blog_posts', 'rejection_reason')) {
                $table->text('rejection_reason')
                    ->nullable()
                    ->after('reviewed_at');
            }

            if (! Schema::hasColumn('blog_posts', 'author_seen_at')) {
                $table->timestamp('author_seen_at')
                    ->nullable()
                    ->after('rejection_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            if (Schema::hasColumn('blog_posts', 'reviewed_by')) {
                try {
                    $table->dropForeign(['reviewed_by']);
                } catch (\Throwable $e) {
                    // Ignore if foreign key does not exist.
                }
            }

            if (Schema::hasColumn('blog_posts', 'author_seen_at')) {
                $table->dropColumn('author_seen_at');
            }

            if (Schema::hasColumn('blog_posts', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }

            if (Schema::hasColumn('blog_posts', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }

            if (Schema::hasColumn('blog_posts', 'reviewed_by')) {
                $table->dropColumn('reviewed_by');
            }
        });
    }
};