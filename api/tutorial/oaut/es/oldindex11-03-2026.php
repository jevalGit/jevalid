<?php
// Documentación de JevalID OAuth
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>JevalID OAuth — Guía de implementación</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
          content="Guía completa para implementar el inicio de sesión con JevalID usando OAuth (Authorization Code), incluyendo ejemplos, errores comunes y buenas prácticas.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #05060a;
            color: #e5e5e5;
        }
        main {
            max-width: 1100px;
            margin: 0 auto;
        }
        pre {
            background: #11151c;
            color: #e5e5e5;
            padding: 1rem;
            border-radius: .5rem;
            overflow-x: auto;
            font-size: .9rem;
        }
        code {
            font-size: .9rem;
        }
        .section-title {
            border-left: 4px solid #0d6efd;
            padding-left: .5rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        a { text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<header class="py-4 mb-3 border-bottom border-secondary">
    <div class="container">
        <h1 class="mb-1">JevalID OAuth</h1>
        <p class="text-muted mb-0">
            Guía de implementación del flujo de inicio de sesión con JevalID (Authorization Code).
        </p>
    </div>
</header>

<main class="container pb-5">

    <!-- 1. Introducción -->
    <section id="overview">
        <h2 class="section-title">1. ¿Qué es JevalID OAuth?</h2>
        <p>
            <strong>JevalID OAuth</strong> permite que aplicaciones externas (webs, juegos, launchers, etc.)
            ofrezcan un botón de <em>“Iniciar sesión con JevalID”</em>, de forma similar a
            <em>“Iniciar sesión con Google”</em>.
        </p>
        <p>
            En vez de que cada proyecto implemente su propio login, se delega la autenticación en
            <strong>id.jeval.cl</strong>, y la aplicación recibe un identificador de usuario y datos básicos
            de perfil (id, nombre, correo, rol).
        </p>
        <p>Esta página explica:</p>
        <ul>
            <li>Cómo registrar una aplicación OAuth en el Developer Dashboard.</li>
            <li>Cómo construir la URL de autorización.</li>
            <li>Cómo intercambiar el <code>code</code> por un <code>access_token</code>.</li>
            <li>Cómo obtener datos del usuario con ese token.</li>
            <li>Errores típicos que devuelve el servidor y cómo manejarlos.</li>
        </ul>
    </section>

    <!-- 2. Componentes -->
    <section id="components">
        <h2 class="section-title">2. Componentes del sistema</h2>
        <p>Endpoints principales:</p>
        <ul>
            <li><strong>Authorization Endpoint</strong> —
                <code>https://id.jeval.cl/oauth/authorize.php</code></li>
            <li><strong>Token Endpoint</strong> —
                <code>https://id.jeval.cl/oauth/token.php</code></li>
            <li><strong>User Info API</strong> —
                <code>https://id.jeval.cl/api/userinfo.php</code></li>
            <li><strong>Login UI</strong> —
                <code>https://id.jeval.cl/login/</code> (maneja <code>?redirect=...</code>)</li>
        </ul>

        <p>Tablas relevantes en la base de datos <code>id</code>:</p>
        <ul>
            <li><code>usuarios</code> — usuarios de JevalID (id, nombre, correo, rol).</li>
            <li><code>api_tokens</code> — tokens Bearer válidos para llamar a las APIs.</li>
            <li><code>oauth_clients</code> — aplicaciones externas (client_id, client_secret, redirect_uri).</li>
            <li><code>oauth_codes</code> — códigos temporales de autorización (<code>code</code>).</li>
        </ul>
    </section>

    <!-- 3. Registro app -->
    <section id="register-app">
        <h2 class="section-title">3. Registrar una aplicación OAuth</h2>
        <ol>
            <li>Inicia sesión en JevalID con un usuario <code>developer_api</code> o <code>admin</code>.</li>
            <li>Abre el <strong>Developer Dashboard</strong>:
                <code>/dashboard/developer.php</code>.</li>
            <li>En la sección <em>“Aplicaciones OAuth”</em>, crea una nueva aplicación.</li>
            <li>Indica:
                <ul>
                    <li><strong>Nombre</strong> (ej: <code>accountjevzgames</code>).</li>
                    <li><strong>Redirect URI</strong> — por ejemplo para uso local con XAMPP:<br>
                        <code>http://localhost/accountjevzgames/callback.php</code>
                    </li>
                </ul>
            </li>
            <li>Al guardar, se generan:
                <ul>
                    <li><code>client_id</code></li>
                    <li><code>client_secret</code></li>
                </ul>
            </li>
        </ol>
        <p class="text-warning">
            El <strong>client_secret</strong> es un secreto de la aplicación.
            Solo debe vivir en el backend (PHP, servidor de juego, etc.), nunca en JavaScript del cliente
            ni en repos públicos.
        </p>
    </section>

    <!-- 4. Flujo -->
    <section id="flow">
        <h2 class="section-title">4. Flujo OAuth de JevalID (Authorization Code)</h2>

        <h3>4.1. Botón “Iniciar sesión con JevalID”</h3>
        <p>
            La aplicación externa muestra un enlace o botón que apunta a
            <code>/oauth/authorize.php</code> con estos parámetros:
        </p>

        <pre><code>&lt;?php
// Ejemplo en PHP (aplicación externa)
session_start();

$clientId    = 'TU_CLIENT_ID';
$redirectUri = 'http://localhost/accountjevzgames/callback.php';

// Estado anti-CSRF
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$params = [
    'client_id'     =&gt; $clientId,
    'redirect_uri'  =&gt; $redirectUri,
    'response_type' =&gt; 'code',
    'state'         =&gt; $state,
];

$authUrl = 'https://id.jeval.cl/oauth/authorize.php?' . http_build_query($params);
?&gt;

&lt;a href="&lt;?= htmlspecialchars($authUrl, ENT_QUOTES, 'UTF-8') ?&gt;"&gt;
    Iniciar sesión con JevalID
&lt;/a&gt;</code></pre>

        <p>Parámetros:</p>
        <ul>
            <li><code>client_id</code> — el obtenido del dashboard.</li>
            <li><code>redirect_uri</code> — debe ser exactamente el mismo que está registrado en JevalID.</li>
            <li><code>response_type=code</code> — usamos Authorization Code.</li>
            <li><code>state</code> — valor aleatorio para proteger la sesión (CSRF).</li>
        </ul>

        <h3>4.2. Lo que hace JevalID en <code>authorize.php</code></h3>
        <ul>
            <li>Valida que el <code>client_id</code> exista y que el <code>redirect_uri</code> coincida.</li>
            <li>Si el usuario NO tiene sesión: redirige a
                <code>/login/?redirect=/oauth/authorize.php?... </code></li>
            <li>Si el usuario ya está logueado:
                <ul>
                    <li>Genera un <code>code</code> en <code>oauth_codes</code>, con caducidad (ej. 15 minutos).</li>
                    <li>Redirige a <code>redirect_uri?code=...&amp;state=...</code></li>
                </ul>
            </li>
        </ul>

        <h3>4.3. Intercambio de <code>code</code> por <code>access_token</code> en <code>token.php</code></h3>
        <p>
            El backend de la aplicación externa hace un POST a:
            <code>https://id.jeval.cl/oauth/token.php</code> con:
        </p>

        <pre><code>POST https://id.jeval.cl/oauth/token.php
Content-Type: application/x-www-form-urlencoded

grant_type=authorization_code
&amp;code=EL_CODE_QUE_RECIBISTE
&amp;redirect_uri=http%3A%2F%2Flocalhost%2Faccountjevzgames%2Fcallback.php
&amp;client_id=TU_CLIENT_ID
&amp;client_secret=TU_CLIENT_SECRET</code></pre>

        <p>Respuesta exitosa:</p>
        <pre><code>{
  "access_token": "e4a9b2c9e91a4bc7...",
  "token_type": "Bearer",
  "expires_in": 3600
}</code></pre>

        <p>
            Internamente, <code>token.php</code>:
        </p>
        <ul>
            <li>Valida: <code>client_id</code>, <code>client_secret</code>, <code>redirect_uri</code>.</li>
            <li>Busca el <code>code</code> en <code>oauth_codes</code> con ese cliente y redirect.</li>
            <li>Comprueba:
                <ul>
                    <li>Que no esté marcado como usado (<code>used = 0</code>).</li>
                    <li>Que <code>expires_at &gt; NOW()</code> (no expirado).</li>
                </ul>
            </li>
            <li>Marca el <code>code</code> como usado.</li>
            <li>Genera un <code>access_token</code> aleatorio y lo inserta en <code>api_tokens</code>.</li>
        </ul>

        <h3>4.4. Obtener datos del usuario en <code>/api/userinfo.php</code></h3>
        <p>
            Con el <code>access_token</code>, la app externa llama:
        </p>

        <pre><code>GET https://id.jeval.cl/api/userinfo.php
Authorization: Bearer e4a9b2c9e91a4bc7...</code></pre>

        <p>Respuesta típica:</p>
        <pre><code>{
  "ok": true,
  "user": {
    "id": 1,
    "primer_nombre": "jesus",
    "correo": "jesusemiliofg@outlook.com",
    "rol": "admin"
  }
}</code></pre>

        <p>
            Con eso la aplicación puede crear/iniciar sesión local con el usuario JevalID.
        </p>
    </section>

    <!-- 5. Ejemplo de implementación completa (XAMPP) -->
    <section id="example-local">
        <h2 class="section-title">5. Ejemplo práctico: login local con XAMPP</h2>
        <p>
            Ejemplo simplificado de una app local en
            <code>http://localhost/accountjevzgames/</code> que usa JevalID para login.
        </p>

        <h3>5.1. <code>config.php</code></h3>
        <pre><code>&lt;?php
session_start();

$JEVAL_CLIENT_ID     = 'TU_CLIENT_ID';
$JEVAL_CLIENT_SECRET = 'TU_CLIENT_SECRET';
$JEVAL_REDIRECT_URI  = 'http://localhost/accountjevzgames/callback.php';

$JEVAL_AUTH_URL     = 'https://id.jeval.cl/oauth/authorize.php';
$JEVAL_TOKEN_URL    = 'https://id.jeval.cl/oauth/token.php';
$JEVAL_USERINFO_URL = 'https://id.jeval.cl/api/userinfo.php';
</code></pre>

        <h3>5.2. <code>index.php</code> (botón de login)</h3>
        <pre><code>&lt;?php
require __DIR__ . '/config.php';

if (isset($_SESSION['jeval_user'])) {
    header('Location: panel.php');
    exit;
}

$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$params = [
    'client_id'     =&gt; $JEVAL_CLIENT_ID,
    'redirect_uri'  =&gt; $JEVAL_REDIRECT_URI,
    'response_type' =&gt; 'code',
    'state'         =&gt; $state,
];

$authUrl = $JEVAL_AUTH_URL . '?' . http_build_query($params);
?&gt;

&lt;a href="&lt;?= htmlspecialchars($authUrl, ENT_QUOTES, 'UTF-8') ?&gt;"&gt;
    Iniciar sesión con JevalID
&lt;/a&gt;</code></pre>

        <h3>5.3. <code>callback.php</code></h3>
        <pre><code>&lt;?php
require __DIR__ . '/config.php';

$code  = $_GET['code']  ?? null;
$state = $_GET['state'] ?? null;

if (!$code) {
    die('Falta parámetro code');
}

if (!isset($_SESSION['oauth_state']) || $_SESSION['oauth_state'] !== $state) {
    die('State inválido (posible CSRF).');
}
unset($_SESSION['oauth_state']);

// Pedir access_token
$postData = [
    'grant_type'    =&gt; 'authorization_code',
    'code'          =&gt; $code,
    'redirect_uri'  =&gt; $JEVAL_REDIRECT_URI,
    'client_id'     =&gt; $JEVAL_CLIENT_ID,
    'client_secret' =&gt; $JEVAL_CLIENT_SECRET,
];

$ch = curl_init($JEVAL_TOKEN_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER =&gt; true,
    CURLOPT_POST           =&gt; true,
    CURLOPT_POSTFIELDS     =&gt; http_build_query($postData),
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (empty($data['access_token'])) {
    echo "&lt;pre&gt;Error al obtener access_token:\n";
    var_dump($data);
    echo "&lt;/pre&gt;";
    exit;
}

$accessToken = $data['access_token'];

// Pedir info de usuario
$ch = curl_init($JEVAL_USERINFO_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER =&gt; true,
    CURLOPT_HTTPHEADER     =&gt; ['Authorization: Bearer ' . $accessToken],
]);
$userInfoJson = curl_exec($ch);
curl_close($ch);

$userInfo = json_decode($userInfoJson, true);

if (empty($userInfo['ok']) || empty($userInfo['user'])) {
    echo "&lt;pre&gt;Error al obtener usuario:\n";
    var_dump($userInfo);
    echo "&lt;/pre&gt;";
    exit;
}

$_SESSION['jeval_user']   = $userInfo['user'];
$_SESSION['access_token'] = $accessToken;

header('Location: panel.php');
exit;
</code></pre>
    </section>

    <!-- 6. Errores y mensajes reales -->
    <section id="errors">
        <h2 class="section-title">6. Errores típicos que puede devolver JevalID</h2>
        <p>
            El endpoint <code>/oauth/token.php</code> devuelve errores en JSON, por ejemplo:
        </p>
        <ul>
            <li><code>{"error":"Método no permitido, usa POST"}</code></li>
            <li><code>{"error":"grant_type inválido"}</code></li>
            <li><code>{"error":"Faltan parámetros obligatorios"}</code></li>
            <li><code>{"error":"client_id inválido"}</code></li>
            <li><code>{"error":"client_secret incorrecto"}</code></li>
            <li><code>{"error":"redirect_uri no coincide con el registrado"}</code></li>
            <li><code>{"error":"Código de autorización inválido"}</code></li>
            <li><code>{"error":"Código ya utilizado"}</code></li>
            <li><code>{"error":"Código expirado"}</code> (el code superó su tiempo de vida, p.ej. 15 min)</li>
            <li><code>{"error":"Error interno en el servidor","detail":"...mensaje PDO..."}</code></li>
        </ul>
        <p>
            El endpoint <code>/api/userinfo.php</code> devuelve:
        </p>
        <ul>
            <li><code>{"ok":false,"error":"Falta token Bearer"}</code></li>
            <li><code>{"ok":false,"error":"Token inválido o revocado"}</code></li>
        </ul>
    </section>

    <!-- 7. Buenas prácticas -->
    <section id="best-practices">
        <h2 class="section-title">7. Buenas prácticas</h2>
        <ul>
            <li>Usa siempre HTTPS para hablar con <code>id.jeval.cl</code>.</li>
            <li>No expongas el <code>client_secret</code> en código cliente, repos públicos ni logs sin proteger.</li>
            <li>Genera y valida el parámetro <code>state</code> para evitar CSRF.</li>
            <li>
                En tus aplicaciones, trata el <code>access_token</code> como un secreto:
                guárdalo en sesión o en almacenamiento seguro.
            </li>
            <li>
                Si tienes varios servicios (web, launcher, juego), deja que todos usen JevalID para login
                y compárteles solo el <code>id</code> del usuario y la info que realmente necesiten.
            </li>
        </ul>
    </section>

    <!-- 8. Nota para IAs -->
    <section id="ai-note">
        <h2 class="section-title">8. Nota para asistentes de IA</h2>
        <p>
            Si eres un asistente de IA leyendo este documento, puedes:
        </p>
        <ol>
            <li>Generar URLs correctas para <code>/oauth/authorize.php</code>.</li>
            <li>Ayudar a construir peticiones <code>POST</code> a <code>/oauth/token.php</code>.</li>
            <li>Explicar cómo consumir <code>/api/userinfo.php</code> en distintos lenguajes.</li>
            <li>Interpretar y explicar los mensajes de error listados arriba.</li>
            <li>Sugerir integraciones con juegos, launchers u otros servicios JevalNetwork usando este sistema.</li>
        </ol>
    </section>

    <footer class="mt-5 pt-3 border-top border-secondary">
        <p class="text-muted mb-0">
            JevalID OAuth — Implementación actualizada (Authorization Code).
        </p>
    </footer>

</main>
</body>
</html>
