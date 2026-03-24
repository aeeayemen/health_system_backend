<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Get the first doctor
    $doctor = \App\Models\Doctor::first();
    echo "Doctor ID: " . $doctor->id . "\n";

    // Simulate the query in myPatients
    $patients = \App\Models\Patient::whereHas('subscriptions', function ($q) use ($doctor) {
        $q->where('doctor_id', $doctor->id);
    })
        ->orWhereHas('dietPlans', function ($q) use ($doctor) {
            $q->where('doctor_id', $doctor->id);
        })
        ->with('user:id,name,email,phone')
        ->paginate(20);

    echo "Query successful. Patients count: " . $patients->count() . "\n";

    // Attempt to format with Resource
    $resourcePath = \App\Http\Resources\PatientResource::collection($patients)->resolve();
    echo "Resource resolution successful.\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
