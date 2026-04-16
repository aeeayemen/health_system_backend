<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Subscription;

$subscriptions = Subscription::with(['doctor', 'patient'])->get();

echo "Total Subscriptions: " . $subscriptions->count() . "\n";
foreach ($subscriptions as $s) {
    echo "ID: {$s->id}, Status: {$s->status}, Patient: " . ($s->patient ? $s->patient->fullname : "null") . ", Doctor: " . ($s->doctor ? $s->doctor->name : "null") . "\n";
}
