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
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->enum('consultation_type', ['initial', 'follow_up', 'review']);
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('scheduled_date')->nullable();
            $table->timestamp('completed_date')->nullable();
            $table->text('notes')->nullable();
            $table->text('recommendations')->nullable();
            $table->string('whatsapp_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
