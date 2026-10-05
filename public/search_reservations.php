<?php
require_once(__DIR__ . "/config.php");
header('Content-Type: application/json');

// --- Read input ---
$fromRaw = $_GET['from'] ?? '';
$toRaw   = $_GET['to']   ?? '';

if (!$fromRaw || !$toRaw) {
    echo json_encode(['error' => 'Please select both From and To dates.']);
    exit;
}

// --- Normalise dates to YYYY-MM-DD ---
// The picker sends MM/DD/YYYY. If your picker is DD/MM/YYYY, swap m/d/Y -> d/m/Y below.
function normalise_date($raw) {
    $raw = trim($raw);
    // Already ISO?
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) return $raw;
    $dt = DateTime::createFromFormat('m/d/Y', $raw);
    if (!$dt) $dt = DateTime::createFromFormat('d/m/Y', $raw);
    return $dt ? $dt->format('Y-m-d') : null;
}

$from = normalise_date($fromRaw);
$to   = normalise_date($toRaw);

if (!$from || !$to) {
    echo json_encode(['error' => 'Invalid date format. Expected MM/DD/YYYY.']);
    exit;
}

if (strtotime($to) <= strtotime($from)) {
    echo json_encode(['error' => '"To" date must be after "From" date.']);
    exit;
}

try {
    $sql = "
        SELECT
            r.room_id, r.room_name, r.room_type, r.room_price,
            r.room_image, r.room_capacity, r.room_view,
            r.room_beds, r.bed_type, r.room_amenities
        FROM rooms r
        WHERE r.room_id NOT IN (
            SELECT res.room_id
            FROM reservations res
            WHERE res.check_in_date  IS NOT NULL
              AND res.check_out_date IS NOT NULL
              AND res.check_in_date  < :to
              AND res.check_out_date > :from
        )
        ORDER BY r.room_price ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':from' => $from, ':to' => $to]);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $nights = max((int)((strtotime($to) - strtotime($from)) / 86400), 1);

    $out = [];
    foreach ($rooms as $r) {
        $price = (float)$r['room_price'];
        $out[] = [
            'id'             => $r['room_id'],
            'room_name'      => $r['room_name'],
            'room_type'      => $r['room_type'],
            'price'          => number_format($price, 2),
            'total'          => number_format($price * $nights, 2),
            'image'          => IMAGEROOT . $r['room_image'],
            'capacity'       => $r['room_capacity'],
            'view'           => $r['room_view'],
            'beds'           => $r['room_beds'],
            'bed_type'       => $r['bed_type'],
            'amenities'      => $r['room_amenities'],
            'check_in_date'  => $from,
            'check_out_date' => $to,
            'nights'         => $nights,
        ];
    }

    echo json_encode(['rooms' => $out]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Search failed: ' . $e->getMessage()]);
}