<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Subscription;

$count = Subscription::count();
$latest = Subscription::latest()->take(3)->get();
$pending = Subscription::where('status', 'pending')->count();

echo "Total Subscriptions: $count\n";
echo "Pending Subscriptions: $pending\n";
echo "Latest IDs: " . $latest->pluck('id')->implode(', ') . "\n";
foreach ($latest as $s) {
    echo "ID: {$s->id}, Status: {$s->status}, Created: {$s->created_at}\n";
}
