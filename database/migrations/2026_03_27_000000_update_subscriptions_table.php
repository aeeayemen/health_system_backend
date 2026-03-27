<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Add receipt_image column
            if (!Schema::hasColumn('subscriptions', 'receipt_image')) {
                $table->string('receipt_image')->nullable()->after('end_date');
            }

            // Modify status column to include 'pending'
            // In MySQL, we need to use a raw statement for ENUM changes or change it to string temporarily
            // For safety and compatibility with standard dashboard requests, we use string if ENUM is too restrictive
            // but let's try to update the ENUM first if it exists.
        });

        // Update ENUM status to include 'pending'
        DB::statement("ALTER TABLE subscriptions MODIFY COLUMN status ENUM('active', 'inactive', 'expired', 'cancelled', 'pending') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'receipt_image')) {
                $table->dropColumn('receipt_image');
            }
        });

        DB::statement("ALTER TABLE subscriptions MODIFY COLUMN status ENUM('active', 'inactive', 'expired', 'cancelled') NOT NULL DEFAULT 'active'");
    }
};
