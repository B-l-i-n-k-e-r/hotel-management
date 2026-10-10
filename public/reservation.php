<?php
require_once(__DIR__ . "/config.php");

// Auth check
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if (isset($_GET["check_in_date"])) {
    $check_in_date  = $_GET["check_in_date"];
    $check_out_date = $_GET["check_out_date"] ?? "";
    $no_children    = (int)($_GET["children"] ?? 0);
    $no_adults      = (int)($_GET["adults"]   ?? 1);
} else {
    $check_in_date  = "";
    $check_out_date = "";
    $no_children    = 0;
    $no_adults      = 1;
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
                  value="<?= htmlspecialchars($check_in_date) ?>"
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
                  value="<?= htmlspecialchars($check_out_date) ?>"
                />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" for="room_id">Room</label>
              <select class="form-control room-select" name="room_id" id="room_id" required>
                <option value="">Select a room</option>
                <?php
                  $stmt = $pdo->prepare("SELECT room_id, room_name FROM rooms WHERE room_booked = 0 ORDER BY room_name ASC");
                  $stmt->execute();
                  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                  foreach ($rows as $row):
                ?>
                  <option value="<?= (int)$row["room_id"]; ?>"><?= htmlspecialchars($row["room_name"]); ?></option>
                <?php endforeach; ?>
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
                  value="<?= (int)$no_adults ?>"
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
                  value="<?= (int)$no_children ?>"
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

  <style>
    /* Ensure the room select is tall enough to show text without clipping */
    select.room-select {
      display: block;
      width: 100%;
      height: auto !important;
      min-height: 48px;
      padding: 12px 14px;
      line-height: 1.4;
      font-size: 15px;
      background-color: #fff;
    }
    select.room-select option {
      padding: 8px;
    }
  </style>

  <?php include(__DIR__ . "/includes/footer.php"); ?>

  <script>
    $(document).ready(function () {
      // ---- Date pickers ----
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
        var inDate   = $("#check_in_date").val()  || "—";
        var outDate  = $("#check_out_date").val() || "—";
        var adults   = parseInt($("#no_adults").val()   || "0", 10);
        var children = parseInt($("#no_children").val() || "0", 10);
        var total    = adults + children;

        $("#aside-check-in").text(inDate);
        $("#aside-check-out").text(outDate);
        $("#aside-guests").text(total + (total === 1 ? " guest" : " guests"));
      }

      updateAside();
      $("#no_adults, #no_children").on("input", updateAside);

      // ---- Submit with client-side guards ----
      $("#reservation_details").submit(function (e) {
        e.preventDefault();
        var $err = $("#reservation-error").attr("hidden", true).text("");

        var checkIn  = ($("#check_in_date").val()  || "").trim();
        var checkOut = ($("#check_out_date").val() || "").trim();
        var roomId   = $("#room_id").val();
        var adults   = parseInt($("#no_adults").val()   || "0", 10);
        var children = parseInt($("#no_children").val() || "0", 10);

        if (!checkIn || !checkOut) {
          $err.text("Please select both check-in and check-out dates.").removeAttr("hidden");
          return;
        }
        if (new Date(checkOut) <= new Date(checkIn)) {
          $err.text("Check-out must be after check-in.").removeAttr("hidden");
          return;
        }
        if (!roomId) {
          $err.text("Please select a room.").removeAttr("hidden");
          $("#room_id").focus();
          return;
        }
        if (adults < 1) {
          $err.text("At least one adult is required.").removeAttr("hidden");
          return;
        }

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