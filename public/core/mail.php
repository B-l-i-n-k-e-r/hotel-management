<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once(__DIR__ . "/../../vendor/autoload.php");

/**
 * Send an email through Gmail SMTP.
 *
 * @param string      $to       Recipient email
 * @param string      $subject  Subject line
 * @param string      $htmlBody HTML body
 * @param string|null $replyTo  Optional Reply-To address
 * @param string|null $toName   Optional recipient display name
 * @return array ['ok' => bool, 'error' => string|null]
 */
function send_mail($to, $subject, $htmlBody, $replyTo = null, $toName = null)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT ?: 587;
        $mail->CharSet    = "UTF-8";

        // From
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME ?: "V&N Hotel");

        // Reply-To (optional)
        if ($replyTo) {
            $mail->addReplyTo($replyTo);
        }

        // To
        $mail->addAddress($to, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);

        $mail->send();
        return ["ok" => true, "error" => null];
    } catch (Exception $e) {
        return ["ok" => false, "error" => $mail->ErrorInfo ?: $e->getMessage()];
    } catch (Throwable $e) {
        return ["ok" => false, "error" => $e->getMessage()];
    }
}