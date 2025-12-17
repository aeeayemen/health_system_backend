<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->decimal('weight', 5, 2);
            $table->decimal('waist_circumference', 5, 2)->nullable();
            $table->decimal('hip_circumference', 5, 2)->nullable();
            $table->decimal('chest_circumference', 5, 2)->nullable();
            $table->decimal('bmi', 5, 2)->nullable();
            $table->date('measurement_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('measurements');
    }
};
