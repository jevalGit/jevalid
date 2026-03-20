<?php
// auth/login.php
session_start();

require __DIR__ . '/../config/db.php';

// Opcional para debug
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

$correo    = $_POST['correo']   ?? '';
$password  = $_POST['password'] ?? '';
$redirect  = $_POST['redirect'] ?? '';

$correo   = trim($correo);
$password = trim($password);

// Función para volver al login con mensaje de error (y conservar redirect)
function volver_login($mensaje, $redirect = '')
{
    $params = ['error' => $mensaje];
    if ($redirect !== '') {
        $params['redirect'] = $redirect;
    }

    header('Location: /login/?' . http_build_query($params));
    exit;
}

// Validación básica
if ($correo === '' || $password === '') {
    volver_login('Debes completar todos los campos.', $redirect);
}

try {
    // Buscar usuario por correo
    $stmt = $pdo->prepare("
        SELECT id, primer_nombre, correo, password_hash, rol, cuenta_bloqueada
        FROM usuarios
        WHERE correo = :correo
        LIMIT 1
    ");
    $stmt->execute([':correo' => $correo]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        volver_login('Correo o contraseña incorrectos.', $redirect);
    }

    if ((int)$user['cuenta_bloqueada'] === 1) {
        volver_login('Tu cuenta está bloqueada.', $redirect);
    }

    if (!password_verify($password, $user['password_hash'])) {
        volver_login('Correo o contraseña incorrectos.', $redirect);
    }

    // Login OK -> guardar datos en sesión
    $_SESSION['user_id']       = (int)$user['id'];
    $_SESSION['rol']           = $user['rol'];
    $_SESSION['primer_nombre'] = $user['primer_nombre'];
    $_SESSION['correo']        = $user['correo'];

    // Si venimos de OAuth, volver a authorize.php
    if ($redirect !== '') {
        header('Location: ' . $redirect);   // ej: /oauth/authorize.php?client_id=...
    } else {
        header('Location: /dashboard/');   // login normal
    }
    exit;

} catch (PDOException $e) {
    // Error interno de BD/servidor
    volver_login('Error interno al iniciar sesión.', $redirect);
}
