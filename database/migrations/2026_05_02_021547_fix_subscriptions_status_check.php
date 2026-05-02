<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::statement('ALTER TABLE subscriptions DROP CONSTRAINT IF EXISTS subscriptions_status_check');
        DB::statement('ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_status_check CHECK (status IN (\'pending\', \'active\', \'expired\', \'rejected\'))');
    }
    public function down()
    {
        DB::statement('ALTER TABLE subscriptions DROP CONSTRAINT subscriptions_status_check');
        DB::statement('ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_status_check CHECK (status IN (\'active\', \'expired\', \'rejected\'))');
    }
};
