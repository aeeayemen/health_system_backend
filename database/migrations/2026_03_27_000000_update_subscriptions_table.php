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
        $driver = DB::getDriverName();

        Schema::table('subscriptions', function (Blueprint $table) use ($driver) {
            // Add receipt_image column
            if (!Schema::hasColumn('subscriptions', 'receipt_image')) {
                $table->string('receipt_image')->nullable()->after('end_date');
            }
        });

        // Update ENUM status to include 'pending'
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE subscriptions MODIFY COLUMN status ENUM('active', 'inactive', 'expired', 'cancelled', 'pending') NOT NULL DEFAULT 'pending'");
        } elseif ($driver === 'pgsql') {
            // For PostgreSQL, changing an ENUM is more complex. 
            // The simplest cross-version way is to change to TEXT/VARCHAR temporarily.
            DB::statement("ALTER TABLE subscriptions ALTER COLUMN status TYPE VARCHAR(50)");
            DB::statement("ALTER TABLE subscriptions ALTER COLUMN status SET DEFAULT 'pending'");
        } else {
            // Fallback for other drivers
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->string('status')->default('pending')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'receipt_image')) {
                $table->dropColumn('receipt_image');
            }
        });

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE subscriptions MODIFY COLUMN status ENUM('active', 'inactive', 'expired', 'cancelled') NOT NULL DEFAULT 'active'");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE subscriptions ALTER COLUMN status SET DEFAULT 'active'");
        }
    }
};
