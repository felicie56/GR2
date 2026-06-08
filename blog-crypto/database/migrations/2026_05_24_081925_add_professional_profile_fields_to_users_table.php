<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('headline')->nullable()->after('bio');
            $table->string('occupation')->nullable()->after('headline');
            $table->string('organization')->nullable()->after('occupation');
            $table->string('location')->nullable()->after('organization');

            $table->string('website_url')->nullable()->after('location');
            $table->string('linkedin_url')->nullable()->after('website_url');
            $table->string('x_url')->nullable()->after('linkedin_url');

            $table->unsignedTinyInteger('experience_years')->nullable()->after('x_url');
            $table->json('expertise_areas')->nullable()->after('experience_years');

            $table->timestamp('profile_completed_at')->nullable()->after('expertise_areas');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'headline',
                'occupation',
                'organization',
                'location',
                'website_url',
                'linkedin_url',
                'x_url',
                'experience_years',
                'expertise_areas',
                'profile_completed_at',
            ]);
        });
    }
};