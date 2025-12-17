<?php

use App\Models\User;
use App\Models\Doctor;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ChatController;
use Illuminate\Http\Request;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

// 1. Setup Data
echo "--- Setting up test data ---\n";
$user = User::factory()->create();
$doctorUser = User::factory()->create();
$doctor = Doctor::create([
    'user_id' => $doctorUser->id,
    'name' => 'Test Doctor',
    'gender' => 'Male',
    'degree' => 'MD',
    'bank_account' => '123',
    'phone_number' => '123',
    'CV' => 'cv.pdf',
    'admin_id' => 1
]);

// Create 3 messages: 2 between this pair, 1 unrelated
$msg1 = Message::create([
    'user_id' => $user->id,
    'doctor_id' => $doctor->id,
    'message' => 'Hello',
    'time' => '10:00',
    'date' => '2023-01-01',
    'read' => 'false'
]);
$msg2 = Message::create([
    'user_id' => $user->id,
    'doctor_id' => $doctor->id,
    'message' => 'Hi there',
    'time' => '10:05',
    'date' => '2023-01-01',
    'read' => 'false'
]);
$msg3 = Message::create([ // Unrelated
    'user_id' => $user->id, // Same user
    'doctor_id' => 9999, // Different doctor (non-existent is fine for test)
    'message' => 'Unrelated',
    'time' => '10:00',
    'date' => '2023-01-01',
    'read' => 'false'
]);

echo "Created Message 1 ID: " . $msg1->id . "\n";
echo "Created Message 2 ID: " . $msg2->id . "\n";
echo "Created Message 3 ID: " . $msg3->id . "\n";

// 2. Test Deletion
echo "\n--- Testing deleteConversation ---\n";
$app->instance('request', Request::create('/'));
$controller = $app->make(ChatController::class);

// Simulate request (not needed for this method but good practice)
// We call deleteConversation with msg1 ID. It should delete msg1 AND msg2.
$response = $controller->deleteConversation($msg1->id);

echo "Response Status: " . $response->getStatusCode() . "\n";
echo "Response Content: " . $response->getContent() . "\n";

// 3. Verify
echo "\n--- Verifying Deletion ---\n";
$check1 = Message::find($msg1->id);
$check2 = Message::find($msg2->id);
$check3 = Message::find($msg3->id);

if (!$check1 && !$check2) {
    echo "SUCCESS: Messages 1 and 2 deleted.\n";
} else {
    echo "FAILURE: Messages 1 or 2 still exist.\n";
}

if ($check3) {
    echo "SUCCESS: Message 3 still exists (unrelated conversation).\n";
} else {
    echo "FAILURE: Message 3 was incorrectly deleted.\n";
}

// Cleanup
$user->delete();
$doctorUser->delete();
$doctor->delete();
if ($check3)
    $check3->delete();
