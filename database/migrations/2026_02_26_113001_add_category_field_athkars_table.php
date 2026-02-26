<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\AthkarCategory;

return new class extends Migration {
    public function up()
    {
        Schema::table('athkars', function (Blueprint $table) {
            $table->enum('category', AthkarCategory::values())->change();
        });
    }

    public function down()
    {
        Schema::table('athkars', function (Blueprint $table) {
            $table->string('category')->change();
        });
    }
};