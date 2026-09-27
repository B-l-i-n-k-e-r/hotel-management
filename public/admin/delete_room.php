<?php
require_once(__DIR__ . "/../config.php");

if (!isset($_GET["room_id"]) || $_GET["room_id"] === "") {
    $_SESSION["error"] = "Room id cannot be empty";
    header("Location: rooms.php");
    exit;
}

$id  = (int)$_GET["room_id"];
$row = getRoomFromId($pdo, $id);

if (!$row) {
    $_SESSION["error"] = "Room not found";
    header("Location: rooms.php");
    exit;
}

if ($row["room_booked"] == 1) {
    $_SESSION["error"] = "Cannot delete a room with an active booking. Cancel the booking first.";
    header("Location: rooms.php");
    exit;
}

// Handle POST before any output
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit"])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE room_id = ?");
        $stmt->execute([$id]);

        if (!empty($row["room_image"])) {
            $inUse = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE room_image = ?");
            $inUse->execute([$row["room_image"]]);
            if ($inUse->fetchColumn() == 0) {
                $img_path = ROOT . DIRECTORY_SEPARATOR . "media" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "rooms" . DIRECTORY_SEPARATOR . $row["room_image"];
                if (file_exists($img_path) && basename($img_path) === $row["room_image"]) {
                    @unlink($img_path);
                }
            }
        }

        $_SESSION["message"] = "Room deleted successfully";
    } catch (PDOException $e) {
        error_log("Room delete failed: " . $e->getMessage());
        $_SESSION["error"] = "Could not delete room. Please try again.";
    }
    header("Location: rooms.php");
    exit;
}

$admin_page  = "rooms";
$admin_title = "Delete room";
$admin_sub   = "This action cannot be undone";

include(__DIR__ . "/includes/header.php");

if (!isLoggedIn() || !isAdmin($pdo)) {
    header("Location: ../login.php");
    exit;
}
?>

<section class="admin-form-card delete-card">
  <div class="delete-warning">
    <div class="delete-icon"><i class="fa fa-exclamation-triangle"></i></div>
    <div>
      <h2>Are you sure you want to delete this room?</h2>
      <p>Room <strong><?= htmlspecialchars($row["room_number"]) ?></strong> — <?= htmlspecialchars($row["room_name"]) ?> will be permanently removed from the inventory.</p>
    </div>
  </div>

  <div class="admin-table-card" style="margin-top:24px;">
    <div class="admin-table-wrap">
      <table class="admin-table" style="min-width:0;">
        <tbody>
          <tr><td><strong>Room ID</strong></td><td class="num"><?= (int)$row["room_id"] ?></td></tr>
          <tr><td><strong>Room number</strong></td><td class="num"><?= htmlspecialchars($row["room_number"]) ?></td></tr>
          <tr><td><strong>Name</strong></td><td><?= htmlspecialchars($row["room_name"]) ?></td></tr>
          <tr><td><strong>Type</strong></td><td><span class="pill pill-neutral"><?= htmlspecialchars($row["room_type"]) ?></span></td></tr>
          <tr><td><strong>Floor</strong></td><td class="num"><?= htmlspecialchars($row["room_floor"]) ?></td></tr>
          <tr><td><strong>View</strong></td><td><?= htmlspecialchars($row["room_view"]) ?></td></tr>
          <tr><td><strong>Beds</strong></td><td><?= htmlspecialchars($row["room_beds"]) ?> / <?= htmlspecialchars($row["bed_type"]) ?></td></tr>
          <tr><td><strong>Capacity</strong></td><td class="num"><?= htmlspecialchars($row["room_capacity"]) ?></td></tr>
          <tr><td><strong>Price</strong></td><td class="num"><strong>Ksh <?= number_format((float)$row["room_price"]) ?></strong></td></tr>
          <tr><td><strong>Amenities</strong></td><td><?= htmlspecialchars($row["room_amenities"]) ?></td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <form method="POST" class="form-actions">
    <a href="rooms.php" class="btn-ghost-pill btn-ghost-light">Cancel</a>
    <button type="submit" name="submit" class="btn-danger">
      <i class="fa fa-trash"></i>&nbsp; Delete room
    </button>
  </form>
</section>

<?php include(__DIR__ . "/includes/footer.php"); ?>