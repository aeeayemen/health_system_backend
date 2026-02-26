<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('athkars', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('title')->nullable();
            $table->text('content');
            $table->integer('repetition')->default(1);
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('athkars');
    }
};
