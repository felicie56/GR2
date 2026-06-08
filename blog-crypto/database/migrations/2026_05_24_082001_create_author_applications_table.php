<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('author_applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])
                ->default('pending');

            // Snapshot thông tin lúc user gửi đơn
            $table->string('full_name');
            $table->string('public_name')->nullable();
            $table->string('headline')->nullable();

            $table->unsignedTinyInteger('experience_years')->nullable();
            $table->json('expertise_areas')->nullable();

            $table->text('experience_summary');
            $table->text('motivation');

            // Bài viết mẫu
            $table->string('sample_article_title');
            $table->longText('sample_article_content');

            // Social proof
            $table->string('website_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('x_url')->nullable();

            // Cam kết
            $table->boolean('truthful_information_confirmed')->default(false);
            $table->boolean('content_policy_confirmed')->default(false);

            // Admin review
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('author_applications');
    }
};