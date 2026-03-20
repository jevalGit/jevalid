<?php
// JevalID - Tutorial OAuth + OpenID Connect (OIDC)
// Archivo sugerido: /api/tutorial/oidc/es/index.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>JevalID — OAuth + OpenID Connect (OIDC) Tutorial</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
          content="Tutorial oficial de JevalID para implementar OAuth Authorization Code y OpenID Connect (OIDC) con ejemplos, configuración, validación de id_token y resolución de errores.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: radial-gradient(circle at top, #1b2436 0, #05060a 55%);
            color: #e5e5e5;
        }
        main {
            max-width: 1150px;
            margin: 0 auto;
        }
        .card-jeval {
            background: rgba(5, 6, 10, 0.92);
            border-radius: 1rem;
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 18px 60px rgba(0,0,0,.65);
        }
        .section-title {
            border-left: 4px solid #0d6efd;
            padding-left: .6rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        pre {
            background: #11151c;
            color: #e5e5e5;
            padding: 1rem;
            border-radius: .6rem;
            overflow-x: auto;
            font-size: .9rem;
        }
        code { color: #cfe3ff; }
        .muted { color: rgba(229,229,229,.7); }
        a { text-decoration: none; }
        a:hover { text-decoration: underline; }
        .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
        .pill {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            padding: 6px 10px;
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 999px;
            color: rgba(229,229,229,.75);
            font-size: 13px;
            background: rgba(0,0,0,.18);
        }
        .warning-box {
            background: rgba(255, 193, 7, 0.08);
            border: 1px solid rgba(255, 193, 7, 0.30);
            border-radius: .8rem;
            padding: .9rem 1rem;
        }
        .danger-box {
            background: rgba(220, 53, 69, 0.10);
            border: 1px solid rgba(220, 53, 69, 0.30);
            border-radius: .8rem;
            padding: .9rem 1rem;
        }
        .ok-box {
            background: rgba(25, 135, 84, 0.10);
            border: 1px solid rgba(25, 135, 84, 0.30);
            border-radius: .8rem;
            padding: .9rem 1rem;
        }
        .toc a { color: #cfe3ff; }
        .toc li { margin: .35rem 0; }
    </style>
</head>
<body>

<header class="py-4 border-bottom border-secondary">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h1 class="mb-1">JevalID — OAuth + OpenID Connect (OIDC)</h1>
                <div class="muted">
                    Tutorial oficial: implementación, configuración, validación y troubleshooting (pensado para humanos y para IA).
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="pill">🔐 OAuth (code)</span>
                <span class="pill">🪪 OIDC (id_token)</span>
                <span class="pill">🧱 RS256 + JWKS</span>
            </div>
        </div>
    </div>
</header>

<main class="container py-4 py-md-5">

    <div class="card-jeval p-4 p-md-5">
        <h2 class="section-title mt-0">Índice</h2>
        <ul class="toc">
            <li><a href="#que-es">1) Qué es OAuth y qué agrega OIDC</a></li>
            <li><a href="#para-que">2) Para qué sirve (casos reales)</a></li>
            <li><a href="#endpoints">3) Endpoints oficiales JevalID</a></li>
            <li><a href="#flujo">4) Flujo completo (paso a paso)</a></li>
            <li><a href="#config">5) Cómo configurar una app (Developer Dashboard)</a></li>
            <li><a href="#validar">6) Cómo validar id_token (JWT RS256 + JWKS)</a></li>
            <li><a href="#proxmox">7) Ejemplo: Proxmox (OIDC Realm)</a></li>
            <li><a href="#unity">8) Ejemplo: Unity (recomendación práctica)</a></li>
            <li><a href="#errores">9) Errores típicos y soluciones</a></li>
            <li><a href="#seguridad">10) Reglas de seguridad y buenas prácticas</a></li>
        </ul>

        <div class="warning-box mt-3">
            <strong>Compatibilidad:</strong> OIDC es <u>opcional</u>. Si tu app NO pide <code>scope=openid</code>, todo funciona como OAuth normal (no se rompe nada).
        </div>
    </div>

    <section id="que-es">
        <h2 class="section-title">1) Qué es OAuth y qué agrega OIDC</h2>

        <div class="card-jeval p-4">
            <p>
                <strong>OAuth (Authorization Code)</strong> sirve para obtener un <code>access_token</code> y llamar APIs.
                En JevalID esto te permite: canjear un <code>code</code> por un token y luego llamar <code>/api/userinfo.php</code>.
            </p>

            <p>
                <strong>OpenID Connect (OIDC)</strong> es una capa encima de OAuth.
                Si tu app pide <code>scope=openid</code>, JevalID te devuelve además un <code>id_token</code>:
                un <strong>JWT firmado</strong> que prueba identidad (estilo Google).
            </p>

            <div class="ok-box">
                <strong>Resumen rápido:</strong><br>
                OAuth = autorización (token para APIs).<br>
                OIDC = identidad estándar (JWT firmado: <code>id_token</code>).
            </div>
        </div>
    </section>

    <section id="para-que">
        <h2 class="section-title">2) Para qué sirve (casos reales)</h2>
        <div class="card-jeval p-4">
            <ul>
                <li><strong>“Iniciar sesión con JevalID”</strong> en cualquier web o servicio (igual que Google).</li>
                <li><strong>SSO</strong> dentro de tu infraestructura: un login para varios paneles.</li>
                <li><strong>Proxmox</strong> (realm OIDC) y otras herramientas que aceptan proveedores OIDC.</li>
                <li><strong>Apps de terceros</strong>: integración fácil usando librerías estándar OIDC.</li>
                <li><strong>Menos llamadas</strong>: con OIDC puedes validar identidad con <code>id_token</code> sin llamar siempre a <code>userinfo</code>.</li>
            </ul>

            <div class="warning-box">
                <strong>Importante:</strong> El <code>access_token</code> es para APIs. El <code>id_token</code> es para identidad.
                No mezcles roles: no uses <code>id_token</code> como “token de API”.
            </div>
        </div>
    </section>

    <section id="endpoints">
        <h2 class="section-title">3) Endpoints oficiales JevalID</h2>
        <div class="card-jeval p-4">
            <ul class="mono">
                <li><strong>Authorization:</strong> https://id.jeval.cl/oauth/authorize.php</li>
                <li><strong>Token:</strong> https://id.jeval.cl/oauth/token.php</li>
                <li><strong>UserInfo:</strong> https://id.jeval.cl/api/userinfo.php</li>
                <li><strong>JWKS:</strong> https://id.jeval.cl/oauth/jwks.json</li>
                <li><strong>Discovery:</strong> https://id.jeval.cl/.well-known/openid-configuration</li>
            </ul>

            <p class="muted mb-0">
                OIDC usa <code>openid-configuration</code> (discovery) para que herramientas (como Proxmox) se configuren solas.
            </p>
        </div>
    </section>

    <section id="flujo">
        <h2 class="section-title">4) Flujo completo (paso a paso)</h2>

        <div class="card-jeval p-4">
            <h3 class="h5">4.1 Paso 1: botón “Iniciar sesión con JevalID”</h3>
            <p>Tu app redirige al usuario a <code>/oauth/authorize.php</code>:</p>

            <pre><code>// Parámetros recomendados:
client_id=TU_CLIENT_ID
redirect_uri=TU_REDIRECT_URI (exacta, igual a la registrada)
response_type=code
state=RANDOM_ANTI_CSRF
scope=openid email profile   (si quieres OIDC)
nonce=RANDOM_ANTI_REPLAY     (recomendado si usas openid)</code></pre>

            <div class="warning-box">
                <strong>state</strong> protege contra CSRF. Debes guardarlo en sesión y compararlo al volver.<br>
                <strong>nonce</strong> previene replay en OIDC. Se devuelve dentro del <code>id_token</code>.
            </div>

            <h3 class="h5 mt-4">4.2 Paso 2: JevalID autentica</h3>
            <ul>
                <li>Si el usuario ya tiene sesión en JevalID: vuelve inmediato.</li>
                <li>Si no tiene sesión: se le muestra login, y luego vuelve al authorize.</li>
            </ul>

            <h3 class="h5 mt-4">4.3 Paso 3: tu app recibe el <code>code</code></h3>
            <p>
                JevalID redirige a tu <code>redirect_uri</code> con:
                <code>?code=...&amp;state=...</code>
            </p>

            <h3 class="h5 mt-4">4.4 Paso 4: canje del <code>code</code> por token</h3>
            <p>Tu backend hace POST a <code>/oauth/token.php</code>:</p>

            <pre><code>POST https://id.jeval.cl/oauth/token.php
Content-Type: application/x-www-form-urlencoded

grant_type=authorization_code
&code=EL_CODE
&redirect_uri=TU_REDIRECT_URI
&client_id=TU_CLIENT_ID
&client_secret=TU_CLIENT_SECRET</code></pre>

            <p>Respuesta:</p>

            <pre><code>{
  "access_token": "....",
  "token_type": "Bearer",
  "expires_in": 3600,
  "id_token": "JWT_FIRMADO_RS256 (solo si scope incluye openid)"
}</code></pre>

            <h3 class="h5 mt-4">4.5 Paso 5: obtener usuario</h3>
            <p>Opción A (clásica): usar <code>userinfo</code>:</p>
            <pre><code>GET https://id.jeval.cl/api/userinfo.php
Authorization: Bearer ACCESS_TOKEN</code></pre>

            <p>Opción B (OIDC): validar <code>id_token</code> localmente (ver sección 6).</p>
        </div>
    </section>

    <section id="config">
        <h2 class="section-title">5) Cómo configurar una app (Developer Dashboard)</h2>
        <div class="card-jeval p-4">
            <ol>
                <li>Entra a <code>/dashboard/developer.php</code> (rol <code>developer_api</code> o <code>admin</code>).</li>
                <li>Crea una app OAuth:
                    <ul>
                        <li><strong>Nombre</strong></li>
                        <li><strong>Redirect URI</strong> EXACTA (mismo https, misma ruta)</li>
                    </ul>
                </li>
                <li>Guarda:
                    <ul>
                        <li><code>client_id</code></li>
                        <li><code>client_secret</code> (solo backend, nunca frontend)</li>
                    </ul>
                </li>
            </ol>

            <div class="danger-box">
                <strong>Prohibido:</strong> poner el <code>client_secret</code> en JavaScript público o en Unity directo.
                Siempre va en backend. Si se filtra, se revoca la app.
            </div>
        </div>
    </section>

    <section id="validar">
        <h2 class="section-title">6) Cómo validar id_token (JWT RS256 + JWKS)</h2>
        <div class="card-jeval p-4">
            <p>
                Si pediste <code>scope=openid</code>, JevalID devuelve <code>id_token</code> firmado (RS256).
                Para validar:
            </p>

            <ol>
                <li>Decodifica JWT header y toma <code>kid</code>.</li>
                <li>Descarga JWKS: <code>https://id.jeval.cl/oauth/jwks.json</code>.</li>
                <li>Selecciona la key cuyo <code>kid</code> coincide.</li>
                <li>Verifica firma RS256.</li>
                <li>Valida claims:
                    <ul>
                        <li><code>iss</code> debe ser <code>https://id.jeval.cl</code></li>
                        <li><code>aud</code> debe ser tu <code>client_id</code></li>
                        <li><code>exp</code> no debe estar expirado</li>
                        <li><code>nonce</code> debe coincidir (si lo usaste)</li>
                    </ul>
                </li>
            </ol>

            <div class="warning-box">
                <strong>Nota:</strong> Si no quieres validar JWT en tu app, puedes seguir usando <code>/api/userinfo.php</code> con <code>access_token</code>.
            </div>
        </div>
    </section>

    <section id="proxmox">
        <h2 class="section-title">7) Ejemplo: Proxmox (OIDC Realm)</h2>
        <div class="card-jeval p-4">
            <p>
                Proxmox puede usar JevalID como proveedor OIDC si:
                <code>/.well-known/openid-configuration</code> y <code>/oauth/jwks.json</code> están disponibles
                y el <code>id_token</code> es RS256.
            </p>

            <p class="muted">
                Configuración típica (conceptual):
            </p>
            <ul>
                <li><strong>Issuer:</strong> <code>https://id.jeval.cl</code></li>
                <li><strong>Client ID:</strong> el de tu app OAuth creada en JevalID</li>
                <li><strong>Client Secret:</strong> el de tu app OAuth</li>
                <li><strong>Scopes:</strong> <code>openid email profile</code></li>
                <li><strong>Redirect URI:</strong> la que Proxmox te indica (debe estar registrada en JevalID)</li>
            </ul>

            <div class="warning-box">
                Si Proxmox dice “OpenID login failed”, casi siempre es:
                issuer mal, redirect_uri distinta, jwks inaccesible, o firma/claims no válidos.
            </div>
        </div>
    </section>

    <section id="unity">
        <h2 class="section-title">8) Ejemplo: Unity (recomendación práctica)</h2>
        <div class="card-jeval p-4">
            <p>
                En Unity lo sano es:
                <strong>Unity NO guarda client_secret</strong>.
                Unity abre el navegador (o webview), recibe un code en tu backend, y tu backend hace el canje.
            </p>
            <ul>
                <li>Unity abre: authorize URL</li>
                <li>Tu backend recibe callback y canjea code</li>
                <li>Tu backend crea “sesión de juego” propia (cookie/token de tu juego)</li>
            </ul>

            <div class="danger-box">
                <strong>No recomendado:</strong> meter <code>client_secret</code> dentro del juego.
                Eso es filtrar credenciales.
            </div>
        </div>
    </section>

    <section id="errores">
        <h2 class="section-title">9) Errores típicos y soluciones</h2>
        <div class="card-jeval p-4">
            <h3 class="h6">Errores OAuth/Token comunes</h3>
            <ul class="mono">
                <li>{"error":"Método no permitido, usa POST"}</li>
                <li>{"error":"grant_type inválido"}</li>
                <li>{"error":"Faltan parámetros obligatorios"}</li>
                <li>{"error":"client_id inválido"}</li>
                <li>{"error":"client_secret incorrecto"}</li>
                <li>{"error":"redirect_uri no coincide con el registrado"}</li>
                <li>{"error":"Código de autorización inválido"}</li>
                <li>{"error":"Código ya utilizado"}</li>
                <li>{"error":"Código expirado"}</li>
                <li>{"error":"client_revoked"}</li>
                <li>{"error":"oidc_key_missing"}</li>
            </ul>

            <h3 class="h6 mt-3">Causa → Solución</h3>
            <ul>
                <li><strong>redirect_uri no coincide</strong> → tu app usa una URI distinta a la registrada (hasta un “/” cambia). Usa coincidencia exacta.</li>
                <li><strong>client_secret incorrecto</strong> → secret equivocado o filtrado (rota/recrea app).</li>
                <li><strong>código inválido</strong> → code no existe, client_id/redirect_uri no coinciden, o lo alteraste.</li>
                <li><strong>código ya utilizado</strong> → el code solo se canjea una vez. No reintentes con el mismo.</li>
                <li><strong>código expirado</strong> → tardaste demasiado; genera uno nuevo.</li>
                <li><strong>client_revoked</strong> → el owner revocó la app en Developer Dashboard.</li>
                <li><strong>oidc_key_missing</strong> → faltan las llaves RS256 en el server (no puede emitir id_token).</li>
            </ul>

            <div class="warning-box">
                <strong>Error ultra típico:</strong> “state inválido”.
                Eso es tu app fallando: guarda state en sesión antes de redirigir y compáralo al volver.
            </div>
        </div>
    </section>

    <section id="seguridad">
        <h2 class="section-title">10) Reglas de seguridad y buenas prácticas</h2>
        <div class="card-jeval p-4">
            <ul>
                <li><strong>client_secret</strong> siempre backend. Nunca en frontend, Unity, JS público.</li>
                <li>Usa <strong>HTTPS</strong> en producción. Solo <code>http://localhost</code> para pruebas.</li>
                <li>Usa <strong>state</strong> siempre (anti-CSRF).</li>
                <li>Si usas OIDC, usa <strong>nonce</strong> y valídalo.</li>
                <li>Valida <strong>id_token</strong> con JWKS y claims (iss/aud/exp).</li>
                <li>Si una app se filtra o abusa → revoca en Developer Dashboard.</li>
                <li>Tu sistema puede mantener logs técnicos para seguridad (IP, user-agent, timestamps).</li>
            </ul>

            <div class="danger-box">
                <strong>Developer_api no es juguete:</strong> abuso de OAuth/APIs puede afectar a toda la infraestructura.
                Tokens y apps pueden ser revocados sin aviso si hay riesgo real.
            </div>
        </div>
    </section>

    <footer class="mt-5 pt-3 border-top border-secondary">
        <div class="muted">
            JevalID — OAuth + OpenID Connect (OIDC). Tutorial pensado para integradores, sysadmins, y asistentes de IA.
        </div>
    </footer>

</main>
</body>
</html>