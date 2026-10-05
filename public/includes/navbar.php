<?php
// Detect current page for active-link highlighting
$current = basename($_SERVER["PHP_SELF"] ?? "");

// Only check admin status if logged in (avoids an unnecessary DB query for guests)
$isAdminUser = isLoggedIn() ? isAdmin($pdo) : false;

$navLinks = [
    ["label" => "Home",        "href" => SROOT . "/index.php",       "match" => ["index.php", ""]],
    ["label" => "Rooms",       "href" => SROOT . "/rooms.php",       "match" => ["rooms.php", "roomdetails.php"]],
    ["label" => "Reservation", "href" => SROOT . "/reservation.php", "match" => ["reservation.php", "payment.php"]],
    ["label" => "About Us",    "href" => SROOT . "/aboutus.php",     "match" => ["aboutus.php"]],
];

function nav_link_is_active($current, $matches) {
    foreach ($matches as $m) {
        if ($m === $current) return true;
    }
    return false;
}
?>
<nav class="site-nav" id="site-nav">
  <div class="site-nav__inner">

    <a class="site-nav__brand" href="<?= SROOT ?>/index.php">
      <img src="<?= SROOT ?>/media/images/logo/logo.jpeg" alt="V&N Hotel" />
    </a>

    <button
      class="site-nav__toggle"
      type="button"
      aria-label="Toggle navigation"
      aria-controls="site-nav__menu"
      aria-expanded="false"
      id="site-nav-toggle">
      <span></span><span></span><span></span>
    </button>

    <div class="site-nav__menu" id="site-nav__menu">
      <ul class="site-nav__links">
        <?php foreach ($navLinks as $link): ?>
          <li>
            <a href="<?= $link["href"] ?>"
               class="site-nav__link<?= nav_link_is_active($current, $link["match"]) ? " is-active" : "" ?>">
              <?= htmlspecialchars($link["label"]) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>

      <div class="site-nav__account">
        <button
          class="site-nav__account-btn"
          type="button"
          id="site-nav-account-toggle"
          aria-haspopup="true"
          aria-expanded="false">
          <i class="fa fa-user-circle-o"></i>
          <span><?= isLoggedIn() ? htmlspecialchars($_SESSION["user_fname"]) : "Account" ?></span>
          <i class="fa fa-angle-down site-nav__caret"></i>
        </button>

        <div class="site-nav__account-menu" id="site-nav-account-menu" hidden>
          <?php if (isLoggedIn()): ?>
            <div class="site-nav__account-header">
              <strong><?= htmlspecialchars(($_SESSION["user_fname"] ?? "") . " " . ($_SESSION["user_lname"] ?? "")) ?></strong>
              <span class="text-muted"><?= htmlspecialchars($_SESSION["user_email"] ?? "") ?></span>
            </div>

            <?php if ($isAdminUser): ?>
              <a class="site-nav__account-item" href="<?= SROOT ?>/admin/index.php">
                <i class="fa fa-lock"></i>&nbsp; Admin dashboard
              </a>
            <?php endif; ?>

            <a class="site-nav__account-item" href="<?= SROOT ?>/accounts.php">
              <i class="fa fa-user-circle-o"></i>&nbsp; My account
            </a>
            <a class="site-nav__account-item" href="<?= SROOT ?>/account-reservations.php">
              <i class="fa fa-bookmark"></i>&nbsp; My reservations
            </a>
            <div class="site-nav__account-divider"></div>
            <a class="site-nav__account-item site-nav__account-item--danger" href="<?= SROOT ?>/core/logout_user.php">
              <i class="fa fa-sign-out"></i>&nbsp; Log out
            </a>
          <?php else: ?>
            <a class="site-nav__account-item" href="<?= SROOT ?>/login.php">
              <i class="fa fa-sign-in"></i>&nbsp; Log in
            </a>
            <a class="site-nav__account-item" href="<?= SROOT ?>/signup.php">
              <i class="fa fa-user-plus"></i>&nbsp; Sign up
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</nav>

<script>
(function () {
  // Transparent-to-solid scroll behavior
  var nav = document.getElementById("site-nav");
  var onHomeHero = document.body.classList.contains("has-hero");

  function updateNav() {
    if (!nav) return;
    if (window.scrollY > 40) nav.classList.add("is-scrolled");
    else nav.classList.remove("is-scrolled");
  }

  if (onHomeHero) {
    nav.classList.add("site-nav--transparent");
    updateNav();
    window.addEventListener("scroll", updateNav, { passive: true });
  }

  // Mobile menu toggle
  var toggle = document.getElementById("site-nav-toggle");
  var menu   = document.getElementById("site-nav__menu");
  if (toggle && menu) {
    toggle.addEventListener("click", function () {
      var isOpen = toggle.getAttribute("aria-expanded") === "true";
      toggle.setAttribute("aria-expanded", String(!isOpen));
      menu.classList.toggle("is-open", !isOpen);
      document.body.classList.toggle("nav-open", !isOpen);
    });

    // Close on link click
    menu.querySelectorAll("a").forEach(function (link) {
      link.addEventListener("click", function () {
        toggle.setAttribute("aria-expanded", "false");
        menu.classList.remove("is-open");
        document.body.classList.remove("nav-open");
      });
    });
  }

  // Account dropdown
  var accBtn  = document.getElementById("site-nav-account-toggle");
  var accMenu = document.getElementById("site-nav-account-menu");
  if (accBtn && accMenu) {
    accBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      var isOpen = !accMenu.hasAttribute("hidden");
      if (isOpen) {
        accMenu.setAttribute("hidden", "");
        accBtn.setAttribute("aria-expanded", "false");
      } else {
        accMenu.removeAttribute("hidden");
        accBtn.setAttribute("aria-expanded", "true");
      }
    });

    // Close on outside click
    document.addEventListener("click", function (e) {
      if (!accMenu.contains(e.target) && !accBtn.contains(e.target)) {
        accMenu.setAttribute("hidden", "");
        accBtn.setAttribute("aria-expanded", "false");
      }
    });
  }
})();
</script>