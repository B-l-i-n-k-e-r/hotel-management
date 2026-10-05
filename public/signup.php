<?php
require_once(__DIR__ . "/config.php");
include(__DIR__ . "/includes/header.php");
?>
<body class="has-nav has-hero">
  <section id="signup-form">
    <div class="auth--wrapper">
      <?php include(__DIR__ . "/includes/navbar.php"); ?>

      <div class="bg-image">
        <div class="form-progress">
          <span>Set up your account in 3 simple steps</span>
          <div class="form-progress-item complete" data-step="1">Phone number</div>
          <hr />
          <div class="form-progress-item" data-step="2">Personal info</div>
          <hr />
          <div class="form-progress-item" data-step="3">Credentials</div>
        </div>
      </div>

      <div class="form-wrapper">
        <div class="form-card">
          <h2>Sign up</h2>

          <form action="" id="signup_user" method="post" class="form" novalidate>

            <!-- ============ Part 1: Phone ============ -->
            <div class="part part-1" data-part="1">
              <div class="form-group">
                <label for="user_phone">Phone number</label>
                <input
                  type="tel"
                  name="user_phone"
                  id="user_phone"
                  class="form-control"
                  placeholder="Enter your phone number"
                  inputmode="tel"
                  autocomplete="tel"
                />
                <small id="user_phone_error" class="error-message text-danger"></small>
              </div>

              <div class="form-group">
                <div class="step">
                  <a class="btn" href="<?= SROOT ?>/index.php">Cancel</a>
                  <button type="button" class="btn" data-next="2">Next</button>
                </div>
              </div>
            </div>

            <!-- ============ Part 2: Personal info ============ -->
            <div class="part part-2" data-part="2" hidden>
              <div class="form-group">
                <label for="user_fname">First name</label>
                <input
                  type="text"
                  class="form-control"
                  name="user_fname"
                  id="user_fname"
                  placeholder="Enter your first name"
                  autocomplete="given-name"
                />
                <small id="user_fname_error" class="error-message text-danger"></small>
              </div>

              <div class="form-group">
                <label for="user_lname">Last name</label>
                <input
                  type="text"
                  class="form-control"
                  name="user_lname"
                  id="user_lname"
                  placeholder="Enter your last name"
                  autocomplete="family-name"
                />
                <small id="user_lname_error" class="error-message text-danger"></small>
              </div>

              <div class="form-group">
                <label for="user_dob">Date of birth</label>
                <input
                  type="text"
                  class="form-control"
                  name="user_dob"
                  id="user_dob"
                  placeholder="YYYY-MM-DD"
                  autocomplete="bday"
                  readonly
                />
                <small id="user_dob_error" class="error-message text-danger"></small>
              </div>

              <div class="form-group">
                <div class="step">
                  <button type="button" class="btn" data-prev="1">Back</button>
                  <button type="button" class="btn" data-next="3">Next</button>
                </div>
              </div>
            </div>

            <!-- ============ Part 3: Credentials ============ -->
            <div class="part part-3" data-part="3" hidden>
              <div class="form-group">
                <label for="user_email">Email</label>
                <input
                  type="email"
                  name="user_email"
                  id="user_email"
                  placeholder="Enter your email"
                  class="form-control"
                  autocomplete="email"
                />
                <small id="user_email_error" class="error-message text-danger"></small>
              </div>

              <div class="form-group">
                <label for="user_password">Password</label>
                <input
                  type="password"
                  name="user_password"
                  id="user_password"
                  placeholder="Enter your password (min 8 characters)"
                  class="form-control"
                  autocomplete="new-password"
                />
                <small id="user_password_error" class="error-message text-danger"></small>
              </div>

              <div id="signup-error" class="form-error" hidden></div>

              <div class="form-group">
                <div class="step">
                  <button type="button" class="btn" data-prev="2">Back</button>
                  <button type="submit" name="signup-user" id="signup-user" class="btn btn-primary">
                    Sign up
                  </button>
                </div>
              </div>
            </div>

            <div class="alternate-auth">
              <span>Already have an account? <a href="<?= SROOT ?>/login.php">Login here</a></span>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

  <?php include(__DIR__ . "/includes/footer.php"); ?>

  <script>
    $(document).ready(function () {
      // ---- Datepicker for DOB ----
      var d = new Date();
      $("#user_dob").datepicker({
        dateFormat: "yy-mm-dd",
        changeMonth: true,
        changeYear: true,
        yearRange: "1930:" + d.getFullYear(),
        defaultDate: d,
      });

      // ---- Wizard navigation ----
      function goToPart(n) {
        $(".part").attr("hidden", true);
        $('.part[data-part="' + n + '"]').removeAttr("hidden");

        // Update the progress indicator
        $(".form-progress-item").removeClass("complete");
        $('.form-progress-item[data-step="' + n + '"]').addClass("complete");
      }

      $("[data-next]").on("click", function () {
        goToPart($(this).data("next"));
      });
      $("[data-prev]").on("click", function () {
        goToPart($(this).data("prev"));
      });

      // ---- Signup submission ----
      function clearErrors() {
        $(".error-message").html("");
        $(".form-control").removeClass("is-invalid");
        $("#signup-error").attr("hidden", true).text("");
      }
      function showError(field, message) {
        if (field) {
          $("#" + field).addClass("is-invalid");
          $("#" + field + "_error").html(message);
        } else {
          $("#signup-error").text(message || "Something went wrong.").removeAttr("hidden");
        }
      }

      $("#signup_user").submit(function (e) {
        e.preventDefault();
        clearErrors();

        var $btn = $("#signup-user").prop("disabled", true).html("Signing you up…");
        var formData = new FormData(this);

        $.ajax({
          url: "core/signup_user.php",
          type: "POST",
          data: formData,
          dataType: "json",
          success: function (data) {
            if (data.error == 1) {
              showError(data.about, data.message);
              $btn.prop("disabled", false).html("Sign up");
              // Jump to the step containing the errored field
              if (data.about) {
                var $input = $("#" + data.about);
                var $part = $input.closest(".part");
                if ($part.length) goToPart($part.data("part"));
              }
            } else {
              $btn.html("Signed up");
              window.location.href = "<?= SROOT ?>/verify_user.php";
            }
          },
          error: function (xhr, message, errorThrown) {
            $("#signup-error").text("Network error: " + errorThrown).removeAttr("hidden");
            $btn.prop("disabled", false).html("Sign up");
          },
          cache: false,
          contentType: false,
          processData: false
        });
      });
    });
  </script>
</body>
</html>