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
$room_amount = (float) getRoomAmountFromId($res["room_id"], $pdo);
$room_name   = getRoomNameFromId($res["room_id"], $pdo) ?? "—";

$already_paid = isset($_GET["paid"]) && $_GET["paid"] == "1" && isset($_GET["reservation_id"]);
$paid_reservation_id = $already_paid ? (int)$_GET["reservation_id"] : null;

$adults   = (int)($res["no_adults"] ?? 0);
$children = (int)($res["no_children"] ?? 0);
$guests_text = $adults . " adult" . ($adults === 1 ? "" : "s");
if ($children > 0) {
    $guests_text .= ", " . $children . " child" . ($children === 1 ? "" : "ren");
}

$amount_display = "Ksh " . number_format($room_amount);
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
          : "Choose how you'd like to pay. You'll receive a confirmation once processed." ?>
      </p>
    </div>
  </header>

  <main>
    <section id="payment" class="container section-pad">
      <div class="payment-grid" id="payment-grid">

        <?php if ($already_paid): ?>
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
          <aside class="payment-aside">
            <div class="aside-card">
              <h2 class="aside-title">Your booking</h2>
              <ul class="aside-list">
                <li><span>Check in</span><strong><?= htmlspecialchars($res["check_in_date"] ?? "—") ?></strong></li>
                <li><span>Check out</span><strong><?= htmlspecialchars($res["check_out_date"] ?? "—") ?></strong></li>
                <li><span>Room</span><strong><?= htmlspecialchars($room_name) ?></strong></li>
                <li><span>Guests</span><strong><?= htmlspecialchars($guests_text) ?></strong></li>
              </ul>

              <div class="aside-total">
                <span>Total due</span>
                <strong><?= $amount_display ?></strong>
              </div>

              <a href="<?= SROOT ?>/reservation.php" class="link-arrow">← Edit booking</a>
            </div>
          </aside>

          <div class="payment-form-wrap">

            <div class="payment-tabs" role="tablist" aria-label="Payment method">
              <button type="button" class="payment-tab is-active" data-method="mpesa" role="tab" aria-selected="true">
                <i class="fa fa-mobile"></i>&nbsp; M-Pesa
              </button>
              <button type="button" class="payment-tab" data-method="card" role="tab" aria-selected="false">
                <i class="fa fa-credit-card"></i>&nbsp; Card
              </button>
              <button type="button" class="payment-tab" data-method="cash" role="tab" aria-selected="false">
                <i class="fa fa-money"></i>&nbsp; Cash on arrival
              </button>
            </div>

            <div id="payment-error" class="form-error" hidden></div>

            <!-- M-PESA -->
            <div class="payment-panel" data-method="mpesa">
              <p class="payment-help">
                Enter your M-Pesa phone number below. You'll receive an STK push
                prompt on your phone to authorize the payment of <strong><?= $amount_display ?></strong>.
              </p>
              <form id="mpesa_form" novalidate>
                <div class="form-group">
                  <label class="form-label" for="mpesa_phone">M-Pesa phone number</label>
                  <input type="tel" name="phone" id="mpesa_phone" class="form-control"
                         placeholder="07XX XXX XXX or 2547XXXXXXXX" required>
                  <small class="form-hint">Safaricom line only. Format: 07XXXXXXXX or 2547XXXXXXXX.</small>
                </div>
                <div class="form-actions">
                  <button type="submit" class="btn-primary-pill" id="mpesa-btn">
                    Pay <?= $amount_display ?> via M-Pesa
                  </button>
                  <p class="secure-note"><i class="fa fa-lock"></i> Transaction secured by Safaricom</p>
                </div>
              </form>
            </div>

            <!-- CARD (Paystack hosted checkout) -->
            <div class="payment-panel" data-method="card" hidden>
              <p class="payment-help">
                You'll be redirected to Paystack's secure checkout page to enter your card details.
                After payment you'll return here automatically.
                You are paying <strong><?= $amount_display ?></strong>.
              </p>
              <form id="card_form" novalidate>
                <div class="form-actions">
                  <button type="submit" class="btn-primary-pill" id="card-btn">
                    Pay <?= $amount_display ?> with Card
                  </button>
                  <p class="secure-note"><i class="fa fa-lock"></i> Secured by Paystack</p>
                </div>
              </form>
            </div>

            <!-- CASH -->
            <div class="payment-panel" data-method="cash" hidden>
              <p class="payment-help">
                Reserve now, pay at the front desk when you check in.
                Your booking will be marked <strong>pending</strong> until payment is collected on arrival.
              </p>
              <form id="cash_form" novalidate>
                <div class="form-actions">
                  <button type="submit" class="btn-primary-pill" id="cash-btn">
                    Confirm booking — pay on arrival
                  </button>
                  <p class="secure-note"><i class="fa fa-info-circle"></i> Cancellation is free up to 24h before check-in</p>
                </div>
              </form>
            </div>

          </div>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <?php include(__DIR__ . "/includes/footer.php"); ?>

  <?php if (!$already_paid): ?>
  <style>
    .payment-tabs { display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid #e5e7eb; }
    .payment-tab {
      flex: 1; padding: 12px 16px; background: transparent; border: none;
      border-bottom: 3px solid transparent; font-size: 14px; font-weight: 600;
      color: #64748b; cursor: pointer; transition: all 0.15s ease;
    }
    .payment-tab:hover { color: #0f172a; }
    .payment-tab.is-active { color: #0f172a; border-bottom-color: #9a7b4f; }
    .payment-help { font-size: 14px; color: #475569; margin-bottom: 18px; line-height: 1.6; }
    .form-hint { display: block; margin-top: 6px; font-size: 12px; color: #94a3b8; }
  </style>

  <script>
    $(document).ready(function () {
      // ---------- Tabs ----------
      $(".payment-tab").on("click", function () {
        var method = $(this).data("method");
        $(".payment-tab").removeClass("is-active").attr("aria-selected", "false");
        $(this).addClass("is-active").attr("aria-selected", "true");
        $(".payment-panel").attr("hidden", true);
        $('.payment-panel[data-method="' + method + '"]').removeAttr("hidden");
        $("#payment-error").attr("hidden", true).text("");
      });

      // ---------- Helpers ----------
      function showError(msg) { $("#payment-error").text(msg || "Something went wrong.").removeAttr("hidden"); }
      function clearError() { $("#payment-error").attr("hidden", true).text(""); }
      function showSuccess(reservationId) {
        var invoiceUrl = "<?= SROOT ?>/bill_view.php?reservation_id=" + reservationId;
        var roomName = <?= json_encode($room_name) ?>;
        $("#payment-grid").html(
          '<div class="payment-success">' +
            '<div class="success-icon" aria-hidden="true">' +
              '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>' +
            '</div>' +
            '<h2>Payment successful</h2>' +
            '<p>Your booking for <strong>' + roomName + '</strong> is confirmed. Click below to view your invoice.</p>' +
            '<div class="success-actions">' +
              '<a href="' + invoiceUrl + '" class="btn-primary-pill">View invoice</a>' +
              '<a href="<?= SROOT ?>/index.php" class="link-arrow">Return to home</a>' +
            '</div>' +
          '</div>'
        );
        window.scrollTo({ top: 0, behavior: "smooth" });
        history.replaceState({ paid: true }, "", "?paid=1&reservation_id=" + reservationId);
      }

      // ---------- M-PESA submit + poll ----------
      $("#mpesa_form").submit(function (e) {
        e.preventDefault();
        clearError();
        var $btn = $("#mpesa-btn").prop("disabled", true).text("Sending STK push…");
        var phone = $("#mpesa_phone").val();

        $.ajax({
          url: "core/mpesa_initiate.php",
          type: "POST",
          data: { phone: phone },
          dataType: "json",
          success: function (data) {
            if (data.error == 1) {
              showError(data.message);
              $btn.prop("disabled", false).text("Pay <?= $amount_display ?> via M-Pesa");
              return;
            }

            $btn.text("Check your phone… (enter PIN)");
            var checkoutId = data.checkout_request_id;
            var attempts = 0;
            var maxAttempts = 20;

            var poll = setInterval(function () {
              attempts++;
              $.get("core/mpesa_status.php", { checkout_id: checkoutId }, function (res) {
                if (res.error == 0 && res.status === "paid") {
                  clearInterval(poll);
                  showSuccess(res.reservation_id);
                } else if (res.error == 0 && res.status === "failed") {
                  clearInterval(poll);
                  showError("Payment was cancelled or failed. Please try again.");
                  $btn.prop("disabled", false).text("Pay <?= $amount_display ?> via M-Pesa");
                } else if (attempts >= maxAttempts) {
                  clearInterval(poll);
                  showError("We didn't receive a confirmation. If you paid, please contact support with your M-Pesa receipt.");
                  $btn.prop("disabled", false).text("Pay <?= $amount_display ?> via M-Pesa");
                }
              });
            }, 3000);
          },
          error: function (xhr, status, err) {
            showError("Network error: " + err);
            $btn.prop("disabled", false).text("Pay <?= $amount_display ?> via M-Pesa");
          }
        });
      });

      // ---------- CARD via Paystack hosted checkout ----------
      $("#card_form").submit(function (e) {
        e.preventDefault();
        clearError();
        var $btn = $("#card-btn").prop("disabled", true).text("Redirecting to Paystack…");

        $.ajax({
          url: "core/paystack_initiate.php",
          type: "POST",
          dataType: "json",
          success: function (data) {
            if (data.error == 1) {
              showError(data.message);
              $btn.prop("disabled", false).text("Pay <?= $amount_display ?> with Card");
              return;
            }
            // Redirect browser to Paystack's checkout
            window.location.href = data.authorization_url;
          },
          error: function (xhr, status, err) {
            showError("Network error: " + err);
            $btn.prop("disabled", false).text("Pay <?= $amount_display ?> with Card");
          }
        });
      });

      // ---------- CASH ----------
      $("#cash_form").submit(function (e) {
        e.preventDefault();
        clearError();
        var $btn = $("#cash-btn").prop("disabled", true).text("Confirming…");

        $.ajax({
          url: "core/cash_confirm.php",
          type: "POST",
          data: { method: "cash" },
          dataType: "json",
          success: function (data) {
            if (data.error == 1) {
              showError(data.message);
              $btn.prop("disabled", false).text("Confirm booking — pay on arrival");
            } else {
              showSuccess(data.reservation_id);
            }
          },
          error: function (xhr, status, err) {
            showError("Network error: " + err);
            $btn.prop("disabled", false).text("Confirm booking — pay on arrival");
          }
        });
      });
    });
  </script>
  <?php endif; ?>
</body>
</html>