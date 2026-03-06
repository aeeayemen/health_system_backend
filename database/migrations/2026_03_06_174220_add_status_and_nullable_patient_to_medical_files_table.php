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
        Schema::table('medical_files', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->nullable()->change();
            $table->string('status')->nullable()->after('file_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_files', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->nullable(false)->change();
            $table->dropColumn('status');
        });
    }
};
