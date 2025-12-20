<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('diet_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diet_id')->constrained('diets')->onDelete('cascade');
            $table->string('name');
            $table->integer('start_day');
            $table->integer('end_day');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diet_periods');
    }
};
