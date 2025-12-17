<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $pdo->exec("CREATE DATABASE IF NOT EXISTS health_nutrition");
    echo "Database created successfully";
} catch (PDOException $e) {
    echo "Failed: " . $e->getMessage();
}
