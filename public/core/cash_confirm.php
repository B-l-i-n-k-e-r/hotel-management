<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
require_once(__DIR__ . "/../config.php");
require_once(__DIR__ . "/mail.php");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendJson("Invalid request method", 1);
}

if (!isLoggedIn()) {
    sendJson("You must be logged in.", 1);
}

if (!isset($_SESSION["reservation"])) {
    sendJson("No pending reservation found.", 1);
}

$res = $_SESSION["reservation"];
$user_id = (int) $_SESSION["user_id"];
$room_id = (int) $res["room_id"];
$amount  = (float) getRoomAmountFromId($room_id, $pdo);

try {
    $pdo->beginTransaction();

    // 1. Create the reservation
   $stmt = $pdo->prepare("
    INSERT INTO reservations
      (user_id, room_id, booking_date, check_in_date, check_out_date, no_adults, no_children, status)
    VALUES
      (:user_id, :room_id, CURDATE(), :check_in, :check_out, :adults, :children, 'pending')
");
$stmt->execute([
    ":user_id"   => $user_id,
    ":room_id"   => $room_id,
    ":check_in"  => $res["check_in_date"],
    ":check_out" => $res["check_out_date"],
    ":adults"    => (int)($res["no_adults"] ?? 0),
    ":children"  => (int)($res["no_children"] ?? 0),
]);
$reservation_id = (int) $pdo->lastInsertId();

// Flip the room to booked
$flip = $pdo->prepare("UPDATE rooms SET room_booked = 1, check_in_date = :ci, check_out_date = :co WHERE room_id = :rid");
$flip->execute([
    ":ci"  => $res["check_in_date"],
    ":co"  => $res["check_out_date"],
    ":rid" => $room_id,
]);

    // 2. Create the payment record (pending cash)
    $pay = $pdo->prepare("
        INSERT INTO payment
          (user_id, reservation_id, currency, method, status, amount)
        VALUES
          (:user_id, :reservation_id, 'KES', 'cash', 'pending', :amount)
    ");
    $pay->execute([
        ":user_id"        => $user_id,
        ":reservation_id" => $reservation_id,
        ":amount"         => (int) $amount,
    ]);

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Cash confirm failed: " . $e->getMessage());
    sendJson("Could not confirm your booking. Please try again.", 1);
}

// Optional: notify admin of the new pending reservation
try {
    $room_name = getRoomNameFromId($room_id, $pdo) ?? "Room";
    $html = "
      <h2>New cash-on-arrival reservation</h2>
      <p><strong>Reservation ID:</strong> #{$reservation_id}</p>
      <p><strong>Room:</strong> " . htmlspecialchars($room_name) . "</p>
      <p><strong>Guest:</strong> " . htmlspecialchars($_SESSION["user_fname"] . " " . $_SESSION["user_lname"]) . "</p>
      <p><strong>Check-in:</strong> " . htmlspecialchars($res["check_in_date"]) . "</p>
      <p><strong>Check-out:</strong> " . htmlspecialchars($res["check_out_date"]) . "</p>
      <p><strong>Amount due at check-in:</strong> Ksh " . number_format($amount) . "</p>
    ";
    send_mail(MAIL_ADMIN, "New cash booking #{$reservation_id}", $html);
} catch (Throwable $e) {
    error_log("Cash notify failed: " . $e->getMessage());
}

// Clear the session reservation now that it's committed
unset($_SESSION["reservation"]);

echo json_encode([
    "error" => 0,
    "message" => "Booking confirmed. Pay on arrival.",
    "reservation_id" => $reservation_id,
]);