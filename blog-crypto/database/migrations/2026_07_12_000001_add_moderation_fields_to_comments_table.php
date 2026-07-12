<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->string('status', 20)
                ->default('pending')
                ->after('content')
                ->index();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->after('status')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')
                ->nullable()
                ->after('reviewed_by');
        });

        // Các bình luận đã có từ trước vẫn được giữ công khai.
        DB::table('comments')->update([
            'status' => 'approved',
        ]);
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropIndex(['status']);
            $table->dropColumn([
                'status',
                'reviewed_by',
                'reviewed_at',
            ]);
        });
    }
};