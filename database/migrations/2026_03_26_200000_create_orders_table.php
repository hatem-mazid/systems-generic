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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('unit_id')
                // ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id') // who opened it (waiter)
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('status')->default('active');
            // active | reserved | closed | cancelled

            $table->decimal('total', 10, 2)->default(0);

            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
