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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            // Note: The default naming convention is table_column_foreign
            $table->dropForeign(['patient_id']);

            // Add the new foreign key constraint referencing subscribed_users
            $table->foreign('patient_id')->references('id')->on('subscribed_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
        });
    }
};
