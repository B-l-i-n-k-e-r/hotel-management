<?php
require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/mail.php");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendJson("Invalid request method", 1);
}

$name    = trim($_POST["name"]    ?? "");
$email   = trim($_POST["email"]   ?? "");
$phone   = trim($_POST["phone"]   ?? "");
$subject = trim($_POST["subject"] ?? "");
$message = trim($_POST["message"] ?? "");

if ($name === "" || $email === "" || $subject === "" || $message === "") {
    sendJson("Please fill in all required fields.", 1);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJson("Please enter a valid email address.", 1);
}

$user_id = isLoggedIn() ? (int)($_SESSION["user_id"] ?? 0) : null;
if ($user_id === 0) $user_id = null;

try {
    $sql = "INSERT INTO contact_messages
            (user_id, name, email, phone, subject, message)
            VALUES
            (:user_id, :name, :email, :phone, :subject, :message)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":user_id" => $user_id,
        ":name"    => $name,
        ":email"   => $email,
        ":phone"   => $phone ?: null,
        ":subject" => $subject,
        ":message" => $message,
    ]);
} catch (PDOException $e) {
    error_log("Contact insert failed: " . $e->getMessage());
    sendJson("Could not save your message. Please try again.", 1);
}

// Notify admin (best-effort — don't fail the request if email fails)
$html = "
  <h2>New customer care message</h2>
  <p><strong>Name:</strong> "    . htmlspecialchars($name)    . "</p>
  <p><strong>Email:</strong> "   . htmlspecialchars($email)   . "</p>
  <p><strong>Phone:</strong> "   . htmlspecialchars($phone ?: "—") . "</p>
  <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
  <hr>
  <p>" . nl2br(htmlspecialchars($message)) . "</p>
";

$mailResult = send_mail(
    MAIL_ADMIN,
    "[Customer Care] " . $subject,
    $html,
    $email,
    $name
);

sendJson("Thanks! We'll be in touch shortly.", 0);