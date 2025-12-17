<?php

use App\Models\Tip;
use Illuminate\Http\Request;
use App\Http\Controllers\TipController;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "--- Testing Tip Creation without admin_id ---\n";

$app->instance('request', Request::create('/'));
$controller = $app->make(TipController::class);

$request = Request::create('/api/tips', 'POST', [
    'describtion' => 'This is a test tip without admin_id',
    'date' => '2023-01-01'
]);

try {
    $response = $controller->store($request);
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Content: " . $response->getContent() . "\n";

    if ($response->getStatusCode() === 201) {
        echo "SUCCESS: Tip created successfully.\n";
    } else {
        echo "FAILURE: Tip creation failed.\n";
    }

} catch (\Illuminate\Validation\ValidationException $e) {
    echo "Validation Error: " . json_encode($e->errors()) . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
