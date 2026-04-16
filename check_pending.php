<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Subscription;

$pending = Subscription::where('status', 'pending')->with(['doctor', 'patient'])->get();

echo "Total Pending Subscriptions: " . $pending->count() . "\n";
foreach ($pending as $s) {
    echo "ID: {$s->id}, Patient Name: " . ($s->patient ? $s->patient->fullname : "null") . " (ID: {$s->patient_id}), Doctor Name: " . ($s->doctor ? $s->doctor->name : "null") . " (ID: {$s->doctor_id})\n";
}
