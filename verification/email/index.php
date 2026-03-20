<?php
// verification/email/index.php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/email_verification_config.php';

// ---------------------------------------------------------------------
// 1) Obtener token de la URL
// ---------------------------------------------------------------------
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $status = 'error';
    $message = 'Token de verificación faltante o inválido.';
    render_page($status, $message);
    exit;
}

// ---------------------------------------------------------------------
// 2) Verificar que tengamos conexión PDO ($pdo)
// ---------------------------------------------------------------------
if (!isset($pdo) || !($pdo instanceof PDO)) {
    // Si en tu db.php tienes una función tipo get_pdo(), puedes hacer:
    // $pdo = get_pdo();
    render_page('error', 'Error interno de conexión a la base de datos.');
    error_log('verification/email/index.php: $pdo no está definido o no es PDO.');
    exit;
}

try {
    // -----------------------------------------------------------------
    // 3) Buscar el token
    // -----------------------------------------------------------------
    $stmt = $pdo->prepare("
        SELECT
            evt.id,
            evt.user_id,
            evt.expires_at,
            evt.used,
            u.email_verificado
        FROM email_verification_tokens AS evt
        INNER JOIN usuarios AS u ON u.id = evt.user_id
        WHERE evt.token = :token
        LIMIT 1
    ");
    $stmt->execute([':token' => $token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $status = 'error';
        $message = 'El enlace de verificación no es válido o ya fue utilizado.';
        render_page($status, $message);
        exit;
    }

    // -----------------------------------------------------------------
    // 4) Validar estado del token
    // -----------------------------------------------------------------
    if ((int)$row['used'] === 1) {
        $status = 'warning';
        $message = 'Este enlace de verificación ya fue utilizado anteriormente.';
        render_page($status, $message);
        exit;
    }

    $now       = new DateTimeImmutable('now');
    $expiresAt = new DateTimeImmutable($row['expires_at']);

    if ($now > $expiresAt) {
        $status = 'error';
        $message = 'El enlace de verificación ha expirado. Solicita un nuevo correo de verificación.';
        render_page($status, $message);
        exit;
    }

    // -----------------------------------------------------------------
    // 5) Si todo ok, marcar email como verificado y token usado
    // -----------------------------------------------------------------
    $pdo->beginTransaction();

    // Marcar al usuario como verificado
    if ((int)$row['email_verificado'] === 0) {
        $stmtUser = $pdo->prepare("
            UPDATE usuarios
            SET email_verificado = 1
            WHERE id = :user_id
        ");
        $stmtUser->execute([':user_id' => $row['user_id']]);
    }

    // Marcar token como usado
    $stmtToken = $pdo->prepare("
        UPDATE email_verification_tokens
        SET used = 1
        WHERE id = :id
    ");
    $stmtToken->execute([':id' => $row['id']]);

    $pdo->commit();

    $status  = 'success';
    $message = '¡Correo verificado correctamente! Ya puedes usar todas las funciones de tu cuenta.';
    render_page($status, $message);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Error en verificación de correo: ' . $e->getMessage());

    $status  = 'error';
    $message = 'Ha ocurrido un error al verificar tu correo. Inténtalo más tarde.';
    render_page($status, $message);
    exit;
}

// ---------------------------------------------------------------------
// 6) Función simple para pintar la respuesta
// ---------------------------------------------------------------------
function render_page(string $status, string $message): void
{
    // Clases CSS de ejemplo según estado
    $class = [
        'success' => 'alert-success',
        'error'   => 'alert-danger',
        'warning' => 'alert-warning',
    ][$status] ?? 'alert-info';

    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Verificación de correo</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body {
                background-color: #0f172a;
                color: #e5e7eb;
                font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                height: 100vh;
                margin: 0;
            }
            .card {
                background-color: #020617;
                padding: 2rem;
                border-radius: 12px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.7);
                max-width: 420px;
                width: 100%;
            }
            .alert-success { color: #4ade80; }
            .alert-danger  { color: #f87171; }
            .alert-warning { color: #facc15; }
            .alert-info    { color: #38bdf8; }
            a {
                color: #38bdf8;
                text-decoration: none;
            }
            a:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
    <div class="card">
        <h1>Verificación de correo</h1>
        <p class="<?php echo htmlspecialchars($class, ENT_QUOTES, 'UTF-8'); ?>">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </p>
        <p>
            <a href="/login">Ir al inicio de sesión</a>
        </p>
    </div>
    </body>
    </html>
    <?php
}
