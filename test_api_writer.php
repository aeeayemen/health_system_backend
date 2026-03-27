<?php
$baseUrl = "https://health-system-backend-l7m5.onrender.com/api";

function post($url, $data)
{
    global $baseUrl;
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\nAccept: application/json\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
            'ignore_errors' => true
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return json_decode($result, true) ?? $result;
}

$out = "Registering User 1 (Patient)...\n";
$u1 = [
    'name' => 'Test Patient1',
    'email' => 't1_' . time() . '@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'type' => 'patient'
];
$r1 = post("$baseUrl/register", $u1);
$out .= json_encode($r1) . "\n\n";

$out .= "Registering User 2 (Doctor)...\n";
$u2 = [
    'name' => 'Test Doctor2',
    'email' => 't2_' . time() . '@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'type' => 'doctor'
];
$r2 = post("$baseUrl/register", $u2);
$out .= json_encode($r2) . "\n\n";

$out .= "Logging in User 1...\n";
$l1 = post("$baseUrl/login", [
    'email' => $u1['email'],
    'password' => $u1['password']
]);
$out .= json_encode($l1) . "\n\n";

$out .= "Logging in User 2...\n";
$l2 = post("$baseUrl/login", [
    'email' => $u2['email'],
    'password' => $u2['password']
]);
$out .= json_encode($l2) . "\n\n";

file_put_contents('test_api_result_fixed.txt', $out);
