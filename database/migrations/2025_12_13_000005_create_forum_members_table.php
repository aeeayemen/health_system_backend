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
        Schema::create('forum_members', function (Blueprint $table) {
            $table->id(); // bigInt (20) PK
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // معرّف المستخدم
            $table->foreignId('forum_id')->constrained('forums')->onDelete('cascade'); // معرّف المنتدى
            $table->timestamps();

            $table->unique(['user_id', 'forum_id']); // منع التكرار
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_members');
    }
};
