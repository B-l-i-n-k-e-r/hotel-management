<?php
require_once(__DIR__ . "/config.php");
include(__DIR__ . "/includes/header.php");

$token   = $_GET['token'] ?? "";
$error   = "";
$success = "";
$user    = null;

if ($token === "") {
    $error = "Missing reset token.";
} else {
    try {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) $error = "Invalid or expired token. Please request a new password reset link.";
    } catch (Throwable $e) {
        $error = "Could not verify token.";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$error) {
    $new_password     = $_POST['password'] ?? "";
    $confirm_password = $_POST['confirm_password'] ?? "";

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        try {
            $hashed = hash("sha256", $new_password);
            $upd = $pdo->prepare("UPDATE users SET user_password = ?, reset_token = NULL, reset_expires = NULL WHERE user_id = ?");
            $upd->execute([$hashed, $user["user_id"]]);
            $success = "Password updated successfully! <a href='login.php'>Login here</a>";
        } catch (Throwable $e) {
            $error = "Could not update your password. Please try again.";
        }
    }
}
?>

<body class="has-nav has-hero">
  <header class="page-header">
    <?php include(__DIR__ . "/includes/navbar.php"); ?>
    <div class="page-header-inner container">
      <span class="eyebrow light">Account recovery</span>
      <h1>Set New Password</h1>
      <p>Choose a strong password you haven't used before.</p>
    </div>
  </header>

  <main>
    <section class="container section-pad">
      <div class="survey-card" style="max-width: 480px; margin: 0 auto;">

        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
          <div class="alert alert-success"><?= $success ?></div>
        <?php elseif (!$error): ?>
          <form method="POST" action="?token=<?= htmlspecialchars($token) ?>" novalidate>

            <div class="survey-question">
              <label for="password">New Password</label>
              <input type="password" name="password" id="password" class="form-control"
                     placeholder="At least 6 characters" required>
            </div>

            <div class="survey-question">
              <label for="confirm_password">Confirm New Password</label>
              <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                     placeholder="Repeat your password" required>
            </div>

            <div class="survey-actions">
              <button type="submit" class="btn-primary-pill" style="width:100%;">
                Update Password
              </button>
            </div>

            <div class="alternate-auth" style="margin-top:16px; text-align:center;">
              <span>Remembered it? <a href="<?= SROOT ?>/login.php">Back to login</a></span>
            </div>
          </form>
        <?php else: ?>
          <div class="alternate-auth" style="text-align:center;">
            <a href="<?= SROOT ?>/forgot_password.php">Request a new reset link</a>
          </div>
        <?php endif; ?>

      </div>
    </section>
  </main>

  <?php include(__DIR__ . "/includes/footer.php"); ?>
</body>
</html>