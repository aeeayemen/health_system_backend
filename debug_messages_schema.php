<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "--- Messages Table Schema ---\n";
$columns = Schema::getColumnListing('messages');
print_r($columns);

echo "\n--- Checking for 'message' column ---\n";
if (Schema::hasColumn('messages', 'message')) {
    echo "Column 'message' EXISTS.\n";
} else {
    echo "Column 'message' DOES NOT EXIST.\n";
}

echo "\n--- Checking for 'massage' column ---\n";
if (Schema::hasColumn('messages', 'massage')) {
    echo "Column 'massage' EXISTS.\n";
} else {
    echo "Column 'massage' DOES NOT EXIST.\n";
}
