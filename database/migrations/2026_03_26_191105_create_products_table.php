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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('name'); // fallback
            $table->text('description')->nullable();

            $table->enum('type', [
                'physical',
                'service_fixed',
                'service_timer'
            ]);

            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('price_per_hour', 10, 2)->nullable();

            $table->boolean('is_limited')->default(false);
            $table->integer('stock_quantity')->nullable();

            $table->boolean('is_available')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
