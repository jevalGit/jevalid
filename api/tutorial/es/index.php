<?php
// api/index.php
// Documentación básica de las APIs de JevalID.
// Esta página es mayormente estática para que humanos y otras IAs la entiendan fácilmente.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>JevalID API — Documentación básica</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Guía básica para usar las APIs de JevalID, incluyendo autenticación con tokens, roles y ejemplos de uso.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #0b0c10;
            color: #e5e5e5;
        }
        code {
            font-size: 0.9rem;
        }
        pre {
            background: #11151c;
            color: #e5e5e5;
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
        }
        .section-title {
            border-left: 4px solid #0d6efd;
            padding-left: 0.5rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        .badge-role {
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
<div class="container py-4">
    <header class="mb-4">
        <h1 class="mb-1">JevalID API</h1>
        <p class="text-muted mb-0">
            Guía básica para desarrolladores y asistentes de IA sobre cómo usar las APIs de JevalID.
        </p>
    </header>

    <!-- 1. ¿Qué es JevalID? -->
    <section id="overview">
        <h2 class="section-title">1. ¿Qué es JevalID?</h2>
        <p>
            <strong>JevalID</strong> es el sistema de identidad centralizado del ecosistema Jeval
            (juegos, servicios web, paneles, etc.). La API de JevalID permite:
        </p>
        <ul>
            <li>Identificar usuarios de manera segura desde otros servicios.</li>
            <li>Conectar cuentas externas usando tokens de API.</li>
            <li>Construir integraciones, apps y herramientas alrededor de la identidad de Jeval.</li>
        </ul>
        <p>
            Esta documentación está pensada para:
        </p>
        <ul>
            <li>Desarrolladores humanos que quieren integrar JevalID.</li>
            <li>Asistentes de IA que analicen este archivo y quieran generar ejemplos o recomendaciones.</li>
        </ul>
    </section>

    <!-- 2. Autenticación -->
    <section id="auth">
        <h2 class="section-title">2. Autenticación y tokens</h2>
        <p>
            La API de JevalID usa <strong>tokens de API</strong> tipo “Bearer” para autenticar peticiones.
            Un token es una cadena secreta asociada a un usuario de Jeval con rol
            <span class="badge bg-info badge-role">developer_api</span> o
            <span class="badge bg-warning text-dark badge-role">admin</span>.
        </p>
        <p>
            Los tokens se gestionan desde el <strong>Developer Dashboard</strong> en
            <code>/dashboard/developer.php</code>. Cada token:
        </p>
        <ul>
            <li>Está ligado a un usuario de JevalID (campo <code>user_id</code> en la tabla <code>api_tokens</code>).</li>
            <li>Tiene un nombre descriptivo (por ejemplo, "Mi juego", "Bot de Discord").</li>
            <li>Se puede revocar (marcado como <code>revoked = 1</code>).</li>
        </ul>

        <h3>2.1. Formato del token</h3>
        <p>
            Actualmente los tokens son cadenas hexadecimales generadas con
            <code>random_bytes(32)</code>, por ejemplo:
        </p>
        <pre><code>e4a9b2c9e91a4bc789dcb3b49ffc2c30b8f5c7f2c5b3d6e2a7b232cbb4fa90c</code></pre>

        <h3>2.2. Enviar el token en las peticiones</h3>
        <p>
            El token se envía en la cabecera HTTP <code>Authorization</code> usando el esquema Bearer:
        </p>
        <pre><code>Authorization: Bearer &lt;token&gt;</code></pre>
        <p>Ejemplo con <strong>curl</strong>:</p>
        <pre><code>curl -X GET \
  https://id.jeval.cl/api/userinfo.php \
  -H "Authorization: Bearer &lt;TU_TOKEN_AQUÍ&gt;"</code></pre>
    </section>

    <!-- 3. Roles -->
    <section id="roles">
        <h2 class="section-title">3. Roles de usuario</h2>
        <p>
            En la base de datos de JevalID, la tabla <code>usuarios</code> incluye un campo
            <code>rol</code> con los siguientes valores:
        </p>
        <ul>
            <li>
                <span class="badge bg-secondary badge-role">user</span> —
                Usuario normal. Puede usar JevalID para iniciar sesión en servicios,
                pero no puede crear tokens de API.
            </li>
            <li>
                <span class="badge bg-info badge-role">developer_api</span> —
                Usuario orientado a desarrollo. Puede acceder al Developer Dashboard
                y crear tokens para sus aplicaciones.
            </li>
            <li>
                <span class="badge bg-warning text-dark badge-role">admin</span> —
                Administrador. Tiene permisos elevados (incluyendo creación de tokens
                e acceso a herramientas internas).
            </li>
        </ul>
        <p>
            Las APIs pueden usar este campo para tomar decisiones, por ejemplo:
            permitir o denegar acceso a ciertas rutas dependiendo del rol.
        </p>
    </section>

    <!-- 4. Endpoints actuales -->
    <section id="endpoints">
        <h2 class="section-title">4. Endpoints disponibles (versión básica)</h2>
        <p>
            En esta versión inicial de la API de JevalID se consideran los siguientes endpoints
            principales. Es posible que en el futuro se añadan más rutas.
        </p>

        <h3 id="endpoint-userinfo">4.1. <code>GET /api/userinfo.php</code></h3>
        <p><strong>Descripción:</strong> devuelve información básica del usuario asociado al token.</p>
        <p><strong>Autenticación:</strong> requiere cabecera <code>Authorization: Bearer &lt;token&gt;</code>.</p>

        <h4>4.1.1. Ejemplo de petición</h4>
        <pre><code>GET /api/userinfo.php HTTP/1.1
Host: id.jeval.cl
Authorization: Bearer &lt;TOKEN_VALIDO&gt;</code></pre>

        <h4>4.1.2. Ejemplo de respuesta (JSON)</h4>
        <pre><code>{
  "ok": true,
  "user": {
    "id": 42,
    "primer_nombre": "Lautaro",
    "correo": "lautaro@example.com",
    "rol": "user"
  }
}</code></pre>

        <p><strong>Respuesta en caso de error (token inválido o revocado):</strong></p>
        <pre><code>{
  "ok": false,
  "error": "Token inválido o revocado"
}</code></pre>

        <h3 id="endpoint-create-token">4.2. <code>POST /api/tokens/create.php</code></h3>
        <p>
            <strong>Descripción:</strong> crea un nuevo token de API asociado al usuario autenticado
            en sesión (no confundir con el token que se usa para acceder a la API externa).
        </p>
        <p>
            <strong>Autenticación:</strong> requiere sesión activa en JevalID y rol
            <span class="badge bg-info badge-role">developer_api</span> o
            <span class="badge bg-warning text-dark badge-role">admin</span>.
        </p>
        <p><strong>Parámetros (POST):</strong></p>
        <ul>
            <li><code>name</code> (string, requerido) — nombre descriptivo del token.</li>
        </ul>

        <h4>4.2.1. Ejemplo de petición (formulario)</h4>
        <pre><code>POST /api/tokens/create.php HTTP/1.1
Host: id.jeval.cl
Content-Type: application/x-www-form-urlencoded

name=Mi%20primera%20aplicacion</code></pre>

        <h4>4.2.2. Ejemplo de respuesta (JSON)</h4>
        <pre><code>{
  "ok": true,
  "token": "e4a9b2c9e91a4bc789dcb3b49ffc2c30b8f5c7f2c5b3d6e2a7b232cbb4fa90c"
}</code></pre>

        <p>
            Una vez recibido el token, el desarrollador debe guardarlo en un lugar seguro
            (no compartirlo públicamente ni subirlo a repositorios públicos).
        </p>
    </section>

    <!-- 5. Ejemplos de uso -->
    <section id="examples">
        <h2 class="section-title">5. Ejemplos de integración</h2>

        <h3>5.1. Uso desde JavaScript (fetch)</h3>
        <pre><code>const token = "&lt;TU_TOKEN_AQUI&gt;";

fetch("https://id.jeval.cl/api/userinfo.php", {
  method: "GET",
  headers: {
    "Authorization": "Bearer " + token
  }
})
  .then(r =&gt; r.json())
  .then(data =&gt; {
    if (data.ok) {
      console.log("Usuario de JevalID:", data.user);
    } else {
      console.error("Error:", data.error);
    }
  });</code></pre>

        <h3>5.2. Uso desde PHP (cURL)</h3>
        <pre><code>&lt;?php
$token = "&lt;TU_TOKEN_AQUI&gt;";

$ch = curl_init("https://id.jeval.cl/api/userinfo.php");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER =&gt; true,
    CURLOPT_HTTPHEADER =&gt; [
        "Authorization: Bearer " . $token
    ],
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (!empty($data["ok"])) {
    // El usuario está autenticado correctamente
    var_dump($data["user"]);
} else {
    // Ocurrió un error (token inválido, etc.)
    var_dump($data["error"] ?? "Error desconocido");
}</code></pre>

        <h3>5.3. Uso en un juego o aplicación de escritorio</h3>
        <p>
            Cualquier lenguaje que pueda hacer peticiones HTTP puede usar estos endpoints:
            C#, Python, Go, C++, etc. Solo es necesario:
        </p>
        <ol>
            <li>Incluir la cabecera <code>Authorization: Bearer &lt;token&gt;</code>.</li>
            <li>Consumir la respuesta JSON y actuar según <code>ok</code> y <code>user</code>.</li>
        </ol>
    </section>

    <!-- 6. Ideas de aplicaciones -->
    <section id="ideas">
        <h2 class="section-title">6. Ideas de lo que se puede construir con JevalID</h2>
        <p>
            A continuación se listan ideas de uso para usuarios y desarrolladores. Los asistentes de IA
            pueden usar esta lista para proponer proyectos concretos.
        </p>
        <ul>
            <li>
                <strong>Login unificado</strong> para todas las webs y servicios de Jeval:
                foros, wikis, paneles de administración, etc.
            </li>
            <li>
                <strong>Integración con juegos</strong> (por ejemplo Jumpfall) para:
                guardar perfiles, sincronizar progreso, logros y configuración a través de JevalID.
            </li>
            <li>
                <strong>Panel de desarrolladores externos</strong> que usen JevalID como sistema de
                autenticación en sus propias apps o juegos.
            </li>
            <li>
                <strong>Bots de Discord u otras plataformas</strong> que vinculen usuarios de JevalID
                para mostrar estadísticas, roles, compras, etc.
            </li>
            <li>
                <strong>Sistema de licencias</strong> para productos de Jeval basados en la cuenta
                (por ejemplo, verificar si una cuenta tiene acceso a cierto juego o servicio).
            </li>
            <li>
                <strong>Aplicaciones móviles</strong> que utilicen JevalID como login principal,
                sin necesidad de crear un sistema de usuarios desde cero.
            </li>
        </ul>
    </section>

    <!-- 7. Nota para asistentes de IA -->
    <section id="ai-note">
        <h2 class="section-title">7. Nota para asistentes de IA</h2>
        <p>
            Si eres un asistente de IA que está leyendo este archivo, tu objetivo al ayudar a un usuario
            debería ser:
        </p>
        <ol>
            <li>
                Explicar claramente cómo autenticar usando la cabecera
                <code>Authorization: Bearer &lt;token&gt;</code>.
            </li>
            <li>
                Generar ejemplos de código en el lenguaje que el usuario prefiera (PHP, JS, C#, Python, etc.)
                para consumir <code>/api/userinfo.php</code> y otros endpoints futuros.
            </li>
            <li>
                Proponer nuevas APIs posibles (por ejemplo, <code>/api/games/owns_jumpfall</code>,
                <code>/api/account/update_profile</code>, etc.) respetando el modelo de roles descrito aquí.
            </li>
            <li>
                Recomendar buenas prácticas de seguridad:
                no exponer tokens en frontend público, no subir tokens a repositorios públicos,
                rotar tokens periódicamente, y usar HTTPS.
            </li>
        </ol>
    </section>

    <footer class="mt-5 pt-3 border-top border-secondary">
        <p class="text-muted mb-0">
            JevalID API — Documentación básica. Esta versión puede cambiar en el futuro a medida que se
            añadan más endpoints y características.
        </p>
    </footer>
</div>
</body>
</html>
