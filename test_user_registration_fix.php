<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Ensure roles exist
if (!Role::where('name', 'patient')->exists()) {
    Role::create(['name' => 'patient']);
}

$email = 'test_fix_' . time() . '@example.com';
$password = 'password123';
$role = 'patient';

echo "Attempting to register user with email: $email\n";

try {
    // Simulate AuthController logic
    $validated = [
        'name' => 'Test User',
        'email' => $email,
        'password' => $password,
        'phone' => '1234567890',
        'role' => $role,
    ];

    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'phone' => $validated['phone'],
        'type' => $validated['role'],
       
    ]);

    $user->assignRole($validated['role']);

    echo "User created successfully.\n";

    // Verify database
    $createdUser = User::where('email', $email)->first();

    if ($createdUser) {
        echo "User found in database.\n";
        echo "User Type: " . $createdUser->type . "\n";

        if ($createdUser->type === $role) {
            echo "SUCCESS: User type matches role.\n";
        } else {
            echo "FAILURE: User type does not match role.\n";
        }

        if ($createdUser->hasRole($role)) {
            echo "SUCCESS: User has correct Spatie role.\n";
        } else {
            echo "FAILURE: User does not have correct Spatie role.\n";
        }

    } else {
        echo "FAILURE: User not found in database.\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
