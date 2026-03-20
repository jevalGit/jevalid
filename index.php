<?php
// index.php — Página principal de JevalID
session_start();

// Si ya hay sesión abierta, mandar al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>JevalID — Inicio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
          content="JevalID es el sistema de cuentas e identidad de la infraestructura Jeval / JevalNetwork. Inicia sesión o crea tu cuenta.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: radial-gradient(circle at top, #1b2436 0, #05060a 55%);
            color: #e5e5e5;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .hero-card {
            background: rgba(5, 6, 10, 0.9);
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: 0 18px 60px rgba(0, 0, 0, 0.75);
        }
        .hero-title {
            font-size: 2.1rem;
            font-weight: 700;
        }
        .hero-subtitle {
            font-size: 0.95rem;
        }
        .btn-login {
            font-size: 1rem;
            padding: 0.75rem 1.5rem;
        }
        .btn-register {
            font-size: 1rem;
            padding: 0.75rem 1.5rem;
        }
        .badge-env {
            font-size: 0.7rem;
            letter-spacing: .06em;
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
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-9 col-lg-7">
            <div class="hero-card p-4 p-md-5">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h1 class="hero-title mb-1">JevalID</h1>
                        <p class="text-muted mb-0 hero-subtitle">
                            Sistema de cuentas e identidad de la infraestructura Jeval / JevalNetwork.
                        </p>
                    </div>
                    <span class="badge bg-secondary badge-env">
                        ID.JEVAL.CL
                    </span>
                </div>

                <hr class="border-secondary mb-4">

                <p class="mb-4">
                    Usa tu cuenta JevalID para acceder a juegos, paneles, APIs y servicios conectados al
                    ecosistema Jeval. Si ya tienes una cuenta, simplemente inicia sesión. Si no, puedes crearla
                    en segundos.
                </p>

                <div class="d-flex flex-column flex-md-row gap-3 mb-4">
                    <a href="/login/" class="btn btn-primary btn-login flex-fill">
                        Iniciar sesión
                    </a>
                    <a href="/register/" class="btn btn-outline-light btn-register flex-fill">
                        Crear cuenta
                    </a>
                </div>

                <p class="text-muted small mb-1">
                    Al continuar aceptas los
                    <a href="/eula" class="link-light">términos de uso de JevalID</a>.
                </p>
                <p class="text-muted small mb-0">
                    ¿Necesitas integrar JevalID en tu app o juego?
                    <a href="/api/tutorial" class="link-light">Consulta la guía de OAuth y APIs</a>.
                </p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
