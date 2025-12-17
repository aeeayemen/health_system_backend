<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('messages');
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('time', 100)->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('message', 255)->nullable();
            $table->string('date', 100)->nullable();
            $table->string('read', 100)->nullable();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
