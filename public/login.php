<?php
require_once(__DIR__ . "/config.php");

// If already logged in, redirect home
if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}
?>
<?php include(__DIR__ . "/includes/header.php"); ?>
<body class="has-nav">
  <section id="auth-form">
    <div class="auth--wrapper">
      <?php include(__DIR__ . "/includes/navbar.php"); ?>

      <div class="bg-image">
        <span>Enjoy personalized recommendations, discounts and much more</span>
      </div>

      <div class="form-wrapper">
        <div class="form-card">
          <h2>Log in to your account</h2>

          <div class="group">
            <img src="./media/logo/google.svg" alt="Google" />
            <button type="button" class="btn">Sign in with Google</button>
          </div>

          <h5><span class="small">or sign in with email</span></h5>

          <form action="" id="login_user" method="post" class="form" novalidate>
            <div id="login-error" class="form-error" hidden></div>

            <div class="form-group">
              <label for="user_email">Email</label>
              <input
                type="email"
                name="user_email"
                id="user_email"
                placeholder="Enter your email"
                class="form-control"
                autocomplete="email"
                required
              />
              <small id="user_email_error" class="error-message text-danger"></small>
            </div>

            <div class="form-group">
              <label for="user_password">Password</label>
              <input
                type="password"
                name="user_password"
                id="user_password"
                placeholder="Enter your password"
                class="form-control"
                autocomplete="current-password"
                required
              />
              <small id="user_password_error" class="error-message text-danger"></small>
            </div>

            <div class="form-group">
              <button type="submit" id="login-user-btn" name="login-user" class="btn">
                Log in
              </button>
            </div>

            <div class="alternate-auth">
              <span>Don't have an account? <a href="<?= SROOT ?>/signup.php">Sign up instead</a></span>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

<?php include(__DIR__ . "/includes/footer.php"); ?>

<script>
  $(document).ready(function () {
    function clearErrors() {
      $(".error-message").html("");
      $(".form-control").removeClass("is-invalid");
      $("#login-error").attr("hidden", true).text("");
    }

    function handleError(about, message) {
      if (about) {
        $("#" + about).addClass("is-invalid");
        $("#" + about + "_error").html(message);
      } else {
        $("#login-error").text(message || "Something went wrong.").removeAttr("hidden");
      }
    }

    $("#login_user").submit(function (e) {
      e.preventDefault();
      clearErrors();

      var $btn = $("#login-user-btn").prop("disabled", true).text("Logging you in…");
      var formData = new FormData(this);

      $.ajax({
        url: "core/login_user.php",
        type: "POST",
        data: formData,
        dataType: "json",
        success: function (data) {
          if (data.error == 1) {
            handleError(data.about, data.message);
            $btn.prop("disabled", false).text("Log in");
          } else {
            // Success — redirect to home (or to wherever the login flow expects)
            window.location.href = "<?= SROOT ?>/index.php";
          }
        },
        error: function (xhr, status, errorThrown) {
          $("#login-error").text("Network error: " + errorThrown).removeAttr("hidden");
          $btn.prop("disabled", false).text("Log in");
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