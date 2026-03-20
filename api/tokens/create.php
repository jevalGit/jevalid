<?php
session_start();
require __DIR__ . '/../../config/db.php';

// Solo usuarios logueados
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

// Solo developer_api o admin
$rol = $_SESSION['rol'] ?? 'user';
if (!in_array($rol, ['developer_api', 'admin'], true)) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'No tienes permisos para crear tokens']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$name = trim($_POST['name'] ?? '');
if ($name === '') {
    echo json_encode(['ok' => false, 'error' => 'Falta el nombre del token']);
    exit;
}

try {
    $token = bin2hex(random_bytes(32)); // 64 chars

    $stmt = $pdo->prepare("
        INSERT INTO api_tokens (user_id, token, name)
        VALUES (:user_id, :token, :name)
    ");
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':token'   => $token,
        ':name'    => $name
    ]);

    echo json_encode([
        'ok'    => true,
        'token' => $token
    ]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'error' => 'Error interno: '.$e->getMessage()]);
}
