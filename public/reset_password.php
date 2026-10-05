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

<main class="container section-pad">
    <div class="form-container" style="max-width: 400px; margin: 0 auto;">
        <h2>Set New Password</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif (!$error): ?>
            <form method="POST" action="?token=<?= htmlspecialchars($token) ?>">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="search-btn" style="width:100%; margin-top:10px;">Update Password</button>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php include(__DIR__ . "/includes/footer.php"); ?>