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
        Schema::create('main_calculations', function (Blueprint $table) {
            $table->id(); // bigInt (20) PK
            $table->string('calories', 100)->nullable(); // السعرات الحرارية
            $table->string('protin', 100)->nullable(); // كمية البروتين
            $table->string('fat', 100)->nullable(); // كمية الدهون
            $table->string('carbo', 100)->nullable(); // كمية الكربوهيدرات
            $table->string('BMR', 100)->nullable(); // معدل الأيض الأساسي
            $table->string('BMI', 100)->nullable(); // مؤشر كتلة الجسم
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // معرّف المستخدم
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('main_calculations');
    }
};
