<?php

use Illuminate\Http\Request;
use App\Http\Controllers\RateController;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::create('/api/rates', 'GET')
);

echo $response->getContent();
