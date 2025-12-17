<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('diet_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            // Patient ID: In ERD 'Patient' is 'SubscribedUser' linked to 'users' via 'id'.
            // So 'patient_id' should reference 'users' (or 'subscribed_users' which shares ID).
            // But existing code uses 'patients' table?
            // Existing Patient model now maps to 'subscribed_users'.
            // 'subscribed_users' PK is 'id' (FK to users).
            // So 'patient_id' should reference 'users' (id).
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('daily_calories');
            $table->integer('duration_days');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diet_plans');
    }
};
