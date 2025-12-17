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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id(); // bigInt (20) PK
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade'); // معرّف المسؤول
            $table->string('date', 100)->nullable(); // تاريخ إدخال السجل
            $table->string('image', 255)->nullable(); // صورة مرتبطة بالحقل
            $table->string('describtion', 255)->nullable(); // وصف نصي
            $table->string('phone_number', 100)->nullable(); // رقم الهاتف
            $table->string('type', 100)->nullable(); // نوع الإعلان
            $table->string('GPS', 100)->nullable(); // موقع جغرافي
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
