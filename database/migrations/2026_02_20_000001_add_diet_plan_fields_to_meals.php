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
        Schema::table('meals', function (Blueprint $table) {
            $table->integer('day_number')->nullable();
            $table->string('meal_type')->nullable(); // breakfast, lunch, dinner, snack
            $table->integer('calories')->nullable();
            $table->string('meal_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meals', function (Blueprint $table) {
            $table->dropColumn(['day_number', 'meal_type', 'calories', 'meal_name']);
        });
    }
};
