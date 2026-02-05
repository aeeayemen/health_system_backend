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
        Schema::table('subscribed_users', function (Blueprint $table) {
            $table->decimal('target_weight', 8, 2)->nullable()->after('weight');
            $table->text('allergies')->nullable()->after('medical');
            $table->unsignedBigInteger('current_doctor_id')->nullable()->after('user_id');
            $table->foreign('current_doctor_id')->references('id')->on('doctors')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscribed_users', function (Blueprint $table) {
            $table->dropForeign(['current_doctor_id']);
            $table->dropColumn(['target_weight', 'allergies', 'current_doctor_id']);
        });
    }
};
