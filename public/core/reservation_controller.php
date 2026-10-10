<?php
require("../config.php");
require_once(__DIR__ . "/mail.php");

ini_set('display_errors', 0);
error_reporting(E_ALL);
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendJson("Invalid request method", 1);
}

// ---- Step 1: user submitted the reservation form ----
if (isset($_POST["action"]) && $_POST["action"] === "reservation") {

    $check_in  = trim($_POST["check_in_date"]  ?? "");
    $check_out = trim($_POST["check_out_date"] ?? "");
    $adults    = (int)($_POST["no_adults"]   ?? 0);
    $children  = (int)($_POST["no_children"] ?? 0);
    $room_id   = (int)($_POST["room_id"]     ?? 0);

    if ($check_in === "" || $check_out === "") {
        sendJson("Please select both check-in and check-out dates.", 1);
    }
    if (strtotime($check_out) <= strtotime($check_in)) {
        sendJson("Check-out must be after check-in.", 1);
    }
    if ($room_id <= 0) {
        sendJson("Please select a room.", 1);
    }
    if ($adults < 1) {
        sendJson("At least one adult is required.", 1);
    }

    // Confirm the room actually exists
    $check = $pdo->prepare("SELECT room_id FROM rooms WHERE room_id = ? LIMIT 1");
    $check->execute([$room_id]);
    if (!$check->fetch()) {
        sendJson("That room no longer exists. Please choose another.", 1);
    }

    $_SESSION["reservation"] = [
        "check_in_date"  => $check_in,
        "check_out_date" => $check_out,
        "no_children"    => $children,
        "no_adults"      => $adults,
        "room_id"        => $room_id,
    ];

    sendJson("OK", 0);
    return;
}

// ---- Step 2: final confirmation (card-flow path; cash uses cash_confirm.php) ----
if (isset($_POST['confirm_booking'])) {

    if (!isset($_SESSION["reservation"])) {
        sendJson("No pending reservation. Please start again.", 1);
    }

    $res           = $_SESSION["reservation"];
    $user_id       = (int) $_SESSION["user_id"];
    $check_in_date = $res["check_in_date"];
    $check_out_date= $res["check_out_date"];
    $no_children   = (int) $res["no_children"];
    $no_adults     = (int) $res["no_adults"];
    $room_id       = (int) $res["room_id"];

    if ($room_id <= 0) {
        sendJson("Your booking is missing a room. Please go back and select one.", 1);
    }

    $amount = (float) getRoomAmountFromId($room_id, $pdo);

    try {
        $pdo->beginTransaction();

        // Mark room as booked with the stay dates
        $stmt = $pdo->prepare("UPDATE rooms SET room_booked = 1, check_in_date = :ci, check_out_date = :co WHERE room_id = :rid");
        $stmt->execute([":ci" => $check_in_date, ":co" => $check_out_date, ":rid" => $room_id]);

        // Create the reservation
        $stmt = $pdo->prepare("
            INSERT INTO reservations
              (user_id, room_id, booking_date, check_in_date, check_out_date, no_adults, no_children, status)
            VALUES
              (:user_id, :room_id, CURDATE(), :ci, :co, :adults, :children, 'confirmed')
        ");
        $stmt->execute([
            ":user_id"  => $user_id,
            ":room_id"  => $room_id,
            ":ci"       => $check_in_date,
            ":co"       => $check_out_date,
            ":adults"   => $no_adults,
            ":children" => $no_children,
        ]);
        $reservation_id = (int) $pdo->lastInsertId();

        // Create the payment row
        $stmt = $pdo->prepare("
            INSERT INTO payment
              (user_id, reservation_id, currency, method, status, amount)
            VALUES
              (:user_id, :reservation_id, 'KES', 'card', 'pending', :amount)
        ");
        $stmt->execute([
            ":user_id"        => $user_id,
            ":reservation_id" => $reservation_id,
            ":amount"         => (int) $amount,
        ]);

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("Reservation confirm failed: " . $e->getMessage());
        sendJson("Could not complete your booking. Please try again.", 1);
    }

    unset($_SESSION["reservation"]);

    echo json_encode([
        "error" => 0,
        "message" => ["reservation_id" => $reservation_id],
    ]);
    return;
}

// Fallthrough
sendJson("Unknown action", 1);