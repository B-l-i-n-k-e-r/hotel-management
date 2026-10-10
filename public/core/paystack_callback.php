<?php
require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/mail.php");

ini_set('display_errors', 0);
error_reporting(E_ALL);

$reference = $_GET["reference"] ?? ($_GET["trxref"] ?? "");

if ($reference === "") {
    header("Location: " . SROOT . "/payment.php");
    exit;
}

// Look up the payment row
$stmt = $pdo->prepare("SELECT * FROM payment WHERE payment_ref = ? LIMIT 1");
$stmt->execute([$reference]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    error_log("Paystack callback: unknown reference $reference");
    header("Location: " . SROOT . "/payment.php");
    exit;
}

// Already processed?
if ($payment["status"] === "paid") {
    header("Location: " . SROOT . "/payment.php?paid=1&reservation_id=" . (int)$payment["reservation_id"]);
    exit;
}

// Verify with Paystack
try {
    $ch = curl_init("https://api.paystack.co/transaction/verify/" . rawurlencode($reference));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
            "Cache-Control: no-cache",
        ],
        CURLOPT_TIMEOUT        => 25,
    ]);
    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($resp, true);

    $verified = (
        $http === 200
        && !empty($data["status"])
        && !empty($data["data"])
        && ($data["data"]["status"] ?? "") === "success"
    );

    if ($verified) {
        // Amount check (Paystack returns amount in kobo)
        $paid_kobo = (int)($data["data"]["amount"] ?? 0);
        $expected  = (int) round((float)$payment["amount"] * 100);

        if ($paid_kobo < $expected) {
            error_log("Paystack amount mismatch: paid=$paid_kobo expected=$expected");
            $pdo->prepare("UPDATE payment SET status='failed', payment_meta=? WHERE payment_id=?")
                ->execute([json_encode(["reason" => "amount_mismatch", "paid" => $paid_kobo]), $payment["payment_id"]]);
            header("Location: " . SROOT . "/payment.php");
            exit;
        }

        $meta = json_encode([
            "paystack_ref"    => $data["data"]["reference"] ?? null,
            "channel"         => $data["data"]["channel"] ?? null,
            "card_last4"      => $data["data"]["authorization"]["last4"] ?? null,
            "card_brand"      => $data["data"]["authorization"]["card_type"] ?? null,
            "paid_at"         => $data["data"]["paid_at"] ?? null,
            "amount_kobo"     => $paid_kobo,
        ]);

        $pdo->beginTransaction();
        $pdo->prepare("UPDATE payment SET status='paid', payment_meta=? WHERE payment_id=?")
            ->execute([$meta, $payment["payment_id"]]);
        $pdo->prepare("UPDATE reservations SET status='confirmed' WHERE reservation_id=?")
            ->execute([$payment["reservation_id"]]);
        // Mark room as booked
        $pdo->prepare("
            UPDATE rooms SET room_booked = 1,
                             check_in_date = (SELECT check_in_date FROM reservations WHERE reservation_id = ?),
                             check_out_date = (SELECT check_out_date FROM reservations WHERE reservation_id = ?)
            WHERE room_id = (SELECT room_id FROM reservations WHERE reservation_id = ?)
        ")->execute([$payment["reservation_id"], $payment["reservation_id"], $payment["reservation_id"]]);
        $pdo->commit();

        // Email the guest
        $u = $pdo->prepare("SELECT user_email, user_fname FROM users WHERE user_id = ? LIMIT 1");
        $u->execute([$payment["user_id"]]);
        $user = $u->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $html = "<h2>Payment received</h2>
                     <p>Hi " . htmlspecialchars($user["user_fname"]) . ",</p>
                     <p>We've received your card payment of Ksh " . number_format((float)$payment["amount"]) . ".</p>
                     <p>Your booking is confirmed. See you soon!</p>";
            send_mail($user["user_email"], "Card payment received — V&N Hotel", $html);
        }

        unset($_SESSION["reservation"]);
        header("Location: " . SROOT . "/payment.php?paid=1&reservation_id=" . (int)$payment["reservation_id"]);
        exit;
    }

    // Not verified
    $pdo->prepare("UPDATE payment SET status='failed', payment_meta=? WHERE payment_id=?")
        ->execute([json_encode(["reason" => "not_verified", "response" => $data]), $payment["payment_id"]]);
    $pdo->prepare("UPDATE reservations SET status='cancelled' WHERE reservation_id=?")
        ->execute([$payment["reservation_id"]]);
    header("Location: " . SROOT . "/payment.php");
    exit;
} catch (Throwable $e) {
    error_log("Paystack verify error: " . $e->getMessage());
    header("Location: " . SROOT . "/payment.php");
    exit;
}