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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->enum('plan_type', ['basic', 'premium', 'vip']);
            $table->decimal('price', 8, 2);
            $table->integer('duration_months')->default(1);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'inactive', 'expired', 'cancelled'])->default('active');
            $table->boolean('auto_renew')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
