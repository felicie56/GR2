<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('news')) {
            return;
        }

        Schema::table('news', function (Blueprint $table) {
            if (! Schema::hasColumn('news', 'source_url')) {
                $table->string('source_url', 1000)->nullable()->after('source');
            }

            if (! Schema::hasColumn('news', 'source_feed')) {
                $table->string('source_feed')->nullable()->after('source_url');
            }

            if (! Schema::hasColumn('news', 'external_id')) {
                $table->string('external_id')->nullable()->after('source_feed');
            }

            if (! Schema::hasColumn('news', 'is_auto')) {
                $table->boolean('is_auto')->default(false)->after('external_id');
            }

            if (! Schema::hasColumn('news', 'fetched_at')) {
                $table->timestamp('fetched_at')->nullable()->after('published_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('news')) {
            return;
        }

        Schema::table('news', function (Blueprint $table) {
            if (Schema::hasColumn('news', 'fetched_at')) {
                $table->dropColumn('fetched_at');
            }

            if (Schema::hasColumn('news', 'is_auto')) {
                $table->dropColumn('is_auto');
            }

            if (Schema::hasColumn('news', 'external_id')) {
                $table->dropColumn('external_id');
            }

            if (Schema::hasColumn('news', 'source_feed')) {
                $table->dropColumn('source_feed');
            }

            if (Schema::hasColumn('news', 'source_url')) {
                $table->dropColumn('source_url');
            }
        });
    }
};