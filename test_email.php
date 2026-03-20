<?php
// test_email.php (modo debug)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Inicio test_email.php<br>";

require_once __DIR__ . '/config/email-connect.php';

echo "email-connect cargado<br>";

if (!function_exists('email_send')) {
    echo "La función email_send() NO existe<br>";
    exit;
}

$destino = 'non@non.com'; // cámbialo
$nombre  = 'Prueba Jeval';
$asunto  = 'Prueba SMTP Jeval ID';
$html    = '<p>Si ves este correo, el servidor SMTP de Jeval ID funciona ✅</p>';

echo "Llamando a email_send()...<br>";

if (email_send($destino, $nombre, $asunto, $html)) {
    echo 'OK: correo enviado, revisa tu bandeja (y spam).';
} else {
    echo 'ERROR: no se pudo enviar el correo, revisa logs y config SMTP.';
}
