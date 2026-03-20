<?php
session_start();

require __DIR__ . '/../../config/db.php';
header('Content-Type: text/html; charset=utf-8');

/**
 * Developer Dashboard (JevalID)
 * - Tokens API (Bearer): crear, revocar y borrar (solo si ya está revocado)
 * - Apps OAuth: crear, revelar/ocultar Client Secret, revocar, borrar (solo si ya está revocada)
 *
 * Nota: para revocar/borrar apps OAuth de forma correcta, la tabla oauth_clients debería tener:
 *   revoked TINYINT(1) NOT NULL DEFAULT 0
 *   revoked_at DATETIME NULL
 */

// ---------------- Helpers ----------------
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function hasColumn(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE :col");
    $stmt->execute([':col' => $column]);
    return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
}

function mask_secret(string $s): string {
    $len = strlen($s);
    if ($len <= 12) return str_repeat('*', $len);
    return substr($s, 0, 6) . str_repeat('*', $len - 12) . substr($s, -6);
}

// ---------------- Auth ----------------
if (!isset($_SESSION['user_id'])) {
    header('Location: /login/');
    exit;
}

$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, primer_nombre, correo, rol, cuenta_bloqueada FROM usuarios WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $userId]);
$me = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$me) {
    header('Location: /logout/');
    exit;
}

if ((int)$me['cuenta_bloqueada'] === 1) {
    http_response_code(403);
    echo "Cuenta bloqueada. No puedes usar el panel.";
    exit;
}

$rol = $me['rol'] ?? 'user';
$_SESSION['rol'] = $rol;

if (!in_array($rol, ['developer_api', 'admin'], true)) {
    http_response_code(403);
    echo "No tienes permisos para acceder a esta sección.";
    exit;
}

// ---------------- CSRF ----------------
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

// ---------------- Schema detection ----------------
$oauthHasRevoked   = hasColumn($pdo, 'oauth_clients', 'revoked');
$oauthHasRevokedAt = hasColumn($pdo, 'oauth_clients', 'revoked_at');

$errors = [];
$messages = [];

// ---------------- POST actions ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedCsrf = $_POST['csrf_token'] ?? '';
    if (!hash_equals($csrf, $postedCsrf)) {
        $errors[] = "CSRF inválido. Recarga la página e intenta de nuevo.";
    } else {
        $action = $_POST['action'] ?? '';

        // ===== TOKENS API (Bearer) =====
        if ($action === 'create_token') {
            $name = trim($_POST['name'] ?? '');
            if ($name === '') {
                $errors[] = "El nombre del token es obligatorio.";
            } else {
                try {
                    $token = bin2hex(random_bytes(32)); // 64 hex
                    $stmt = $pdo->prepare("INSERT INTO api_tokens (user_id, token, name) VALUES (:uid, :tok, :name)");
                    $stmt->execute([':uid' => $userId, ':tok' => $token, ':name' => $name]);

                    $messages[] = [
                        'title' => 'Token creado',
                        'body'  => "Guárdalo ahora. Trátalo como contraseña.",
                        'value_label' => 'Token (Bearer)',
                        'value' => $token
                    ];
                } catch (PDOException $e) {
                    $errors[] = "Error al crear token: " . h($e->getMessage());
                }
            }
        }

        if ($action === 'revoke_token') {
            $tokenId = (int)($_POST['token_id'] ?? 0);
            if ($tokenId <= 0) {
                $errors[] = "Token inválido.";
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE api_tokens SET revoked = 1 WHERE id = :id AND user_id = :uid");
                    $stmt->execute([':id' => $tokenId, ':uid' => $userId]);
                    if ($stmt->rowCount() > 0) $messages[] = ['simple' => "Token revocado."];
                    else $errors[] = "No se encontró el token o no te pertenece.";
                } catch (PDOException $e) {
                    $errors[] = "Error al revocar token: " . h($e->getMessage());
                }
            }
        }

        if ($action === 'delete_token') {
            $tokenId = (int)($_POST['token_id'] ?? 0);
            if ($tokenId <= 0) {
                $errors[] = "Token inválido.";
            } else {
                try {
                    $stmt = $pdo->prepare("DELETE FROM api_tokens WHERE id = :id AND user_id = :uid AND revoked = 1");
                    $stmt->execute([':id' => $tokenId, ':uid' => $userId]);
                    if ($stmt->rowCount() > 0) $messages[] = ['simple' => "Token borrado."];
                    else $errors[] = "Solo puedes borrar tokens que ya estén revocados (o no te pertenece).";
                } catch (PDOException $e) {
                    $errors[] = "Error al borrar token: " . h($e->getMessage());
                }
            }
        }

        // ===== OAUTH APPS =====
        if ($action === 'create_oauth_client') {
            $appName = trim($_POST['app_name'] ?? '');
            $redirectUri = trim($_POST['redirect_uri'] ?? '');

            if ($appName === '' || $redirectUri === '') {
                $errors[] = "Nombre y Redirect URI son obligatorios.";
            } elseif (!filter_var($redirectUri, FILTER_VALIDATE_URL)) {
                $errors[] = "Redirect URI no es una URL válida.";
            } else {
                try {
                    $clientId = bin2hex(random_bytes(16));     // 32
                    $clientSecret = bin2hex(random_bytes(32)); // 64

                    if ($oauthHasRevoked && $oauthHasRevokedAt) {
                        $stmt = $pdo->prepare("
                            INSERT INTO oauth_clients (client_id, client_secret, name, redirect_uri, owner_user_id, revoked, revoked_at)
                            VALUES (:cid, :sec, :name, :uri, :uid, 0, NULL)
                        ");
                    } elseif ($oauthHasRevoked) {
                        $stmt = $pdo->prepare("
                            INSERT INTO oauth_clients (client_id, client_secret, name, redirect_uri, owner_user_id, revoked)
                            VALUES (:cid, :sec, :name, :uri, :uid, 0)
                        ");
                    } else {
                        $stmt = $pdo->prepare("
                            INSERT INTO oauth_clients (client_id, client_secret, name, redirect_uri, owner_user_id)
                            VALUES (:cid, :sec, :name, :uri, :uid)
                        ");
                    }

                    $stmt->execute([
                        ':cid'  => $clientId,
                        ':sec'  => $clientSecret,
                        ':name' => $appName,
                        ':uri'  => $redirectUri,
                        ':uid'  => $userId
                    ]);

                    $messages[] = [
                        'title' => 'Aplicación OAuth creada',
                        'body'  => "Guárdalo ahora. El <b>Client Secret</b> no debería compartirse nunca.",
                        'value_label' => 'Client ID',
                        'value' => $clientId,
                        'value2_label' => 'Client Secret',
                        'value2' => $clientSecret
                    ];
                } catch (PDOException $e) {
                    $errors[] = "Error al crear app OAuth: " . h($e->getMessage());
                }
            }
        }

        if ($action === 'revoke_oauth_client') {
            $oauthId = (int)($_POST['oauth_id'] ?? 0);
            if ($oauthId <= 0) {
                $errors[] = "App OAuth inválida.";
            } elseif (!$oauthHasRevoked) {
                $errors[] = "Te falta el campo <b>revoked</b> en <b>oauth_clients</b>. (Recomendado: agregar revoked + revoked_at)";
            } else {
                try {
                    if ($oauthHasRevokedAt) {
                        $stmt = $pdo->prepare("UPDATE oauth_clients SET revoked = 1, revoked_at = NOW() WHERE id = :id AND owner_user_id = :uid");
                    } else {
                        $stmt = $pdo->prepare("UPDATE oauth_clients SET revoked = 1 WHERE id = :id AND owner_user_id = :uid");
                    }
                    $stmt->execute([':id' => $oauthId, ':uid' => $userId]);

                    if ($stmt->rowCount() > 0) $messages[] = ['simple' => "Aplicación OAuth revocada."];
                    else $errors[] = "No existe esa app o no te pertenece.";
                } catch (PDOException $e) {
                    $errors[] = "Error al revocar app OAuth: " . h($e->getMessage());
                }
            }
        }

        if ($action === 'delete_oauth_client') {
            $oauthId = (int)($_POST['oauth_id'] ?? 0);
            if ($oauthId <= 0) {
                $errors[] = "App OAuth inválida.";
            } elseif (!$oauthHasRevoked) {
                $errors[] = "Para borrar con seguridad, primero agrega la columna <b>revoked</b> y revoca la app.";
            } else {
                try {
                    $stmt = $pdo->prepare("DELETE FROM oauth_clients WHERE id = :id AND owner_user_id = :uid AND revoked = 1");
                    $stmt->execute([':id' => $oauthId, ':uid' => $userId]);

                    if ($stmt->rowCount() > 0) $messages[] = ['simple' => "Aplicación OAuth borrada."];
                    else $errors[] = "Solo puedes borrar apps OAuth que ya estén revocadas (o no te pertenece).";
                } catch (PDOException $e) {
                    $errors[] = "Error al borrar app OAuth: " . h($e->getMessage());
                }
            }
        }
    }
}

// ---------------- Load lists ----------------
$stmt = $pdo->prepare("SELECT id, name, token, created_at, last_used_at, revoked FROM api_tokens WHERE user_id = :uid ORDER BY id DESC");
$stmt->execute([':uid' => $userId]);
$tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT id, client_id, client_secret, name, redirect_uri, created_at" . ($oauthHasRevoked ? ", revoked" : "") . ($oauthHasRevokedAt ? ", revoked_at" : "") . " FROM oauth_clients WHERE owner_user_id = :uid ORDER BY id DESC");
$stmt->execute([':uid' => $userId]);
$oauthApps = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Developer Dashboard — JevalID</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root{
            --bg0:#0b0f14;
            --bg1:#0f1720;
            --txt:#e6f0ff;
            --muted:#9bb0c9;
            --acc:#00d4ff;
            --acc2:#ff2bd6;
            --good:#2ad97d;
            --bad:#ff4d6d;
            --warn:#ffc857;
        }

        body{
            min-height: 100vh;
            color: var(--txt);
            background:
                radial-gradient(1200px 600px at 10% 10%, rgba(0,212,255,.18), transparent 60%),
                radial-gradient(900px 500px at 90% 10%, rgba(255,43,214,.14), transparent 60%),
                radial-gradient(900px 600px at 50% 110%, rgba(42,217,125,.10), transparent 60%),
                linear-gradient(180deg, var(--bg0), var(--bg1));
        }

        .metro-topbar{
            border-bottom: 1px solid rgba(255,255,255,.08);
            background: rgba(0,0,0,.25);
            backdrop-filter: blur(10px);
        }

        .brand{
            font-weight: 800;
            letter-spacing:.3px;
            font-size: 34px;
            line-height: 1.1;
        }
        .brand .accent{ color: var(--acc); text-shadow: 0 0 16px rgba(0,212,255,.25); }
        .brand .accent2{ color: var(--acc2); text-shadow: 0 0 16px rgba(255,43,214,.20); }

        .metro-pill{
            display: inline-flex;
            gap: 8px;
            align-items: center;
            padding: 6px 10px;
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 999px;
            color: var(--muted);
            font-size: 13px;
            background: rgba(0,0,0,.18);
        }

        .metro-card{
            background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
            border: 1px solid rgba(255,255,255,.10);
            box-shadow:
                0 18px 50px rgba(0,0,0,.45),
                0 0 0 1px rgba(0,212,255,.06) inset;
            border-radius: 18px;
        }

        .metro-card .card-header{
            background: rgba(0,0,0,.20);
            border-bottom: 1px solid rgba(255,255,255,.08);
            border-top-left-radius: 18px;
            border-top-right-radius: 18px;
        }

        .metro-title{ font-weight: 700; letter-spacing: .2px; }
        .mono{ font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }

        /* Fix del "se corta": en tabla va truncado (masked) + reveal lo muestra con wrap */
        .secret-wrap{ white-space: normal !important; overflow-wrap: anywhere !important; word-break: break-word !important; }
        .secret-masked{
            max-width: 420px;
            display: inline-block;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: bottom;
            white-space: nowrap;
        }

        .btn-metro{
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,.14);
            background: rgba(0,0,0,.22);
            color: var(--txt);
        }
        .btn-metro:hover{
            border-color: rgba(0,212,255,.45);
            box-shadow: 0 0 0 2px rgba(0,212,255,.10);
        }
        .btn-acc{
            background: linear-gradient(135deg, rgba(0,212,255,.22), rgba(255,43,214,.14));
            border-color: rgba(0,212,255,.40);
        }
        .btn-warn{ border-color: rgba(255,200,87,.45); }
        .btn-bad{ border-color: rgba(255,77,109,.50); }

        .badge-good{ background: rgba(42,217,125,.18); border: 1px solid rgba(42,217,125,.38); color: #bfffe0; }
        .badge-bad{ background: rgba(255,77,109,.18); border: 1px solid rgba(255,77,109,.38); color: #ffd2da; }
        .badge-warn{ background: rgba(255,200,87,.16); border: 1px solid rgba(255,200,87,.36); color: #ffe2a8; }

        .table-dark{
            --bs-table-bg: rgba(0,0,0,.22);
            --bs-table-striped-bg: rgba(255,255,255,.03);
            --bs-table-border-color: rgba(255,255,255,.10);
        }
        .table thead th{
            font-size: 12.5px;
            color: rgba(230,240,255,.75);
            letter-spacing: .35px;
            text-transform: uppercase;
        }

        .hint{ color: var(--muted); font-size: 13px; }

        textarea.mono{
            background: rgba(0,0,0,.20);
            color: var(--txt);
            border: 1px solid rgba(255,255,255,.14);
        }
    </style>
</head>
<body>

<div class="metro-topbar py-3">
    <div class="container d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <div class="brand">
                Developer <span class="accent">Dash</span><span class="accent2">board</span>
            </div>
            <div class="metro-pill mt-2">
                <span>👤</span>
                <span><?= h($me['primer_nombre']) ?> (<?= h($me['correo']) ?>)</span>
                <span class="badge badge-warn ms-2"><?= h($rol) ?></span>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a class="btn btn-metro btn-sm" href="/dashboard/">Volver</a>
            <a class="btn btn-metro btn-sm btn-bad" href="/logout/">Logout</a>
        </div>
    </div>
</div>

<div class="container my-4">

    <?php if (!$oauthHasRevoked): ?>
        <div class="metro-card p-3 mb-3">
            <div class="metro-title mb-2">⚠️ Recomendación de esquema (OAuth)</div>
            <div class="hint mb-2">
                Para que el panel pueda marcar apps como <b>Revocadas</b> y luego permitir borrarlas,
                agrega estas columnas en <span class="mono">oauth_clients</span>:
            </div>
            <pre class="mono mb-0" style="white-space:pre-wrap; color: rgba(230,240,255,.85);">
ALTER TABLE oauth_clients
  ADD COLUMN revoked TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN revoked_at DATETIME NULL;
            </pre>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="metro-card p-3 mb-3" style="border-color: rgba(255,77,109,.45);">
            <div class="metro-title mb-2">❌ Errores</div>
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                    <li><?= $e ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($messages)): ?>
        <?php foreach ($messages as $m): ?>
            <div class="metro-card p-3 mb-3" style="border-color: rgba(42,217,125,.40);">
                <?php if (!empty($m['simple'])): ?>
                    <div class="metro-title">✅ <?= h($m['simple']) ?></div>
                <?php else: ?>
                    <div class="metro-title mb-2">✅ <?= h($m['title'] ?? 'Listo') ?></div>
                    <div class="hint mb-3"><?= $m['body'] ?? '' ?></div>

                    <?php if (!empty($m['value'])): ?>
                        <div class="row g-3">
                            <div class="col-12 col-lg-6">
                                <div class="hint mb-1"><?= h($m['value_label'] ?? 'Valor') ?></div>
                                <div class="d-flex gap-2 align-items-start flex-wrap">
                                    <textarea class="form-control mono" rows="2" readonly style="overflow-wrap:anywhere; word-break:break-all;"><?= h($m['value']) ?></textarea>
                                    <button type="button" class="btn btn-metro btn-sm btn-acc js-copy" data-copy="<?= h($m['value']) ?>">Copiar</button>
                                </div>
                            </div>

                            <?php if (!empty($m['value2'])): ?>
                                <div class="col-12 col-lg-6">
                                    <div class="hint mb-1"><?= h($m['value2_label'] ?? 'Valor 2') ?></div>
                                    <div class="d-flex gap-2 align-items-start flex-wrap">
                                        <textarea class="form-control mono" rows="2" readonly style="overflow-wrap:anywhere; word-break:break-all;"><?= h($m['value2']) ?></textarea>
                                        <button type="button" class="btn btn-metro btn-sm btn-acc js-copy" data-copy="<?= h($m['value2']) ?>">Copiar</button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="row g-4">

        <!-- TOKENS API -->
        <div class="col-12 col-xl-6">
            <div class="metro-card">
                <div class="card-header px-4 py-3">
                    <div class="metro-title">Tokens API (Bearer)</div>
                    <div class="hint">Para scripts, bots, Unity con backend, y llamadas servidor-servidor.</div>
                </div>
                <div class="p-4">
                    <form method="post" class="mb-3">
                        <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                        <input type="hidden" name="action" value="create_token">

                        <label class="form-label">Nombre del token</label>
                        <input type="text" name="name" class="form-control" placeholder="Ej: Bot Discord / App Unity / Script" required>

                        <button class="btn btn-metro btn-acc mt-3" type="submit">Crear token</button>
                        <div class="hint mt-2">Consejo: trátalo como contraseña. No lo pegues en videos, streams o capturas.</div>
                    </form>

                    <div class="metro-title mb-2">Tus tokens</div>

                    <?php if (empty($tokens)): ?>
                        <div class="hint">Todavía no tienes tokens.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-dark table-striped align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Token</th>
                                    <th>Creado</th>
                                    <th>Último uso</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $n=1; foreach ($tokens as $t): ?>
                                    <tr>
                                        <td><?= $n++ ?></td>
                                        <td><?= h($t['name']) ?></td>
                                        <td>
                                            <div class="d-flex gap-2 align-items-start flex-wrap">
                                                <code class="mono js-secret secret-masked"
                                                      data-secret="<?= h($t['token']) ?>"
                                                      data-masked="<?= h(mask_secret($t['token'])) ?>"><?= h(mask_secret($t['token'])) ?></code>
                                                <button type="button" class="btn btn-metro btn-sm js-toggle-secret">Revelar</button>
                                                <button type="button" class="btn btn-metro btn-sm btn-acc js-copy" data-copy="<?= h($t['token']) ?>">Copiar</button>
                                            </div>
                                        </td>
                                        <td class="hint"><?= h($t['created_at']) ?></td>
                                        <td class="hint"><?= h($t['last_used_at'] ?? '-') ?></td>
                                        <td>
                                            <?php if ((int)$t['revoked'] === 1): ?>
                                                <span class="badge badge-bad">Revocado</span>
                                            <?php else: ?>
                                                <span class="badge badge-good">Activo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <?php if ((int)$t['revoked'] === 0): ?>
                                                <form method="post" class="d-inline" onsubmit="return confirm('¿Revocar este token?');">
                                                    <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                                                    <input type="hidden" name="action" value="revoke_token">
                                                    <input type="hidden" name="token_id" value="<?= (int)$t['id'] ?>">
                                                    <button class="btn btn-metro btn-sm btn-warn" type="submit">Revocar</button>
                                                </form>
                                            <?php else: ?>
                                                <form method="post" class="d-inline" onsubmit="return confirm('Esto borrará el token (ya revocado). ¿Continuar?');">
                                                    <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                                                    <input type="hidden" name="action" value="delete_token">
                                                    <input type="hidden" name="token_id" value="<?= (int)$t['id'] ?>">
                                                    <button class="btn btn-metro btn-sm btn-bad" type="submit">Borrar</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- OAUTH APPS -->
        <div class="col-12 col-xl-6">
            <div class="metro-card">
                <div class="card-header px-4 py-3">
                    <div class="metro-title">Aplicaciones OAuth (Login con JevalID)</div>
                    <div class="hint">
                        Flujo típico: <span class="mono">/oauth/authorize.php</span> → <span class="mono">/oauth/token.php</span> → <span class="mono">/api/userinfo.php</span>
                        <span class="text-warning">(El Client Secret úsalo SOLO en backend)</span>
                    </div>
                </div>
                <div class="p-4">
                    <form method="post" class="mb-3">
                        <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                        <input type="hidden" name="action" value="create_oauth_client">

                        <label class="form-label">Nombre de la aplicación</label>
                        <input type="text" name="app_name" class="form-control" placeholder="Ej: Mi web, Mi juego, Mi foro" required>

                        <label class="form-label mt-3">Redirect URI</label>
                        <input type="url" name="redirect_uri" class="form-control" placeholder="https://tusitio.com/auth/jevalid/callback  (o http://localhost/... para pruebas)" required>

                        <button class="btn btn-metro btn-acc mt-3" type="submit">Crear aplicación OAuth</button>
                        <div class="hint mt-2">No compartas tu Client Secret. Revela solo cuando lo necesites (se oculta solo).</div>
                    </form>

                    <div class="metro-title mb-2">Tus aplicaciones registradas</div>

                    <?php if (empty($oauthApps)): ?>
                        <div class="hint">Todavía no tienes aplicaciones OAuth registradas.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-dark table-striped align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Redirect URI</th>
                                    <th>Client ID</th>
                                    <th>Client Secret</th>
                                    <th>Creado</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $n2=1; foreach ($oauthApps as $app): ?>
                                    <?php $isRevoked = $oauthHasRevoked ? ((int)$app['revoked'] === 1) : false; ?>
                                    <tr>
                                        <td><?= $n2++ ?></td>
                                        <td class="hint"><?= h($app['redirect_uri']) ?></td>
                                        <td><code class="mono secret-wrap"><?= h($app['client_id']) ?></code></td>
                                        <td>
                                            <div class="d-flex gap-2 align-items-start flex-wrap">
                                                <code class="mono js-secret secret-masked"
                                                      data-secret="<?= h($app['client_secret']) ?>"
                                                      data-masked="<?= h(mask_secret($app['client_secret'])) ?>"><?= h(mask_secret($app['client_secret'])) ?></code>
                                                <button type="button" class="btn btn-metro btn-sm js-toggle-secret">Revelar</button>
                                                <button type="button" class="btn btn-metro btn-sm btn-acc js-copy" data-copy="<?= h($app['client_secret']) ?>">Copiar</button>
                                            </div>
                                        </td>
                                        <td class="hint"><?= h($app['created_at']) ?></td>
                                        <td>
                                            <?php if ($oauthHasRevoked): ?>
                                                <?php if ($isRevoked): ?>
                                                    <span class="badge badge-bad">Revocada</span>
                                                <?php else: ?>
                                                    <span class="badge badge-good">Activa</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge badge-warn">Sin estado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <?php if ($oauthHasRevoked): ?>
                                                <?php if (!$isRevoked): ?>
                                                    <form method="post" class="d-inline" onsubmit="return confirm('¿Revocar esta app OAuth?');">
                                                        <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                                                        <input type="hidden" name="action" value="revoke_oauth_client">
                                                        <input type="hidden" name="oauth_id" value="<?= (int)$app['id'] ?>">
                                                        <button class="btn btn-metro btn-sm btn-warn" type="submit">Revocar</button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="post" class="d-inline" onsubmit="return confirm('Esto borrará la app (ya revocada). ¿Continuar?');">
                                                        <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                                                        <input type="hidden" name="action" value="delete_oauth_client">
                                                        <input type="hidden" name="oauth_id" value="<?= (int)$app['id'] ?>">
                                                        <button class="btn btn-metro btn-sm btn-bad" type="submit">Borrar</button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="hint">Agrega columnas revoked</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="hint mt-2">
                            Tip: ver “saltos” de IDs reales es normal (auto_increment). Aquí mostramos un <b>#</b> consecutivo para que se vea ordenado.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('click', async (e) => {
  const toggle = e.target.closest('.js-toggle-secret');
  if (toggle) {
    const row = toggle.closest('td') || toggle.closest('div');
    const code = row ? row.querySelector('.js-secret') : null;
    if (!code) return;

    const revealed = code.getAttribute('data-revealed') === '1';
    if (!revealed) {
      code.textContent = code.getAttribute('data-secret') || '';
      code.classList.remove('secret-masked');
      code.classList.add('secret-wrap');
      code.setAttribute('data-revealed', '1');
      toggle.textContent = 'Ocultar';
    } else {
      code.textContent = code.getAttribute('data-masked') || '';
      code.classList.remove('secret-wrap');
      code.classList.add('secret-masked');
      code.setAttribute('data-revealed', '0');
      toggle.textContent = 'Revelar';
    }
    return;
  }

  const copyBtn = e.target.closest('.js-copy');
  if (copyBtn) {
    const value = copyBtn.getAttribute('data-copy') || '';
    try {
      await navigator.clipboard.writeText(value);
      const old = copyBtn.textContent;
      copyBtn.textContent = 'Copiado ✅';
      setTimeout(() => copyBtn.textContent = old, 1400);
    } catch (err) {
      alert('No pude copiar automáticamente. Copia manualmente.');
    }
  }
});
</script>

</body>
</html>
