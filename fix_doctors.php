<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Doctor;
use App\Models\User;

echo "Starting Doctor data sync...\n";

$doctors = Doctor::all();
$updatedCount = 0;

foreach ($doctors as $doctor) {
    if ($doctor->user_id) {
        $user = User::find($doctor->user_id);
        if ($user) {
            $changed = false;

            // Sync name if null or mismatch
            if (empty($doctor->name) || $doctor->name !== $user->name) {
                $doctor->name = $user->name;
                $changed = true;
            }

            // Sync phone if empty but user has phone
            if (empty($doctor->phone_number) && !empty($user->phone)) {
                $doctor->phone_number = $user->phone;
                $changed = true;
            }

            // Provide default values for null fields that should probably be set
            if (empty($doctor->specialization)) {
                $doctor->specialization = 'General';
                $changed = true;
            }

            if (empty($doctor->consultation_fee) && $doctor->consultation_fee !== '0.00' && $doctor->consultation_fee !== 0) {
                // Set a default fee if it's completely null, maybe 100 based on standard.
                $doctor->consultation_fee = 100;
                $changed = true;
            }

            if ($changed) {
                $doctor->save();
                $updatedCount++;
                echo "Updated Doctor ID: {$doctor->id} ({$doctor->name})\n";
            }
        }
    }
}

echo "Sync completed! Updated {$updatedCount} doctors.\n";
