<?php

use App\Models\Rate;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "DB Connection: " . DB::getDefaultConnection() . "\n";
echo "DB Database: " . DB::connection()->getDatabaseName() . "\n";

$count = Rate::count();
echo "Rate Count: $count\n";

if ($count > 0) {
    $rate = Rate::with(['user', 'doctor'])->first();
    echo "First Rate JSON: " . json_encode($rate) . "\n";
} else {
    echo "No rates found.\n";
}
