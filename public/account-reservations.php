<?php
require_once(__DIR__ . "/config.php");
include(__DIR__ . "/includes/header.php");

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT
          reservations.reservation_id,
          room.check_in_date,
          room.check_out_date,
          room.room_name,
          p.amount,
          p.payment_id
        FROM reservations
        LEFT JOIN rooms as room ON room.room_id = reservations.room_id
        LEFT JOIN payment as p ON p.reservation_id = reservations.reservation_id
        WHERE reservations.user_id = :user_id
        ORDER BY room.check_in_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute(array(":user_id" => $_SESSION["user_id"]));
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
?>
<body class="has-nav has-hero">
  <header class="page-header">
    <?php include(__DIR__ . "/includes/navbar.php"); ?>
    <div class="page-header-inner container">
      <span class="eyebrow light">Your account</span>
      <h1>Your reservations</h1>
      <p>Every stay you've booked with V&amp;N Hotel.</p>
    </div>
  </header>

  <main>
    <section id="account" class="container section-pad">
      <div class="account-grid">

        <!-- Sidebar -->
        <aside class="account-sidebar">
          <nav class="account-nav">
            <a href="<?= SROOT ?>/accounts.php">
              <span>Account</span>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            </a>
            <a href="<?= SROOT ?>/account-reservations.php" class="active">
              <span>Reservations</span>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
            </a>
          </nav>
        </aside>

        <!-- Main content -->
        <div class="account-content">

          <?php if (empty($rows)): ?>
            <div class="account-card empty-state">
              <div class="empty-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                  <line x1="16" y1="2" x2="16" y2="6"/>
                  <line x1="8" y1="2" x2="8" y2="6"/>
                  <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
              </div>
              <h2>No reservations yet</h2>
              <p>When you book a room, it'll show up here along with your invoice.</p>
              <a href="<?= SROOT ?>/rooms.php" class="btn-primary-pill">Browse rooms</a>
            </div>

          <?php else: ?>
            <div class="account-card">
              <div class="account-card-head">
                <h2>All reservations</h2>
                <p><?= count($rows) ?> booking<?= count($rows) === 1 ? "" : "s" ?> found</p>
              </div>

              <div class="reservations-table-wrap">
                <table class="reservations-table">
                  <thead>
                    <tr>
                      <th>Room</th>
                      <th>Check in</th>
                      <th>Check out</th>
                      <th class="num">Amount</th>
                      <th class="actions">Invoice</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($rows as $row): ?>
                      <tr>
                        <td data-label="Room">
                          <strong><?= htmlspecialchars($row["room_name"] ?? "—") ?></strong>
                        </td>
                        <td data-label="Check in">
                          <?= htmlspecialchars($row["check_in_date"] ?? "—") ?>
                        </td>
                        <td data-label="Check out">
                          <?= htmlspecialchars($row["check_out_date"] ?? "—") ?>
                        </td>
                        <td data-label="Amount" class="num">
                          <?php if ($row["amount"] !== null): ?>
                            <strong>Ksh <?= number_format((float)$row["amount"], 2) ?></strong>
                          <?php else: ?>
                            <span class="muted">Unpaid</span>
                          <?php endif; ?>
                        </td>
                        <td class="actions">
                          <?php if (!empty($row["payment_id"])): ?>
                            <a href="<?= SROOT ?>/bill_view.php?reservation_id=<?= (int)$row["reservation_id"] ?>" class="row-action">
                              View
                            </a>
                          <?php else: ?>
                            <span class="muted">—</span>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </section>
  </main>

  <?php include(__DIR__ . "/includes/footer.php"); ?>
</body>
</html>