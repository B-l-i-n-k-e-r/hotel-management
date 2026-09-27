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

$admin_page  = "rooms";
$admin_title = "Edit room";
$admin_sub   = "Room " . $row["room_number"] . " — " . $row["room_name"];

include(__DIR__ . "/includes/header.php");

if (!isLoggedIn() || !isAdmin($pdo)) {
    header("Location: ../login.php");
    exit;
}

$isFeatured = ((int)$row["room_featured"] === 1);
?>

<div class="admin-topbar-actions">
  <a href="rooms.php" class="btn-ghost-pill btn-ghost-light">
    <i class="fa fa-arrow-left"></i>&nbsp; Back to rooms
  </a>
</div>

<?php if (isset($_SESSION["message"])): ?>
  <div class="admin-flash admin-flash-success">
    <?= htmlspecialchars($_SESSION["message"]) ?>
  </div>
  <?php unset($_SESSION["message"]); ?>
<?php endif; ?>

<section class="admin-form-card">
  <form action="" id="edit_room_form" method="post" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="room_id" value="<?= (int)$id ?>" />
    <div id="error-form" class="form-error" hidden></div>

    <div class="form-grid">
      <div class="form-col">
        <div class="form-group">
          <label class="form-label" for="room_number">Room number</label>
          <input type="number" name="room_number" id="room_number" class="form-control" value="<?= htmlspecialchars($row["room_number"]) ?>" required />
          <small id="room_number_error" class="field-error"></small>
        </div>
        <div class="form-group">
          <label class="form-label" for="room_name">Room name</label>
          <input type="text" name="room_name" id="room_name" class="form-control" value="<?= htmlspecialchars($row["room_name"]) ?>" required />
          <small id="room_name_error" class="field-error"></small>
        </div>
        <div class="form-group">
          <label class="form-label" for="room_type">Room type</label>
          <input type="text" name="room_type" id="room_type" class="form-control" value="<?= htmlspecialchars($row["room_type"]) ?>" required />
          <small id="room_type_error" class="field-error"></small>
        </div>
        <div class="form-group">
          <label class="form-label" for="room_floor">Floor</label>
          <input type="number" name="room_floor" id="room_floor" class="form-control" value="<?= htmlspecialchars($row["room_floor"]) ?>" required />
          <small id="room_floor_error" class="field-error"></small>
        </div>
        <div class="form-group">
          <label class="form-label" for="room_view">View</label>
          <input type="text" name="room_view" id="room_view" class="form-control" value="<?= htmlspecialchars($row["room_view"]) ?>" required />
          <small id="room_view_error" class="field-error"></small>
        </div>
        <div class="form-group">
          <label class="form-label" for="room_amenities">Amenities</label>
          <input type="text" name="amenities" id="room_amenities" class="form-control" value="<?= htmlspecialchars($row["room_amenities"]) ?>" required />
          <small id="amenities_error" class="field-error"></small>
        </div>
        <div class="form-group">
          <label class="form-label" for="room_beds">Number of beds</label>
          <input type="number" name="room_beds" id="room_beds" class="form-control" value="<?= htmlspecialchars($row["room_beds"]) ?>" required />
          <small id="room_beds_error" class="field-error"></small>
        </div>
      </div>

      <div class="form-col">
        <div class="form-group">
          <label class="form-label" for="bed_type">Bed type</label>
          <input type="text" name="bed_type" id="bed_type" class="form-control" value="<?= htmlspecialchars($row["bed_type"]) ?>" required />
          <small id="bed_type_error" class="field-error"></small>
        </div>
        <div class="form-group">
          <label class="form-label" for="room_capacity">Capacity</label>
          <input type="number" name="room_capacity" id="room_capacity" class="form-control" value="<?= htmlspecialchars($row["room_capacity"]) ?>" required />
          <small id="room_capacity_error" class="field-error"></small>
        </div>
        <div class="form-group">
          <label class="form-label" for="room_price">Price (per day)</label>
          <input type="number" name="room_price" id="room_price" class="form-control" value="<?= htmlspecialchars($row["room_price"]) ?>" required />
          <small id="room_price_error" class="field-error"></small>
        </div>

        <div class="form-group">
          <label class="form-label">Featured room</label>
          <div class="radio-group">
            <label class="radio-option">
              <input type="radio" name="room_featured" value="1" <?= $isFeatured ? "checked" : "" ?> />
              <span>Yes</span>
            </label>
            <label class="radio-option">
              <input type="radio" name="room_featured" value="0" <?= $isFeatured ? "" : "checked" ?> />
              <span>No</span>
            </label>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="new_room_image">Room image</label>
          <div class="image-edit">
            <div class="image-edit-preview">
              <img src="<?= htmlspecialchars(IMAGEROOT . $row["room_image"]) ?>" alt="Current room image" id="current-image-preview" />
            </div>
            <div class="image-edit-controls">
              <label class="file-drop file-drop-compact" for="new_room_image">
                <i class="fa fa-cloud-upload"></i>
                <span class="file-drop-text">Replace image</span>
                <span class="file-drop-hint">PNG or JPG, up to 5MB</span>
                <input type="file" name="new_room_image" id="new_room_image" accept="image/png, image/jpeg" hidden />
              </label>
              <input type="hidden" name="room_image" value="<?= htmlspecialchars($row["room_image"]) ?>" />
            </div>
          </div>
          <small id="new_room_image_error" class="field-error"></small>
        </div>
      </div>
    </div>

    <div class="form-actions">
      <a href="delete_room.php?room_id=<?= (int)$id ?>" class="btn-danger">Delete room</a>
      <div class="form-actions-right">
        <a href="rooms.php" class="btn-ghost-pill btn-ghost-light">Cancel</a>
        <button id="edit_room" type="submit" name="edit_room" value="edit_room" class="btn-primary-pill">
          Save changes
        </button>
      </div>
    </div>
  </form>
</section>

<?php include(__DIR__ . "/includes/footer.php"); ?>

<script>
  $(document).ready(function () {
    $("#new_room_image").on("change", function () {
      var file = this.files && this.files[0];
      if (!file) return;
      $(".file-drop-text").text(file.name);
      var reader = new FileReader();
      reader.onload = function (e) { $("#current-image-preview").attr("src", e.target.result); };
      reader.readAsDataURL(file);
    });

    function clearErrors() {
      $(".field-error").text("");
      $(".form-control").removeClass("is-invalid");
      $("#error-form").attr("hidden", true).text("");
    }
    function showFieldError(field, message) {
      $("#" + field).addClass("is-invalid");
      $("#" + field + "_error").text(message);
    }

    $("#edit_room_form").submit(function (e) {
      e.preventDefault();
      clearErrors();

      var $btn = $("#edit_room").prop("disabled", true).text("Saving…");
      var formData = new FormData(this);

      $.ajax({
        url: "edit_room_controller.php",
        type: "POST",
        data: formData,
        dataType: "json",
        success: function (data) {
          if (data.error == 1) {
            if (data.about) showFieldError(data.about, data.message);
            else $("#error-form").text(data.message || "Something went wrong.").removeAttr("hidden");
            $btn.prop("disabled", false).text("Save changes");
          } else {
            $btn.text("Saved");
            window.location.href = "rooms.php";
          }
        },
        error: function (xhr, message, errorThrown) {
          $("#error-form").text("Network error: " + errorThrown).removeAttr("hidden");
          $btn.prop("disabled", false).text("Save changes");
        },
        cache: false, contentType: false, processData: false
      });
    });
  });
</script>