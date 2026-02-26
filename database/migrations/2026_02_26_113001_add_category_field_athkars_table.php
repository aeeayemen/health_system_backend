<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Enums\AthkarCategory;

return new class extends Migration {
    public function up()
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE athkars ALTER COLUMN category TYPE VARCHAR(255)');
            DB::statement('ALTER TABLE athkars ADD CONSTRAINT athkars_category_check CHECK (category IN (\'صباحي\', \'مسائي\'))');
        } else {
            Schema::table('athkars', function (Blueprint $table) {
                $table->enum('category', AthkarCategory::values())->change();
            });
        }
    }

    public function down()
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE athkars DROP CONSTRAINT IF EXISTS athkars_category_check');
        } else {
            Schema::table('athkars', function (Blueprint $table) {
                $table->string('category')->change();
            });
        }
    }
};