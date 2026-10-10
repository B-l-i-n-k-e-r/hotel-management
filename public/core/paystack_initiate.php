<?php
require_once(__DIR__ . "/../config.php");
header("Content-Type: application/json; charset=utf-8");
ini_set('display_errors', 0);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] !== "POST") sendJson("Invalid request method", 1);
if (!isLoggedIn()) sendJson("You must be logged in.", 1);
if (!isset($_SESSION["reservation"])) sendJson("No pending reservation found.", 1);

$res    = $_SESSION["reservation"];
$amount = (float) getRoomAmountFromId($res["room_id"], $pdo);
if ($amount <= 0) sendJson("Invalid amount for this booking.", 1);

$email = $_SESSION["user_email"] ?? "";
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJson("A valid email is required to pay by card.", 1);
}

// Paystack amount is in the smallest currency unit (kobo for NGN, cents for USD).
// For KES, Paystack expects the amount in *cents* too (KES is 2-decimal).
$amount_kobo = (int) round($amount * 100);

$reference = "VN-" . strtoupper(bin2hex(random_bytes(6))) . "-" . time();

// Create pending reservation + payment upfront
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO reservations
          (user_id, room_id, booking_date, check_in_date, check_out_date, no_adults, no_children, status)
        VALUES
          (:user_id, :room_id, CURDATE(), :ci, :co, :ad, :ch, 'pending')
    ");
    $stmt->execute([
        ":user_id" => (int)$_SESSION["user_id"],
        ":room_id" => (int)$res["room_id"],
        ":ci"      => $res["check_in_date"],
        ":co"      => $res["check_out_date"],
        ":ad"      => (int)$res["no_adults"],
        ":ch"      => (int)$res["no_children"],
    ]);
    $reservation_id = (int)$pdo->lastInsertId();

    $stmt = $pdo->prepare("
        INSERT INTO payment
          (user_id, reservation_id, currency, method, status, amount, payment_ref)
        VALUES
          (:user_id, :rid, 'KES', 'card', 'pending', :amt, :ref)
    ");
    $stmt->execute([
        ":user_id" => (int)$_SESSION["user_id"],
        ":rid"     => $reservation_id,
        ":amt"     => (int)round($amount),
        ":ref"     => $reference,
    ]);
    $payment_id = (int)$pdo->lastInsertId();

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Paystack pre-insert failed: " . $e->getMessage());
    sendJson("Could not start card payment. Please try again.", 1);
}

// Call Paystack transaction/initialize
$payload = [
    "email"        => $email,
    "amount"       => $amount_kobo,
    "currency"     => "KES",
    "reference"    => $reference,
    "callback_url" => PAYSTACK_CALLBACK_URL,
    "metadata"     => [
        "reservation_id" => $reservation_id,
        "payment_id"     => $payment_id,
        "user_id"        => (int)$_SESSION["user_id"],
        "room_id"        => (int)$res["room_id"],
    ],
];

try {
    $ch = curl_init("https://api.paystack.co/transaction/initialize");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
            "Content-Type: application/json",
            "Cache-Control: no-cache",
        ],
        CURLOPT_TIMEOUT        => 25,
    ]);
    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($resp, true);

    if ($http !== 200 || empty($data["status"]) || empty($data["data"]["authorization_url"])) {
        error_log("Paystack init failed: $resp");
        $pdo->prepare("UPDATE payment SET status='failed' WHERE payment_id=?")->execute([$payment_id]);
        $pdo->prepare("UPDATE reservations SET status='cancelled' WHERE reservation_id=?")->execute([$reservation_id]);
        sendJson($data["message"] ?? "Could not reach Paystack. Please try again.", 1);
    }

    echo json_encode([
        "error"             => 0,
        "authorization_url" => $data["data"]["authorization_url"],
        "reference"         => $reference,
        "reservation_id"    => $reservation_id,
    ]);
} catch (Throwable $e) {
    error_log("Paystack init error: " . $e->getMessage());
    sendJson("Paystack error.", 1);
}