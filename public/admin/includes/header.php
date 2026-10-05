<?php
// Each admin page sets $admin_page and $admin_title BEFORE including this file.
// Example in index.php:
//   $admin_page  = "dashboard";
//   $admin_title = "Dashboard";
//   $admin_sub   = "Here's what's happening at V&N Hotel today.";
//   include(__DIR__ . "/includes/header.php");

$admin_page  = $admin_page  ?? "dashboard";
$admin_title = $admin_title ?? "Admin";
$admin_sub   = $admin_sub   ?? "";

$nav_items = [
    ["key" => "dashboard",    "href" => "index.php",       "icon" => "fa-th-large", "label" => "Dashboard",    "group" => "Main"],
    ["key" => "rooms",        "href" => "rooms.php",       "icon" => "fa-home",     "label" => "Rooms",        "group" => "Main"],
    ["key" => "reservations", "href" => "reservation.php", "icon" => "fa-bookmark", "label" => "Reservations", "group" => "Main"],
    ["key" => "users",        "href" => "users.php",       "icon" => "fa-users",    "label" => "Users",        "group" => "Manage"],
    ["key" => "feedback",     "href" => "feedback.php",    "icon" => "fa-comments", "label" => "Feedback",     "group" => "Manage"],
    ["key" => "messages", "href" => "messages.php", "icon" => "fa-envelope-o", "label" => "Customer Care", "group" => "Manage"],

    ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>V&N Hotel — Admin — <?= htmlspecialchars($admin_title) ?></title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
        integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css" />
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= SROOT ?>/css/main.css" />
</head>
<body id="admin-dashboard">
  <div id="admin-shell">

    <!-- ============ Sidebar ============ -->
    <aside class="admin-sidebar">

      <div class="admin-brand">
        <img src="<?= SROOT ?>/media/images/logo/aaa_logo.png" alt="V&N Hotel" class="admin-brand-logo" />
        <div class="admin-brand-text">
          <strong>V&amp;N</strong>
          <span>Hotel Admin</span>
        </div>
      </div>

      <nav class="admin-nav">
        <?php
          $current_group = null;
          foreach ($nav_items as $item):
            if ($current_group !== $item["group"]):
              if ($current_group !== null) echo '</div>';
              $current_group = $item["group"];
        ?>
          <span class="admin-nav-group"><?= htmlspecialchars($current_group) ?></span>
          <div class="admin-nav-list">
        <?php endif; ?>

            <a href="<?= $item["href"] ?>" class="<?= $admin_page === $item["key"] ? "active" : "" ?>">
              <span class="admin-nav-icon">
                <i class="fa <?= $item["icon"] ?>"></i>
              </span>
              <span class="admin-nav-label"><?= htmlspecialchars($item["label"]) ?></span>
            </a>

        <?php endforeach; ?>
          </div><!-- /.admin-nav-list -->
      </nav>

      <div class="admin-sidebar-footer">
        <a href="<?= SROOT ?>/index.php" class="admin-back-link">
          <i class="fa fa-arrow-left"></i>
          <span>Back to site</span>
        </a>
      </div>
    </aside>

    <!-- ============ Main ============ -->
    <main id="admin-main">

      <!-- Topbar -->
      <header class="admin-topbar">
        <div class="admin-topbar-left">
          <span class="admin-crumb">Admin / <?= htmlspecialchars($admin_title) ?></span>
          <h1><?= htmlspecialchars($admin_title) ?></h1>
          <?php if ($admin_sub): ?>
            <p><?= htmlspecialchars($admin_sub) ?></p>
          <?php endif; ?>
        </div>

        <div class="admin-topbar-right">
          <button type="button" class="admin-topbar-icon" aria-label="Notifications">
            <i class="fa fa-bell-o"></i>
            <span class="admin-badge-dot"></span>
          </button>

          <div class="admin-user" id="admin-user-menu">
            <button type="button" class="admin-user-btn" aria-haspopup="true" aria-expanded="false">
              <span class="admin-user-avatar">
                <?= strtoupper(substr($_SESSION["user_fname"] ?? "A", 0, 1)) ?>
              </span>
              <span class="admin-user-info">
                <strong><?= htmlspecialchars(($_SESSION["user_fname"] ?? "") . " " . ($_SESSION["user_lname"] ?? "")) ?></strong>
                <span>Administrator</span>
              </span>
              <i class="fa fa-angle-down admin-user-caret"></i>
            </button>
            <div class="admin-user-dropdown" hidden>
              <a href="<?= SROOT ?>/index.php">
                <i class="fa fa-globe"></i>&nbsp; View site
              </a>
              <a href="<?= SROOT ?>/core/logout_user.php">
                <i class="fa fa-sign-out"></i>&nbsp; Log out
              </a>
            </div>
          </div>
        </div>
      </header>