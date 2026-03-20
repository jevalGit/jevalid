<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Términos de uso – Jeval ID (OAuth)</title>
  <meta name="description" content="Términos de uso de Jeval ID (OAuth) para desarrolladores y usuarios." />

  <style>
    :root{
      --bg:#0b0f14;
      --panel:#0f1621;
      --panel2:#0c121b;
      --text:#e6eefc;
      --muted:#a9b7d0;
      --line:#1e2a3a;
      --accent:#ff2e2e; /* jeval rojo */
      --accent2:#ff6b6b;
      --good:#1fe08a;
      --warn:#ffcc00;
      --bad:#ff3b3b;
      --shadow: 0 10px 30px rgba(0,0,0,.45);
      --radius: 18px;
      --mono: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      --sans: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, "Noto Sans", Arial, "Apple Color Emoji","Segoe UI Emoji";
    }

    *{ box-sizing: border-box; }
    body{
      margin:0;
      font-family: var(--sans);
      color: var(--text);
      background:
        radial-gradient(900px 400px at 10% 0%, rgba(255,46,46,.14), transparent 60%),
        radial-gradient(900px 400px at 90% 10%, rgba(255,107,107,.10), transparent 55%),
        linear-gradient(180deg, var(--bg), #070a0f);
      line-height: 1.55;
    }

    .wrap{
      max-width: 980px;
      margin: 0 auto;
      padding: 40px 18px 70px;
    }

    header{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap: 14px;
      margin-bottom: 18px;
    }

    .brand{
      display:flex;
      flex-direction:column;
      gap:6px;
    }
    .kicker{
      font-family: var(--mono);
      color: var(--accent2);
      letter-spacing:.04em;
      font-size: 12px;
      text-transform: uppercase;
    }
    h1{
      margin:0;
      font-size: 28px;
      letter-spacing: .2px;
    }
    .meta{
      margin-top: 4px;
      color: var(--muted);
      font-size: 13px;
    }

    .pillbar{
      display:flex;
      gap:8px;
      flex-wrap:wrap;
      margin-top: 10px;
    }
    .pill{
      border:1px solid var(--line);
      background: rgba(15,22,33,.65);
      padding: 8px 10px;
      border-radius: 999px;
      font-size: 12px;
      color: var(--muted);
    }
    .pill strong{ color: var(--text); font-weight: 700; }

    nav{
      margin: 16px 0 26px;
      padding: 12px;
      border:1px solid var(--line);
      background: rgba(15,22,33,.6);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
    }
    nav a{
      color: var(--muted);
      text-decoration:none;
      font-size: 13px;
      margin-right: 12px;
      display:inline-block;
      padding: 6px 8px;
      border-radius: 10px;
      border: 1px solid transparent;
    }
    nav a:hover{
      color: var(--text);
      border-color: var(--line);
      background: rgba(255,46,46,.08);
    }

    .grid{
      display:grid;
      grid-template-columns: 1.1fr .9fr;
      gap: 16px;
      align-items: start;
    }
    @media (max-width: 860px){
      .grid{ grid-template-columns: 1fr; }
    }

    .card{
      border:1px solid var(--line);
      background: rgba(15,22,33,.75);
      border-radius: var(--radius);
      padding: 18px;
      box-shadow: var(--shadow);
    }
    .card h2{
      margin:0 0 10px;
      font-size: 18px;
    }
    .card h3{
      margin: 16px 0 8px;
      font-size: 15px;
      color: var(--text);
    }
    .muted{ color: var(--muted); }

    .callout{
      border:1px solid var(--line);
      background: rgba(12,18,27,.65);
      border-radius: 14px;
      padding: 14px;
      margin: 12px 0;
    }
    .callout .title{
      font-family: var(--mono);
      font-size: 12px;
      letter-spacing:.03em;
      color: var(--muted);
      text-transform: uppercase;
      margin-bottom: 6px;
    }
    .badge{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding: 8px 10px;
      border-radius: 14px;
      border:1px solid var(--line);
      background: rgba(7,10,15,.55);
      margin: 6px 6px 0 0;
      font-size: 13px;
    }
    .dot{
      width: 10px; height: 10px; border-radius: 999px;
      background: var(--accent);
      box-shadow: 0 0 0 4px rgba(255,46,46,.12);
      flex: 0 0 auto;
    }
    .dot.good{ background: var(--good); box-shadow: 0 0 0 4px rgba(31,224,138,.12); }
    .dot.warn{ background: var(--warn); box-shadow: 0 0 0 4px rgba(255,204,0,.12); }
    .dot.bad{ background: var(--bad); box-shadow: 0 0 0 4px rgba(255,59,59,.12); }

    ul{ margin: 8px 0 0 18px; }
    li{ margin: 6px 0; }

    code{
      font-family: var(--mono);
      font-size: 12.5px;
      padding: 2px 6px;
      border-radius: 8px;
      border:1px solid var(--line);
      background: rgba(7,10,15,.55);
      color: #d7e6ff;
    }

    .hr{
      height:1px;
      background: linear-gradient(90deg, transparent, var(--line), transparent);
      margin: 18px 0;
    }

    footer{
      margin-top: 22px;
      color: var(--muted);
      font-size: 13px;
      text-align:center;
    }

    .danger{
      border-color: rgba(255,59,59,.35);
      background: rgba(255,59,59,.06);
    }
    .good{
      border-color: rgba(31,224,138,.35);
      background: rgba(31,224,138,.06);
    }
    .warn{
      border-color: rgba(255,204,0,.35);
      background: rgba(255,204,0,.06);
    }

    .small{
      font-size: 12.5px;
      color: var(--muted);
    }
    a{
      color: var(--accent2);
    }
  </style>
</head>

<body>
  <main class="wrap">
    <header>
      <div class="brand">
        <div class="kicker">JevalNetworks • Jeval ID</div>
        <h1>Términos de uso – Jeval ID (OAuth)</h1>
        <div class="meta">
          Última actualización: <strong>2026-02-17</strong> • Estado: <strong>Best-effort / Beta</strong>
        </div>

        <div class="pillbar" aria-label="Resumen rápido">
          <span class="pill"><strong>Clave:</strong> el desarrollador responde por su app</span>
          <span class="pill"><strong>Seguridad:</strong> secretos no se publican</span>
          <span class="pill"><strong>Abuso:</strong> se sanciona sin aviso si hay riesgo</span>
        </div>
      </div>
    </header>

    <nav aria-label="Índice">
      <a href="#puntos-clave">Puntos clave</a>
      <a href="#definiciones">Definiciones</a>
      <a href="#responsabilidad">Responsabilidad</a>
      <a href="#scopes">Scopes</a>
      <a href="#seguridad">Seguridad</a>
      <a href="#limites">Límites</a>
      <a href="#prohibiciones">Prohibiciones</a>
      <a href="#sanciones">Sanciones</a>
      <a href="#privacidad">Privacidad</a>
      <a href="#reporte">Reporte</a>
    </nav>

    <section class="grid">
      <article class="card" id="puntos-clave">
        <h2>0) Puntos clave (para evitar malentendidos)</h2>

        <div class="callout danger">
          <div class="title">Lo más importante</div>
          <div class="badge"><span class="dot bad"></span>Si usas Jeval ID en tu app, <strong>tú eres el responsable</strong> (no JevalNetworks).</div>
          <div class="badge"><span class="dot bad"></span>Podemos <strong>suspender/revocar</strong> si hay abuso o riesgo, incluso <strong>sin aviso</strong>.</div>
          <div class="badge"><span class="dot warn"></span>El servicio es <strong>best-effort</strong>: puede haber cambios, mantenimiento o caídas.</div>
        </div>

        <div class="callout good">
          <div class="title">Buenas prácticas mínimas</div>
          <ul>
            <li>No publiques el <code>Client Secret</code> (ni en front-end, ni repos públicos).</li>
            <li>Pide solo los permisos necesarios (scopes mínimos).</li>
            <li>Respeta rate limits y no hagas flood.</li>
          </ul>
        </div>

        <div class="hr"></div>

        <h2>1) ¿Qué es Jeval ID?</h2>
        <p class="muted">
          Jeval ID es un sistema de autenticación tipo OAuth para iniciar sesión en servicios compatibles.
          JevalNetworks provee la infraestructura (Jeval ID), pero no controla ni administra las aplicaciones de terceros.
        </p>

        <div class="hr"></div>

        <h2 id="definiciones">2) Definiciones</h2>
        <ul>
          <li><strong>Usuario:</strong> persona que inicia sesión con Jeval ID en una app/servicio.</li>
          <li><strong>Desarrollador:</strong> quien registra una app y recibe <code>Client ID</code> y <code>Client Secret</code>.</li>
          <li><strong>Aplicación de terceros:</strong> app o sitio que NO es oficial de JevalNetworks.</li>
          <li><strong>Credenciales:</strong> llaves y tokens asociados a una app.</li>
        </ul>

        <div class="hr"></div>

        <h2 id="responsabilidad">3) Responsabilidad del desarrollador</h2>
        <p>
          El desarrollador es responsable total del uso de Jeval ID dentro de su aplicación.
          Si el desarrollador vende, monetiza o administra un servicio usando Jeval ID, JevalNetworks NO se hace responsable por:
        </p>
        <ul>
          <li>contenido, cobros, estafas, soporte, fallas o consecuencias del servicio del desarrollador,</li>
          <li>el tratamiento de datos que haga la aplicación,</li>
          <li>la conducta de usuarios dentro de esa plataforma.</li>
        </ul>

        <div class="hr"></div>

        <h2 id="scopes">4) Permisos y acceso (Scopes)</h2>
        <p>
          Jeval ID puede entregar datos según permisos solicitados por la aplicación (scopes).
          Regla: la aplicación debe pedir <strong>solo lo mínimo necesario</strong>.
        </p>
        <div class="callout">
          <div class="title">Ejemplos de scopes (ajusta a tu implementación real)</div>
          <ul>
            <li><code>basic</code>: id y nombre visible</li>
            <li><code>email</code>: correo (si aplica)</li>
            <li><code>profile</code>: datos públicos adicionales</li>
          </ul>
          <p class="small">JevalNetworks puede limitar o cambiar scopes por seguridad o mantenimiento.</p>
        </div>

        <div class="hr"></div>

        <h2 id="seguridad">5) Seguridad: Client Secret y tokens</h2>
        <ul>
          <li>El <code>Client Secret</code> es secreto: no se publica, no se comparte, no se sube a repos públicos.</li>
          <li>No debe estar en front-end (JavaScript público, apps que cualquiera puede decompilar, etc.).</li>
          <li>Si hay filtración o sospecha: el desarrollador debe <strong>rotar</strong> credenciales.</li>
          <li>JevalNetworks puede revocar credenciales comprometidas por seguridad.</li>
        </ul>

        <div class="hr"></div>

        <h2 id="limites">6) Límites de uso (Rate limits)</h2>
        <p>
          Para proteger la infraestructura y la estabilidad del servicio, existen límites de solicitudes.
          Si se exceden, se puede aplicar:
        </p>
        <ul>
          <li>limitación temporal,</li>
          <li>bloqueo automático,</li>
          <li>suspensión o revocación.</li>
        </ul>
        <p class="small">
          (Consejo realista: define números en tu doc técnica. Acá es la regla general.)
        </p>

        <div class="hr"></div>

        <h2 id="prohibiciones">7) Prohibiciones</h2>
        <p>Se prohíbe usar Jeval ID y/o la API para:</p>
        <ol>
          <li>contenido ilegal,</li>
          <li>hacking/pentesting sin autorización expresa de JevalNetworks,</li>
          <li>bypass de seguridad, evasión de bans, automatización agresiva o abuso,</li>
          <li>phishing, suplantación, ingeniería social o apps engañosas,</li>
          <li>malware o distribución de acciones/archivos maliciosos (aunque digan “es una prueba”),</li>
          <li>hacerse pasar por JevalNetworks o presentar una app como “oficial” sin permiso,</li>
          <li>scraping masivo, brute force o intentos de romper la infraestructura.</li>
        </ol>

        <div class="callout warn">
          <div class="title">Marca y presentación</div>
          <p class="muted">
            Si tu app usa Jeval ID, puedes decir “Iniciar sesión con Jeval ID”, pero no puedes hacerte pasar por servicio oficial.
            Si usas logos/nombre “Jeval” como branding principal, necesitas permiso.
          </p>
        </div>

        <div class="hr"></div>

        <h2 id="sanciones">8) Inactividad y sanciones</h2>
        <h3>8.1 Inactividad</h3>
        <p class="muted">
          JevalNetworks puede desactivar o eliminar credenciales por inactividad prolongada,
          especialmente si la app nunca se usó, para mantener el sistema ordenado y seguro.
        </p>

        <h3>8.2 Escala de sanciones</h3>
        <ul>
          <li>Advertencia</li>
          <li>Limitación temporal</li>
          <li>Suspensión</li>
          <li>Revocación permanente</li>
        </ul>
        <p class="small">
          Si hay riesgo real para usuarios o infraestructura, JevalNetworks puede actuar sin aviso.
        </p>

        <div class="hr"></div>

        <h2 id="privacidad">9) Privacidad (resumen simple)</h2>
        <p>
          JevalNetworks puede registrar logs técnicos y de seguridad (ej: IP, timestamps, eventos de login, errores)
          para prevenir abuso, fraudes y auditoría.
        </p>
        <ul>
          <li>No se vende información personal.</li>
          <li>Retención de logs: <strong>[X días]</strong> (definir según tu política).</li>
        </ul>

        <div class="hr"></div>

        <h2 id="reporte">10) Reporte de abuso / seguridad</h2>
        <p>
          Si detectas mal uso o vulnerabilidades, repórtalo por canal oficial:
          <br />
          <strong>report-api.jeval.cl/oauth</strong>
        </p>
        <div class="callout">
          <div class="title">Incluye</div>
          <ul>
            <li>URL / app involucrada</li>
            <li>Evidencia (capturas, logs)</li>
            <li>Explicación breve</li>
          </ul>
        </div>

        <div class="hr"></div>

        <p class="small">
          Al usar Jeval ID (OAuth), el usuario y/o desarrollador acepta estos términos.
        </p>
      </article>

      <aside class="card" aria-label="Panel lateral">
        <h2>Checklist rápido (para devs)</h2>
        <div class="callout good">
          <div class="title">Antes de publicar tu app</div>
          <ul>
            <li>¿Tu <code>Client Secret</code> está solo en backend?</li>
            <li>¿Usas HTTPS?</li>
            <li>¿Scopes mínimos?</li>
            <li>¿Tienes rate limit del lado tuyo también?</li>
            <li>¿Tienes un contacto/soporte visible?</li>
          </ul>
        </div>

        <div class="callout warn">
          <div class="title">Lo que suele causar dramas</div>
          <ul>
            <li>Guardar secretos en el front-end.</li>
            <li>Pedir <code>email</code> sin necesidad.</li>
            <li>Flood / scraping / bots sin control.</li>
            <li>Login “parecido al oficial” (phishing).</li>
          </ul>
        </div>

        <div class="callout danger">
          <div class="title">Corte inmediato (ejemplos)</div>
          <ul>
            <li>Phishing / suplantación.</li>
            <li>Bypass de seguridad o brute force.</li>
            <li>Uso para malware o contenido ilegal.</li>
          </ul>
        </div>

        <div class="hr"></div>

        <h2>Notas</h2>
        <p class="muted">
          Si quieres, puedo adaptarlo a tu panel Jeval ID para que se vea igual al dashboard,
          con tu header/footer, y con links internos tipo <code>/docs</code> y <code>/support</code>.
        </p>
      </aside>
    </section>

    <footer>
      © <span id="year"></span> JevalNetworks • Jeval ID (OAuth)
    </footer>
  </main>

  <script>
    document.getElementById("year").textContent = new Date().getFullYear();
  </script>
</body>
</html>
