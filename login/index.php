<?php
session_start();

// Leer mensaje de error y redirect (si viene de OAuth)
$error    = $_GET['error'] ?? '';
$redirect = $_GET['redirect'] ?? '';

// Solo redirigir al dashboard si YA estás logueado
// y no vienes desde OAuth (sin redirect) ni con error.
if (isset($_SESSION['user_id']) && $redirect === '' && $error === '') {
    header("Location: /dashboard/");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión — Jeval ID</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
<div class="container py-5" style="max-width: 420px;">
    <h1 class="mb-4 text-center">Iniciar sesión</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="post" action="/auth/login.php" autocomplete="off">
        <div class="mb-3">
            <input type="email" name="correo" class="form-control"
                   placeholder="Correo electrónico" required>
        </div>
        <div class="mb-3">
            <input type="password" name="password" class="form-control"
                   placeholder="Contraseña" required>
        </div>

        <!-- Muy importante: esto permite volver a /oauth/authorize.php después del login -->
        <input type="hidden" name="redirect"
               value="<?= htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8') ?>">

        <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>

    <p class="text-center mt-3">
        ¿No tienes cuenta? <a href="/register/">Regístrate</a>
    </p>
</div>
</body>
</html>
