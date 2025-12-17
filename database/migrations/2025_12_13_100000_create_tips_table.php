<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tips', function (Blueprint $table) {
            $table->id();
            $table->string('describtion', 255)->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('tip_categories')->onDelete('set null');
            $table->string('date', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tips');
    }
};
