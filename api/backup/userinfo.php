<?php
require __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');
// Si quieres ver errores mientras desarrollas, descomenta estas dos líneas:
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// ---- 1. Leer header Authorization de forma robusta ----
$auth = '';

if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $auth = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $auth = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
} elseif (function_exists('getallheaders')) {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $auth = $headers['Authorization'];
    }
}

// Validar formato Bearer
if (!$auth || !preg_match('/^Bearer\s+(.+)$/i', $auth, $m)) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Falta token Bearer']);
    exit;
}

$token = trim($m[1]);

// ---- 2. Buscar token en la BD ----
$stmt = $pdo->prepare("
    SELECT u.id, u.primer_nombre, u.correo, u.rol
    FROM api_tokens t
    JOIN usuarios u ON u.id = t.user_id
    WHERE t.token = :token AND t.revoked = 0
    LIMIT 1
");
$stmt->execute([':token' => $token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Token inválido o revocado']);
    exit;
}

// (Opcional) actualizar last_used_at si tienes esa columna
// $upd = $pdo->prepare("UPDATE api_tokens SET last_used_at = NOW() WHERE token = :token");
// $upd->execute([':token' => $token]);

echo json_encode([
    'ok'   => true,
    'user' => [
        'id'            => (int)$user['id'],
        'primer_nombre' => $user['primer_nombre'],
        'correo'        => $user['correo'],
        'rol'           => $user['rol'],
    ]
]);
