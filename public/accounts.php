<?php
require_once(__DIR__ . "/config.php");
include(__DIR__ . "/includes/header.php");

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT * FROM users WHERE user_id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(array(":user_id" => $_SESSION["user_id"]));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<body class="has-nav has-hero">
  <header class="page-header">
    <?php include(__DIR__ . "/includes/navbar.php"); ?>
    <div class="page-header-inner container">
      <span class="eyebrow light">Your account</span>
      <h1>Account settings</h1>
      <p>Manage your personal details and reservations.</p>
    </div>
  </header>

  <main>
    <section id="account" class="container section-pad">
      <div class="account-grid">

        <!-- Sidebar -->
        <aside class="account-sidebar">
          <nav class="account-nav">
            <a href="<?= SROOT ?>/accounts.php" class="active">
              <span>Account</span>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            </a>
            <a href="<?= SROOT ?>/account-reservations.php">
              <span>Reservations</span>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            </a>
          </nav>
        </aside>

        <!-- Main content -->
        <div class="account-content">

          <!-- Details card -->
          <div class="account-card">
            <div class="account-card-head">
              <h2>Personal details</h2>
              <p>Contact our front desk if any of these need updating.</p>
            </div>

            <form class="account-form">
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label" for="user_fname">First name</label>
                  <input
                    type="text"
                    class="form-control"
                    id="user_fname"
                    value="<?= htmlspecialchars($row["user_fname"] ?? "") ?>"
                    readonly
                  />
                </div>
                <div class="form-group">
                  <label class="form-label" for="user_lname">Last name</label>
                  <input
                    type="text"
                    class="form-control"
                    id="user_lname"
                    value="<?= htmlspecialchars($row["user_lname"] ?? "") ?>"
                    readonly
                  />
                </div>
              </div>

              <div class="form-group">
                <label class="form-label" for="user_email">Email</label>
                <input
                  type="email"
                  class="form-control"
                  id="user_email"
                  value="<?= htmlspecialchars($row["user_email"] ?? "") ?>"
                  readonly
                />
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label" for="user_phone">Phone number</label>
                  <input
                    type="text"
                    class="form-control"
                    id="user_phone"
                    value="<?= htmlspecialchars($row["user_phone"] ?? "") ?>"
                    readonly
                  />
                </div>
                <div class="form-group">
                  <label class="form-label" for="user_dob">Date of birth</label>
                  <input
                    type="date"
                    class="form-control"
                    id="user_dob"
                    value="<?= htmlspecialchars($row["user_dob"] ?? "") ?>"
                    readonly
                  />
                </div>
              </div>
            </form>
          </div>

          <!-- Danger zone -->
          <div class="account-card danger-card">
            <div class="danger-body">
              <div>
                <h3>Delete account</h3>
                <p>Deleting your account removes all bookings, invoices, and personal data. This cannot be undone.</p>
              </div>
              <a href="<?= SROOT ?>/core/delete_account.php" class="btn-danger" onclick="return confirm('Delete your account permanently? This cannot be undone.');">
                Delete account
              </a>
            </div>
          </div>

        </div>
      </div>
    </section>
  </main>

  <?php include(__DIR__ . "/includes/footer.php"); ?>
</body>
</html>