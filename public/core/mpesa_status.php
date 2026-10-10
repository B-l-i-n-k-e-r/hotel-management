<?php
require_once(__DIR__ . "/../config.php");
header("Content-Type: application/json; charset=utf-8");

$checkout_id = $_GET["checkout_id"] ?? "";
if ($checkout_id === "") sendJson("Missing checkout_id", 1);

try {
    $stmt = $pdo->prepare("SELECT status, payment_ref, reservation_id FROM payment WHERE payment_ref = ? OR payment_meta LIKE ? LIMIT 1");
    $stmt->execute([$checkout_id, "%$checkout_id%"]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) sendJson("Not found", 1);
    echo json_encode(["error" => 0, "status" => $row["status"], "reservation_id" => $row["reservation_id"]]);
} catch (Throwable $e) {
    sendJson("Lookup failed", 1);
}