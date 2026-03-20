<?php
// config/email_verification_config.php

require_once __DIR__ . '/email-connect.php';

// URL base de tu Jeval ID (ajústala a tu dominio real)
const EMAIL_VERIF_BASE_URL = 'https://id.jeval.cl';

// Tiempo de vida del token en minutos
const EMAIL_VERIF_TOKEN_LIFETIME = 60; // 1 hora

/**
 * Genera un token aleatorio seguro.
 */
function ev_generate_token(): string
{
    return bin2hex(random_bytes(32)); // 64 caracteres hex
}

/**
 * Envía el correo de verificación.
 *
 * @param PDO    $pdo
 * @param int    $userId
 * @param string $email
 * @param string $nombre
 */
function ev_send_verification_email(PDO $pdo, int $userId, string $email, string $nombre): bool
{
    // 1) Generar token y fecha de expiración
    $token = ev_generate_token();
    $expiresAt = (new DateTime('+'.EMAIL_VERIF_TOKEN_LIFETIME.' minutes'))
                    ->format('Y-m-d H:i:s');

    // 2) Guardar en la base de datos
    $stmt = $pdo->prepare("
        INSERT INTO email_verification_tokens (user_id, token, expires_at)
        VALUES (:user_id, :token, :expires_at)
    ");
    $stmt->execute([
        ':user_id'    => $userId,
        ':token'      => $token,
        ':expires_at' => $expiresAt,
    ]);

    // 3) Construir enlace de verificación
    $verificationLink = EMAIL_VERIF_BASE_URL . '/verification/email/?token=' . urlencode($token);

    // 4) Cuerpo del correo (HTML + texto plano)
    $subject = 'Verifica tu correo en Jeval ID';

    $htmlBody = '
        <p>Hola ' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . ',</p>
        <p>Para terminar de activar la seguridad de tu cuenta, verifica tu correo electrónico.</p>
        <p>
            Haz clic en este enlace (o cópialo en tu navegador):<br>
            <a href="' . htmlspecialchars($verificationLink, ENT_QUOTES, 'UTF-8') . '">' .
            htmlspecialchars($verificationLink, ENT_QUOTES, 'UTF-8') .
            '</a>
        </p>
        <p>Este enlace es válido por ' . EMAIL_VERIF_TOKEN_LIFETIME . ' minutos.</p>
        <p>Si tú no solicitaste esto, puedes ignorar el mensaje.</p>
        <p>Jeval ID</p>
    ';

    $plainBody = "Hola {$nombre},\n\n"
               . "Para terminar de activar la seguridad de tu cuenta, verifica tu correo electrónico.\n\n"
               . "Enlace de verificación:\n"
               . "{$verificationLink}\n\n"
               . "Este enlace es válido por " . EMAIL_VERIF_TOKEN_LIFETIME . " minutos.\n\n"
               . "Si tú no solicitaste esto, puedes ignorar el mensaje.\n\n"
               . "Jeval ID";

    // 5) Enviar usando el servidor SMTP de email-connect.php
    return email_send($email, $nombre, $subject, $htmlBody, $plainBody);
}
