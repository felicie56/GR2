<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('author_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('author_applications', 'user_seen_at')) {
                $table->timestamp('user_seen_at')->nullable()->after('rejection_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('author_applications', function (Blueprint $table) {
            if (Schema::hasColumn('author_applications', 'user_seen_at')) {
                $table->dropColumn('user_seen_at');
            }
        });
    }
};