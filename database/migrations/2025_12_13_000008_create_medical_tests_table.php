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
        Schema::create('medical_tests', function (Blueprint $table) {
            $table->id(); // bigInt (20) PK
            $table->string('name', 100); // اسم العنصر
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // معرّف المستخدم
            $table->string('image', 255)->nullable(); // صورة مرتبطة بالحقل
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_tests');
    }
};
