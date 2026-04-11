<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Checking MedicalTest columns:\n";
    $columns = Illuminate\Support\Facades\Schema::getColumnListing('medical_tests');
    print_r($columns);

    echo "\nChecking MedicalFile columns:\n";
    $columnsFile = Illuminate\Support\Facades\Schema::getColumnListing('medical_files');
    print_r($columnsFile);

    echo "\nChecking first MedicalTest if any:\n";
    $test = \App\Models\MedicalTest::first();
    if ($test) {
        print_r($test->toArray());
    } else {
        echo "No MedicalTest found.\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
