<?php
// eula.php — Términos de Uso e Infraestructura Jeval (JevalID & APIs)
$ultimaActualizacion = '30 de enero de 2026';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Términos de Uso — Infraestructura Jeval</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
          content="EULA y política de uso de la infraestructura Jeval (JevalID, APIs, OAuth y servicios asociados), con roles de usuario, developer y admin, política de eliminación de cuentas y uso de logs.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #05060a;
            color: #e5e5e5;
        }
        .eula-container {
            max-width: 1100px;
        }
        h1, h2, h3 {
            scroll-margin-top: 80px;
        }
        .section-title {
            border-left: 4px solid #0d6efd;
            padding-left: .6rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        pre, code {
            background: #11151c;
            color: #e5e5e5;
        }
        pre {
            padding: 0.75rem;
            border-radius: .5rem;
            font-size: .9rem;
            overflow-x: auto;
        }
        .badge-role {
            font-size: .75rem;
        }
        a {
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .toc a {
            font-size: .9rem;
        }
    </style>
</head>
<body>
<header class="py-3 border-bottom border-secondary mb-3">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h4 mb-0">Infraestructura Jeval — Términos de Uso</h1>
            <small class="text-muted">JevalID, APIs y servicios asociados</small>
        </div>
        <div class="text-end">
            <small class="text-muted">Última actualización:<br><?= htmlspecialchars($ultimaActualizacion, ENT_QUOTES, 'UTF-8') ?></small>
        </div>
    </div>
</header>

<main class="container eula-container pb-5">
    <div class="row">
        <!-- Índice -->
        <aside class="col-12 col-lg-3 mb-4">
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <h2 class="h6 mb-3">Índice</h2>
                    <nav class="toc">
                        <ul class="list-unstyled mb-0">
                            <li><a href="#alcance">1. Alcance del acuerdo</a></li>
                            <li><a href="#principios">2. Principios generales</a></li>
                            <li><a href="#roles">3. Roles de cuenta</a></li>
                            <li><a href="#apis">4. Uso de APIs y OAuth</a></li>
                            <li><a href="#logs">5. Logs y seguridad</a></li>
                            <li><a href="#eliminacion">6. Eliminación de cuentas</a></li>
                            <li><a href="#base-legal">7. Base legal (Chile)</a></li>
                            <li><a href="#responsabilidad">8. Limitación de responsabilidad</a></li>
                            <li><a href="#cambios">9. Cambios en el acuerdo</a></li>
                            <li><a href="#aceptacion">10. Aceptación</a></li>
                            <li><a href="#contacto">11. Contacto</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </aside>

        <!-- Contenido -->
        <section class="col-12 col-lg-9">

            <div class="alert alert-info small">
                Este documento regula el uso de la infraestructura <strong>Jeval</strong> (JevalID, APIs, OAuth y servicios
                asociados). Al crear una cuenta o utilizar estos servicios, aceptas íntegramente estas condiciones.
            </div>

            <!-- 1. Alcance -->
            <h2 id="alcance" class="section-title">1. Alcance del acuerdo</h2>
            <p>
                Este acuerdo se aplica al uso de:
            </p>
            <ul>
                <li><strong>JevalID</strong>: sistema de identidad y autenticación centralizado.</li>
                <li><strong>APIs de Jeval</strong>: servicios consumidos mediante tokens.</li>
                <li><strong>OAuth / JevalID Login</strong>: inicio de sesión en aplicaciones externas usando JevalID.</li>
                <li><strong>Paneles y herramientas</strong>: dashboards de usuario, developer y administración.</li>
                <li><strong>Servicios asociados</strong>: actuales y futuros dentro de la infraestructura Jeval/JevalNetwork.</li>
            </ul>
            <p>
                Al crear una cuenta, solicitar acceso developer o usar cualquier servicio cubierto por este documento,
                confirmas que has leído y aceptado estas condiciones.
            </p>

            <!-- 2. Principios generales -->
            <h2 id="principios" class="section-title">2. Principios generales (equilibrio 50/50)</h2>
            <p>
                Jeval opera bajo un equilibrio entre:
            </p>
            <ul>
                <li><strong>Derechos del usuario</strong>: acceso razonable, transparencia y opción de eliminación de datos personales.</li>
                <li><strong>Protección de la infraestructura</strong>: evitar abusos, ataques y uso malicioso de servicios compartidos.</li>
            </ul>
            <p>
                El usuario reconoce que:
            </p>
            <blockquote class="border-start border-secondary ps-3">
                Está utilizando infraestructura ajena y compartida, que puede afectar a otros usuarios y servicios si es
                mal utilizada.
            </blockquote>

            <!-- 3. Roles -->
            <h2 id="roles" class="section-title">3. Roles de cuenta y responsabilidades</h2>

            <h3 class="h5 mt-3">
                3.1 Rol <span class="badge bg-secondary badge-role">user</span>
            </h3>
            <p>
                Es el rol por defecto de cualquier cuenta JevalID. Permite:
            </p>
            <ul>
                <li>Iniciar sesión en servicios compatibles.</li>
                <li>Gestionar datos básicos de perfil.</li>
            </ul>
            <p>
                No otorga acceso a funciones de administración ni a la gestión avanzada de APIs.
            </p>

            <h3 class="h5 mt-3">
                3.2 Rol <span class="badge bg-info text-dark badge-role">developer_api</span>
            </h3>
            <div class="alert alert-warning small mb-2">
                <strong>Aviso importante:</strong> las cuentas <code>developer_api</code> son cuentas especiales orientadas
                a desarrolladores. No son juguetes ni cuentas de pruebas sin consecuencia.
            </div>
            <p>
                Este rol permite:
            </p>
            <ul>
                <li>Crear aplicaciones OAuth (Client ID / Client Secret) en el Developer Dashboard.</li>
                <li>Emitir tokens de API vinculados a su cuenta.</li>
                <li>Integrar JevalID Login en servicios externos.</li>
            </ul>
            <p>
                El titular de una cuenta <code>developer_api</code> entiende y acepta que:
            </p>
            <ul>
                <li>Está utilizando APIs e infraestructura compartida con otros usuarios.</li>
                <li>Un uso incorrecto puede causar interrupciones, fallos o problemas de seguridad para otros.</li>
                <li>Es responsable del comportamiento de las aplicaciones que desarrolla utilizando estas APIs.</li>
            </ul>
            <p>
                Jeval podrá, a su exclusivo criterio y sin aviso previo cuando exista riesgo real o inminente:
            </p>
            <ul>
                <li>Revocar tokens de API u OAuth.</li>
                <li>Limitar tasas de uso (rate limiting) o bloquear endpoints específicos.</li>
                <li>Degradar el rol del usuario a <code>user</code>.</li>
                <li>Suspender o eliminar la cuenta, según la gravedad.</li>
            </ul>

            <h3 class="h5 mt-3">
                3.3 Rol <span class="badge bg-danger badge-role">admin</span>
            </h3>
            <p>
                El rol <code>admin</code> está reservado exclusivamente para administradores de la infraestructura Jeval.
                No se asigna a petición de usuarios.
            </p>
            <p>
                Cualquier intento de:
            </p>
            <ul>
                <li>Obtener privilegios de administración sin autorización.</li>
                <li>Acceder a endpoints internos o paneles administrativos.</li>
                <li>Suplantar identidades de administradores.</li>
            </ul>
            <p>
                será considerado una violación grave de este acuerdo y puede derivar en:
            </p>
            <ul>
                <li>Suspensión inmediata de la cuenta.</li>
                <li>Bloqueo permanente de acceso a la infraestructura.</li>
                <li>Acciones adicionales si corresponde legalmente.</li>
            </ul>

            <h3 class="h5 mt-3">
                3.4 Rol técnico <span class="badge bg-secondary badge-role">eliminado</span>
            </h3>
            <p>
                Es un rol especial interno utilizado cuando una cuenta:
            </p>
            <ul>
                <li>Solicita eliminación de sus datos personales, o</li>
                <li>Es eliminada por motivos de seguridad o infracción grave.</li>
            </ul>
            <p>
                Las cuentas en este estado:
            </p>
            <ul>
                <li>No permiten iniciar sesión.</li>
                <li>No tienen acceso a servicios ni APIs.</li>
                <li>Se mantienen solo con información técnica mínima (ver sección 6).</li>
            </ul>

            <!-- 4. APIs -->
            <h2 id="apis" class="section-title">4. Uso de APIs, OAuth y tokens</h2>
            <p>
                Los tokens (API u OAuth) emitidos por Jeval:
            </p>
            <ul>
                <li>Son personales y revocables.</li>
                <li>No deben compartirse públicamente.</li>
                <li>No deben subirse a repositorios públicos ni documentos sin protección.</li>
            </ul>
            <p>
                El usuario/developer es responsable de:
            </p>
            <ul>
                <li>Proteger sus <code>client_secret</code> y tokens de acceso.</li>
                <li>Configurar correctamente los <code>redirect_uri</code> en sus apps.</li>
                <li>Respetar límites de uso y no abusar de los endpoints.</li>
            </ul>
            <p>
                Jeval no garantiza:
            </p>
            <ul>
                <li>Disponibilidad continua 24/7.</li>
                <li>Retrocompatibilidad absoluta de todas las APIs.</li>
            </ul>

            <!-- 5. Logs -->
            <h2 id="logs" class="section-title">5. Logs, monitoreo y seguridad</h2>
            <h3 class="h6 mt-2">5.1 Registro de actividad</h3>
            <p>
                Por motivos de seguridad, estabilidad y diagnóstico, Jeval puede registrar logs técnicos, incluyendo:
            </p>
            <ul>
                <li>Direcciones IP de origen.</li>
                <li>User-Agent y datos básicos del cliente.</li>
                <li>Fechas y horas de acceso.</li>
                <li>Uso de endpoints de API y OAuth.</li>
                <li>Intentos fallidos de autenticación y patrones anómalos.</li>
            </ul>
            <p>
                Estos logs se utilizan exclusivamente para:
            </p>
            <ul>
                <li>Seguridad de la infraestructura.</li>
                <li>Prevención de abusos y ataques.</li>
                <li>Diagnóstico y mejora técnica de los servicios.</li>
            </ul>
            <p>
                No se utilizan para publicidad ni venta de datos.
            </p>

            <h3 class="h6 mt-3">5.2 Solicitud de tratamiento especial de logs</h3>
            <p>
                El usuario puede solicitar un tratamiento más restringido de sus logs (por ejemplo, minimizar retención),
                entendiendo que:
            </p>
            <ul>
                <li>Debe hacerlo mediante una solicitud explícita por los canales de contacto.</li>
                <li>Jeval evaluará si es compatible con la seguridad y operación del sistema.</li>
                <li>Jeval puede rechazar la solicitud si compromete la estabilidad o seguridad.</li>
            </ul>

            <!-- 6. Eliminación -->
            <h2 id="eliminacion" class="section-title">6. Política de eliminación de cuentas</h2>
            <h3 class="h6 mt-2">6.1 Solicitud de eliminación</h3>
            <p>
                El titular de una cuenta puede solicitar la eliminación de su cuenta JevalID y de sus datos personales.
            </p>

            <h3 class="h6 mt-3">6.2 Proceso y estado eliminado</h3>
            <p>
                Al aceptarse la solicitud:
            </p>
            <ul>
                <li>La cuenta pasa a estado <code>eliminado</code>.</li>
                <li>Se eliminan o anonimizan:
                    <ul>
                        <li>Correo electrónico.</li>
                        <li>Nombre y datos de perfil.</li>
                        <li>Contraseña y credenciales de acceso.</li>
                        <li>Tokens activos y clientes OAuth asociados.</li>
                    </ul>
                </li>
                <li>Se conserva únicamente información técnica mínima:
                    <ul>
                        <li>ID interno de usuario.</li>
                        <li>Estado <code>eliminado</code>.</li>
                        <li>Logs técnicos vinculados a seguridad y auditoría.</li>
                    </ul>
                </li>
            </ul>

            <h3 class="h6 mt-3">6.3 Retención de 30 días</h3>
            <p>
                Tras la solicitud de eliminación, cierta información técnica mínima puede conservarse hasta por
                <strong>30 días</strong> para:
            </p>
            <ul>
                <li>Prevenir reuso malicioso.</li>
                <li>Investigar incidentes de seguridad si los hubiera.</li>
                <li>Asegurar la integridad del sistema durante el periodo de cierre.</li>
            </ul>
            <p>
                Durante este periodo:
            </p>
            <ul>
                <li>No se puede acceder a la cuenta.</li>
                <li>No se pueden recuperar datos personales.</li>
                <li>No existe opción de reactivación normal de la cuenta.</li>
            </ul>

            <h3 class="h6 mt-3">6.4 Eliminación o archivo definitivo</h3>
            <p>
                Transcurrido el periodo de retención:
            </p>
            <ul>
                <li>Los datos restantes podrán ser borrados o archivados de manera irreversible.</li>
                <li>La cuenta deja de existir a efectos prácticos.</li>
            </ul>

            <!-- 7. Base legal -->
            <h2 id="base-legal" class="section-title">7. Base legal — Legislación chilena</h2>
            <p>
                La infraestructura Jeval se rige por la legislación de la República de Chile. En materia de datos personales,
                se consideran especialmente los principios establecidos en la:
            </p>
            <ul>
                <li><strong>Ley N° 19.628</strong> sobre Protección de la Vida Privada (Chile).</li>
            </ul>
            <p>
                De acuerdo con esta normativa y principios generales de protección de datos, Jeval:
            </p>
            <ul>
                <li>Limita el uso de los datos a fines operativos, técnicos y de seguridad.</li>
                <li>Permite la solicitud de eliminación de datos personales.</li>
                <li>Implementa medidas razonables de seguridad para evitar accesos no autorizados.</li>
            </ul>

            <!-- 8. Responsabilidad -->
            <h2 id="responsabilidad" class="section-title">8. Limitación de responsabilidad</h2>
            <p>
                Jeval no será responsable de:
            </p>
            <ul>
                <li>Daños derivados del uso indebido de las APIs o tokens por parte de terceros.</li>
                <li>Pérdida de datos causada por configuraciones erróneas del usuario o de sus aplicaciones.</li>
                <li>Interrupciones del servicio derivadas de mantenimiento, ataques o fuerza mayor.</li>
            </ul>
            <p>
                El usuario acepta que el uso de la infraestructura conlleva riesgos técnicos y se compromete a usarla de
                forma responsable.
            </p>

            <!-- 9. Cambios -->
            <h2 id="cambios" class="section-title">9. Cambios en el acuerdo</h2>
            <p>
                Jeval podrá modificar estos términos cuando sea necesario para:
            </p>
            <ul>
                <li>Adaptarse a cambios técnicos.</li>
                <li>Cumplir nuevas exigencias legales.</li>
                <li>Mejorar la seguridad y estabilidad del sistema.</li>
            </ul>
            <p>
                Cuando corresponda, se comunicará de forma razonable. El uso continuado de los servicios implica la
                aceptación de las versiones actualizadas del acuerdo.
            </p>

            <!-- 10. Aceptación -->
            <h2 id="aceptacion" class="section-title">10. Aceptación explícita</h2>
            <p>
                Al crear una cuenta JevalID, utilizar JevalID para iniciar sesión en otros servicios, solicitar el rol
                <code>developer_api</code> o usar las APIs de Jeval, declaras que:
            </p>
            <blockquote class="border-start border-secondary ps-3">
                Has leído, comprendido y aceptado este acuerdo en su totalidad, incluyendo las
                responsabilidades asociadas a los roles especiales y la política de eliminación de cuentas.
            </blockquote>

            <!-- 11. Contacto -->
            <h2 id="contacto" class="section-title">11. Contacto y solicitudes</h2>
            <p>
                Para:
            </p>
            <ul>
                <li>Solicitar eliminación de cuenta.</li>
                <li>Realizar consultas sobre privacidad o tratamiento de datos.</li>
                <li>Plantear dudas sobre el uso de APIs o roles especiales.</li>
            </ul>
            <p>
                Deberás utilizar los canales oficiales de contacto definidos por Jeval/JevalNetwork, que podrán incluir
                formularios web, correo específico o sistemas de soporte designados.
            </p>

            <div class="mt-4">
                <a href="javascript:history.back()" class="btn btn-outline-light btn-sm">
                    ← Volver a la página anterior
                </a>
            </div>

        </section>
    </div>
</main>
</body>
</html>
