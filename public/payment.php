<?php
require_once(__DIR__ . "/config.php");
include(__DIR__ . "/includes/header.php");

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION["reservation"])) {
    header("Location: reservation.php");
    exit;
}

$res = $_SESSION["reservation"];
$room_amount = getRoomAmountFromId($res["room_id"], $pdo);
$room_name   = getRoomNameFromId($res["room_id"], $pdo) ?? "—";

$already_paid = isset($_GET["paid"]) && $_GET["paid"] == "1" && isset($_GET["reservation_id"]);
$paid_reservation_id = $already_paid ? (int)$_GET["reservation_id"] : null;

$adults   = (int)($res["no_adults"] ?? 0);
$children = (int)($res["no_children"] ?? 0);
$guests_text = $adults . " adult" . ($adults === 1 ? "" : "s");
if ($children > 0) {
    $guests_text .= ", " . $children . " child" . ($children === 1 ? "" : "ren");
}
?>
<body class="has-nav has-hero">
  <header class="page-header">
    <?php include(__DIR__ . "/includes/navbar.php"); ?>
    <div class="page-header-inner container">
      <span class="eyebrow light"><?= $already_paid ? "Booking confirmed" : "Step 2 of 2" ?></span>
      <h1><?= $already_paid ? "Payment complete" : "Payment details" ?></h1>
      <p>
        <?= $already_paid
          ? "Your reservation is confirmed. View your invoice below."
          : "Secure checkout. You'll receive a confirmation once payment is processed." ?>
      </p>
    </div>
  </header>

  <main>
    <section id="payment" class="container section-pad">
      <div class="payment-grid" id="payment-grid">

        <?php if ($already_paid): ?>
          <!-- SUCCESS SCREEN -->
          <div class="payment-success">
            <div class="success-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
            </div>
            <h2>Payment successful</h2>
            <p>
              Your booking for <strong><?= htmlspecialchars($room_name) ?></strong> is confirmed.
              Click below to view your invoice.
            </p>
            <div class="success-actions">
              <a href="<?= SROOT ?>/bill_view.php?reservation_id=<?= (int)$paid_reservation_id ?>" class="btn-primary-pill">
                View invoice
              </a>
              <a href="<?= SROOT ?>/index.php" class="link-arrow">Return to home</a>
            </div>
          </div>

        <?php else: ?>
          <!-- NORMAL PAYMENT VIEW -->

          <aside class="payment-aside">
            <div class="aside-card">
              <h2 class="aside-title">Your booking</h2>
              <ul class="aside-list">
                <li>
                  <span>Check in</span>
                  <strong><?= htmlspecialchars($res["check_in_date"] ?? "—") ?></strong>
                </li>
                <li>
                  <span>Check out</span>
                  <strong><?= htmlspecialchars($res["check_out_date"] ?? "—") ?></strong>
                </li>
                <li>
                  <span>Room</span>
                  <strong><?= htmlspecialchars($room_name) ?></strong>
                </li>
                <li>
                  <span>Guests</span>
                  <strong><?= htmlspecialchars($guests_text) ?></strong>
                </li>
              </ul>

              <div class="aside-total">
                <span>Total due</span>
                <strong>Ksh <?= number_format((float)$room_amount) ?></strong>
              </div>

              <a href="<?= SROOT ?>/reservation.php" class="link-arrow">← Edit booking</a>
            </div>
          </aside>

          <div class="payment-form-wrap">
            <div class="payment-card-visual">
              <div class="cc-chip"></div>
              <div class="cc-number" id="cc-preview-number">•••• •••• •••• ••••</div>
              <div class="cc-bottom">
                <div>
                  <span class="cc-label">Card holder</span>
                  <div class="cc-value" id="cc-preview-name">Your name</div>
                </div>
                <div>
                  <span class="cc-label">Expires</span>
                  <div class="cc-value" id="cc-preview-expiry">MM / YY</div>
                </div>
              </div>
            </div>

            <form id="payment_form" novalidate>
              <div id="payment-error" class="form-error" hidden></div>

              <div class="form-group">
                <label class="form-label" for="card_number">Card number</label>
                <input
                  type="text"
                  name="card_number"
                  id="card_number"
                  class="form-control"
                  placeholder="1234 5678 9012 3456"
                  inputmode="numeric"
                  autocomplete="cc-number"
                  maxlength="19"
                  pattern="[0-9 ]{12,19}"
                  required
                />
              </div>

              <div class="form-group">
                <label class="form-label" for="card_owner">Card holder name</label>
                <input
                  type="text"
                  name="card_owner"
                  id="card_owner"
                  class="form-control"
                  placeholder="Name as it appears on card"
                  autocomplete="cc-name"
                  required
                />
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label" for="expiry_date">Expiry date</label>
                  <input
                    type="text"
                    name="expiry_date"
                    id="expiry_date"
                    class="form-control"
                    placeholder="MM / YY"
                    autocomplete="cc-exp"
                    maxlength="7"
                    required
                  />
                </div>
                <div class="form-group">
                  <label class="form-label" for="cvc">CVC</label>
                  <input
                    type="text"
                    name="cvc"
                    id="cvc"
                    class="form-control"
                    placeholder="123"
                    inputmode="numeric"
                    autocomplete="cc-csc"
                    maxlength="4"
                    required
                  />
                </div>
              </div>

              <div class="form-actions">
                <button type="submit" class="btn-primary-pill" id="pay-btn">
                  Process payment — Ksh <?= number_format((float)$room_amount) ?>
                </button>
                <p class="secure-note">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                  </svg>
                  Payments are encrypted and secure
                </p>
              </div>
            </form>
          </div>

        <?php endif; ?>
      </div>
    </section>
  </main>

  <?php include(__DIR__ . "/includes/footer.php"); ?>

  <?php if (!$already_paid): ?>
  <script>
    $(document).ready(function () {
      function formatCardNumber(v) {
        return v.replace(/\D/g, "").slice(0, 16).replace(/(.{4})/g, "$1 ").trim();
      }
      function formatExpiry(v) {
        v = v.replace(/\D/g, "").slice(0, 4);
        if (v.length >= 3) v = v.slice(0, 2) + " / " + v.slice(2);
        return v;
      }

      $("#card_number").on("input", function () {
        var formatted = formatCardNumber(this.value);
        this.value = formatted;
        $("#cc-preview-number").text(formatted || "•••• •••• •••• ••••");
      });

      $("#card_owner").on("input", function () {
        $("#cc-preview-name").text(this.value || "Your name");
      });

      $("#expiry_date").on("input", function () {
        var formatted = formatExpiry(this.value);
        this.value = formatted;
        $("#cc-preview-expiry").text(formatted || "MM / YY");
      });

      $("#cvc").on("input", function () {
        this.value = this.value.replace(/\D/g, "").slice(0, 4);
      });

      $("#payment_form").submit(function (e) {
        e.preventDefault();
        var $err = $("#payment-error").attr("hidden", true).text("");
        var $btn = $("#pay-btn").prop("disabled", true).text("Processing…");

        var formData = new FormData(this);
        formData.append("confirm_booking", "confirm_booking");

        $.ajax({
          url: "core/reservation_controller.php",
          type: "POST",
          data: formData,
          dataType: "json",
          success: function (data) {
            if (data.error == 1) {
              $err.text(data.message || "Payment failed. Please check your card details and try again.").removeAttr("hidden");
              $btn.prop("disabled", false).text("Process payment — Ksh <?= number_format((float)$room_amount) ?>");
            } else {
              var reservation_id = data.message.reservation_id;
              var invoiceUrl = "<?= SROOT ?>/bill_view.php?reservation_id=" + reservation_id;

              $("#payment-grid").html(`
                <div class="payment-success">
                  <div class="success-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                  </div>
                  <h2>Payment successful</h2>
                  <p>
                    Your booking for <strong>${<?= json_encode($room_name) ?>}</strong> is confirmed.
                    Click below to view your invoice.
                  </p>
                  <div class="success-actions">
                    <a href="${invoiceUrl}" class="btn-primary-pill">View invoice</a>
                    <a href="<?= SROOT ?>/index.php" class="link-arrow">Return to home</a>
                  </div>
                </div>
              `);

              window.scrollTo({ top: 0, behavior: "smooth" });
              history.replaceState({ paid: true }, "", "?paid=1&reservation_id=" + reservation_id);
            }
          },
          error: function (xhr, message, errorThrown) {
            $err.text("Network error: " + errorThrown).removeAttr("hidden");
            $btn.prop("disabled", false).text("Process payment — Ksh <?= number_format((float)$room_amount) ?>");
          },
          cache: false,
          contentType: false,
          processData: false
        });
      });
    });
  </script>
  <?php endif; ?>
</body>
</html>