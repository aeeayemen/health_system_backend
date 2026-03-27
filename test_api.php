<?php
$baseUrl = "https://health-system-backend-l7m5.onrender.com/api";

function post($url, $data)
{
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
            'ignore_errors' => true // to get HTTP status code and body on error
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return json_decode($result, true) ?? $result;
}

echo "Registering User 1 (Patient)...\n";
$u1 = [
    'name' => 'Test Patient1',
    'email' => 't1_' . time() . '@example.com',
    'password' => 'password123',
    'type' => 'patient'
];
$r1 = post("$baseUrl/register", $u1);
echo json_encode($r1) . "\n\n";

echo "Registering User 2 (Doctor)...\n";
$u2 = [
    'name' => 'Test Doctor2',
    'email' => 't2_' . time() . '@example.com',
    'password' => 'password123',
    'type' => 'doctor'
];
$r2 = post("$baseUrl/register", $u2);
echo json_encode($r2) . "\n\n";

echo "Logging in User 1...\n";
$l1 = post("$baseUrl/login", [
    'email' => $u1['email'],
    'password' => $u1['password']
]);
echo json_encode($l1) . "\n\n";

echo "Logging in User 2...\n";
$l2 = post("$baseUrl/login", [
    'email' => $u2['email'],
    'password' => $u2['password']
]);
echo json_encode($l2) . "\n\n";
