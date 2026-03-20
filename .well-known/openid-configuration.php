<?php
header('Content-Type: application/json; charset=utf-8');

$issuer = 'https://id.jeval.cl';

echo json_encode([
  'issuer' => $issuer,
  'authorization_endpoint' => $issuer . '/oauth/authorize.php',
  'token_endpoint' => $issuer . '/oauth/token.php',
  'userinfo_endpoint' => $issuer . '/api/userinfo.php',
  'jwks_uri' => $issuer . '/oauth/jwks.json',
  'response_types_supported' => ['code'],
  'subject_types_supported' => ['public'],
  'id_token_signing_alg_values_supported' => ['RS256'],
  'scopes_supported' => ['openid', 'profile', 'email'],
  'claims_supported' => ['iss','aud','sub','exp','iat','nonce','email','email_verified','name'],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
