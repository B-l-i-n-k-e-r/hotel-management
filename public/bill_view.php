<?php
require_once(__DIR__ . "/config.php");
require '../vendor/autoload.php';

if (!isset($_GET["reservation_id"])) {
    include(__DIR__ . "/includes/header.php");
    echo '<body class="has-nav"><header class="page-header">' . htmlspecialchars("Not found") . '</header><main class="container section-pad"><p>Missing reservation id.</p></main></body>';
    exit;
}

$sql = "SELECT r.reservation_id, r.check_in_date, r.check_out_date, r.status,
               rm.room_name, rm.room_number, rm.room_type, rm.room_price,
               u.user_fname, u.user_lname, u.user_email,
               p.payment_id, p.amount, p.method
        FROM reservations r
        LEFT JOIN rooms   rm ON rm.room_id = r.room_id
        LEFT JOIN users   u  ON u.user_id  = r.user_id
        LEFT JOIN payment p  ON p.reservation_id = r.reservation_id
        WHERE r.reservation_id = :rid
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([":rid" => $_GET["reservation_id"]]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    include(__DIR__ . "/includes/header.php");
    ?>
    <body class="has-nav"><header class="page-header">
    <?php include(__DIR__ . "/includes/navbar.php"); ?>
    <div class="page-header-inner container"><span class="eyebrow light">Invoice</span><h1>Invoice not found</h1></div>
    </header><main class="container section-pad" style="text-align:center;">
    <p>We couldn't find that reservation.</p>
    <a href="<?= SROOT ?>/index.php" class="btn-primary-pill">Return home</a></main></body></html>
    <?php exit;
}

$date       = date("d M Y");
$total      = (float)($row["amount"] ?? 0);
$room_price = (float)($row["room_price"] ?? 0);
$no_days    = max((new DateTime($row["check_in_date"]))->diff(new DateTime($row["check_out_date"]))->days, 1);
$guest_name = trim(($row["user_fname"] ?? "") . " " . ($row["user_lname"] ?? "")) ?: "Guest";
$ref        = str_pad((string)$row["reservation_id"], 6, "0", STR_PAD_LEFT);
$method_map = ["mpesa" => "M-Pesa", "card" => "Card", "cash" => "Cash on arrival"];
$method     = $method_map[$row["method"] ?? ""] ?? ucfirst($row["method"] ?? "—");
$status_map = ["pending" => "Pending", "confirmed" => "Confirmed", "checked_in" => "Checked in", "checked_out" => "Checked out", "cancelled" => "Cancelled"];
$status     = $status_map[$row["status"] ?? "pending"] ?? "Pending";

$css = <<<CSS
  * { box-sizing: border-box; }
  body { font-family: 'Lato', 'Helvetica', sans-serif; color: #1f2937; font-size: 13px; margin: 0; padding: 0; }
  .page { padding: 40px; max-width: 780px; margin: 0 auto; }
  .brand { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 24px; border-bottom: 2px solid #0f172a; }
  .brand h1 { font-size: 22px; margin: 0; color: #0f172a; letter-spacing: 0.02em; }
  .brand span { color: #6b7280; font-size: 12px; letter-spacing: 0.15em; text-transform: uppercase; }
  .brand-right { text-align: right; }
  .brand-right .tag { display: inline-block; padding: 4px 10px; background: #f1f5f9; color: #475569; border-radius: 999px; font-size: 11px; letter-spacing: 0.05em; }
  .meta { margin-top: 24px; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px; }
  .meta-col { min-width: 200px; }
  .meta-col h4 { font-size: 10px; letter-spacing: 0.15em; text-transform: uppercase; color: #94a3b8; margin: 0 0 8px; }
  .meta-col p { margin: 3px 0; font-size: 13px; color: #334155; }
  .meta-col .strong { color: #0f172a; font-weight: 700; font-size: 15px; }
  table.items { width: 100%; border-collapse: collapse; margin-top: 34px; }
  table.items th { text-align: left; font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase; color: #94a3b8; padding: 10px 0; border-bottom: 1px solid #e5e7eb; font-weight: 600; }
  table.items td { padding: 14px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
  table.items td.num { text-align: right; }
  .totals { margin-top: 24px; width: 100%; }
  .totals td { padding: 6px 0; font-size: 13px; }
  .totals .label { text-align: right; padding-right: 20px; color: #64748b; }
  .totals .value { text-align: right; width: 140px; font-weight: 600; color: #0f172a; }
  .totals .total .label { color: #0f172a; font-size: 12px; text-transform: uppercase; letter-spacing: 0.1em; }
  .totals .total .value { font-size: 18px; font-weight: 800; color: #0f172a; padding-top: 12px; border-top: 2px solid #0f172a; }
  .footer-note { margin-top: 40px; padding-top: 18px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #94a3b8; line-height: 1.6; }
  .footer-note strong { color: #475569; }
CSS;

$html = <<<HTML
<div class="page">
  <div class="brand">
    <div>
      <h1>V&amp;N Hotel</h1>
      <span>Invoice</span>
    </div>
    <div class="brand-right">
      <div class="tag">Reservation #{$ref}</div>
      <p style="margin: 10px 0 0; color: #64748b; font-size: 12px;">Issued {$date}</p>
    </div>
  </div>

  <div class="meta">
    <div class="meta-col">
      <h4>Billed to</h4>
      <p class="strong">{$guest_name}</p>
      <p>{$row["user_email"]}</p>
    </div>
    <div class="meta-col">
      <h4>Stay</h4>
      <p class="strong">{$row["check_in_date"]} → {$row["check_out_date"]}</p>
      <p>{$no_days} night(s) · {$row["room_number"]} · {$row["room_name"]}</p>
    </div>
    <div class="meta-col">
      <h4>Status</h4>
      <p class="strong">{$status}</p>
      <p>Paid via {$method}</p>
    </div>
  </div>

  <table class="items">
    <thead>
      <tr>
        <th>Room</th>
        <th>Type</th>
        <th class="num">Rate / night</th>
        <th class="num">Nights</th>
        <th class="num">Line total</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>{$row["room_name"]}</td>
        <td>{$row["room_type"]}</td>
        <td class="num">Ksh {$room_price}</td>
        <td class="num">{$no_days}</td>
        <td class="num">Ksh {$total}</td>
      </tr>
    </tbody>
  </table>

  <table class="totals">
    <tr><td class="label">Subtotal</td><td class="value">Ksh {$total}</td></tr>
    <tr><td class="label">Taxes &amp; fees</td><td class="value">Included</td></tr>
    <tr class="total"><td class="label">Amount paid</td><td class="value">Ksh {$total}</td></tr>
  </table>

  <div class="footer-note">
    <strong>Thank you</strong> for choosing V&amp;N Hotel. This invoice confirms your reservation and payment.
    Standard incidentals policy applies at check-in. For questions, reply to this email or contact our front desk.
  </div>
</div>
HTML;

$mpdf = new \Mpdf\Mpdf([
    'margin_left'  => 0,
    'margin_right' => 0,
    'margin_top'   => 0,
    'margin_bottom'=> 0,
    'format'       => 'A4',
]);
$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

if (ob_get_length()) ob_end_clean();

$filename = 'V&N_Invoice_' . $ref . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');

$mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
exit;