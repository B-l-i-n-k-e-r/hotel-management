<?php
require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/../core/mail.php");

if (!isLoggedIn() || !isAdmin($pdo)) {
    header("Location: ../login.php");
    exit;
}

// ---- Handle status change ----
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["change_status"])) {
    $rid       = (int)($_POST["reservation_id"] ?? 0);
    $newStatus = $_POST["status"] ?? "";
    $allowed   = ["pending", "confirmed", "checked_in", "checked_out", "cancelled"];

    if ($rid > 0 && in_array($newStatus, $allowed, true)) {
        try {
            // Load current reservation + user for the email
            $q = $pdo->prepare("
                SELECT r.*, u.user_email, u.user_fname, u.user_lname, rm.room_name
                FROM reservations r
                LEFT JOIN users u ON u.user_id = r.user_id
                LEFT JOIN rooms rm ON rm.room_id = r.room_id
                WHERE r.reservation_id = ? LIMIT 1
            ");
            $q->execute([$rid]);
            $res = $q->fetch(PDO::FETCH_ASSOC);

            if ($res) {
                $pdo->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?")
                    ->execute([$newStatus, $rid]);

                // Email the guest if the address exists
                if (!empty($res["user_email"])) {
                    $labels = [
                        "pending"     => "Pending",
                        "confirmed"   => "Confirmed",
                        "checked_in"  => "Checked in",
                        "checked_out" => "Checked out",
                        "cancelled"   => "Cancelled",
                    ];
                    $label = $labels[$newStatus] ?? ucfirst($newStatus);

                    $html = "
                      <h2>Your reservation status was updated</h2>
                      <p>Hi " . htmlspecialchars($res["user_fname"] ?: "there") . ",</p>
                      <p>Your booking <strong>#" . $rid . "</strong> for <strong>" . htmlspecialchars($res["room_name"] ?? "your room") . "</strong> is now:</p>
                      <p style='font-size:20px;font-weight:700;'>" . htmlspecialchars($label) . "</p>
                      <p>Stay dates: " . htmlspecialchars($res["check_in_date"]) . " → " . htmlspecialchars($res["check_out_date"]) . "</p>
                      <p>You can view details anytime on your reservations page.</p>
                    ";

                    send_mail(
                        $res["user_email"],
                        "Reservation #$rid — " . $label,
                        $html,
                        MAIL_ADMIN,
                        trim(($res["user_fname"] ?? "") . " " . ($res["user_lname"] ?? ""))
                    );
                }

                $_SESSION["message"] = "Status updated to " . htmlspecialchars($label);
            }
        } catch (Throwable $e) {
            error_log("Status change failed: " . $e->getMessage());
            $_SESSION["error"] = "Could not update status.";
        }
    } else {
        $_SESSION["error"] = "Invalid status value.";
    }

    header("Location: reservation.php");
    exit;
}

$sql = "SELECT
          reservations.reservation_id,
          reservations.status,
          reservations.user_id,
          u.user_fname, u.user_lname, u.user_email,
          r.room_number, r.room_name, r.room_type,
          r.check_in_date, r.check_out_date,
          p.payment_id, p.amount
        FROM reservations
        LEFT JOIN rooms   AS r ON r.room_id = reservations.room_id
        LEFT JOIN users   AS u ON u.user_id = reservations.user_id
        LEFT JOIN payment AS p ON p.reservation_id = reservations.reservation_id
        ORDER BY r.check_in_date DESC, reservations.reservation_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$total = count($rows);
$paid_count = 0;
$unpaid_count = 0;
$total_revenue = 0.0;
foreach ($rows as $r) {
    if (!empty($r["payment_id"])) { $paid_count++; $total_revenue += (float)($r["amount"] ?? 0); }
    else { $unpaid_count++; }
}

$admin_page  = "reservations";
$admin_title = "Reservations";
$admin_sub   = $total . " reservation" . ($total === 1 ? "" : "s") . " on record";

include(__DIR__ . "/includes/header.php");

$status_labels = [
    "pending"     => ["Pending",     "pill-neutral"],
    "confirmed"   => ["Confirmed",   "pill-success"],
    "checked_in"  => ["Checked in",  "pill-success"],
    "checked_out" => ["Checked out", "pill-muted"],
    "cancelled"   => ["Cancelled",   "pill-danger-soft"],
];
?>

<section class="admin-kpis admin-kpis-4">
  <article class="kpi-card kpi-rooms">
    <div class="kpi-icon"><i class="fa fa-bookmark"></i></div>
    <div class="kpi-info"><span class="kpi-label">Total</span><span class="kpi-value"><?= (int)$total ?></span></div>
  </article>
  <article class="kpi-card kpi-reservations">
    <div class="kpi-icon"><i class="fa fa-check"></i></div>
    <div class="kpi-info"><span class="kpi-label">Paid</span><span class="kpi-value"><?= (int)$paid_count ?></span></div>
  </article>
  <article class="kpi-card kpi-users">
    <div class="kpi-icon"><i class="fa fa-clock"></i></div>
    <div class="kpi-info"><span class="kpi-label">Unpaid</span><span class="kpi-value"><?= (int)$unpaid_count ?></span></div>
  </article>
  <article class="kpi-card kpi-revenue">
    <div class="kpi-icon"><i class="fa fa-money"></i></div>
    <div class="kpi-info"><span class="kpi-label">Revenue</span><span class="kpi-value"><?= number_format($total_revenue) ?></span></div>
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
      <div class="admin-empty-icon"><i class="fa fa-bookmark"></i></div>
      <h2>No reservations yet</h2>
      <p>Bookings will appear here as soon as guests make them.</p>
    </div>
  <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th><th>Guest</th><th>Room</th><th>Check in</th><th>Check out</th>
            <th>Payment</th><th>Status</th><th class="num">Amount</th><th class="actions">Invoice</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row):
            $st = $row["status"] ?? "pending";
            [$label, $cls] = $status_labels[$st] ?? ["Pending", "pill-neutral"];
          ?>
            <tr>
              <td data-label="#"><strong>#<?= (int)$row["reservation_id"] ?></strong></td>
              <td data-label="Guest">
                <strong><?= htmlspecialchars(trim(($row["user_fname"] ?? "") . " " . ($row["user_lname"] ?? "")) ?: "—") ?></strong>
                <?php if (!empty($row["user_email"])): ?><br><small class="text-muted"><?= htmlspecialchars($row["user_email"]) ?></small><?php endif; ?>
              </td>
              <td data-label="Room"><?= htmlspecialchars($row["room_number"] ?? "—") ?> — <?= htmlspecialchars($row["room_name"] ?? "—") ?></td>
              <td data-label="Check in"><?= htmlspecialchars($row["check_in_date"] ?? "—") ?></td>
              <td data-label="Check out"><?= htmlspecialchars($row["check_out_date"] ?? "—") ?></td>
              <td data-label="Payment">
                <?php if (!empty($row["payment_id"])): ?>
                  <span class="pill pill-success">Paid</span>
                <?php else: ?>
                  <span class="pill pill-danger-soft">Unpaid</span>
                <?php endif; ?>
              </td>
              <td data-label="Status">
                <form method="POST" class="status-form" style="display:flex; gap:6px; align-items:center;">
                  <input type="hidden" name="change_status" value="1">
                  <input type="hidden" name="reservation_id" value="<?= (int)$row["reservation_id"] ?>">
                  <span class="pill <?= $cls ?>"><?= htmlspecialchars($label) ?></span>
                  <select name="status" class="status-select" onchange="this.form.submit()">
                    <?php foreach ($status_labels as $key => [$lbl, $c]): ?>
                      <option value="<?= $key ?>"<?= $key === $st ? " selected" : "" ?>><?= htmlspecialchars($lbl) ?></option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </td>
              <td data-label="Amount" class="num">
                <?php if ($row["amount"] !== null && $row["amount"] !== ""): ?>
                  <strong>Ksh <?= number_format((float)$row["amount"]) ?></strong>
                <?php else: ?>
                  <span class="pill pill-muted">—</span>
                <?php endif; ?>
              </td>
              <td data-label="Invoice" class="actions">
                <?php if (!empty($row["payment_id"])): ?>
                  <a href="../bill_view.php?reservation_id=<?= (int)$row["reservation_id"] ?>" target="_blank" rel="noopener" class="row-action">View</a>
                <?php else: ?>
                  <span class="pill pill-muted">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>

<style>
  .status-select {
    font-size: 12px; padding: 4px 8px; border-radius: 6px;
    border: 1px solid #e2e8f0; background: #fff; cursor: pointer;
  }
</style>

<?php include(__DIR__ . "/includes/footer.php"); ?>