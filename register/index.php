<?php
session_start();

// Si ya está logueado, lo mandamos al dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: /dashboard/");
    exit;
}

// Modo desarrollo: muestra errores (luego lo puedes comentar)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conexión a la base de datos
require __DIR__ . '/../config/db.php'; // cambia la ruta si tu db.php está en otro sitio

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $primer_nombre = trim($_POST['primer_nombre'] ?? '');
    $correo        = trim($_POST['correo'] ?? '');
    $password      = $_POST['password'] ?? '';

    // Validaciones básicas
    if ($primer_nombre === '' || strlen($primer_nombre) < 2) {
        $errors[] = "El nombre debe tener al menos 2 caracteres.";
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El correo electrónico no es válido.";
    }

    if (strlen($password) < 8) {
        $errors[] = "La contraseña debe tener al menos 8 caracteres.";
    }

    if (empty($errors)) {
        try {
            // Comprobar si ya existe el correo
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = :correo LIMIT 1");
            $stmt->execute([':correo' => $correo]);

            if ($stmt->fetch()) {
                $errors[] = "Este correo ya está registrado.";
            } else {
                // Crear usuario
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $rol = 'user'; // todos los registros públicos serán usuarios normales

                $insert = $pdo->prepare("
                    INSERT INTO usuarios (primer_nombre, correo, password_hash, rol)
                    VALUES (:primer_nombre, :correo, :password_hash, :rol)
                ");

                $insert->execute([
                    ':primer_nombre' => $primer_nombre,
                    ':correo'        => $correo,
                    ':password_hash' => $password_hash,
                    ':rol'           => $rol
                ]);

                $user_id = $pdo->lastInsertId();

                // Iniciar sesión
                $_SESSION['user_id']       = $user_id;
                $_SESSION['primer_nombre'] = $primer_nombre;
                $_SESSION['rol']           = $rol;

                header("Location: /dashboard/");
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = "Error interno al registrar: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registrarse — Jeval ID</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/assets/jeval.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
  <div class="container py-5" style="max-width: 420px;">
    <h1 class="mb-4 text-center">Crear cuenta Jeval ID</h1>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <div class="mb-3">
        <input type="text" name="primer_nombre" class="form-control"
               placeholder="Nombre"
               value="<?= isset($primer_nombre) ? htmlspecialchars($primer_nombre, ENT_QUOTES, 'UTF-8') : '' ?>"
               required>
      </div>
      <div class="mb-3">
        <input type="email" name="correo" class="form-control"
               placeholder="Correo electrónico"
               value="<?= isset($correo) ? htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') : '' ?>"
               required>
      </div>
      <div class="mb-3">
        <input type="password" name="password" class="form-control"
               placeholder="Contraseña (mínimo 8 caracteres)"
               required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Registrarse</button>
    </form>

    <p class="text-center mt-3">
      ¿Ya tienes cuenta? <a href="/login/" class="text-info">Inicia sesión</a>
    </p>
  </div>
</body>
</html>
