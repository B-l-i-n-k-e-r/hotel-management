<?php
require_once(__DIR__ . "/config.php");

// Auth check
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if (isset($_GET["check_in_date"])) {
    $check_in_date  = $_GET["check_in_date"];
    $check_out_date = $_GET["check_out_date"];
    $no_children    = $_GET["children"];
    $no_adults      = $_GET["adults"];
}
?>
<?php include(__DIR__ . "/includes/header.php"); ?>
<body class="has-nav has-hero">
  <header class="page-header">
    <?php include(__DIR__ . "/includes/navbar.php"); ?>
    <div class="page-header-inner container">
      <span class="eyebrow light">Almost there</span>
      <h1>Make your reservation</h1>
      <p>Our hotel is self-certified to follow a series of precautionary measures to keep your stay safe and healthy.</p>
    </div>
  </header>

  <main>
    <section id="reservation" class="container section-pad">
      <div class="reservation-grid">
        <!-- Left: summary -->
        <aside class="reservation-aside">
          <div class="aside-card">
            <h2 class="aside-title">Your stay at a glance</h2>
            <ul class="aside-list">
              <li><span>Check in</span><strong id="aside-check-in">—</strong></li>
              <li><span>Check out</span><strong id="aside-check-out">—</strong></li>
              <li><span>Guests</span><strong id="aside-guests">—</strong></li>
            </ul>
            <p class="aside-note">
              Booking is confirmed after payment. You'll receive a confirmation email shortly after.
            </p>
          </div>
        </aside>

        <!-- Right: the form -->
        <div class="reservation-form-wrap">
          <form method="POST" action="" id="reservation_details" novalidate>
            <div id="reservation-error" class="form-error" hidden></div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="check_in_date">Check in</label>
                <input
                  class="form-control"
                  name="check_in_date"
                  id="check_in_date"
                  type="text"
                  autocomplete="off"
                  required
                  value="<?= isset($check_in_date) ? htmlspecialchars($check_in_date) : '' ?>"
                />
              </div>
              <div class="form-group">
                <label class="form-label" for="check_out_date">Check out</label>
                <input
                  class="form-control"
                  name="check_out_date"
                  id="check_out_date"
                  type="text"
                  autocomplete="off"
                  required
                  value="<?= isset($check_out_date) ? htmlspecialchars($check_out_date) : '' ?>"
                />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" for="room_id">Room</label>
              <select class="form-control" name="room_id" id="room_id" required>
                <option value="">Select a room</option>
                <?php
                  $sql = "SELECT * FROM rooms WHERE room_booked = 0";
                  $stmt = $pdo->prepare($sql);
                  $stmt->execute();
                  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                  foreach ($rows as $row) {
                ?>
                  <option value="<?= (int)$row["room_id"]; ?>">
                    <?= htmlspecialchars($row["room_name"]) ?>
                  </option>
                <?php } ?>
              </select>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="no_adults">Adults</label>
                <input
                  type="number"
                  min="1"
                  class="form-control"
                  name="no_adults"
                  id="no_adults"
                  value="<?= isset($no_adults) ? (int)$no_adults : 1 ?>"
                />
              </div>
              <div class="form-group">
                <label class="form-label" for="no_children">Children</label>
                <input
                  type="number"
                  min="0"
                  class="form-control"
                  name="no_children"
                  id="no_children"
                  value="<?= isset($no_children) ? (int)$no_children : 0 ?>"
                />
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn-primary-pill" name="confirm_booking">
                Continue to payment
              </button>
            </div>
          </form>
        </div>
      </div>
    </section>
  </main>

  <?php include(__DIR__ . "/includes/footer.php"); ?>

  <script>
    $("#check_in_date").datepicker({
      dateFormat: "yy-mm-dd",
      minDate: 0,
      numberOfMonths: [1, 2],
      onSelect: function (data) {
        $("#check_out_date").datepicker("option", "minDate", data);
        updateAside();
      }
    });

    $("#check_out_date").datepicker({
      dateFormat: "yy-mm-dd",
      minDate: 1,
      numberOfMonths: [1, 2],
      onSelect: function () {
        updateAside();
      }
    });

    function updateAside() {
      var inDate  = $("#check_in_date").val()  || "—";
      var outDate = $("#check_out_date").val() || "—";
      var adults  = $("#no_adults").val()   || 0;
      var children= $("#no_children").val() || 0;
      var total   = parseInt(adults) + parseInt(children);

      $("#aside-check-in").text(inDate);
      $("#aside-check-out").text(outDate);
      $("#aside-guests").text(total + (total === 1 ? " guest" : " guests"));
    }

    $(document).ready(function () {
      updateAside();
      $("#no_adults, #no_children").on("input", updateAside);

      $("#reservation_details").submit(function (e) {
        e.preventDefault();
        var $err = $("#reservation-error").attr("hidden", true).text("");
        var formData = new FormData(this);
        formData.append("action", "reservation");

        $.ajax({
          url: "core/reservation_controller.php",
          type: "POST",
          data: formData,
          dataType: "json",
          success: function (data) {
            if (data.error == 1) {
              $err.text(data.message || "Something went wrong. Please check your details and try again.").removeAttr("hidden");
            } else {
              window.location.href = "<?= SROOT ?>/payment.php";
            }
          },
          error: function (xhr, message, errorThrown) {
            $err.text("Network error: " + errorThrown).removeAttr("hidden");
          },
          cache: false,
          contentType: false,
          processData: false
        });
      });
    });
  </script>
</body>
</html>