<?php
require_once(__DIR__ . "/../config.php");
include(__DIR__ . "/../includes/header.php");

if (!isLoggedIn() || !isAdmin($pdo)) {
    header("Location: ../login.php");
    exit;
}
?>
<body id="admin-dashboard">
  <div id="admin-shell">

    <!-- ============ Sidebar ============ -->
    <aside class="admin-sidebar">
      <div class="admin-brand">
        <span class="brand-mark">V&amp;N</span>
        <span class="brand-text">Admin</span>
      </div>
      <nav class="admin-nav">
        <a href="index.php">
          <i class="fa fa-th-large"></i><span>Dashboard</span>
        </a>
        <a href="rooms.php">
          <i class="fa fa-home"></i><span>Rooms</span>
        </a>
        <a href="users.php">
          <i class="fa fa-users"></i><span>Users</span>
        </a>
        <a href="reservation.php">
          <i class="fa fa-bookmark"></i><span>Reservations</span>
        </a>
        <a href="calendar.php" class="active">
          <i class="fa fa-calendar"></i><span>Calendar</span>
        </a>
        <a href="../index.php" class="admin-nav-back">
          <i class="fa fa-arrow-left"></i><span>Back to site</span>
        </a>
      </nav>
    </aside>

    <!-- ============ Main ============ -->
    <main id="admin-main">

      <header class="admin-topbar">
        <div class="admin-topbar-left">
          <h1>Bookings calendar</h1>
          <p>Every reservation, at a glance</p>
        </div>
        <div class="admin-topbar-right">
          <div class="calendar-legend">
            <span><i class="legend-dot" style="background:#be0854;"></i> Booked</span>
          </div>
        </div>
      </header>

      <section class="admin-calendar-card">
        <div id="calendar"></div>
      </section>

    </main>
  </div>

<?php include(__DIR__ . "/../includes/footer.php"); ?>

<!-- FullCalendar v6 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    var calendarEl = document.getElementById("calendar");

    var calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: "dayGridMonth",
      height: "auto",
      firstDay: 1, // Monday
      headerToolbar: {
        left: "prev,next today",
        center: "title",
        right: "dayGridMonth,timeGridWeek,listMonth"
      },
      buttonText: {
        today: "Today",
        month: "Month",
        week: "Week",
        list: "List"
      },
      dayMaxEvents: 3, // shows "+ more" link when a day has many
      events: function (info, successCallback, failureCallback) {
        fetch("core/calendar_data.php")
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (data.error) {
              console.error("Calendar load error:", data.message);
              successCallback([]);
              return;
            }
            successCallback(data.events || []);
          })
          .catch(function (err) {
            console.error("Calendar fetch failed:", err);
            failureCallback(err);
          });
      },
      eventClick: function (info) {
        var props = info.event.extendedProps;
        if (props.reservation_id) {
          window.open("../bill_view.php?reservation_id=" + props.reservation_id, "_blank");
        }
      },
      eventDidMount: function (info) {
        info.el.title = info.event.title;
      }
    });

    calendar.render();
  });
</script>
</body>
</html>