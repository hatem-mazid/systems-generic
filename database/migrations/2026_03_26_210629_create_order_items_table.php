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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // snapshot (VERY IMPORTANT — never depend on product later)
            $table->string('name');
            $table->text('notes')->nullable();
            $table->decimal('price', 10, 2);

            $table->integer('quantity')->default(1);
            $table->decimal('total', 10, 2);

            // for services / timers later
            $table->string('type')->default('product');
            // product | service | timer

            $table->json('meta')->nullable();
            // store timer duration, notes, etc.
            $table->integer('batch_no')->nullable();
            $table->boolean('is_printed')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
