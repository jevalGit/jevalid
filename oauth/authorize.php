<?php
session_start();

require __DIR__ . '/../config/db.php';

header('Content-Type: text/html; charset=utf-8');

function errorRedirect($redirectUri, $message, $state = null)
{
    if (!$redirectUri) {
        http_response_code(400);
        echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        exit;
    }

    $params = ['error' => $message];
    if ($state !== null && $state !== '') $params['state'] = $state;

    $sep = (strpos($redirectUri, '?') === false) ? '?' : '&';
    header('Location: ' . $redirectUri . $sep . http_build_query($params));
    exit;
}

function dbHasColumn(PDO $pdo, string $table, string $column): bool
{
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

$responseType = trim($_GET['response_type'] ?? '');
$clientId     = trim($_GET['client_id'] ?? '');
$redirectUri  = trim($_GET['redirect_uri'] ?? '');
$state        = trim($_GET['state'] ?? '');
$scope        = trim($_GET['scope'] ?? '');   // proxmox manda "openid email"
$nonce        = trim($_GET['nonce'] ?? '');   // proxmox manda nonce
$codeChallenge = trim($_GET['code_challenge'] ?? '');
$codeChallengeMethod = trim($_GET['code_challenge_method'] ?? '');

if ($responseType !== 'code') {
    http_response_code(400);
    echo "response_type inválido";
    exit;
}
if ($clientId === '' || $redirectUri === '') {
    http_response_code(400);
    echo "Faltan parámetros: client_id o redirect_uri";
    exit;
}

if ($codeChallenge !== '') {
    if ($codeChallengeMethod === '') $codeChallengeMethod = 'S256';
    if (!in_array($codeChallengeMethod, ['S256', 'plain'], true)) {
        errorRedirect($redirectUri, 'code_challenge_method inválido', $state);
    }
}

// 1) Validar cliente
$stmt = $pdo->prepare("
    SELECT client_id, name, redirect_uri, revoked
    FROM oauth_clients
    WHERE client_id = :cid
    LIMIT 1
");
$stmt->execute([':cid' => $clientId]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    http_response_code(400);
    echo "client_id inválido";
    exit;
}
if (!empty($client['revoked'])) {
    errorRedirect($redirectUri, 'client_revoked', $state);
}
if ($client['redirect_uri'] !== $redirectUri) {
    // match exacto: Proxmox lo exige y tú también
    errorRedirect($redirectUri, 'redirect_uri no coincide con el registrado', $state);
}

// 2) Si no está logueado, mandar a login y luego volver
if (!isset($_SESSION['user_id'])) {
    $returnTo = '/oauth/authorize.php?' . http_build_query($_GET);
    header('Location: /login/?redirect=' . urlencode($returnTo));
    exit;
}

// 3) Usuario autenticado -> generar code y guardar
$userId = (int)$_SESSION['user_id'];
$code   = bin2hex(random_bytes(32));

// Insert dinámico según columnas disponibles
$cols = ['code', 'user_id', 'client_id', 'redirect_uri', 'expires_at'];
$vals = [':code', ':user_id', ':client_id', ':redirect_uri', "DATE_ADD(NOW(), INTERVAL 15 MINUTE)"];

$params = [
    ':code'        => $code,
    ':user_id'     => $userId,
    ':client_id'   => $clientId,
    ':redirect_uri'=> $redirectUri
];

if (dbHasColumn($pdo, 'oauth_codes', 'scope')) {
    $cols[] = 'scope';
    $vals[] = ':scope';
    $params[':scope'] = $scope;
}
if (dbHasColumn($pdo, 'oauth_codes', 'nonce')) {
    $cols[] = 'nonce';
    $vals[] = ':nonce';
    $params[':nonce'] = $nonce;
}
if (dbHasColumn($pdo, 'oauth_codes', 'code_challenge')) {
    $cols[] = 'code_challenge';
    $vals[] = ':cc';
    $params[':cc'] = ($codeChallenge !== '' ? $codeChallenge : null);
}
if (dbHasColumn($pdo, 'oauth_codes', 'code_challenge_method')) {
    $cols[] = 'code_challenge_method';
    $vals[] = ':ccm';
    $params[':ccm'] = ($codeChallenge !== '' ? $codeChallengeMethod : null);
}

$sql = "INSERT INTO oauth_codes (" . implode(', ', $cols) . ")
        VALUES (" . implode(', ', $vals) . ")";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

// 4) Redirigir a la app
$q = ['code' => $code];
if ($state !== '') $q['state'] = $state;

$sep = (strpos($redirectUri, '?') === false) ? '?' : '&';
header('Location: ' . $redirectUri . $sep . http_build_query($q));
exit;