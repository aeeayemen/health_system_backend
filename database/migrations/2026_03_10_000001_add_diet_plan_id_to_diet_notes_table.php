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
            if (!Schema::hasColumn('diet_notes', 'diet_plan_id')) {
                $table->foreignId('diet_plan_id')->nullable()->constrained('diet_plans')->onDelete('cascade');
            }

            // Make diet_id nullable if it wasn't already, since we might use diet_plan_id instead
            // However, it's already nullable in the previous migration we saw.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diet_notes', function (Blueprint $table) {
            $table->dropForeign(['diet_plan_id']);
            $table->dropColumn('diet_plan_id');
        });
    }
};
