<?php
require_once(__DIR__ . "/config.php");
include(__DIR__ . "/includes/header.php");
?>
<body class="has-nav has-hero">
  <header class="page-header">
    <?php include(__DIR__ . "/includes/navbar.php"); ?>
    <div class="page-header-inner container">
      <span class="eyebrow light">We'd love to hear from you</span>
      <h1>Share your feedback</h1>
      <p>Help us improve your experience at V&amp;N Hotel.</p>
    </div>
  </header>

  <main>
    <section class="container section-pad">
      <div class="survey-card">

        <form id="survey_form" novalidate>

          <div class="survey-question">
            <label for="impression">1. What was your first impression when you entered the website?</label>
            <textarea id="impression" class="form-control" rows="3"></textarea>
          </div>

          <div class="survey-question">
            <label for="first_hear">2. How did you first hear about us?</label>
            <textarea id="first_hear" class="form-control" rows="3"></textarea>
          </div>

          <div class="survey-question">
            <label for="missing_features">3. Is there anything missing on this page?</label>
            <textarea id="missing_features" class="form-control" rows="3"></textarea>
          </div>

          <div class="survey-question">
            <label for="recommend">4. How likely are you to recommend us to a friend or colleague?</label>
            <div class="survey-slider">
              <span class="survey-slider-end">0</span>
              <input type="range" id="recommend" min="0" max="5" value="3" step="1" />
              <span class="survey-slider-end">5</span>
              <output id="recommend-output" class="survey-slider-output">3</output>
            </div>
          </div>

          <div class="survey-question">
            <label for="useful_features">5. What is the most useful feature of our website?</label>
            <textarea id="useful_features" class="form-control" rows="3"></textarea>
          </div>

          <div class="survey-question">
            <label for="ease_of_use">6. How easy was it to use our website? Did you have any problems?</label>
            <textarea id="ease_of_use" class="form-control" rows="3"></textarea>
          </div>

          <div id="survey-feedback" class="form-error" hidden></div>

          <div class="survey-actions">
            <button type="submit" id="survey-submit" class="btn-primary-pill">
              Send feedback
            </button>
          </div>

        </form>

      </div>
    </section>
  </main>

  <?php include(__DIR__ . "/includes/footer.php"); ?>

  <script>
    $(document).ready(function () {
      // Live slider output
      var $range = $("#recommend");
      var $out = $("#recommend-output");
      $out.text($range.val());
      $range.on("input", function () { $out.text(this.value); });

      // Submit to our own backend
      $("#survey_form").submit(function (e) {
        e.preventDefault();

        var $btn = $("#survey-submit").prop("disabled", true).text("Sending…");
        var $err = $("#survey-feedback").attr("hidden", true).text("");

        $.ajax({
          url: "core/submit_feedback.php",
          type: "POST",
          data: {
            impression:       $("#impression").val(),
            first_hear:       $("#first_hear").val(),
            missing_features: $("#missing_features").val(),
            recommend:        $("#recommend").val(),
            useful_features:  $("#useful_features").val(),
            ease_of_use:      $("#ease_of_use").val()
          },
          dataType: "json",
          success: function (data) {
            if (data.error == 1) {
              $err.text(data.message || "Something went wrong.").removeAttr("hidden");
              $btn.prop("disabled", false).text("Send feedback");
            } else {
              $btn.text("Thanks for your feedback!");
              setTimeout(function () {
                window.location.href = "<?= SROOT ?>/index.php";
              }, 1200);
            }
          },
          error: function (xhr, status, err) {
            $err.text("Network error: " + err).removeAttr("hidden");
            $btn.prop("disabled", false).text("Send feedback");
          }
        });
      });
    });
  </script>
</body>
</html>