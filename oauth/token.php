<?php
require __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

function b64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
function json_error(int $code, string $msg): void {
    http_response_code($code);
    echo json_encode(['error' => $msg], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
function dbHasColumn(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = :t
          AND COLUMN_NAME = :c
    ");
    $stmt->execute([':t' => $table, ':c' => $column]);
    return (int)$stmt->fetchColumn() > 0;
}

// Solo POST
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_error(405, 'Método no permitido, usa POST');
}

$input = $_POST; // application/x-www-form-urlencoded

$grant_type    = trim($input['grant_type'] ?? '');
$code          = trim($input['code'] ?? '');
$redirect_uri  = trim($input['redirect_uri'] ?? '');
$client_id     = trim($input['client_id'] ?? '');
$client_secret = trim($input['client_secret'] ?? '');
$code_verifier = trim($input['code_verifier'] ?? ''); // PKCE

// Soportar client_secret_basic (Proxmox lo usa)
$auth = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
if (preg_match('/^Basic\s+(.+)$/i', $auth, $m)) {
    $decoded = base64_decode($m[1], true);
    if ($decoded !== false && str_contains($decoded, ':')) {
        [$cid, $csec] = explode(':', $decoded, 2);
        // sobreescribir para compatibilidad total
        $client_id = trim($cid);
        $client_secret = trim($csec);
    }
}

if ($grant_type !== 'authorization_code') json_error(400, 'grant_type inválido');
if ($code === '' || $redirect_uri === '' || $client_id === '' || $client_secret === '') {
    json_error(400, 'Faltan parámetros obligatorios');
}

// 1) Cargar cliente
$stmt = $pdo->prepare("
    SELECT client_id, client_secret, redirect_uri, revoked
    FROM oauth_clients
    WHERE client_id = :cid
    LIMIT 1
");
$stmt->execute([':cid' => $client_id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) json_error(400, 'client_id inválido');
if (!empty($client['revoked'])) json_error(400, 'client_revoked');
if (!hash_equals($client['client_secret'], $client_secret)) json_error(401, 'invalid_client');
if (!hash_equals($client['redirect_uri'], $redirect_uri)) json_error(400, 'redirect_uri no coincide con el registrado');

// 2) Buscar code y bloquearlo
$codeHasScope = dbHasColumn($pdo, 'oauth_codes', 'scope');
$codeHasNonce = dbHasColumn($pdo, 'oauth_codes', 'nonce');
$codeHasCC    = dbHasColumn($pdo, 'oauth_codes', 'code_challenge');
$codeHasCCM   = dbHasColumn($pdo, 'oauth_codes', 'code_challenge_method');

$selectCols = "id, code, user_id, client_id, redirect_uri, expires_at, used";
if ($codeHasScope) $selectCols .= ", scope";
if ($codeHasNonce) $selectCols .= ", nonce";
if ($codeHasCC)    $selectCols .= ", code_challenge";
if ($codeHasCCM)   $selectCols .= ", code_challenge_method";

$pdo->beginTransaction();

$stmt = $pdo->prepare("
    SELECT $selectCols
    FROM oauth_codes
    WHERE code = :code
      AND client_id = :client_id
      AND redirect_uri = :redirect_uri
    LIMIT 1
    FOR UPDATE
");
$stmt->execute([
    ':code' => $code,
    ':client_id' => $client_id,
    ':redirect_uri' => $redirect_uri
]);

$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    $pdo->rollBack();
    json_error(400, 'Código de autorización inválido');
}
if ((int)$row['used'] === 1) {
    $pdo->rollBack();
    json_error(400, 'Código ya utilizado');
}

// exp
$exp = strtotime($row['expires_at']);
if ($exp !== false && $exp < time()) {
    $pdo->rollBack();
    json_error(400, 'Código expirado');
}

// 3) Validar PKCE si existe code_challenge
$challenge = $row['code_challenge'] ?? null;
$method    = $row['code_challenge_method'] ?? 'S256';

if ($challenge !== null && $challenge !== '') {
    if ($code_verifier === '') {
        $pdo->rollBack();
        json_error(400, 'missing_code_verifier');
    }

    if ($method === 'plain') {
        if (!hash_equals($challenge, $code_verifier)) {
            $pdo->rollBack();
            json_error(400, 'invalid_code_verifier');
        }
    } else { // S256
        $calc = b64url_encode(hash('sha256', $code_verifier, true));
        if (!hash_equals($challenge, $calc)) {
            $pdo->rollBack();
            json_error(400, 'invalid_code_verifier');
        }
    }
}

// 4) Marcar code como usado
$upd = $pdo->prepare("UPDATE oauth_codes SET used = 1 WHERE id = :id");
$upd->execute([':id' => (int)$row['id']]);

$pdo->commit();

// 5) Crear access_token y guardarlo en api_tokens (si existe tabla)
$access_token = bin2hex(random_bytes(32));
$user_id = (int)$row['user_id'];

$hasApiOauthClientId = dbHasColumn($pdo, 'api_tokens', 'oauth_client_id');
$hasApiTokenType     = dbHasColumn($pdo, 'api_tokens', 'token_type');

if ($hasApiOauthClientId && $hasApiTokenType) {
    $stmt = $pdo->prepare("
        INSERT INTO api_tokens (user_id, oauth_client_id, token_type, token, name, created_at, revoked)
        VALUES (:uid, :ocid, 'oauth', :tok, :name, NOW(), 0)
    ");
    $stmt->execute([
        ':uid'  => $user_id,
        ':ocid' => $client_id,
        ':tok'  => $access_token,
        ':name' => 'oauth:proxmox'
    ]);
} elseif ($hasApiTokenType) {
    $stmt = $pdo->prepare("
        INSERT INTO api_tokens (user_id, token_type, token, name, created_at, revoked)
        VALUES (:uid, 'oauth', :tok, :name, NOW(), 0)
    ");
    $stmt->execute([
        ':uid'  => $user_id,
        ':tok'  => $access_token,
        ':name' => 'oauth:proxmox'
    ]);
} else {
    // esquema viejo
    $stmt = $pdo->prepare("
        INSERT INTO api_tokens (user_id, token, name, created_at, revoked)
        VALUES (:uid, :tok, :name, NOW(), 0)
    ");
    $stmt->execute([
        ':uid'  => $user_id,
        ':tok'  => $access_token,
        ':name' => 'oauth:proxmox'
    ]);
}

$response = [
    'access_token' => $access_token,
    'token_type' => 'Bearer',
    'expires_in' => 3600
];

// 6) OIDC: si scope incluye openid -> emitir id_token RS256
$scope = $row['scope'] ?? '';
$wantOidc = preg_match('/(^|\s)openid(\s|$)/', $scope) === 1;

if ($wantOidc) {
    $PRIVATE_KEY_PATH = __DIR__ . '/keys/oidc_private.pem';
    $KID = 'jevalid-rs256-1';
    $ISS = 'https://id.jeval.cl';

    if (!file_exists($PRIVATE_KEY_PATH)) {
        json_error(500, 'oidc_private_key_missing');
    }

    // cargar usuario (para claims)
    $u = $pdo->prepare("SELECT id, primer_nombre, correo, email_verificado FROM usuarios WHERE id = :id LIMIT 1");
    $hasEmailVer = dbHasColumn($pdo, 'usuarios', 'email_verificado');
    if (!$hasEmailVer) {
        $u = $pdo->prepare("SELECT id, primer_nombre, correo FROM usuarios WHERE id = :id LIMIT 1");
    }
    $u->execute([':id' => $user_id]);
    $user = $u->fetch(PDO::FETCH_ASSOC);

    $now = time();
    $claims = [
        'iss' => $ISS,
        'aud' => $client_id,
        'sub' => 'user:' . $user_id,
        'iat' => $now,
        'exp' => $now + 3600,
    ];

    if (!empty($row['nonce'])) $claims['nonce'] = $row['nonce'];

    // scopes: email/profile
    if (preg_match('/(^|\s)email(\s|$)/', $scope) === 1 && !empty($user['correo'])) {
        $claims['email'] = $user['correo'];
        if ($hasEmailVer && isset($user['email_verificado'])) {
            $claims['email_verified'] = ((int)$user['email_verificado'] === 1);
        }
    }
    if (preg_match('/(^|\s)profile(\s|$)/', $scope) === 1 && !empty($user['primer_nombre'])) {
        $claims['name'] = $user['primer_nombre'];
    }

    $header = ['alg' => 'RS256', 'typ' => 'JWT', 'kid' => $KID];
    $h = b64url_encode(json_encode($header, JSON_UNESCAPED_SLASHES));
    $p = b64url_encode(json_encode($claims, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    $signingInput = $h . '.' . $p;

    $privPem = file_get_contents($PRIVATE_KEY_PATH);
    $privKey = openssl_pkey_get_private($privPem);
    if (!$privKey) {
        json_error(500, 'oidc_private_key_invalid');
    }

    $sig = '';
    $ok = openssl_sign($signingInput, $sig, $privKey, OPENSSL_ALGO_SHA256);
    openssl_free_key($privKey);

    if (!$ok) json_error(500, 'oidc_sign_failed');

    $jwt = $signingInput . '.' . b64url_encode($sig);

    $response['id_token'] = $jwt;
}

echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);