<?php
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/core/mail.php");

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = "Security validation failed. Please try again.";
        $message_type = "danger";
    } else {
        $email = trim($_POST['email'] ?? "");

        if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Please enter a valid email address.";
            $message_type = "danger";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT user_id, user_fname FROM users WHERE user_email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Always show the same message — don't reveal if email exists
                $message = "If that email exists in our system, a password reset link has been sent.";
                $message_type = "success";

                if ($user) {
                    $token   = bin2hex(random_bytes(32));
                    $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

                    $upd = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE user_id = ?");
                    $upd->execute([$token, $expires, $user["user_id"]]);

                    $link = SROOT . "/reset_password.php?token=" . $token;

                    $html = "
                      <h2>Password reset requested</h2>
                      <p>Hi " . htmlspecialchars($user["user_fname"]) . ",</p>
                      <p>We received a request to reset your V&N Hotel password. Click the link below to set a new one. This link expires in 1 hour.</p>
                      <p><a href=\"{$link}\">Reset my password</a></p>
                      <p>Or paste this into your browser:<br>{$link}</p>
                      <p>If you didn't request this, you can safely ignore this email.</p>
                    ";

                    $result = send_mail($email, "Reset your V&N Hotel password", $html, MAIL_ADMIN, $user["user_fname"]);
                    if (!$result["ok"]) {
                        error_log("Password reset email failed for {$email}: " . $result["error"]);
                    }
                }
            } catch (Throwable $e) {
                error_log("Forgot password error: " . $e->getMessage());
                $message = "Something went wrong. Please try again later.";
                $message_type = "danger";
            }
        }
    }
}
?>
<?php include(__DIR__ . "/includes/header.php"); ?>

<main class="auth-page-container">
    <div class="auth-card">
        <h2>Reset Password</h2>
        <p class="auth-sub">Enter your registered email address and we'll send you a link to reset your password.</p>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" class="form-control"
                       placeholder="you@example.com" autocomplete="email" required>
            </div>

            <button type="submit" class="btn-primary-pill">Send Reset Link</button>
        </form>

        <a href="login.php" class="auth-back-link">
            <i class="fa fa-arrow-left"></i> Back to Login
        </a>
    </div>
</main>

<?php include(__DIR__ . "/includes/footer.php"); ?>