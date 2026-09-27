<?php
require_once(__DIR__ . "/../config.php");

$admin_page  = "rooms";
$admin_title = "Add room";
$admin_sub   = "Create a new room in the inventory";

include(__DIR__ . "/includes/header.php");

if (!isLoggedIn() || !isAdmin($pdo)) {
    header("Location: ../login.php");
    exit;
}
?>

<div class="admin-topbar-actions">
  <a href="rooms.php" class="btn-ghost-pill btn-ghost-light">
    <i class="fa fa-arrow-left"></i>&nbsp; Back to rooms
  </a>
</div>

<section class="admin-form-card">
  <form action="" id="add_room_form" method="post" enctype="multipart/form-data" novalidate>
    <div id="error-form" class="form-error" hidden></div>

    <div class="form-grid">
      <div class="form-col">
        <div class="form-group">
          <label class="form-label" for="room_number">Room number</label>
          <input type="number" name="room_number" id="room_number" class="form-control" required />
          <small id="room_number_error" class="field-error"></small>
        </div>
        <div class="form-group">
          <label class="form-label" for="room_name">Room name</label>
          <input type="text" name="room_name" id="room_name" class="form-control" required />
          <small id="room_name_error" class="field-error"></small>
        </div>
        <div class="form-group">
          <label class="form-label" for="room_type">Room type</label>
          <input type="text" name="room_type" id="room_type" class="form-control" required />
          <small id="room_type_error" class="field-error"></small>
        </div>
        <div class="form-group">
          <label class="form-label" for="room_floor">Floor</label>
          <input type="number" name="room_floor" id="room_floor" class="form-control" required />
          <small id="room_floor_error" class="field-error"></small>
        </div>
        <div class="form-group">
          <label class="form-label" for="room_view">View</label>
          <input type="text" name="room_view" id="room_view" class="form-control" required />
          <small id="room_view_error" class="field-error"></small>
        </div>
        <div class="form-group">
          <label class="form-label" for="room_amenities">Amenities</label>
          <input type="text" name="amenities" id="room_amenities" class="form-control" required />
          <small id="amenities_error" class="field-error"></small>
        </div>
        <div class="form-group">
          <label class="form-label" for="room_beds">Number of beds</label>
          <input type="number" name="room_beds" id="room_beds" class="form-control" required />
          <small id="room_beds_error" class="field-error"></small>
        </div>
      </div>

      <div class="form-col">
        <div class="form-group">
          <label class="form-label" for="bed_type">Bed type</label>
          <input type="text" name="bed_type" id="bed_type" class="form-control" required />
          <small id="bed_type_error" class="field-error"></small>
        </div>
        <div class="form-group">
          <label class="form-label" for="room_capacity">Capacity</label>
          <input type="number" name="room_capacity" id="room_capacity" class="form-control" required />
          <small id="room_capacity_error" class="field-error"></small>
        </div>
        <div class="form-group">
          <label class="form-label" for="room_price">Price (per day)</label>
          <input type="number" name="room_price" id="room_price" class="form-control" required />
          <small id="room_price_error" class="field-error"></small>
        </div>
        <div class="form-group">
          <label class="form-label">Featured room</label>
          <div class="radio-group">
            <label class="radio-option">
              <input type="radio" name="room_featured" value="1" checked />
              <span>Yes</span>
            </label>
            <label class="radio-option">
              <input type="radio" name="room_featured" value="0" />
              <span>No</span>
            </label>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label" for="room_image">Room image</label>
          <label class="file-drop" for="room_image">
            <i class="fa fa-cloud-upload"></i>
            <span class="file-drop-text">Choose a file or drop it here</span>
            <span class="file-drop-hint">PNG or JPG, up to 5MB</span>
            <input type="file" name="room_image" id="room_image" accept="image/png, image/jpeg" hidden />
          </label>
          <small id="room_image_error" class="field-error"></small>
        </div>
      </div>
    </div>

    <div class="form-actions">
      <a href="rooms.php" class="btn-ghost-pill btn-ghost-light">Cancel</a>
      <button id="add_room" type="submit" name="add_room" value="add_room" class="btn-primary-pill">
        Add room
      </button>
    </div>
  </form>
</section>

<?php include(__DIR__ . "/includes/footer.php"); ?>

<script>
  $(document).ready(function () {
    $("#room_image").on("change", function () {
      var name = this.files && this.files[0] ? this.files[0].name : null;
      $(".file-drop-text").text(name || "Choose a file or drop it here");
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

    $("#add_room_form").submit(function (e) {
      e.preventDefault();
      clearErrors();

      var $btn = $("#add_room").prop("disabled", true).text("Creating…");
      var formData = new FormData(this);

      $.ajax({
        url: "add_room_controller.php",
        type: "POST",
        data: formData,
        dataType: "json",
        success: function (data) {
          if (data.error == 1) {
            if (data.about) {
              showFieldError(data.about, data.message);
            } else {
              $("#error-form").text(data.message || "Something went wrong.").removeAttr("hidden");
            }
            $btn.prop("disabled", false).text("Add room");
          } else {
            $btn.text("Success");
            window.location.href = "rooms.php";
          }
        },
        error: function (xhr, message, errorThrown) {
          $("#error-form").text("Network error: " + errorThrown).removeAttr("hidden");
          $btn.prop("disabled", false).text("Add room");
        },
        cache: false,
        contentType: false,
        processData: false
      });
    });
  });
</script>