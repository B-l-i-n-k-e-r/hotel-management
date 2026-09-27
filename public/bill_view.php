<?php
include("./config.php");
require '../vendor/autoload.php';

// Get details
if (isset($_GET["reservation_id"])) {

    $sql = "SELECT room.check_in_date, room.check_out_date, room.room_name, room.room_type, room.room_price, room.room_id, p.payment_id, p.amount 
            FROM reservations 
            LEFT JOIN rooms as room ON room.room_id = reservations.room_id 
            LEFT JOIN payment as p ON p.reservation_id = reservations.reservation_id 
            WHERE reservations.reservation_id = :reservation_id";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
        ":reservation_id" => $_GET["reservation_id"],
    ));

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $payment_id = $row["payment_id"];
        $total_cost = $row["amount"];
        $room_id = $row["room_id"];
        $room_price = $row["room_price"];
        $room_type = $row["room_type"];
        $room_name = $row["room_name"];
        $date = date("d-m-Y", time());

        // Safe day interval calculation across month increments
        $datetime1 = new DateTime($row["check_in_date"]);
        $datetime2 = new DateTime($row["check_out_date"]);
        $interval = $datetime1->diff($datetime2);
        $no_days = $interval->days;
        if ($no_days == 0) { $no_days = 1; } // Fallback baseline logic

        $mpdf = new \Mpdf\Mpdf();   
        $css = file_get_contents('./vendor/bill_style.css');
        
        $html = '<body>
        <div id="page-wrap">
    
            <p id="header">INVOICE</p>
            <div style="clear:both"></div>
    
            <div id="customer">
    
                <p id="customer-title">V&N Hotel</p>
    
                <table id="meta">
                    <tr>
                        <td class="meta-head">Invoice #</td>
                        <td>
                            <p>'.$payment_id.'</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="meta-head">Date</td>
                        <td>
                            <p id="date">'.$date.'</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="meta-head">Amount Due</td>
                        <td>
                            <div class="due">Ksh '.number_format($total_cost, 2).'</div>
                        </td>
                    </tr>
    
                </table>
    
            </div>
    
            <table id="items">
    
                <tr>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Unit Cost</th>
                    <th>Days</th>
                    <th>Price</th>
                </tr>
    
                <tr class="item-row">
                    <td class="item-name">
                        <div class="delete-wpr">
                            <p>'.$room_name.'</p>
                        </div>
                    </td>
                    <td class="description">
                        <p>'.$room_type.'</p>
                    </td>
                    <td>
                        <p class="cost">Ksh '.number_format($room_price, 2).'</p>
                    </td>
                    <td>
                        <p class="qty">'.$no_days.' days</p>
                    </td>
                    <td><span class="price">Ksh '.number_format($total_cost, 2).'</span></td>
                </tr>
    
                <tr>
                    <td colspan="2" class="blank"> </td>
                    <td colspan="2" class="total-line">Subtotal</td>
                    <td class="total-value">
                        <div id="subtotal">Ksh '.number_format($total_cost, 2).'</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="blank"> </td>
                    <td colspan="2" class="total-line">Total</td>
                    <td class="total-value">
                        <div id="total">Ksh '.number_format($total_cost, 2).'</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="blank"> </td>
                    <td colspan="2" class="total-line">Amount Paid</td>
                    <td class="total-value">
                        <p id="paid">Ksh '.number_format($total_cost, 2).'</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="blank"> </td>
                    <td colspan="2" class="total-line balance">Balance Due</td>
                    <td class="total-value balance">
                        <div class="due">Ksh 0.00</div>
                    </td>
                </tr>
    
            </table>
    
            <div id="terms">
                <h5>Terms</h5>
                <p>Thank you for choosing V&N Hotel. Pre-paid booking balance verified. Standard incidentals policy applies at check-in.</p>
            </div>
    
        </div>
        </body>';
    
        $mpdf->writeHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->writeHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

        if (ob_get_length()) {
            ob_end_clean();
        }

        $filename = 'hotel_reservation_' . $_GET["reservation_id"] . '.pdf';

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
        exit;
    }
}
?>
<?php include(__DIR__ . "/includes/header.php"); ?>
<body class="has-nav">
  <header class="page-header">
    <?php include(__DIR__ . "/includes/navbar.php"); ?>
    <div class="page-header-inner container">
      <span class="eyebrow light">Invoice</span>
      <h1>Invoice not found</h1>
      <p>We couldn't verify those reservation details.</p>
    </div>
  </header>

  <main>
    <section class="container section-pad" style="text-align: center;">
      <p style="color: var(--muted); margin-bottom: 24px;">
        The invoice you're looking for doesn't exist or has been removed.
      </p>
      <a href="<?= SROOT ?>/index.php" class="btn-primary-pill" style="display: inline-flex; max-width: 240px; margin: 0 auto;">
        Return to home
      </a>
    </section>
  </main>

  <?php include(__DIR__ . "/includes/footer.php"); ?>
</body>
</html>