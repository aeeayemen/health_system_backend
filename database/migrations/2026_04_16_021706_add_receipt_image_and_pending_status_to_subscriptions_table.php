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
            if (!Schema::hasColumn('subscriptions', 'receipt_image')) {
                $table->string('receipt_image')->nullable()->after('end_date');
            }
            // For enum, we usually need to use DB::statement for changes depending on DB type
            // but since we are adding 'pending', let's try to update it
        });

        // Use raw query to update enum if needed (MySQL/PostgreSQL)
        try {
            DB::statement("ALTER TABLE subscriptions MODIFY COLUMN status ENUM('active', 'inactive', 'expired', 'cancelled', 'pending') DEFAULT 'pending'");
        } catch (\Exception $e) {
            // Fallback for other DBs or already updated
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('receipt_image');
        });
    }
};
