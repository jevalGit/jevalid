<?php
header('Content-Type: application/json; charset=utf-8');

$PUBLIC_KEY_PATH = __DIR__ . '/keys/oidc_public.pem';
$KID = 'jevalid-rs256-1';

function b64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

if (!file_exists($PUBLIC_KEY_PATH)) {
    http_response_code(500);
    echo json_encode(['error' => 'oidc_public_key_missing', 'detail' => 'Falta ' . $PUBLIC_KEY_PATH]);
    exit;
}

$pubPem = file_get_contents($PUBLIC_KEY_PATH);
$pubKey = openssl_pkey_get_public($pubPem);
if (!$pubKey) {
    http_response_code(500);
    echo json_encode(['error' => 'oidc_public_key_invalid']);
    exit;
}

$details = openssl_pkey_get_details($pubKey);
openssl_free_key($pubKey);

if (!isset($details['rsa']['n'], $details['rsa']['e'])) {
    http_response_code(500);
    echo json_encode(['error' => 'oidc_public_key_not_rsa']);
    exit;
}

$n = b64url_encode($details['rsa']['n']);
$e = b64url_encode($details['rsa']['e']);

echo json_encode([
  'keys' => [[
    'kty' => 'RSA',
    'use' => 'sig',
    'alg' => 'RS256',
    'kid' => $KID,
    'n'   => $n,
    'e'   => $e
  ]]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
