<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('diet_components', function (Blueprint $table) {
            $table->id();
            $table->string('periods_time', 100)->nullable();
            $table->string('period_name', 100)->nullable();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->foreignId('diet_id')->constrained('diets')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diet_components');
    }
};
