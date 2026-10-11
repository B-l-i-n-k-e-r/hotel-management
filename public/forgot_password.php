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

                $message = "A password reset link has been sent to your email.";
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

<style>
  /* ===== Forgot password page ===== */
  #forgot-wrap {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background:
      linear-gradient(180deg, rgba(0,0,0,0.55) 0%, rgba(0,0,0,0.65) 100%),
      url("./media/images/backgrounds/home-1.jpg") center/cover no-repeat;
    padding: 140px 20px 80px;
    position: relative;
  }

  #forgot-wrap .site-nav {
    position: absolute;
    top: 0; left: 0; right: 0;
  }

  .forgot-card {
    width: 100%;
    max-width: 520px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 24px 60px rgba(0,0,0,0.25);
    padding: 40px 36px;
    text-align: center;
    margin: 0 auto;
  }

  .forgot-card .eyebrow {
    display: inline-block;
    font-size: 12px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #9a7b4f;
    font-weight: 600;
    margin-bottom: 12px;
  }

  .forgot-card h2 {
    font-size: 26px;
    line-height: 1.25;
    margin: 0 0 10px;
    color: #1c1c1c;
    font-weight: 700;
  }

  .forgot-card .sub {
    color: #5b5b5b;
    font-size: 15px;
    line-height: 1.6;
    margin: 0 0 24px;
  }

  .forgot-card .form-group {
    text-align: left;
    margin-bottom: 20px;
  }

  .forgot-card label {
    display: block;
    font-size: 12px;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #64748b;
    font-weight: 600;
    margin-bottom: 8px;
  }

  .forgot-card input.form-control {
    width: 100%;
    padding: 14px 16px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-size: 15px;
    color: #0f172a;
    background: #fff;
    outline: none;
    transition: border-color 0.15s ease;
  }

  .forgot-card input.form-control:focus {
    border-color: #9a7b4f;
  }

  .forgot-card .btn-primary-pill {
    width: 100%;
    margin-top: 4px;
  }

  .forgot-card .auth-back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 22px;
    color: #64748b;
    font-size: 13px;
    text-decoration: none;
    transition: color 0.15s ease;
  }

  .forgot-card .auth-back-link:hover { color: #0f172a; }

  .forgot-card .alert {
    text-align: left;
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 14px;
    margin-bottom: 20px;
  }
  .forgot-card .alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
  .forgot-card .alert-danger  { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

  @media (max-width: 480px) {
    .forgot-card { padding: 30px 22px; }
    .forgot-card h2 { font-size: 22px; }
  }
</style>

<body class="has-nav has-hero">
  <header id="forgot-wrap">
    <?php include(__DIR__ . "/includes/navbar.php"); ?>

    <div class="forgot-card">
      <span class="eyebrow">Account recovery</span>
      <h2>Reset Password</h2>
      <p class="sub">Enter your registered email address and we'll send you a link to reset your password.</p>

      <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" name="email" id="email" class="form-control"
                 placeholder="you@example.com" autocomplete="email" required>
        </div>

        <button type="submit" class="btn-primary-pill">Send Reset Link</button>
      </form>

      <a href="login.php" class="auth-back-link">
        <i class="fa fa-arrow-left"></i> Back to Login
      </a>
    </div>
  </header>

  <main></main>

  <?php include(__DIR__ . "/includes/footer.php"); ?>
</body>
</html>