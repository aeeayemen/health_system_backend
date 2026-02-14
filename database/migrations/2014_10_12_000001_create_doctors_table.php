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
        if (!Schema::hasTable('doctors')) {
            Schema::create('doctors', function (Blueprint $table) {
                $table->id(); // bigInt (20) PK
                $table->string('name', 100); // اسم العنصر
                $table->string('gender', 100)->nullable(); // الجنس
                $table->string('degree', 100)->nullable(); // الدرجة العلمية
                $table->string('bank_account', 100)->nullable(); // الحساب البنكي
                $table->string('phone_number', 100)->nullable(); // رقم الهاتف
                $table->string('CV', 255)->nullable(); // السيرة الذاتية
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // Added for Auth link
                $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null'); // معرّف المسؤول
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
