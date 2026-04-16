<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Patient;
use App\Models\DietPlan;
use App\Models\Diet;

$data = [
    'total_users' => User::count(),
    'total_patients' => Patient::count(),
    'total_diet_plans' => DietPlan::count(),
    'total_legacy_diets' => Diet::count(),
    'latest_plans' => DietPlan::latest()->take(5)->get(['id', 'patient_id', 'title']),
    'latest_patients' => Patient::latest()->take(5)->get(['id', 'user_id', 'fullname']),
];

echo json_encode($data, JSON_PRETTY_PRINT);
