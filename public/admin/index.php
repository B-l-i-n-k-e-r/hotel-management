<?php
require_once(__DIR__ . "/../config.php");

$admin_page  = "dashboard";
$admin_title = "Dashboard";
$admin_sub   = "Here's what's happening at V&N Hotel today.";

include(__DIR__ . "/includes/header.php");

if (!isLoggedIn() || !isAdmin($pdo)) {
    header("Location: ../login.php");
    exit;
}

$room_count        = count(getAllRooms($pdo) ?: []);
$user_count        = count(getAllUsers($pdo) ?: []);
$reservation_count = count(getAllReservations($pdo) ?: []);
?>

<section class="admin-welcome">
  <div class="admin-welcome-text">
    <h2>Welcome back, <?= htmlspecialchars($_SESSION["user_fname"] ?? "Admin") ?>.</h2>
    <p>Here's what's happening at V&amp;N Hotel today.</p>
  </div>
  <div class="admin-welcome-actions">
    <a href="rooms.php" class="btn-primary-pill">Manage rooms</a>
    <a href="reservation.php" class="btn-ghost-pill">View reservations</a>
  </div>
</section>

<section class="admin-kpis">
  <article class="kpi-card kpi-rooms">
    <div class="kpi-icon"><i class="fa fa-home"></i></div>
    <div class="kpi-info">
      <span class="kpi-label">Total rooms</span>
      <span class="kpi-value"><?= (int)$room_count ?></span>
    </div>
  </article>
  <article class="kpi-card kpi-users">
    <div class="kpi-icon"><i class="fa fa-users"></i></div>
    <div class="kpi-info">
      <span class="kpi-label">Registered users</span>
      <span class="kpi-value"><?= (int)$user_count ?></span>
    </div>
  </article>
  <article class="kpi-card kpi-reservations">
    <div class="kpi-icon"><i class="fa fa-bookmark"></i></div>
    <div class="kpi-info">
      <span class="kpi-label">Reservations</span>
      <span class="kpi-value"><?= (int)$reservation_count ?></span>
    </div>
  </article>
</section>

<section class="admin-charts">
  <div class="chart-card">
    <div class="chart-head">
      <h3>Booked vs unbooked</h3>
      <p>Room occupancy at a glance</p>
    </div>
    <div class="chart-body">
      <canvas id="booking-chart"></canvas>
    </div>
  </div>
  <div class="chart-card">
    <div class="chart-head">
      <h3>Room types</h3>
      <p>Distribution across our inventory</p>
    </div>
    <div class="chart-body">
      <canvas id="room-types"></canvas>
    </div>
  </div>
</section>

<?php include(__DIR__ . "/includes/footer.php"); ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js" integrity="sha512-d9xgZrVZpmmQlfonhQUvTR7lMPtO7NkZMkA0ABN3PHCbKA5nqylQ/yWlFAyY6hYgdF1Qh6nYiuADWwKB4C2WSw==" crossorigin="anonymous"></script>
<script src="<?= SROOT ?>/js/chart_render.js"></script>
<script>
  $(document).ready(function () {
    $.ajax({
      url: "core/chart_data.php?content=booking",
      type: "GET",
      dataType: "json",
      success: function (data) {
        if (!data || !data.message) return;
        try {
          renderBooking(data.message);
          roomTypes(data.message);
        } catch (e) {
          console.error("Chart render failed:", e);
        }
      },
      error: function (xhr, status, err) {
        console.error("Chart data fetch failed:", status, err);
      },
      cache: false
    });
  });
</script>