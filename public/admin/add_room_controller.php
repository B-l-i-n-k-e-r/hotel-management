<?php
require_once(__DIR__ . "/../config.php");   // ← FIXED
header("Content-Type: application/json; charset=utf-8");

// Only accept POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendJson("Invalid request method", 1);
}

// ----- Collect and trim inputs -----
$room_number    = trim($_POST["room_number"] ?? "");
$room_name      = trim($_POST["room_name"] ?? "");
$room_type      = trim($_POST["room_type"] ?? "");
$room_floor     = trim($_POST["room_floor"] ?? "");
$room_amenities = trim($_POST["amenities"] ?? "");
$room_beds      = trim($_POST["room_beds"] ?? "");
$room_capacity  = trim($_POST["room_capacity"] ?? "");
$bed_type       = trim($_POST["bed_type"] ?? "");
$room_price     = trim($_POST["room_price"] ?? "");
$room_view      = trim($_POST["room_view"] ?? "");

// Accept both "1"/"0" and "yes"/"no" for featured
$featured_raw  = $_POST["room_featured"] ?? "0";
$room_featured = in_array($featured_raw, ["1", "yes"], true) ? 1 : 0;

// ----- Validate required fields -----
$required = [
    "room_number"   => $room_number,
    "room_name"     => $room_name,
    "room_type"     => $room_type,
    "room_floor"    => $room_floor,
    "amenities"     => $room_amenities,
    "room_beds"     => $room_beds,
    "room_capacity" => $room_capacity,
    "bed_type"      => $bed_type,
    "room_price"    => $room_price,
    "room_view"     => $room_view,
];

foreach ($required as $field => $value) {
    if ($value === "") {
        $label = ucwords(str_replace("_", " ", $field));
        sendJson("$label is required", 1, $field);
    }
}

// Numeric sanity
if (!is_numeric($room_price) || (float)$room_price <= 0) {
    sendJson("Room price must be a positive number", 1, "room_price");
}
if (!is_numeric($room_capacity) || (int)$room_capacity <= 0) {
    sendJson("Room capacity must be a positive number", 1, "room_capacity");
}
if (!is_numeric($room_beds) || (int)$room_beds <= 0) {
    sendJson("Number of beds must be a positive number", 1, "room_beds");
}

// Duplicate room number check
$check = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE room_number = ?");
$check->execute([$room_number]);
if ($check->fetchColumn() > 0) {
    sendJson("A room with this number already exists", 1, "room_number");
}

// ----- Handle image upload -----
if (!isset($_FILES["room_image"]) || $_FILES["room_image"]["name"] === "") {
    sendJson("Please select a valid image", 1, "room_image");
}

if ($_FILES["room_image"]["error"] !== UPLOAD_ERR_OK) {
    sendJson("Upload failed (error code " . $_FILES["room_image"]["error"] . ")", 1, "room_image");
}

$allowed = ["image/jpeg" => "jpg", "image/png" => "png"];
$mime = mime_content_type($_FILES["room_image"]["tmp_name"]);
if (!isset($allowed[$mime])) {
    sendJson("Only JPG and PNG images are allowed", 1, "room_image");
}

if ($_FILES["room_image"]["size"] > 5 * 1024 * 1024) {
    sendJson("Image must be smaller than 5MB", 1, "room_image");
}

// Image dimension check (blocks decompression bombs / fake images)
$size = @getimagesize($_FILES["room_image"]["tmp_name"]);
if (!$size || $size[0] < 50 || $size[1] < 50) {
    sendJson("Image is invalid or too small (min 50×50)", 1, "room_image");
}
if ($size[0] > 5000 || $size[1] > 5000) {
    sendJson("Image dimensions are too large (max 5000×5000)", 1, "room_image");
}

// Generate unique filename — no collisions
$ext = $allowed[$mime];
$room_image = "room_" . bin2hex(random_bytes(8)) . "." . $ext;

$target_dir = ROOT . DIRECTORY_SEPARATOR . "media" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "rooms" . DIRECTORY_SEPARATOR;
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
}
$target_file = $target_dir . $room_image;

if (!move_uploaded_file($_FILES["room_image"]["tmp_name"], $target_file)) {
    sendJson("There was an error saving the image", 1, "room_image");
}

// ----- Insert -----
try {
    $sql = "INSERT INTO rooms
            (room_number, room_name, room_type, room_featured, room_price, room_image, room_floor, room_view, room_beds, bed_type, room_capacity, room_amenities)
            VALUES
            (:room_number, :room_name, :room_type, :room_featured, :room_price, :room_image, :room_floor, :room_view, :room_beds, :bed_type, :room_capacity, :room_amenities)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":room_number"   => $room_number,
        ":room_name"     => $room_name,
        ":room_type"     => $room_type,
        ":room_featured" => $room_featured,
        ":room_price"    => $room_price,
        ":room_image"    => $room_image,
        ":room_floor"    => $room_floor,
        ":room_view"     => $room_view,
        ":room_beds"     => $room_beds,
        ":bed_type"      => $bed_type,
        ":room_capacity" => $room_capacity,
        ":room_amenities"=> $room_amenities,
    ]);

    $_SESSION["message"] = "Successfully added new room";
    sendJson("Room added successfully", 0);
} catch (PDOException $e) {
    if (file_exists($target_file)) {
        @unlink($target_file);
    }
    error_log("Room insert failed: " . $e->getMessage());
    sendJson("Could not save room. Please try again.", 1);
}