<?php
require_once(__DIR__ . "/../../config.php");
header("Content-Type: application/json; charset=utf-8");

if (!isLoggedIn() || !isAdmin($pdo)) {
    echo json_encode(["error" => 1, "message" => "Not authorized"]);
    exit;
}

$sql = "SELECT
          reservations.reservation_id,
          reservations.room_id,
          r.room_number,
          r.room_name,
          r.check_in_date,
          r.check_out_date
        FROM reservations
        LEFT JOIN rooms AS r ON r.room_id = reservations.room_id
        WHERE r.check_in_date IS NOT NULL
          AND r.check_out_date IS NOT NULL
        ORDER BY r.check_in_date ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$events = [];
$palette = ["#be0854", "#0f1115", "#2c4a7c", "#15803d", "#9a0640", "#6d28d9"];
$i = 0;

foreach ($rows as $row) {
    $events[] = [
        "id"    => (int)$row["reservation_id"],
        "title" => "Room " . $row["room_number"] . " — " . $row["room_name"],
        "start" => $row["check_in_date"],
        "end"   => $row["check_out_date"], // FullCalendar's `end` is exclusive; see note below
        "color" => $palette[$i % count($palette)],
        "extendedProps" => [
            "room_id"        => (int)$row["room_id"],
            "reservation_id" => (int)$row["reservation_id"],
        ],
    ];
    $i++;
}

echo json_encode(["error" => 0, "events" => $events]);