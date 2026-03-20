


<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$token = '271b54892db481d715c3a5b89e581c0c2cd0de12f66b5565fc83f9de5673326b';

$ch = curl_init('https://id.jeval.cl/api/userinfo.php');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
    ],
]);

$response = curl_exec($ch);
$error    = curl_error($ch);
curl_close($ch);

header('Content-Type: text/plain; charset=utf-8');

if ($error) {
    echo "Error cURL: " . $error . "\n";
} else {
    echo "Respuesta de la API:\n";
    echo $response . "\n";
}
