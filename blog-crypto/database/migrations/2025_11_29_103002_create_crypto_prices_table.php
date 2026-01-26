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
        Schema::create('crypto_prices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('coin_id')
                  ->constrained('crypto_coins')
                  ->onDelete('cascade');

            $table->decimal('price_usd', 20, 8);
            $table->decimal('volume_24h', 20, 2)->nullable();
            $table->float('percent_change_24h')->nullable();

            $table->dateTime('fetched_at'); // thời điểm lấy giá

            // nếu muốn nhanh khi query theo thời gian
            $table->index(['coin_id', 'fetched_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crypto_prices');
    }
};
