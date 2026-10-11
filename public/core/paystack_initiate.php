<?php
require_once(__DIR__ . "/../config.php");
header("Content-Type: application/json; charset=utf-8");
ini_set('display_errors', 0);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] !== "POST") sendJson("Invalid request method", 1);
if (!isLoggedIn()) sendJson("You must be logged in.", 1);
if (!isset($_SESSION["reservation"])) sendJson("No pending reservation found.", 1);

$email = trim($_POST["email"] ?? ($_SESSION["user_email"] ?? ""));
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJson("A valid email is required to pay by card.", 1);
}

$res    = $_SESSION["reservation"];
$amount = (float) getRoomAmountFromId($res["room_id"], $pdo);
if ($amount <= 0) sendJson("Invalid amount for this booking.", 1);

$amount_kobo = (int) round($amount * 100);
$reference   = "VN-" . strtoupper(bin2hex(random_bytes(6))) . "-" . time();

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

    echo json_encode([
        "error"          => 0,
        "reference"      => $reference,
        "amount_kobo"    => $amount_kobo,
        "reservation_id" => $reservation_id,
        "payment_id"     => $payment_id,
        "email"          => $email,
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Paystack initiate error: " . $e->getMessage());
    sendJson("Could not start card payment. Please try again.", 1);
}