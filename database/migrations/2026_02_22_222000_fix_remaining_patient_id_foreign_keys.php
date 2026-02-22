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
        // Fix measurements table
        if (Schema::hasTable('measurements')) {
            Schema::table('measurements', function (Blueprint $table) {
                // Determine the correct constraint name if possible, or just use the column name
                $table->dropForeign(['patient_id']);
                $table->foreign('patient_id')->references('id')->on('subscribed_users')->onDelete('cascade');
            });
        }

        // Fix consultations table
        if (Schema::hasTable('consultations')) {
            Schema::table('consultations', function (Blueprint $table) {
                $table->dropForeign(['patient_id']);
                $table->foreign('patient_id')->references('id')->on('subscribed_users')->onDelete('cascade');
            });
        }

        // Fix medical_files table
        if (Schema::hasTable('medical_files')) {
            Schema::table('medical_files', function (Blueprint $table) {
                $table->dropForeign(['patient_id']);
                $table->foreign('patient_id')->references('id')->on('subscribed_users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('measurements')) {
            Schema::table('measurements', function (Blueprint $table) {
                $table->dropForeign(['patient_id']);
                $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('consultations')) {
            Schema::table('consultations', function (Blueprint $table) {
                $table->dropForeign(['patient_id']);
                $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('medical_files')) {
            Schema::table('medical_files', function (Blueprint $table) {
                $table->dropForeign(['patient_id']);
                $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            });
        }
    }
};
