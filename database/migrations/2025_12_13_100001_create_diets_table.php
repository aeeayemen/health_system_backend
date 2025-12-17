<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('diets', function (Blueprint $table) {
            $table->id();
            $table->string('price', 100)->nullable();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->string('periods', 100)->nullable();
            $table->bigInteger('states_id')->nullable(); // Assuming this is a FK or just an ID
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diets');
    }
};
