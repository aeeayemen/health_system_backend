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
        Schema::table('doctors', function (Blueprint $table) {
            if (!Schema::hasColumn('doctors', 'diet_price')) {
                $table->decimal('diet_price', 10, 2)->nullable()->after('phone_number'); // سعر الحمية
            }
            if (!Schema::hasColumn('doctors', 'email')) {
                $table->string('email', 255)->nullable()->after('name'); // البريد الإلكتروني
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            if (Schema::hasColumn('doctors', 'diet_price')) {
                $table->dropColumn('diet_price');
            }
            if (Schema::hasColumn('doctors', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};
