    </main><!-- /#admin-main -->
  </div><!-- /#admin-shell -->

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"
          integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
          crossorigin="anonymous"></script>

  <!-- Admin UI JS -->
  <script>
    (function () {
      var menu = document.getElementById("admin-user-menu");
      if (!menu) return;
      var btn = menu.querySelector(".admin-user-btn");
      var dd  = menu.querySelector(".admin-user-dropdown");

      btn.addEventListener("click", function (e) {
        e.stopPropagation();
        var open = !dd.hasAttribute("hidden");
        if (open) {
          dd.setAttribute("hidden", "");
          btn.setAttribute("aria-expanded", "false");
        } else {
          dd.removeAttribute("hidden");
          btn.setAttribute("aria-expanded", "true");
        }
      });

      document.addEventListener("click", function (e) {
        if (!menu.contains(e.target)) {
          dd.setAttribute("hidden", "");
          btn.setAttribute("aria-expanded", "false");
        }
      });
    })();
  </script>

  <!-- Page-specific scripts can be added by each admin page AFTER this include -->
</body>
</html>