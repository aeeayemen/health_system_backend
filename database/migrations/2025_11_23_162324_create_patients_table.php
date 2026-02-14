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
        if (!Schema::hasTable('patients')) {
            Schema::create('patients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->date('date_of_birth')->nullable();
                $table->enum('gender', ['male', 'female']);
                $table->decimal('current_weight', 5, 2)->nullable();
                $table->decimal('target_weight', 5, 2)->nullable();
                $table->decimal('height', 5, 2)->nullable();
                $table->text('medical_history')->nullable();
                $table->text('allergies')->nullable();
                $table->foreignId('current_doctor_id')->nullable()->constrained('doctors')->onDelete('set null');
                $table->enum('subscription_status', ['active', 'inactive', 'expired'])->default('inactive');
                $table->timestamp('subscription_end_date')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
