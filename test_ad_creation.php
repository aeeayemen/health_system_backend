<?php

use App\Models\Advertisement;
use Illuminate\Http\Request;
use App\Http\Controllers\AdvertisementController;
use Illuminate\Support\Facades\Auth;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "--- Testing Advertisement Creation (Default admin_id) ---\n";

$app->instance('request', Request::create('/'));
$controller = $app->make(AdvertisementController::class);

// Simulate request without Auth
// We are NOT logging in, so Auth::id() should be null.
// The controller should set admin_id to 1.

$request = Request::create('/api/advertisements', 'POST', [
    'describtion' => 'Test Ad Description',
    'image' => 'test_image.jpg',
    'phone_number' => '123456789',
    'type' => 'عرض', // Valid type
    'GPS' => 'Location'
]);

try {
    $response = $controller->store($request);
    echo "Response Status: " . $response->getStatusCode() . "\n";
    $content = $response->getContent();
    echo "Response Content: " . $content . "\n";

    if ($response->getStatusCode() === 201) {
        $adData = json_decode($content, true);
        if (isset($adData['admin_id']) && $adData['admin_id'] == 1) {
            echo "SUCCESS: Advertisement created with default admin_id = 1.\n";
        } else {
            echo "FAILURE: Advertisement created but admin_id is not 1. It is: " . ($adData['admin_id'] ?? 'null') . "\n";
        }
    } else {
        echo "FAILURE: Advertisement creation failed.\n";
    }

} catch (\Illuminate\Validation\ValidationException $e) {
    echo "Validation Error: " . json_encode($e->errors()) . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
