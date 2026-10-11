<?php
require_once(__DIR__ . "/config.php");

// Default status
$status = ["verified" => null]; // null = unknown, true = verified, false = invalid link

// --- Case 1: user is logged in ---
if (isLoggedIn()) {
    $verification = isUserVerified($pdo, $_SESSION["user_email"]);

    // If already verified, send them home
    if (!empty($verification["verified"]) && $verification["verified"] == 1) {
        header("Location: index.php");
        exit;
    }
    // Otherwise: fall through so they see the "check your email" screen
}

// --- Case 2: user clicked a verification link ---
if (isset($_GET["email"], $_GET["hash"])) {
    $user_email        = $_GET["email"];
    $verification_hash = $_GET["hash"];

    // Look for a matching user with a non-expired verification
    $sql = "SELECT user_id FROM users
            WHERE user_email = :email
              AND verification_hash = :hash
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":email" => $user_email,
        ":hash"  => $verification_hash,
    ]);

    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        // Match found — mark as verified
        $update = $pdo->prepare("
            UPDATE users
            SET user_verified = 1, verification_hash = ''
            WHERE user_email = :email AND verification_hash = :hash
        ");
        $update->execute([
            ":email" => $user_email,
            ":hash"  => $verification_hash,
        ]);

        $_SESSION["message"] = "Your account is now verified. You can log in.";

        // If they're already logged in, send home; otherwise to login
        if (isLoggedIn()) {
            header("Location: index.php");
        } else {
            header("Location: login.php");
        }
        exit;
    }

    // No match — invalid link
    $status["verified"] = false;
}
?>
<?php include(__DIR__ . "/includes/header.php"); ?>

<style>
  /* ===== Verify page – matches system UI ===== */
  #verify-user {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background:
      linear-gradient(180deg, rgba(0,0,0,0.55) 0%, rgba(0,0,0,0.65) 100%),
      url("./media/images/backgrounds/home-1.jpg") center/cover no-repeat;
    padding: 120px 20px 80px;
  }

  .verify-card {
    width: 100%;
    max-width: 520px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 24px 60px rgba(0,0,0,0.25);
    padding: 40px 36px;
    text-align: center;
  }

  .verify-card .eyebrow {
    display: inline-block;
    font-size: 12px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #9a7b4f;
    font-weight: 600;
    margin-bottom: 12px;
  }

  .verify-card h2 {
    font-size: 26px;
    line-height: 1.25;
    margin: 0 0 12px;
    color: #1c1c1c;
    font-weight: 700;
  }

  .verify-card p {
    color: #5b5b5b;
    font-size: 15px;
    line-height: 1.6;
    margin: 0 0 12px;
  }

  .verify-card p.text-muted {
    color: #8a8a8a !important;
    font-size: 13.5px;
  }

  .verify-card h3.text-danger {
    font-size: 17px;
    color: #c0392b !important;
    margin: 8px 0 10px;
    font-weight: 600;
  }

  .verify-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    margin: 0 auto 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    color: #fff;
  }

  .verify-icon.success { background: #2e8b57; }
  .verify-icon.warning { background: #c0392b; }

  .verify-card form {
    margin-top: 24px;
  }

  .verify-card .btn-resend {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #1c1c1c;
    color: #fff;
    border: none;
    border-radius: 999px;
    padding: 12px 26px;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.02em;
    cursor: pointer;
    transition: transform 0.15s ease, background 0.15s ease;
  }

  .verify-card .btn-resend:hover {
    background: #9a7b4f;
    transform: translateY(-1px);
  }

  .verify-card .btn-resend i {
    font-size: 13px;
  }

  @media (max-width: 480px) {
    .verify-card { padding: 30px 22px; }
    .verify-card h2 { font-size: 22px; }
  }
  /* ===== Verify page – matches system UI ===== */
#verify-user {
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

#verify-user--bg-image {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  flex: 1;
}

/* Keep the navbar pinned to the top edge of the hero */
#verify-user .site-nav {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
}

.verify-card {
  width: 100%;
  max-width: 520px;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 24px 60px rgba(0,0,0,0.25);
  padding: 40px 36px;
  text-align: center;
  margin: 0 auto;
}
</style>

<body class="has-nav has-hero">
  <header id="verify-user">
    <div id="verify-user--bg-image">
      <?php include(__DIR__ . "/includes/navbar.php"); ?>

      <div class="verify-card">
        <?php if ($status["verified"] === false): ?>
          <div class="verify-icon warning">
            <i class="fa fa-times"></i>
          </div>
          <span class="eyebrow">Verification failed</span>
          <h2>Invalid or expired link</h2>
          <p>The verification link you used is no longer valid. Request a new one below and check your inbox again.</p>
        <?php else: ?>
          <div class="verify-icon success">
            <i class="fa fa-envelope"></i>
          </div>
          <span class="eyebrow">Almost there</span>
          <h2>Verify your account</h2>
          <p>A verification link has been sent to your registered email address.</p>
          <p class="text-muted">
            Check your inbox (and spam folder) for an email from V&amp;N Hotel.
          </p>
        <?php endif; ?>

        <form action="<?= SROOT ?>/core/resend_mail.php" method="GET">
          <button class="btn-resend" type="submit" name="resend" value="resend">
            <i class="fa fa-paper-plane"></i> Resend verification email
          </button>
        </form>
      </div>
    </div>
  </header>

  <main></main>

  <?php include(__DIR__ . "/includes/footer.php"); ?>
</body>
</html>