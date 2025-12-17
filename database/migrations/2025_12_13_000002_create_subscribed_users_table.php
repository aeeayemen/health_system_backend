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
        Schema::create('subscribed_users', function (Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('users')->onDelete('cascade'); // FK للمستخدم
            $table->string('fullname', 100)->nullable(); // الاسم الكامل
            $table->string('gender', 100)->nullable(); // الجنس
            $table->integer('height')->nullable(); // الطول
            $table->integer('weight')->nullable(); // الوزن
            $table->string('phone_number', 100)->nullable(); // رقم الهاتف
            $table->string('image', 255)->nullable(); // صورة مرتبطة بالحقل
            $table->string('birthdate', 100)->nullable(); // تاريخ الميلاد
            $table->string('physical_activity', 100)->nullable(); // النشاط البدني
            $table->string('medical', 100)->nullable(); // الحالة الطبية
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscribed_users');
    }
};
