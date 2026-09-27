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
            SET user_verified = 1, verification_hash = NULL
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
<body class="has-nav has-hero">
  <header id="verify-user">
    <div id="verify-user--bg-image">
      <?php include(__DIR__ . "/includes/navbar.php"); ?>
      <div class="wrapper">
        <div class="container">
          <div class="content p-5">
            <h2><b>Verify your account</b></h2>

            <?php if ($status["verified"] === false): ?>
              <h3 class="text-danger">Invalid or expired link</h3>
              <p>The verification link you used is not valid. Request a new one below.</p>
            <?php else: ?>
              <p>A verification link has been sent to your registered email address.</p>
              <p class="text-muted" style="font-size: 14px;">
                Check your inbox (and spam folder) for an email from V&amp;N Hotel.
              </p>
            <?php endif; ?>

            <form action="<?= SROOT ?>/core/resend_mail.php" method="GET">
              <button class="btn btn-outline-primary" type="submit" name="resend" value="resend">
                Resend verification email
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main></main>

  <?php include(__DIR__ . "/includes/footer.php"); ?>
</body>
</html>