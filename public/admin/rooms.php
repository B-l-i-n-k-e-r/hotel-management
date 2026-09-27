<?php
require_once(__DIR__ . "/../config.php");

$rows = getAllRooms($pdo) ?: [];

$admin_page  = "rooms";
$admin_title = "Rooms";
$admin_sub   = count($rows) . " room" . (count($rows) === 1 ? "" : "s") . " in inventory";

include(__DIR__ . "/includes/header.php");

if (!isLoggedIn() || !isAdmin($pdo)) {
    header("Location: ../login.php");
    exit;
}
?>

<div class="admin-topbar-actions">
  <a href="add_room.php" class="btn-primary-pill btn-pill-inline">
    <i class="fa fa-plus"></i>&nbsp; Add room
  </a>
</div>

<?php if (isset($_SESSION["message"])): ?>
  <div class="admin-flash admin-flash-success">
    <?= htmlspecialchars($_SESSION["message"]) ?>
  </div>
  <?php unset($_SESSION["message"]); ?>
<?php endif; ?>
<?php if (isset($_SESSION["error"])): ?>
  <div class="admin-flash"><?= htmlspecialchars($_SESSION["error"]) ?></div>
  <?php unset($_SESSION["error"]); ?>
<?php endif; ?>

<section class="admin-table-card">
  <?php if (empty($rows)): ?>
    <div class="admin-empty">
      <div class="admin-empty-icon"><i class="fa fa-home"></i></div>
      <h2>No rooms yet</h2>
      <p>Add your first room to start accepting bookings.</p>
      <a href="add_room.php" class="btn-primary-pill">Add a room</a>
    </div>
  <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Type</th>
            <th>Featured</th>
            <th>Image</th>
            <th class="num">Price</th>
            <th>Status</th>
            <th>Check in</th>
            <th>Check out</th>
            <th>Floor</th>
            <th>View</th>
            <th>Beds</th>
            <th>Cap.</th>
            <th class="actions">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td data-label="#"><strong><?= htmlspecialchars($row["room_number"]) ?></strong></td>
              <td data-label="Name"><?= htmlspecialchars($row["room_name"]) ?></td>
              <td data-label="Type"><span class="pill pill-neutral"><?= htmlspecialchars($row["room_type"]) ?></span></td>
              <td data-label="Featured">
                <?php if ($row["room_featured"] == 1): ?>
                  <span class="pill pill-success">Featured</span>
                <?php else: ?>
                  <span class="pill pill-muted">—</span>
                <?php endif; ?>
              </td>
              <td data-label="Image" class="img-cell">
                <img src="<?= htmlspecialchars(IMAGEROOT . $row["room_image"]) ?>" alt="" loading="lazy">
              </td>
              <td data-label="Price" class="num">
                <strong><?= number_format((float)$row["room_price"]) ?></strong>
              </td>
              <td data-label="Status">
                <?php if ($row["room_booked"] == 1): ?>
                  <span class="pill pill-neutral">Booked</span>
                <?php else: ?>
                  <span class="pill pill-success">Available</span>
                <?php endif; ?>
              </td>
              <td data-label="Check in"><?= $row["check_in_date"] ? htmlspecialchars($row["check_in_date"]) : "—" ?></td>
              <td data-label="Check out"><?= $row["check_out_date"] ? htmlspecialchars($row["check_out_date"]) : "—" ?></td>
              <td data-label="Floor"><?= htmlspecialchars($row["room_floor"]) ?></td>
              <td data-label="View"><?= htmlspecialchars($row["room_view"]) ?></td>
              <td data-label="Beds"><?= htmlspecialchars(($row["room_beds"] ?? "—") . " / " . ($row["bed_type"] ?? "—")) ?></td>
              <td data-label="Capacity"><?= htmlspecialchars($row["room_capacity"]) ?></td>
              <td data-label="Actions" class="actions">
                <a href="edit_room.php?room_id=<?= (int)$row["room_id"] ?>"
                   class="icon-btn icon-btn-edit" title="Edit room" aria-label="Edit room">
                  <i class="fa fa-pencil"></i>
                </a>
                <a href="delete_room.php?room_id=<?= (int)$row["room_id"] ?>"
                   class="icon-btn icon-btn-delete" title="Delete room" aria-label="Delete room"
                   onclick="return confirm('Delete this room? This cannot be undone.');">
                  <i class="fa fa-trash"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>

<?php include(__DIR__ . "/includes/footer.php"); ?>