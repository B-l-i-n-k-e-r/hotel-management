<?php
require_once(__DIR__ . "/../config.php");   // ← FIXED
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendJson("Invalid request method", 1);
}

// ----- Collect and trim inputs -----
$room_id        = (int)($_POST["room_id"] ?? 0);
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
$room_image     = trim($_POST["room_image"] ?? "");

$featured_raw  = $_POST["room_featured"] ?? "0";
$room_featured = in_array($featured_raw, ["1", "yes"], true) ? 1 : 0;

// ----- Validate room_id -----
if ($room_id <= 0) {
    sendJson("Invalid room id", 1);
}

$check = $pdo->prepare("SELECT room_id FROM rooms WHERE room_id = ?");
$check->execute([$room_id]);
if (!$check->fetchColumn()) {
    sendJson("Room not found", 1);
}

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

// Duplicate room number check (excluding this room)
$check = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE room_number = ? AND room_id != ?");
$check->execute([$room_number, $room_id]);
if ($check->fetchColumn() > 0) {
    sendJson("Another room already uses this number", 1, "room_number");
}

// ----- Handle image replacement (optional) -----
$target_file_to_delete = null;

if (isset($_FILES["new_room_image"]) && $_FILES["new_room_image"]["name"] !== "") {
    if ($_FILES["new_room_image"]["error"] !== UPLOAD_ERR_OK) {
        sendJson("Upload failed (error code " . $_FILES["new_room_image"]["error"] . ")", 1, "new_room_image");
    }

    $allowed = ["image/jpeg" => "jpg", "image/png" => "png"];
    $mime = mime_content_type($_FILES["new_room_image"]["tmp_name"]);
    if (!isset($allowed[$mime])) {
        sendJson("Only JPG and PNG images are allowed", 1, "new_room_image");
    }

    if ($_FILES["new_room_image"]["size"] > 5 * 1024 * 1024) {
        sendJson("Image must be smaller than 5MB", 1, "new_room_image");
    }

    // Image dimension check
    $size = @getimagesize($_FILES["new_room_image"]["tmp_name"]);
    if (!$size || $size[0] < 50 || $size[1] < 50) {
        sendJson("Image is invalid or too small (min 50×50)", 1, "new_room_image");
    }
    if ($size[0] > 5000 || $size[1] > 5000) {
        sendJson("Image dimensions are too large (max 5000×5000)", 1, "new_room_image");
    }

    $ext = $allowed[$mime];
    $new_filename = "room_" . bin2hex(random_bytes(8)) . "." . $ext;

    $target_dir = ROOT . DIRECTORY_SEPARATOR . "media" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "rooms" . DIRECTORY_SEPARATOR;
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    $target_file = $target_dir . $new_filename;

    if (!move_uploaded_file($_FILES["new_room_image"]["tmp_name"], $target_file)) {
        sendJson("There was an error saving the image", 1, "new_room_image");
    }

    // Only schedule old image for deletion if no other room uses it
    if ($room_image !== "" && $room_image !== $new_filename) {
        $inUse = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE room_image = ? AND room_id != ?");
        $inUse->execute([$room_image, $room_id]);
        if ($inUse->fetchColumn() == 0) {
            $old_file = $target_dir . $room_image;
            if (file_exists($old_file) && basename($old_file) === $room_image) {
                $target_file_to_delete = $old_file;
            }
        }
    }

    $room_image = $new_filename;
}

// ----- Update -----
try {
    $sql = "UPDATE rooms SET
                room_number    = :room_number,
                room_name      = :room_name,
                room_type      = :room_type,
                room_featured  = :room_featured,
                room_price     = :room_price,
                room_floor     = :room_floor,
                room_view      = :room_view,
                room_beds      = :room_beds,
                bed_type       = :bed_type,
                room_capacity  = :room_capacity,
                room_amenities = :room_amenities,
                room_image     = :room_image
            WHERE room_id = :room_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":room_id"        => $room_id,
        ":room_number"    => $room_number,
        ":room_name"      => $room_name,
        ":room_type"      => $room_type,
        ":room_featured"  => $room_featured,
        ":room_price"     => $room_price,
        ":room_floor"     => $room_floor,
        ":room_view"      => $room_view,
        ":room_beds"      => $room_beds,
        ":bed_type"       => $bed_type,
        ":room_capacity"  => $room_capacity,
        ":room_amenities" => $room_amenities,
        ":room_image"     => $room_image,
    ]);

    if ($target_file_to_delete && file_exists($target_file_to_delete)) {
        @unlink($target_file_to_delete);
    }

    $_SESSION["message"] = "Successfully edited room";
    sendJson("Room updated successfully", 0);
} catch (PDOException $e) {
    if (isset($target_file) && file_exists($target_file)) {
        @unlink($target_file);
    }
    if ($e->getCode() === "23000") {
        sendJson("Another room already uses this number", 1, "room_number");
    }
    error_log("Room update failed: " . $e->getMessage());
    sendJson("Could not update room. Please try again.", 1);
}