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
        if (!Schema::hasColumn('forum_posts', 'forum_id')) {
            Schema::table('forum_posts', function (Blueprint $table) {
                $table->foreignId('forum_id')->nullable()->after('id')->constrained('forums')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forum_posts', function (Blueprint $table) {
            $table->dropForeign(['forum_id']);
            $table->dropColumn('forum_id');
        });
    }
};
