<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $doctor = \App\Models\Doctor::first();
    $patients = \App\Models\Patient::whereHas('subscriptions', function ($q) use ($doctor) {
        $q->where('doctor_id', $doctor->id);
    })->orWhereHas('dietPlans', function ($q) use ($doctor) {
        $q->where('doctor_id', $doctor->id);
    })->with('user:id,name,email,phone')->paginate(20);
    $resourcePath = \App\Http\Resources\PatientResource::collection($patients)->resolve();
    file_put_contents('output_dump2.txt', "SUCCESS");
} catch (\Exception $e) {
    file_put_contents('output_dump2.txt', "ERROR MSG: " . $e->getMessage() . "\nFILE: " . $e->getFile() . "\nLINE: " . $e->getLine());
}
