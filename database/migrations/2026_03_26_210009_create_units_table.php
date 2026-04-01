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
        Schema::create('units', function (Blueprint $table) {
            $table->id();

            $table->foreignId('unit_group_id')->constrained()->cascadeOnDelete();

            $table->string('name'); // fallback
            $table->integer('capacity')->nullable();
            $table->string('type')->nullable(); // table, game, room, ...
            $table->string('color', 20)->nullable();
            $table->json('properties')->nullable();

            $table->string('status')->default('available'); // available, reserved, occupied, inactive
            $table->timestamp('reserved_at')->nullable();

            // Core logic
            $table->foreignId('current_order_id')
                ->nullable();
                // ->constrained('orders')
                // ->nullOnDelete();

            $table->integer('position')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
