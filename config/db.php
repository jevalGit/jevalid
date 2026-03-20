<?php
$host = "0.0.0.0";
$port = "3306";
$user = "jevalidtest";
$pass = "non";
$dbname = "bd_name";

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET time_zone = '+00:00'");
} catch (Exception $e) {
    error_log("DB CONNECT ERROR: " . $e->getMessage());
    // No imprimir texto aquí
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'db_connection_failed']);
    exit;
}