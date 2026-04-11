<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MedicalTest;
use App\Models\Patient;
use App\Models\User;

try {
    echo "Verifying relationships...\n";
    
    $test = MedicalTest::with(['user', 'patient'])->first();
    if ($test) {
        echo "MedicalTest ID: " . $test->id . "\n";
        echo "User Name: " . ($test->user->name ?? 'N/A') . "\n";
        echo "Patient Fullname: " . ($test->patient->fullname ?? 'N/A') . "\n";
    } else {
        echo "No MedicalTest found to verify relationships.\n";
    }

    $patient = Patient::with('medicalTests')->first();
    if ($patient) {
        echo "\nPatient Name: " . $patient->fullname . "\n";
        echo "MedicalTests Count: " . $patient->medicalTests->count() . "\n";
    }

    echo "\nVerification script completed successfully.\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
