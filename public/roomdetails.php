<?php
require_once(__DIR__ . "/config.php");
include(__DIR__ . "/includes/header.php");

if (!isset($_GET["room_id"]) || $_GET["room_id"] === "") {
    header("Location: rooms.php");
    exit;
}

$room_id = (int)$_GET["room_id"];

$sql = "SELECT * FROM rooms WHERE room_id = :room_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([":room_id" => $room_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    header("Location: rooms.php");
    exit;
}
?>
<body class="has-nav has-hero">
  <header class="page-header">
    <?php include(__DIR__ . "/includes/navbar.php"); ?>
    <div class="page-header-inner container">
      <span class="eyebrow light"><?= htmlspecialchars($row["room_type"]) ?></span>
      <h1><?= htmlspecialchars($row["room_name"]) ?></h1>
      <p>Room <?= htmlspecialchars($row["room_number"]) ?> &middot; Floor <?= htmlspecialchars($row["room_floor"]) ?> &middot; <?= htmlspecialchars($row["room_view"]) ?> view</p>
    </div>
  </header>

  <main>
    <section class="container section-pad">

      <div class="room-detail-grid">

        <!-- ============ Left: gallery + description ============ -->
        <div class="room-detail-main">

          <!-- Image -->
          <div class="room-detail-hero">
            <img
              src="<?= htmlspecialchars(IMAGEROOT . $row["room_image"]) ?>"
              alt="<?= htmlspecialchars($row["room_name"]) ?>"
            />
            <?php if ($row["room_featured"] == 1): ?>
              <span class="badge-float">Featured</span>
            <?php endif; ?>
          </div>

          <!-- Price + Book row -->
          <div class="room-detail-bar">
            <div>
              <span class="room-detail-price-label">Starting from</span>
              <div class="room-detail-price">
                Ksh <?= number_format((float)$row["room_price"]) ?><span>/day</span>
              </div>
            </div>
            <a href="<?= SROOT ?>/reservation.php" class="btn-primary-pill room-detail-cta">
              <i class="fa fa-calendar-check-o"></i>&nbsp; Book this room
            </a>
          </div>

          <!-- Quick facts -->
          <ul class="room-detail-facts">
            <li>
              <i class="fa fa-bed"></i>
              <div>
                <span>Beds</span>
                <strong><?= htmlspecialchars($row["room_beds"]) ?> × <?= htmlspecialchars($row["bed_type"]) ?></strong>
              </div>
            </li>
            <li>
              <i class="fa fa-users"></i>
              <div>
                <span>Capacity</span>
                <strong><?= htmlspecialchars($row["room_capacity"]) ?> guests</strong>
              </div>
            </li>
            <li>
              <i class="fa fa-eye"></i>
              <div>
                <span>View</span>
                <strong><?= htmlspecialchars($row["room_view"]) ?></strong>
              </div>
            </li>
            <li>
              <i class="fa fa-building-o"></i>
              <div>
                <span>Floor</span>
                <strong><?= htmlspecialchars($row["room_floor"]) ?></strong>
              </div>
            </li>
          </ul>

          <!-- About this room -->
          <div class="room-detail-section">
            <h2>About this room</h2>
            <p>
              The <?= htmlspecialchars($row["room_name"]) ?> is a <?= htmlspecialchars($row["room_type"]) ?>
              room with a <?= htmlspecialchars($row["room_view"]) ?> view, <?= htmlspecialchars($row["room_beds"]) ?>
              <?= htmlspecialchars($row["bed_type"]) ?> bed<?= $row["room_beds"] == 1 ? "" : "s" ?>, and capacity for
              <?= htmlspecialchars($row["room_capacity"]) ?> guests. Ideal for guests looking for comfort and convenience
              in the heart of V&amp;N Hotel.
            </p>
          </div>

          <!-- Amenities -->
          <div class="room-detail-section">
            <h2>Amenities</h2>
            <?php
            // Split amenities string on commas for a clean list
            $amenities = array_filter(array_map("trim", explode(",", $row["room_amenities"] ?? "")));
            ?>
            <?php if ($amenities): ?>
              <ul class="amenities-list">
                <?php foreach ($amenities as $a): ?>
                  <li>
                    <i class="fa fa-check"></i>
                    <?= htmlspecialchars($a) ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <p class="text-muted">Amenities will be listed here.</p>
            <?php endif; ?>
          </div>

        </div>

        <!-- ============ Right: sticky booking sidebar ============ -->
        <aside class="room-detail-aside">
          <div class="aside-card">
            <h2 class="aside-title">Reserve this room</h2>

            <ul class="aside-list">
              <li>
                <span>Room</span>
                <strong><?= htmlspecialchars($row["room_name"]) ?></strong>
              </li>
              <li>
                <span>Type</span>
                <strong><?= htmlspecialchars($row["room_type"]) ?></strong>
              </li>
              <li>
                <span>Price</span>
                <strong>Ksh <?= number_format((float)$row["room_price"]) ?>/day</strong>
              </li>
              <li>
                <span>Status</span>
                <strong>
                  <?php if ($row["room_booked"] == 1): ?>
                    <span class="pill pill-neutral">Booked</span>
                  <?php else: ?>
                    <span class="pill pill-success">Available</span>
                  <?php endif; ?>
                </strong>
              </li>
            </ul>

            <?php if ($row["room_booked"] == 0): ?>
              <a href="<?= SROOT ?>/reservation.php" class="btn-primary-pill" style="width: 100%;">
                Continue to booking
              </a>
            <?php else: ?>
              <a href="<?= SROOT ?>/rooms.php" class="btn-ghost-pill btn-ghost-light" style="width: 100%; justify-content: center;">
                Browse other rooms
              </a>
            <?php endif; ?>
          </div>
        </aside>

      </div>
    </section>
  </main>

  <?php include(__DIR__ . "/includes/footer.php"); ?>
</body>
</html>