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
        Schema::table('subscribed_users', function (Blueprint $table) {
            $table->decimal('subscription_price', 8, 2)->nullable();
            $table->string('subscription_type')->nullable();
            $table->date('subscription_start_date')->nullable();
            $table->date('subscription_end_date')->nullable();
            $table->string('subscription_receipt_image')->nullable();
            $table->string('subscription_status')->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscribed_users', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_price',
                'subscription_type',
                'subscription_start_date',
                'subscription_end_date',
                'subscription_receipt_image',
                'subscription_status',
            ]);
        });
    }
};
