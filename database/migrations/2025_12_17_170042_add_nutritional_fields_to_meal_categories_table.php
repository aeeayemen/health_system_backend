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
        Schema::table('meal_categories', function (Blueprint $table) {
            $table->decimal('protein', 8, 2)->nullable()->after('name_ar');
            $table->decimal('fat', 8, 2)->nullable()->after('protein');
            $table->decimal('carbohydrates', 8, 2)->nullable()->after('fat');
            $table->decimal('energy', 8, 2)->nullable()->after('carbohydrates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meal_categories', function (Blueprint $table) {
            $table->dropColumn(['protein', 'fat', 'carbohydrates', 'energy']);
        });
    }
};
