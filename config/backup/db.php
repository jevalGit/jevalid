<?php
$host = "192.168.1.94:3306";
$user = "jesus";
$pass = "2952404";
$dbname = "id";

try {
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
$pdo->exec("SET time_zone = '+00:00'");
?>
