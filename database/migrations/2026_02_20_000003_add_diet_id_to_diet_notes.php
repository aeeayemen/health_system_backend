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
        Schema::table('diet_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('diet_notes', 'diet_id')) {
                $table->foreignId('diet_id')->nullable()->constrained('diets')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diet_notes', function (Blueprint $table) {
            $table->dropForeign(['diet_id']);
            $table->dropColumn('diet_id');
        });
    }
};
