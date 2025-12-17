<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('meals');
        Schema::create('meals', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('meal_id')->nullable(); // Unclear what this is, maybe external ID?
            $table->string('name', 100)->nullable();
            $table->string('serving', 100)->nullable();
            $table->string('describtion', 255)->nullable(); // Typo in ERD
            $table->string('carbo', 100)->nullable();
            $table->string('protin', 100)->nullable();
            $table->string('fat', 100)->nullable();
            $table->string('energy', 100)->nullable();
            $table->string('category', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meals');
    }
};
