<?php
ini_set('display_errors', '0');
error_reporting(0);

require __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

function out(int $code, array $data): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

// Authorization header robusto
$auth = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
if (!$auth && function_exists('getallheaders')) {
    $h = getallheaders();
    $auth = $h['Authorization'] ?? $h['authorization'] ?? '';
}

if (!preg_match('/^Bearer\s+(.+)$/i', $auth, $m)) {
    out(401, ['ok' => false, 'error' => 'missing_bearer']);
}
$token = trim($m[1]);
if ($token === '') out(401, ['ok' => false, 'error' => 'missing_bearer']);

try {
    // 1) Query mínima (nunca falla por columnas inexistentes)
    $stmt = $pdo->prepare("
        SELECT u.id, u.primer_nombre, u.correo
        FROM api_tokens t
        JOIN usuarios u ON u.id = t.user_id
        WHERE t.token = :token AND t.revoked = 0
        LIMIT 1
    ");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        out(401, ['ok' => false, 'error' => 'invalid_token']);
    }

    // 2) Opcionales: rol + email_verificado (solo si existen)
    $extra = [];

    // rol
    try {
        $q = $pdo->prepare("SELECT rol FROM usuarios WHERE id = :id LIMIT 1");
        $q->execute([':id' => (int)$user['id']]);
        $r = $q->fetch(PDO::FETCH_ASSOC);
        if ($r && isset($r['rol'])) $extra['rol'] = $r['rol'];
    } catch (Throwable $e) {
        // ignorar
    }

    // email_verificado
    try {
        $q = $pdo->prepare("SELECT email_verificado FROM usuarios WHERE id = :id LIMIT 1");
        $q->execute([':id' => (int)$user['id']]);
        $r = $q->fetch(PDO::FETCH_ASSOC);
        if ($r && array_key_exists('email_verificado', $r)) {
            $extra['email_verificado'] = ((int)$r['email_verificado'] === 1);
        }
    } catch (Throwable $e) {
        // ignorar
    }

    // Compatibilidad + OIDC claims extra
    $response = [
        'ok'   => true,
        'user' => array_merge($user, $extra),

        // OIDC
        'sub'  => 'u' . (int)$user['id'],            // sin ":" (válido)
        'email' => $user['correo'] ?? null,
        'name'  => $user['primer_nombre'] ?? null,
    ];

    if (isset($extra['email_verificado'])) {
        $response['email_verified'] = $extra['email_verificado'];
    }
    if (isset($extra['rol'])) {
        $response['role'] = $extra['rol'];
    }

    // opcional para futuro
    $response['preferred_username'] = !empty($user['correo'])
        ? preg_replace('/@.*/', '', (string)$user['correo'])
        : ('u' . (int)$user['id']);

    out(200, $response);

} catch (Throwable $e) {
    // Nunca HTML, nunca texto
    error_log("USERINFO_FATAL: " . $e->getMessage());
    out(500, ['ok' => false, 'error' => 'server_error']);
}