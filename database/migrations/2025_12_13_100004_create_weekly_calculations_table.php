<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('weekly_calculations', function (Blueprint $table) {
            $table->id();
            $table->string('waist', 100)->nullable();
            $table->string('stomach', 100)->nullable();
            $table->string('arm', 100)->nullable();
            $table->string('chest', 100)->nullable();
            $table->string('thigh', 100)->nullable();
            $table->string('shoulder', 100)->nullable();
            $table->string('buttocks', 100)->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_calculations');
    }
};
