<?php
require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/mail.php");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendJson("Invalid request method", 1);
}

$user_fname = trim($_POST["user_fname"] ?? "");
$user_lname = trim($_POST["user_lname"] ?? "");
$user_email = trim($_POST["user_email"] ?? "");
$user_phone = trim($_POST["user_phone"] ?? "");
$user_dob   = trim($_POST["user_dob"]   ?? "");
$user_password = $_POST["user_password"] ?? "";

if ($user_password === "") {
    sendJson("Password cannot be empty", 1, "user_password");
}

if ($user_fname === "" || $user_lname === "" || $user_email === "" || $user_phone === "" || $user_dob === "") {
    sendJson("Please fill in all fields", 1);
}

if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
    sendJson("Please enter a valid email address", 1, "user_email");
}

$pwd_hash = hash("sha256", $user_password);
$hash     = bin2hex(random_bytes(32)); // 64-char verification hash

try {
    $sql = "INSERT INTO users
            (user_fname, user_lname, user_email, user_phone, user_dob, user_password, verification_hash)
            VALUES
            (:user_fname, :user_lname, :user_email, :user_phone, :user_dob, :user_password, :verification_hash)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":user_fname"         => $user_fname,
        ":user_lname"         => $user_lname,
        ":user_email"         => $user_email,
        ":user_phone"         => $user_phone,
        ":user_dob"           => $user_dob,
        ":user_password"      => $pwd_hash,
        ":verification_hash"  => $hash,
    ]);
} catch (PDOException $e) {
    error_log("Signup failed: " . $e->getMessage());
    sendJson("Could not create your account. Please try again.", 1);
}

$_SESSION["user_id"]    = $pdo->lastInsertId();
$_SESSION["user_email"] = $user_email;
$_SESSION["user_fname"] = $user_fname;
$_SESSION["user_lname"] = $user_lname;

// Send verification email (best-effort)
$verify_link = SROOT . "/verify_user.php?email=" . urlencode($user_email) . "&hash=" . $hash;

$html = "
  <h2>Welcome to V&N Hotel, " . htmlspecialchars($user_fname) . "!</h2>
  <p>Please confirm your email address by clicking the link below:</p>
  <p><a href=\"{$verify_link}\">Verify my account</a></p>
  <p>Or paste this into your browser:<br>{$verify_link}</p>
  <p>— V&N Hotel</p>
";

$mailResult = send_mail(
    $user_email,
    "Verify your V&N Hotel account",
    $html,
    MAIL_ADMIN,
    $user_fname . " " . $user_lname
);

if (!$mailResult["ok"]) {
    error_log("Verification email failed for {$user_email}: " . $mailResult["error"]);
    // Still return success — account exists, user can hit "Resend verification"
}

sendJson("User logged in", 0);