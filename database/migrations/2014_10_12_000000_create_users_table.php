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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // bigInt (20) PK
            $table->string('name', 100); // اسم العنصر
            $table->string('email', 100)->unique(); // البريد الإلكتروني
            $table->string('password', 100); // كلمة المرور
            $table->string('type', 100)->default('user'); // نوع المستخدم (admin, doctor, user)
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
