<?php
require_once("./config.php");
include("./includes/header.php");
?>
<body>
  <header>
    <?php include("./includes/navbar.php"); ?>
  </header>
  <div class="container my-5">
    <div class="section-title">
      <h2>Our finest collection of rooms</h2>
    </div>
    <div class="row custom-room-cards">
      <?php
          $sql = "SELECT * FROM rooms";

          $statement = $pdo->prepare($sql);
          $statement->execute();

          $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

          foreach ($rows as $row) {
              $room_image = IMAGEROOT.$row["room_image"]; ?>
      <!-- Room element start -->
      <div class="room-card col-md-6">
        <div class="card card-expanded">
          <div class="basic">
            <div class="card-body">
              <img src="<?= $room_image ?>" alt="<?= htmlspecialchars($row["room_name"]); ?>" />
            </div>
            <div class="card-footer">
              <div class="footer-head">
                <div class="label <?= ucfirst($row["room_type"]); ?>"><?= ucfirst($row["room_type"]); ?></div>
                <div class="price">$<?= $row["room_price"]; ?>/day</div>
              </div>
              <div class="footer-body"><?= ucfirst($row["room_name"]); ?></div>
            </div>
          </div>
          <div class="detail">
            <ul class="list-group list-unstyled">
              <li><span>View:&nbsp;</span><?= $row["room_view"]; ?></li>
              <li><span>Bed type:&nbsp;</span><?= $row["bed_type"]; ?></li>
              <li><span>Capacity:&nbsp;</span><?= $row["room_capacity"]; ?></li>
              <li>
                <span>Amenities:&nbsp;</span><?= $row["room_amenities"]; ?>
              </li>
              <p></p>
              <li>
                <a id="<?= $row["room_id"] ?>" href="roomdetails.php?room_id=<?= $row["room_id"] ?>" class="details btn btn-sm btn-outline-success">Room Details</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <!-- Room element end -->
      <?php
          }
      ?>
    </div>
  </div>
  <?php include("./includes/footer.php"); ?>
  <script>
    $(document).ready(function() {
        $("nav").eq(0).addClass("bg-dark");
        $("nav").eq(0).addClass("navbar-dark");
    
        $("footer").eq(0).addClass("bg-dark");
        $("footer").eq(0).addClass("text-light");
    });
  </script>
</body>
</html>