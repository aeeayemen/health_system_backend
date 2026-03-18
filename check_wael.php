<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;

$waels = User::where('name', 'like', '%Wael%')->get();
foreach ($waels as $u) {
    echo "User ID: {$u->id}, Name: {$u->name}, Type: {$u->type}\n";
    $doctor = Doctor::where('user_id', $u->id)->first();
    if ($doctor)
        echo "  - Doctor Profile: ID={$doctor->id}\n";
    $patient = Patient::where('user_id', $u->id)->first();
    if ($patient)
        echo "  - Patient Profile: ID={$patient->id}\n";
}
