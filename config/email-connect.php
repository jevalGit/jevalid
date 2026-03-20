<?php
// config/email-connect.php

// Cargar PHPMailer desde /library
require_once __DIR__ . '/../library/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../library/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../library/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/*
 |------------------------------------------------------------------
 | CONFIGURACIÓN SMTP
 |------------------------------------------------------------------
 | AJUSTA ESTO A LOS DATOS REALES DE TU SERVIDOR DE CORREO.
 */

const SMTP_HOST       = 'non.non.com';      // host SMTP real
const SMTP_PORT       = 587;                  // 587 STARTTLS, 465 SMTPS
const SMTP_USER       = 'non@non.com';  // usuario SMTP
const SMTP_PASS       = 'pass'; // contraseña SMTP

// Tipo de cifrado: STARTTLS (587) o SMTPS (465)
const SMTP_ENCRYPTION = PHPMailer::ENCRYPTION_STARTTLS;

// Datos del remitente por defecto
const SMTP_FROM_EMAIL = 'no-reply@jeval.cl';
const SMTP_FROM_NAME  = 'Jeval ID';

/**
 * Envía un correo usando el servidor SMTP configurado.
 */
function email_send(
    string $toEmail,
    string $toName,
    string $subject,
    string $htmlBody,
    ?string $plainBody = null
): bool {
    $mail = new PHPMailer(true);

    $mail->SMTPDebug = 2;
    $mail->Debugoutput = 'html';

    try {
        // Config servidor
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->Port       = SMTP_PORT;
        $mail->SMTPSecure = SMTP_ENCRYPTION;

        $mail->CharSet = 'UTF-8';

        // Remitente & destinatario
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $plainBody ?: strip_tags($htmlBody);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Error enviando correo: ' . $mail->ErrorInfo);
        return false;
    }
}
