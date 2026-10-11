<?php
require_once(__DIR__ . "/../config.php");

$rows = getAllUsers($pdo) ?: [];
$total = count($rows);
$verified_count = 0;
$admin_count = 0;
foreach ($rows as $r) {
    if (!empty($r["user_verified"])) $verified_count++;
    if (!empty($r["user_admin"]))    $admin_count++;
}

$admin_page  = "users";
$admin_title = "Users";
$admin_sub   = $total . " registered user" . ($total === 1 ? "" : "s");

include(__DIR__ . "/includes/header.php");

if (!isLoggedIn() || !isAdmin($pdo)) {
    header("Location: ../login.php");
    exit;
}
?>

<section class="admin-kpis">
  <article class="kpi-card kpi-rooms">
    <div class="kpi-icon"><i class="fa fa-users"></i></div>
    <div class="kpi-info">
      <span class="kpi-label">Total users</span>
      <span class="kpi-value"><?= (int)$total ?></span>
    </div>
  </article>
  <article class="kpi-card kpi-reservations">
    <div class="kpi-icon"><i class="fa fa-check"></i></div>
    <div class="kpi-info">
      <span class="kpi-label">Verified</span>
      <span class="kpi-value"><?= (int)$verified_count ?></span>
    </div>
  </article>
  <article class="kpi-card kpi-users">
    <div class="kpi-icon"><i class="fa fa-shield"></i></div>
    <div class="kpi-info">
      <span class="kpi-label">Admins</span>
      <span class="kpi-value"><?= (int)$admin_count ?></span>
    </div>
  </article>
</section>

<?php if (isset($_SESSION["message"])): ?>
  <div class="admin-flash admin-flash-success"><?= htmlspecialchars($_SESSION["message"]) ?></div>
  <?php unset($_SESSION["message"]); ?>
<?php endif; ?>
<?php if (isset($_SESSION["error"])): ?>
  <div class="admin-flash"><?= htmlspecialchars($_SESSION["error"]) ?></div>
  <?php unset($_SESSION["error"]); ?>
<?php endif; ?>

<section class="admin-table-card">
  <?php if (empty($rows)): ?>
    <div class="admin-empty">
      <div class="admin-empty-icon"><i class="fa fa-users"></i></div>
      <h2>No users yet</h2>
      <p>Registered users will appear here.</p>
    </div>
  <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Date of birth</th>
            <th>Verified</th>
            <th>Role</th>
            <th class="actions">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td data-label="#"><strong><?= (int)$row["user_id"] ?></strong></td>
              <td data-label="Name"><strong><?= htmlspecialchars(trim(($row["user_fname"] ?? "") . " " . ($row["user_lname"] ?? ""))) ?: "—" ?></strong></td>
              <td data-label="Email"><?= htmlspecialchars($row["user_email"] ?? "—") ?></td>
              <td data-label="Phone"><?= htmlspecialchars($row["user_phone"] ?? "—") ?></td>
              <td data-label="Date of birth"><?= !empty($row["user_dob"]) ? htmlspecialchars($row["user_dob"]) : "—" ?></td>
              <td data-label="Verified">
                <?php if (!empty($row["user_verified"])): ?>
                  <span class="pill pill-success">Verified</span>
                <?php else: ?>
                  <span class="pill pill-danger-soft">Unverified</span>
                <?php endif; ?>
              </td>
              <td data-label="Role">
                <?php if (!empty($row["user_admin"])): ?>
                  <span class="pill pill-neutral">Admin</span>
                <?php else: ?>
                  <span class="pill pill-muted">User</span>
                <?php endif; ?>
              </td>
              <td data-label="Actions" class="actions">
                <?php if (!empty($row["user_admin"])): ?>
                  <a href="core/toggle_admin.php?user_id=<?= (int)$row["user_id"] ?>" class="icon-btn" title="Remove admin" aria-label="Remove admin" onclick="return confirm('Remove admin privileges from this user?');"><i class="fa fa-user"></i></a>
                <?php else: ?>
                  <a href="core/toggle_admin.php?user_id=<?= (int)$row["user_id"] ?>" class="icon-btn icon-btn-edit" title="Make admin" aria-label="Make admin" onclick="return confirm('Make this user an admin?');"><i class="fa fa-shield"></i></a>
                <?php endif; ?>
                <a href="core/delete_user.php?user_id=<?= (int)$row["user_id"] ?>" class="icon-btn icon-btn-delete" title="Delete user" aria-label="Delete user" onclick="return confirm('Delete this user? This cannot be undone.');"><i class="fa fa-trash"></i></a>
             <a href="edit_user.php?user_id=<?= (int)$row["user_id"] ?>" class="icon-btn icon-btn-edit" title="Edit user" aria-label="Edit user"><i class="fa fa-pencil"></i></a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>

<?php include(__DIR__ . "/includes/footer.php"); ?>