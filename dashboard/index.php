<?php
// /dashboard/index.php — Dashboard principal de JevalID
session_start();

// Si no hay sesión, mandar al login
if (!isset($_SESSION['user_id'])) {
    header('Location: /login/');
    exit;
}

require __DIR__ . '/../config/db.php';

$userId = (int) $_SESSION['user_id'];

// Cargar datos frescos desde la base de datos
$stmt = $pdo->prepare("
    SELECT id, primer_nombre, correo, rol, cuenta_bloqueada, creado_en
    FROM usuarios
    WHERE id = :id
    LIMIT 1
");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // Si por alguna razón la cuenta ya no existe, forzar logout
    header('Location: /logout/');
    exit;
}

$nombre      = $user['primer_nombre'] ?: $user['correo'];
$correo      = $user['correo'];
$rol         = $user['rol'];
$bloqueada   = (int)$user['cuenta_bloqueada'] === 1;
$creadoEn    = $user['creado_en'] ?? null;

// Roles especiales
$isDeveloper = ($rol === 'developer_api' || $rol === 'admin');
$isAdmin     = ($rol === 'admin');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — JevalID</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
          content="Panel principal de JevalID. Gestiona tu cuenta, seguridad e integraciones con la infraestructura Jeval.">
    <link href="https://id.jeval.cl/library/boostrap/bootstrap/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: radial-gradient(circle at top, #1b2436 0, #05060a 55%);
            color: #e5e5e5;
            min-height: 100vh;
        }
        .navbar-jeval {
            background: rgba(5, 6, 10, 0.95);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }
        .dashboard-container {
            max-width: 1150px;
        }
        .hero-card {
            background: rgba(5, 6, 10, 0.9);
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: 0 18px 60px rgba(0, 0, 0, 0.75);
        }
        .hero-title {
            font-size: 1.9rem;
            font-weight: 700;
        }
        .hero-subtitle {
            font-size: 0.96rem;
        }
        .tile-card {
            background: #11151c;
            border-radius: .9rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 1.25rem;
            height: 100%;
        }
        .tile-title {
            font-size: 0.95rem;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: #9fa6b2;
        }
        .tile-main {
            font-size: 1.05rem;
        }
        .badge-role {
            font-size: 0.75rem;
        }
        .btn-outline-soft {
            border-color: rgba(255, 255, 255, 0.25);
        }
        .btn-outline-soft:hover {
            background-color: rgba(255, 255, 255, 0.06);
        }
        a {
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-jeval navbar-expand-lg navbar-dark">
    <div class="container dashboard-container">
        <a class="navbar-brand" href="/">
            JevalID
        </a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="small text-muted d-none d-md-inline">
                <?= htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') ?>
                <?php if ($rol): ?>
                    · <span class="badge bg-secondary badge-role"><?= htmlspecialchars($rol, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </span>
            <a href="/logout/" class="btn btn-sm btn-outline-light">
                Cerrar sesión
            </a>
        </div>
    </div>
</nav>

<main class="container dashboard-container py-4 py-md-5">

    <!-- Hero -->
    <section class="mb-4">
        <div class="hero-card p-4 p-md-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h1 class="hero-title mb-1">
                        Bienvenido, <?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>
                    </h1>
                    <p class="hero-subtitle text-muted mb-0">
                        Este es tu panel de JevalID. Desde aquí podrás gestionar tu cuenta, seguridad e integraciones con el ecosistema Jeval.
                    </p>
                </div>
                <div class="text-md-end">
                    <span class="badge bg-secondary badge-role d-block mb-2">
                        ID interno: #<?= (int)$user['id'] ?>
                    </span>
                    <?php if ($bloqueada): ?>
                        <span class="badge bg-danger">Cuenta bloqueada</span>
                    <?php else: ?>
                        <span class="badge bg-success">Cuenta activa</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Tiles -->
    <section class="mb-4">
        <div class="row g-3">

            <!-- Información de cuenta -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="tile-card">
                    <div class="tile-title mb-2">Cuenta JevalID</div>
                    <div class="tile-main mb-2">
                        <strong><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></strong><br>
                        <span class="text-muted small"><?= htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <ul class="small text-muted mb-3">
                        <li>Rol: <strong><?= htmlspecialchars($rol, ENT_QUOTES, 'UTF-8') ?></strong></li>
                        <?php if ($creadoEn): ?>
                            <li>Creada: <?= htmlspecialchars($creadoEn, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endif; ?>
                        <li>Estado: <?= $bloqueada ? 'Bloqueada' : 'Activa' ?></li>
                    </ul>
                    <a href="/dashboard/account.php" class="btn btn-sm btn-outline-soft w-100">
                        Configurar cuenta (próximamente)
                    </a>
                </div>
            </div>

            <!-- Seguridad -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="tile-card">
                    <div class="tile-title mb-2">Seguridad</div>
                    <p class="tile-main mb-2">
                        Gestiona tu contraseña, sesiones activas y revisa actividad reciente asociada a tu cuenta.
                    </p>
                    <ul class="small text-muted mb-3">
                        <li>No compartas tu contraseña ni tokens.</li>
                        <li>Evita reutilizar contraseñas de otros sitios.</li>
                    </ul>
                    <a href="/dashboard/security.php" class="btn btn-sm btn-outline-soft w-100">
                        Revisar opciones de seguridad
                    </a>
                </div>
            </div>

            <!-- APIs y OAuth (solo developer/admin) -->
            <?php if ($isDeveloper): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="tile-card border border-primary">
                    <div class="tile-title mb-2 text-primary">APIs y OAuth</div>
                    <p class="tile-main mb-2">
                        Crea aplicaciones OAuth, gestiona tus tokens de API y consulta la guía de integración con la infraestructura Jeval.
                    </p>
                    <ul class="small text-muted mb-3">
                        <li>Uso exclusivo para desarrollo.</li>
                        <li>El mal uso puede provocar suspensión de accesos.</li>
                    </ul>
                    <a href="/dashboard/developer.php" class="btn btn-sm btn-primary w-100">
                        Abrir dashboard de APIs
                    </a>
                </div>
            </div>
            <?php else: ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="tile-card">
                    <div class="tile-title mb-2">APIs y OAuth</div>
                    <p class="tile-main mb-2">
                        Para usar JevalID en tus juegos o servicios necesitas una cuenta con rol developer.
                    </p>
                    <ul class="small text-muted mb-3">
                        <li>Las APIs no son un entorno de pruebas sin límite.</li>
                        <li>Requieren responsabilidad y uso correcto.</li>
                    </ul>
                    <a href="/dashboard/request-developer.php" class="btn btn-sm btn-outline-soft w-100">
                        Solicitar acceso developer (próximamente)
                    </a>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </section>

    <!-- Acciones rápidas abajo -->
    <section class="mt-3">
        <div class="d-flex flex-column flex-md-row gap-3">
            <a href="/eula.php" class="btn btn-sm btn-outline-soft flex-fill">
                Ver términos de uso de JevalID
            </a>
            <a href="/oauth/" class="btn btn-sm btn-outline-soft flex-fill">
                Guía técnica de OAuth / APIs
            </a>
            <a href="/logout/" class="btn btn-sm btn-outline-light flex-fill">
                Cerrar sesión
            </a>
        </div>
    </section>

</main>
</body>
</html>
