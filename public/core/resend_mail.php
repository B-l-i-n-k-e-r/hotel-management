<?php
require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/mail.php");

if (!isLoggedIn()) {
    header("Location: " . SROOT . "/login.php");
    exit;
}

$email = $_SESSION["user_email"];

try {
    // Regenerate a fresh verification hash
    $hash = bin2hex(random_bytes(32));
    $upd = $pdo->prepare("UPDATE users SET verification_hash = ?, user_verified = 0 WHERE user_email = ?");
    $upd->execute([$hash, $email]);

    $link = SROOT . "/verify_user.php?email=" . urlencode($email) . "&hash=" . $hash;
    $name = $_SESSION["user_fname"] ?? "there";

    $html = "
      <h2>Verify your V&N Hotel account</h2>
      <p>Hi " . htmlspecialchars($name) . ",</p>
      <p>Click the link below to verify your email:</p>
      <p><a href=\"{$link}\">Verify my account</a></p>
      <p>Or paste this into your browser:<br>{$link}</p>
    ";

    $result = send_mail($email, "Verify your V&N Hotel account", $html, MAIL_ADMIN, $name);
    $_SESSION["message"] = $result["ok"]
        ? "Verification email resent to {$email}."
        : "Could not send the email. Please try again.";
} catch (Throwable $e) {
    error_log("Resend verification failed: " . $e->getMessage());
    $_SESSION["message"] = "Something went wrong. Please try again.";
}

header("Location: " . SROOT . "/verify_user.php");
exit;