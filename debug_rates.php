<?php

use App\Models\Rate;
use App\Models\User;
use App\Models\Doctor;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$kernel->bootstrap();

echo "--- Debugging Rates ---\n";
$count = Rate::count();
echo "Total Rates in DB (Eloquent): " . $count . "\n";

if ($count > 0) {
    $rate = Rate::first();
    echo "First Rate ID: " . $rate->id . "\n";
    echo "User ID: " . $rate->user_id . "\n";
    echo "Doctor ID: " . $rate->doctor_id . "\n";

    $user = User::find($rate->user_id);
    echo "User found: " . ($user ? 'Yes' : 'No') . "\n";

    $doctor = Doctor::find($rate->doctor_id);
    echo "Doctor found: " . ($doctor ? 'Yes' : 'No') . "\n";

    echo "Rate with relations: \n";
    print_r(Rate::with(['user', 'doctor'])->first()->toArray());
} else {
    echo "No rates found via Eloquent.\n";
}
