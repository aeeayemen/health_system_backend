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
        Schema::table('doctors', function (Blueprint $table) {
            $table->string('application_status')->default('pending');
            $table->string('specialization')->nullable();
            $table->string('license_number')->nullable();
            $table->integer('years_of_experience')->nullable();
            $table->text('bio')->nullable();
            $table->string('profile_image')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->decimal('rating', 3, 2)->default(0);
            $table->decimal('consultation_fee', 8, 2)->nullable();
            $table->boolean('is_available')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn([
                'application_status',
                'specialization',
                'license_number',
                'years_of_experience',
                'bio',
                'profile_image',
                'is_verified',
                'rating',
                'consultation_fee',
                'is_available',
            ]);
        });
    }
};
