<?php
require_once(__DIR__ . "/../config.php");

if (!isLoggedIn() || !isAdmin($pdo)) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int)($_GET["user_id"] ?? 0);

if ($user_id <= 0) {
    $_SESSION["error"] = "Invalid user.";
    header("Location: users.php");
    exit;
}

// ---- Handle POST ----
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fname    = trim($_POST["user_fname"]    ?? "");
    $lname    = trim($_POST["user_lname"]    ?? "");
    $email    = trim($_POST["user_email"]    ?? "");
    $phone    = trim($_POST["user_phone"]    ?? "");
    $dob      = trim($_POST["user_dob"]      ?? "");
    $verified = isset($_POST["user_verified"]) ? 1 : 0;
    $is_admin = isset($_POST["user_admin"])    ? 1 : 0;

    if ($fname === "" || $lname === "" || $email === "") {
        $_SESSION["error"] = "Name and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["error"] = "Please enter a valid email.";
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE users
                SET user_fname = ?, user_lname = ?, user_email = ?, user_phone = ?,
                    user_dob = ?, user_verified = ?, user_admin = ?
                WHERE user_id = ?
            ");
            $stmt->execute([$fname, $lname, $email, $phone, $dob, $verified, $is_admin, $user_id]);
            $_SESSION["message"] = "User updated successfully.";
            header("Location: users.php");
            exit;
        } catch (Throwable $e) {
            error_log("Edit user failed: " . $e->getMessage());
            $_SESSION["error"] = "Could not update user.";
        }
    }
}

// ---- Load user ----
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION["error"] = "User not found.";
    header("Location: users.php");
    exit;
}

$admin_page  = "users";
$admin_title = "Edit User";
$admin_sub   = "Editing " . htmlspecialchars(($user["user_fname"] ?? "") . " " . ($user["user_lname"] ?? ""));
include(__DIR__ . "/includes/header.php");
?>

<?php if (isset($_SESSION["error"])): ?>
  <div class="admin-flash"><?= htmlspecialchars($_SESSION["error"]) ?></div>
  <?php unset($_SESSION["error"]); ?>
<?php endif; ?>

<section class="admin-table-card" style="padding: 28px 32px; max-width: 720px;">
  <form method="POST" novalidate>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px;">
      <label style="display:flex; flex-direction:column; gap:6px;">
        <span style="font-size:12px; color:#64748b; text-transform:uppercase; letter-spacing:0.08em;">First name</span>
        <input type="text" name="user_fname" value="<?= htmlspecialchars($user["user_fname"] ?? "") ?>" required
               style="padding:10px 12px; border:1px solid #e2e8f0; border-radius:8px;">
      </label>
      <label style="display:flex; flex-direction:column; gap:6px;">
        <span style="font-size:12px; color:#64748b; text-transform:uppercase; letter-spacing:0.08em;">Last name</span>
        <input type="text" name="user_lname" value="<?= htmlspecialchars($user["user_lname"] ?? "") ?>" required
               style="padding:10px 12px; border:1px solid #e2e8f0; border-radius:8px;">
      </label>
    </div>

    <label style="display:flex; flex-direction:column; gap:6px; margin-top:18px;">
      <span style="font-size:12px; color:#64748b; text-transform:uppercase; letter-spacing:0.08em;">Email</span>
      <input type="email" name="user_email" value="<?= htmlspecialchars($user["user_email"] ?? "") ?>" required
             style="padding:10px 12px; border:1px solid #e2e8f0; border-radius:8px;">
    </label>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-top:18px;">
      <label style="display:flex; flex-direction:column; gap:6px;">
        <span style="font-size:12px; color:#64748b; text-transform:uppercase; letter-spacing:0.08em;">Phone</span>
        <input type="text" name="user_phone" value="<?= htmlspecialchars($user["user_phone"] ?? "") ?>"
               style="padding:10px 12px; border:1px solid #e2e8f0; border-radius:8px;">
      </label>
      <label style="display:flex; flex-direction:column; gap:6px;">
        <span style="font-size:12px; color:#64748b; text-transform:uppercase; letter-spacing:0.08em;">Date of birth</span>
        <input type="date" name="user_dob" value="<?= htmlspecialchars($user["user_dob"] ?? "") ?>"
               style="padding:10px 12px; border:1px solid #e2e8f0; border-radius:8px;">
      </label>
    </div>

    <div style="display:flex; gap:32px; margin-top:22px;">
      <label style="display:flex; gap:8px; align-items:center;">
        <input type="checkbox" name="user_verified" value="1" <?= !empty($user["user_verified"]) ? "checked" : "" ?>>
        <span>Verified</span>
      </label>
      <label style="display:flex; gap:8px; align-items:center;">
        <input type="checkbox" name="user_admin" value="1" <?= !empty($user["user_admin"]) ? "checked" : "" ?>>
        <span>Admin</span>
      </label>
    </div>

    <p style="margin-top:22px; padding:12px; background:#f8fafc; border-radius:8px; font-size:12px; color:#64748b;">
      <i class="fa fa-lock"></i>
      Password changes are handled through the forgot-password flow — admins cannot set user passwords directly.
    </p>

    <div style="display:flex; gap:10px; margin-top:24px;">
      <button type="submit" class="btn-primary-pill">Save changes</button>
      <a href="users.php" class="row-action" style="padding: 12px 20px;">Cancel</a>
    </div>
  </form>
</section>

<?php include(__DIR__ . "/includes/footer.php"); ?>