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
        Schema::create('crypto_coins', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->unique(); // BTC, ETH...
            $table->string('name');
            $table->integer('rank')->nullable(); // thứ hạng theo market cap
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crypto_coins');
    }
};
