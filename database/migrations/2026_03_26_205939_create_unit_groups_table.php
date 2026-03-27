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
        Schema::create('unit_groups', function (Blueprint $table) {
            $table->id();

            $table->string('name'); // fallback
            $table->string('color')->nullable(); // UI (optional but useful)

            $table->boolean('is_active')->default(true);
            $table->integer('position')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_groups');
    }
};
