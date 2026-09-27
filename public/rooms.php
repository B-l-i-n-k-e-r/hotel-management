<?php
require_once(__DIR__ . "/config.php");
include(__DIR__ . "/includes/header.php");
?>
<body class="has-nav has-hero">
  <header class="page-header">
    <?php include(__DIR__ . "/includes/navbar.php"); ?>
    <div class="page-header-inner container">
      <span class="eyebrow light">Our collection</span>
      <h1>Rooms &amp; Suites</h1>
      <p>Every room crafted for comfort — pick the one that fits your stay.</p>
    </div>
  </header>

  <main>
    <section class="container section-pad">
      <div class="section-title">
        <span class="eyebrow">Handpicked</span>
        <h2>Our finest collection of rooms</h2>
      </div>

      <div class="rooms-grid rooms-grid-2">
        <?php
          $sql = "SELECT * FROM rooms";
          $statement = $pdo->prepare($sql);
          $statement->execute();
          $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

          foreach ($rows as $row):
            $room_image = IMAGEROOT.$row["room_image"];
            $type = strtolower($row["room_type"]);
        ?>
          <article class="room-card">
            <div class="room-card-media">
              <span class="badge-float <?= htmlspecialchars($type); ?>">
                <?= ucfirst(htmlspecialchars($row["room_type"])); ?>
              </span>
              <img
                src="<?= htmlspecialchars($room_image) ?>"
                alt="<?= htmlspecialchars($row["room_name"]); ?>"
                loading="lazy"
              />
            </div>

            <div class="room-card-body">
              <div class="room-card-head">
                <h3 class="room-name"><?= ucfirst(htmlspecialchars($row["room_name"])); ?></h3>
                <div class="room-price">
                  Ksh <?= number_format((float)$row["room_price"]); ?><span>/day</span>
                </div>
              </div>

              <ul class="room-meta">
                <li><span>View</span><?= htmlspecialchars($row["room_view"]); ?></li>
                <li><span>Bed</span><?= htmlspecialchars($row["bed_type"]); ?></li>
                <li><span>Capacity</span><?= htmlspecialchars($row["room_capacity"]); ?></li>
                <li class="amenities"><span>Amenities</span><?= htmlspecialchars($row["room_amenities"]); ?></li>
              </ul>

              <div class="room-card-actions">
                <a
                  id="<?= (int)$row["room_id"] ?>"
                  href="<?= SROOT ?>/roomdetails.php?room_id=<?= (int)$row["room_id"] ?>"
                  class="btn-primary-pill"
                >
                  View room details
                </a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  </main>

  <?php include(__DIR__ . "/includes/footer.php"); ?>
</body>
</html>