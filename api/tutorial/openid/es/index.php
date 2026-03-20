<?php
// JevalID - Tutorial OpenID Connect (índice)
// Archivo sugerido: /api/tutorial/openid/es/index.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>JevalID — OpenID Connect (OIDC)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{background:radial-gradient(circle at top,#1b2436 0,#05060a 55%);color:#e5e5e5}
    main{max-width:1050px;margin:0 auto}
    .card-jeval{background:rgba(5,6,10,.92);border-radius:1rem;border:1px solid rgba(255,255,255,.08);box-shadow:0 18px 60px rgba(0,0,0,.65)}
    a{color:#cfe3ff;text-decoration:none} a:hover{text-decoration:underline}
    .muted{color:rgba(229,229,229,.72)}
  </style>
</head>
<body>
<main class="container py-5">
  <div class="card-jeval p-4 p-md-5">
    <h1 class="mb-2">JevalID — OpenID Connect (OIDC)</h1>
    <p class="muted mb-4">Elige el tutorial según lo que quieras integrar.</p>

    <div class="list-group">
      <a class="list-group-item list-group-item-action" href="/api/tutorial/oidc/es/">
        📘 OAuth + OpenID Connect (OIDC) — Tutorial general (Web/Apps)
      </a>
      <a class="list-group-item list-group-item-action" href="/api/tutorial/openid/proxmox/es/">
        🧱 Proxmox VE — Configurar OpenID Connect con JevalID
      </a>
    </div>

    <p class="muted mt-4 mb-0">
      Nota: Si usas Proxmox, recuerda que el scope debe incluir <b>openid</b> y se recomienda <b>username-claim=email</b>.
    </p>
  </div>
</main>
</body>
</html>
