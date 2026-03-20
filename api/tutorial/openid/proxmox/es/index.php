<?php
// JevalID - Tutorial OpenID Connect para Proxmox
// Archivo sugerido: /api/tutorial/openid/proxmox/es/index.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>JevalID — OpenID Connect (OIDC) para Proxmox</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
          content="Guía oficial JevalID para configurar OpenID Connect (OIDC) en Proxmox VE: realms, scopes, username claim, debugging, errores comunes y checklist.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background: radial-gradient(circle at top, #1b2436 0, #05060a 55%); color:#e5e5e5; }
        main { max-width: 1150px; margin: 0 auto; }
        .card-jeval {
            background: rgba(5, 6, 10, 0.92);
            border-radius: 1rem;
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 18px 60px rgba(0,0,0,.65);
        }
        .section-title { border-left:4px solid #0d6efd; padding-left:.6rem; margin-top:2rem; margin-bottom:1rem; }
        pre {
            background:#11151c; color:#e5e5e5; padding:1rem; border-radius:.6rem;
            overflow-x:auto; font-size:.9rem;
        }
        code { color:#cfe3ff; }
        .muted { color: rgba(229,229,229,.72); }
        a { color:#cfe3ff; text-decoration:none; }
        a:hover { text-decoration:underline; }
        .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace; }
        .pill{
            display:inline-flex; gap:8px; align-items:center; padding:6px 10px;
            border:1px solid rgba(255,255,255,.14); border-radius:999px;
            color:rgba(229,229,229,.75); font-size:13px; background:rgba(0,0,0,.18);
        }
        .warning-box{ background:rgba(255,193,7,0.08); border:1px solid rgba(255,193,7,0.30); border-radius:.8rem; padding:.9rem 1rem; }
        .danger-box{ background:rgba(220,53,69,0.10); border:1px solid rgba(220,53,69,0.30); border-radius:.8rem; padding:.9rem 1rem; }
        .ok-box{ background:rgba(25,135,84,0.10); border:1px solid rgba(25,135,84,0.30); border-radius:.8rem; padding:.9rem 1rem; }
        .toc a { color:#cfe3ff; }
        .toc li { margin:.35rem 0; }
        .kbd{
            display:inline-block; padding:.15rem .4rem; border-radius:.4rem;
            background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12);
            font-size:.85em;
        }
        .hr { height:1px; background:rgba(255,255,255,.10); margin:1.2rem 0; }
    </style>
</head>
<body>

<header class="py-4 border-bottom border-secondary">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h1 class="mb-1">JevalID — OpenID Connect para Proxmox</h1>
                <div class="muted">
                    Configuración paso a paso (UI + archivo), checklist y errores típicos.
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="pill">🧱 Proxmox VE</span>
                <span class="pill">🪪 OIDC</span>
                <span class="pill">🔐 RS256 + JWKS</span>
            </div>
        </div>
    </div>
</header>

<main class="container py-4 py-md-5">

    <div class="card-jeval p-4 p-md-5">
        <h2 class="section-title mt-0">Índice</h2>
        <ul class="toc">
            <li><a href="#antes">1) Antes de empezar (requisitos)</a></li>
            <li><a href="#endpoints">2) Endpoints OIDC de JevalID (lo que Proxmox consume)</a></li>
            <li><a href="#crearapp">3) Crear App OAuth/OIDC en JevalID (Client ID/Secret)</a></li>
            <li><a href="#config-ui">4) Configurar Proxmox por UI (Realm OpenID Connect)</a></li>
            <li><a href="#config-file">5) Configurar Proxmox por archivo (/etc/pve/domains.cfg)</a></li>
            <li><a href="#username-claim">6) Username Claim (email vs sub) y por qué importa</a></li>
            <li><a href="#prueba">7) Prueba rápida + checklist de “ya quedó”</a></li>
            <li><a href="#errores">8) Errores típicos (los más comunes) y soluciones</a></li>
            <li><a href="#seguridad">9) Seguridad recomendada</a></li>
        </ul>

        <div class="ok-box mt-3">
            <strong>Objetivo:</strong> que el login en Proxmox muestre <em>“Iniciar sesión con JevalID”</em>
            y que el usuario entre sin crear cuentas manuales.
        </div>

        <div class="warning-box mt-3">
            <strong>Dato clave:</strong> Proxmox requiere <code>scope=openid</code> para OIDC.
            Si solo pones <code>email</code>, no es OIDC completo y suele fallar.
        </div>
    </div>

    <section id="antes">
        <h2 class="section-title">1) Antes de empezar (requisitos)</h2>
        <div class="card-jeval p-4">
            <ul>
                <li>Proxmox VE con acceso a internet saliente hacia <span class="mono">https://id.jeval.cl</span>.</li>
                <li>Un <strong>Client ID</strong> y <strong>Client Secret</strong> creados en JevalID Developer Dashboard.</li>
                <li>Un <strong>Redirect URI</strong> correcto para Proxmox: <span class="mono">https://TU_HOST:8006</span> (o el FQDN real).</li>
                <li>JevalID debe exponer Discovery y JWKS para que Proxmox valide firmas.</li>
            </ul>

            <div class="warning-box">
                Si tu Proxmox está en LAN, igual puede usar JevalID público.
                Solo asegúrate de que el <strong>redirect</strong> apunte a la URL exacta del panel Proxmox.
            </div>
        </div>
    </section>

    <section id="endpoints">
        <h2 class="section-title">2) Endpoints OIDC de JevalID</h2>
        <div class="card-jeval p-4">
            <p class="muted">
                Proxmox normalmente “descubre” endpoints leyendo <code>.well-known/openid-configuration</code>.
                Debe devolver JSON válido.
            </p>

            <ul class="mono">
                <li><strong>Discovery:</strong> https://id.jeval.cl/.well-known/openid-configuration</li>
                <li><strong>Authorization endpoint:</strong> https://id.jeval.cl/oauth/authorize.php</li>
                <li><strong>Token endpoint:</strong> https://id.jeval.cl/oauth/token.php</li>
                <li><strong>UserInfo endpoint:</strong> https://id.jeval.cl/api/userinfo.php</li>
                <li><strong>JWKS:</strong> https://id.jeval.cl/oauth/jwks.json</li>
            </ul>

            <div class="hr"></div>

            <p><strong>Prueba rápida desde Proxmox (CLI):</strong></p>
            <pre><code># Debe devolver JSON (200 OK)
curl -i https://id.jeval.cl/.well-known/openid-configuration | head -n 40

# Debe devolver JSON con "keys" (200 OK)
curl -i https://id.jeval.cl/oauth/jwks.json | head -n 60</code></pre>

            <div class="danger-box">
                Si <code>jwks.json</code> da 404, Proxmox NO puede validar el <code>id_token</code>.
                Resultado típico: “OpenID login failed (401)” o “Failed to parse server response”.
            </div>
        </div>
    </section>

    <section id="crearapp">
        <h2 class="section-title">3) Crear App OAuth/OIDC en JevalID</h2>
        <div class="card-jeval p-4">
            <ol>
                <li>Entra a JevalID y ve al <strong>Developer Dashboard</strong>.</li>
                <li>Crea una aplicación OAuth.</li>
                <li>En <strong>Redirect URI</strong> pon exactamente la URL del panel Proxmox:
                    <span class="mono">https://TU_HOST:8006</span>
                </li>
                <li>Guarda <strong>Client ID</strong> y <strong>Client Secret</strong>.</li>
            </ol>

            <div class="warning-box">
                El <strong>redirect_uri</strong> debe coincidir 1:1 con lo registrado.
                Si no coincide, el authorize/token se cae o Proxmox se queda en bucle.
            </div>
        </div>
    </section>

    <section id="config-ui">
        <h2 class="section-title">4) Configurar Proxmox por UI (Realm OpenID Connect)</h2>
        <div class="card-jeval p-4">
            <p>Ruta: <strong>Datacenter → Permissions → Realms → Add → OpenID Connect</strong></p>

            <ul>
                <li><strong>Realm:</strong> <span class="mono">jevalid</span> (o el nombre que quieras)</li>
                <li><strong>Issuer URL:</strong> <span class="mono">https://id.jeval.cl</span></li>
                <li><strong>Client ID:</strong> tu client_id</li>
                <li><strong>Client Key:</strong> tu client_secret</li>
                <li><strong>Scopes:</strong> <span class="mono">openid email</span></li>
                <li><strong>Username Claim:</strong> <span class="mono">email</span> (si quieres “humano”)</li>
                <li><strong>Userinfo:</strong> activar “Query Userinfo” (recomendado)</li>
                <li><strong>Autocreate users:</strong> ON (si quieres que Proxmox cree usuarios automáticamente)</li>
            </ul>

            <div class="ok-box">
                Con <code>username-claim=email</code>, Proxmox va a crear usuarios como:
                <span class="mono">correo@tu-dominio@jevalid</span> (funciona, aunque se vea raro por el doble @).
            </div>
        </div>
    </section>

    <section id="config-file">
        <h2 class="section-title">5) Configurar Proxmox por archivo (/etc/pve/domains.cfg)</h2>
        <div class="card-jeval p-4">
            <p>
                Proxmox guarda realms en <span class="mono">/etc/pve/domains.cfg</span>.
                Si editas por archivo, reinicia <code>pveproxy</code> y <code>pvedaemon</code>.
            </p>

            <pre><code>openid: jevalid
        comment jevalid-beta
        client-id TU_CLIENT_ID
        issuer-url https://id.jeval.cl
        client-key TU_CLIENT_SECRET
        default 0
        query-userinfo 1
        scopes openid email
        username-claim email</code></pre>

            <div class="warning-box">
                Si te falta <code>openid</code> en scopes, no es OIDC completo.
                Error típico: “OpenID login failed (401)”.
            </div>

            <pre><code># Reiniciar servicios del panel (no reinicia el nodo)
systemctl restart pveproxy pvedaemon</code></pre>
        </div>
    </section>

    <section id="username-claim">
        <h2 class="section-title">6) Username Claim (email vs sub)</h2>
        <div class="card-jeval p-4">
            <p><strong>¿Por qué importa?</strong> Proxmox crea el usuario local como:</p>
            <pre><code>&lt;valor_del_claim&gt;@&lt;realm&gt;</code></pre>

            <h3 class="h5">Opción A — email (recomendada si quieres “humano”)</h3>
            <ul>
                <li><strong>username-claim:</strong> <span class="mono">email</span></li>
                <li>Resultado: <span class="mono">leni@jeval.cl@jevalid</span></li>
                <li>Ventaja: se entiende fácil</li>
                <li>Contras: el doble <span class="mono">@</span> es feo, pero es normal en Proxmox</li>
            </ul>

            <div class="hr"></div>

            <h3 class="h5">Opción B — sub (recomendada si quieres “técnico/limpio”)</h3>
            <ul>
                <li><strong>username-claim:</strong> <span class="mono">sub</span></li>
                <li>Resultado: <span class="mono">u1@jevalid</span></li>
                <li>Ventaja: corto, sin símbolos raros</li>
            </ul>

            <div class="danger-box">
                <strong>No uses sub con “:”</strong> (ej <span class="mono">user:1</span>) porque Proxmox lo marca inválido:
                “value 'user:1@realm' does not look like a valid user name”.
            </div>
        </div>
    </section>

    <section id="prueba">
        <h2 class="section-title">7) Prueba rápida + checklist</h2>
        <div class="card-jeval p-4">
            <h3 class="h5">Checklist de “ya está”</h3>
            <ul>
                <li>✅ Discovery responde JSON: <span class="mono">/.well-known/openid-configuration</span></li>
                <li>✅ JWKS responde JSON con keys: <span class="mono">/oauth/jwks.json</span></li>
                <li>✅ Proxmox realm tiene <span class="mono">scopes openid email</span></li>
                <li>✅ Proxmox realm tiene <span class="mono">username-claim email</span> (o sub, pero bien)</li>
                <li>✅ Reiniciaste <span class="mono">pveproxy</span> y <span class="mono">pvedaemon</span> después de cambios</li>
                <li>✅ Login por UI te muestra el botón/realm y entra</li>
            </ul>

            <div class="ok-box">
                Si entras y ves tu usuario creado en <strong>Datacenter → Permissions → Users</strong>,
                entonces OIDC ya quedó operativo.
            </div>
        </div>
    </section>

    <section id="errores">
        <h2 class="section-title">8) Errores típicos y soluciones</h2>

        <div class="card-jeval p-4">

            <h3 class="h5">8.1 “Failed to parse server response” (500)</h3>
            <p class="muted">Proxmox recibió algo que no era JSON (HTML, warnings, texto plano).</p>
            <ul>
                <li>Revisa logs Apache: <span class="mono">/var/log/apache2/error.log</span></li>
                <li>Asegura que <code>userinfo</code> y discovery devuelvan JSON SIEMPRE.</li>
                <li>Evita <code>die("texto")</code> en DB/endpoint: eso rompe JSON.</li>
            </ul>

            <div class="hr"></div>

            <h3 class="h5">8.2 “OpenID login failed (401)”</h3>
            <ul>
                <li>Falta <code>openid</code> en scopes → usa <span class="mono">scopes openid email</span></li>
                <li>JWKS 404 → arregla <span class="mono">/oauth/jwks.json</span></li>
                <li>Client ID/Secret equivocados</li>
                <li>Redirect URI no coincide exactamente</li>
            </ul>

            <div class="hr"></div>

            <h3 class="h5">8.3 “value 'user:1@jevalid' does not look like a valid user name”</h3>
            <ul>
                <li>Tu <code>sub</code> contiene <span class="mono">:</span> y Proxmox lo está usando como username.</li>
                <li>Solución rápida: en Proxmox define <span class="mono">username-claim email</span>.</li>
                <li>Solución “limpia”: usar <span class="mono">sub</span> como <span class="mono">u1</span> (sin <span class="mono">:</span>).</li>
            </ul>

            <div class="hr"></div>

            <h3 class="h5">8.4 “Failed to contact token endpoint: Request failed”</h3>
            <ul>
                <li>Proxmox no puede salir a internet / DNS / firewall</li>
                <li>Certificados / CA en Proxmox: actualiza CA si hace falta</li>
                <li>Prueba desde Proxmox: <code>curl -vk https://id.jeval.cl/oauth/token.php</code></li>
            </ul>

            <div class="hr"></div>

            <h3 class="h5">8.5 Debug recomendado (Proxmox)</h3>
            <pre><code>journalctl -n 200 -u pvedaemon --no-pager | grep -i openid -n
journalctl -n 200 -u pveproxy  --no-pager | grep -i openid -n</code></pre>
        </div>
    </section>

    <section id="seguridad">
        <h2 class="section-title">9) Seguridad recomendada</h2>
        <div class="card-jeval p-4">
            <ul>
                <li>No compartas <strong>Client Secret</strong>. Es como una contraseña de aplicación.</li>
                <li>Registra redirects exactos (no uses comodines abiertos).</li>
                <li>Si JevalID rota llaves RS256, mantén JWKS siempre accesible.</li>
                <li>Logs de seguridad: guarda registros mínimos para diagnosticar fraude y abusos.</li>
            </ul>

            <div class="warning-box">
                Si tu panel Proxmox está expuesto a internet, usa buenas prácticas de hardening (2FA, firewall, etc.).
                OIDC no reemplaza seguridad del panel, solo autentica.
            </div>
        </div>
    </section>

    <footer class="mt-5 pb-3 text-center muted">
        JevalID — Tutorial OIDC Proxmox · <span class="mono">/api/tutorial/openid/proxmox/es/</span>
    </footer>

</main>
</body>
</html>
