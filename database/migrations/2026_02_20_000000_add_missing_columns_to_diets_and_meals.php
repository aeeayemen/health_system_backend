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
        Schema::table('diets', function (Blueprint $table) {
            if (!Schema::hasColumn('diets', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('diets', 'status')) {
                $table->string('status')->default('active')->after('user_id');
            }
        });

        Schema::table('meals', function (Blueprint $table) {
            if (!Schema::hasColumn('meals', 'diet_plan_id')) {
                $table->foreignId('diet_plan_id')->nullable()->constrained('diet_plans')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'status']);
        });

        Schema::table('meals', function (Blueprint $table) {
            $table->dropForeign(['diet_plan_id']);
            $table->dropColumn('diet_plan_id');
        });
    }
};
