<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'admin@example.com';
$password = 'password';

$user = User::where('email', $email)->first();

if ($user) {
    $user->password = Hash::make($password);
    $user->save();
    echo "Password for $email has been reset to '$password'.\n";
} else {
    echo "User $email not found.\n";
}
