<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

try {
    $users = User::orderBy('id', 'desc')->take(10)->get();
    echo "Last 10 users:\n";
    foreach ($users as $u) {
        echo "ID: {$u->id}, Email: {$u->email}, Type: {$u->type}\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
