<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Use raw SQL for PostgreSQL to handle the casting
        if (config('database.default') === 'pgsql') {
            DB::statement('ALTER TABLE rates ALTER COLUMN rate TYPE INTEGER USING rate::integer');
        } else {
            Schema::table('rates', function (Blueprint $table) {
                $table->integer('rate')->change();
            });
        }
    }

    public function down(): void
    {
        if (config('database.default') === 'pgsql') {
            DB::statement('ALTER TABLE rates ALTER COLUMN rate TYPE VARCHAR(100) USING rate::varchar');
        } else {
            Schema::table('rates', function (Blueprint $table) {
                $table->string('rate', 100)->change();
            });
        }
    }
};
