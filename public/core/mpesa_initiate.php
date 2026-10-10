<?php
require_once(__DIR__ . "/../config.php");
header("Content-Type: application/json; charset=utf-8");
ini_set('display_errors', 0);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] !== "POST") sendJson("Invalid request method", 1);
if (!isLoggedIn()) sendJson("You must be logged in.", 1);
if (!isset($_SESSION["reservation"])) sendJson("No pending reservation found.", 1);

$phone = preg_replace('/\D+/', '', trim($_POST["phone"] ?? ""));
if (preg_match('/^0(7\d{8}|1\d{8})$/', $phone))         $phone = "254" . substr($phone, 1);
elseif (!preg_match('/^254(7\d{8}|1\d{8})$/', $phone))  sendJson("Enter a valid Safaricom number.", 1);

$res    = $_SESSION["reservation"];
$amount = (float) getRoomAmountFromId($res["room_id"], $pdo);
if ($amount <= 0) sendJson("Invalid amount for this booking.", 1);

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
          (:user_id, :rid, 'KES', 'mpesa', 'pending', :amt, '')
    ");
    $stmt->execute([
        ":user_id" => (int)$_SESSION["user_id"],
        ":rid"     => $reservation_id,
        ":amt"     => (int)round($amount),
    ]);
    $payment_id = (int)$pdo->lastInsertId();

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("M-Pesa pre-insert failed: " . $e->getMessage());
    sendJson("Could not start payment. Please try again.", 1);
}

$timestamp = date("YmdHis");
$password  = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);

try {
    $ch = curl_init("https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ["Authorization: Basic " . base64_encode(MPESA_CONSUMER_KEY . ":" . MPESA_CONSUMER_SECRET)],
        CURLOPT_TIMEOUT => 20,
    ]);
    $resp = curl_exec($ch); $http = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($http !== 200) { error_log("M-Pesa OAuth: $resp"); sendJson("Could not reach M-Pesa.", 1); }
    $token = json_decode($resp, true)["access_token"] ?? "";
    if (!$token) sendJson("M-Pesa auth failed.", 1);

    $payload = [
        "BusinessShortCode" => MPESA_SHORTCODE,
        "Password"          => $password,
        "Timestamp"         => $timestamp,
        "TransactionType"   => "CustomerPayBillOnline",
        "Amount"            => (int) round($amount),
        "PartyA"            => $phone,
        "PartyB"            => MPESA_SHORTCODE,
        "PhoneNumber"       => $phone,
        "CallBackURL"       => MPESA_CALLBACK_URL,
        "AccountReference"  => "VN-" . $reservation_id,
        "TransactionDesc"   => "Hotel reservation #" . $reservation_id,
    ];

    $ch = curl_init("https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => ["Authorization: Bearer $token", "Content-Type: application/json"],
        CURLOPT_TIMEOUT => 25,
    ]);
    $resp = curl_exec($ch); $http = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    $data = json_decode($resp, true);

    if ($http !== 200 || empty($data["CheckoutRequestID"])) {
        error_log("M-Pesa STK: $resp");
        $pdo->prepare("UPDATE payment SET status='failed' WHERE payment_id=?")->execute([$payment_id]);
        $pdo->prepare("UPDATE reservations SET status='cancelled' WHERE reservation_id=?")->execute([$reservation_id]);
        sendJson($data["errorMessage"] ?? "Could not initiate M-Pesa payment.", 1);
    }

    $checkout_id = $data["CheckoutRequestID"];
    $pdo->prepare("UPDATE payment SET payment_ref=? WHERE payment_id=?")->execute([$checkout_id, $payment_id]);

    echo json_encode([
        "error" => 0,
        "message" => "STK push sent. Check your phone.",
        "checkout_request_id" => $checkout_id,
        "reservation_id" => $reservation_id,
        "payment_id" => $payment_id,
    ]);
} catch (Throwable $e) {
    error_log("M-Pesa initiate error: " . $e->getMessage());
    sendJson("M-Pesa error.", 1);
}