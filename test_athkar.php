<?php

// Load Laravel App
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Run DB Query
try {
    $athkar = \App\Models\Athkar::all();
    echo "Count: " . $athkar->count() . "\n\n";
    print_r($athkar->toArray());
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
