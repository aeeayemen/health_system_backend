<?php

use App\Models\Tip;
use App\Models\TipCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\TipController;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "--- Testing Tip Category Link ---\n";

// 1. Create a Category
$category = TipCategory::create([
    'name_en' => 'Test Category',
    'name_ar' => 'فئة اختبار'
]);
echo "Created Category ID: " . $category->id . "\n";

// 2. Create a Tip linked to this Category
$app->instance('request', Request::create('/'));
$controller = $app->make(TipController::class);

$request = Request::create('/api/tips', 'POST', [
    'describtion' => 'Tip with Category',
    'category_id' => $category->id,
    'date' => '2023-01-01'
]);

try {
    $response = $controller->store($request);
    echo "Store Response Status: " . $response->getStatusCode() . "\n";
    $tipData = json_decode($response->getContent(), true);

    if ($response->getStatusCode() === 201) {
        echo "Tip Created ID: " . $tipData['id'] . "\n";

        // 3. Verify Relationship
        $tip = Tip::find($tipData['id']);
        if ($tip->category_id == $category->id) {
            echo "SUCCESS: Tip linked to Category correctly.\n";
        } else {
            echo "FAILURE: Tip category_id mismatch.\n";
        }

        if ($tip->category && $tip->category->id == $category->id) {
            echo "SUCCESS: Tip->category relationship works.\n";
        } else {
            echo "FAILURE: Tip->category relationship failed.\n";
        }

        // 4. Verify Inverse Relationship
        $cat = TipCategory::with('tips')->find($category->id);
        if ($cat->tips->contains('id', $tip->id)) {
            echo "SUCCESS: Category->tips relationship works.\n";
        } else {
            echo "FAILURE: Category->tips relationship failed.\n";
        }

    } else {
        echo "FAILURE: Tip creation failed.\n";
        print_r($tipData);
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Cleanup
if (isset($tip))
    $tip->delete();
$category->delete();
