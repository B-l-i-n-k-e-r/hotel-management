<?php
require_once(__DIR__ . "/config.php");
include(__DIR__ . "/includes/header.php");
?>
<body class="has-nav has-hero">
  <header class="page-header">
    <?php include(__DIR__ . "/includes/navbar.php"); ?>
    <div class="page-header-inner container">
      <span class="eyebrow light">We're here to help</span>
      <h1>Customer Care</h1>
      <p>Send us a message — our team responds within 24 hours.</p>
    </div>
  </header>

  <main>
    <section class="container section-pad">
      <div class="survey-card">

        <form id="contact_form" novalidate>

          <div class="survey-question">
            <label for="c_name">Your name</label>
            <input type="text" id="c_name" name="name" class="form-control"
                   value="<?= htmlspecialchars(($_SESSION["user_fname"] ?? "") . " " . ($_SESSION["user_lname"] ?? "")) ?>"
                   required>
          </div>

          <div class="survey-question">
            <label for="c_email">Email</label>
            <input type="email" id="c_email" name="email" class="form-control"
                   value="<?= htmlspecialchars($_SESSION["user_email"] ?? "") ?>"
                   required>
          </div>

          <div class="survey-question">
            <label for="c_phone">Phone (optional)</label>
            <input type="text" id="c_phone" name="phone" class="form-control">
          </div>

          <div class="survey-question">
            <label for="c_subject">Subject</label>
            <input type="text" id="c_subject" name="subject" class="form-control" required>
          </div>

          <div class="survey-question">
            <label for="c_message">Message</label>
            <textarea id="c_message" name="message" rows="6" class="form-control" required></textarea>
          </div>

          <div id="contact-feedback" class="form-error" hidden></div>

          <div class="survey-actions">
            <button type="submit" id="contact-submit" class="btn-primary-pill">
              Send message
            </button>
          </div>

        </form>

      </div>
    </section>
  </main>

  <?php include(__DIR__ . "/includes/footer.php"); ?>

  <script>
    $(document).ready(function () {
      $("#contact_form").submit(function (e) {
        e.preventDefault();

        var $btn = $("#contact-submit").prop("disabled", true).text("Sending…");
        var $err = $("#contact-feedback").attr("hidden", true).text("");

        $.ajax({
          url: "core/submit_contact.php",
          type: "POST",
          data: {
            name:    $("#c_name").val(),
            email:   $("#c_email").val(),
            phone:   $("#c_phone").val(),
            subject: $("#c_subject").val(),
            message: $("#c_message").val()
          },
          dataType: "json",
          success: function (data) {
            if (data.error == 1) {
              $err.text(data.message || "Something went wrong.").removeAttr("hidden");
              $btn.prop("disabled", false).text("Send message");
            } else {
              $btn.text("Message sent!");
              $("#contact_form")[0].reset();
              setTimeout(function () {
                $btn.prop("disabled", false).text("Send message");
              }, 2000);
            }
          },
          error: function (xhr, status, err) {
            $err.text("Network error: " + err).removeAttr("hidden");
            $btn.prop("disabled", false).text("Send message");
          }
        });
      });
    });
  </script>
</body>
</html>