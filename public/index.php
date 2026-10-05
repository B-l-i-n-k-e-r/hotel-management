<?php
require_once(__DIR__ . "/config.php");
include(__DIR__ . "/includes/header.php");
?>
<body class="has-nav has-hero">
  <header id="home-header">
    <div id="home-header--bg-image">
      <?php include(__DIR__ . "/includes/navbar.php"); ?>

      <div class="home-header--title">
        <div class="container">
          <span class="eyebrow light">V &amp; N Hotel</span>
          <h1>Find the best deals<br>for your next trip</h1>
          <p class="subtitle">Handpicked rooms, premium service, unforgettable stays.</p>

          <div class="hero-actions">
            <a href="#featured-rooms" class="hero-cta">
              <i class="fa fa-arrow-down"></i>&nbsp; Explore rooms
            </a>
            <a href="reservation.php" class="btn-ghost-pill hero-ghost">
              Book now
            </a>
          </div>

          <div class="hero-meta">
            <div class="hero-meta-item">
              <strong>7</strong>
              <span>Room types</span>
            </div>
            <div class="hero-meta-sep"></div>
            <div class="hero-meta-item">
              <strong>24/7</strong>
              <span>Concierge</span>
            </div>
            <div class="hero-meta-sep"></div>
            <div class="hero-meta-item">
              <strong>4.9</strong>
              <span>Guest rating</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main>
    <!-- ============ Search form ============ -->
    <section id="home-form">
      <div class="wrapper">
        <form id="find-available-rooms-form">
          <div class="form-group">
            <label for="check-in-button">From</label>
            <input type="hidden" id="check-in" name="check-in" />
            <button id="check-in-button" type="button" class="input-button">
              Select date
            </button>
          </div>

          <div class="form-group">
            <label for="check-out-button">To</label>
            <input type="hidden" id="check-out" name="check-out" />
            <button id="check-out-button" type="button" class="input-button">
              Select date
            </button>
          </div>

          <div class="form-group">
            <button type="button" id="clear-dates" class="input-button">
              <i class="fa fa-times"></i>&nbsp; Clear
            </button>
          </div>

          <div class="form-group form-group-submit">
            <button type="submit" class="search-btn" name="find-rooms">
              <i class="fa fa-search"></i>&nbsp; Search
            </button>
          </div>
        </form>
      </div>
    </section>

    <!-- ============ Search results (hidden until search runs) ============ -->
    <section id="search-results" style="display:none;">
      <div class="container section-pad">
        <div class="section-head">
          <div>
            <span class="eyebrow">Bookings</span>
            <h2 id="results-title">Matching reservations</h2>
          </div>
        </div>
        <p id="results-status"></p>
        <div class="rooms-grid" id="results-grid"></div>
      </div>
    </section>

    <!-- ============ Featured Rooms ============ -->
    <section id="featured-rooms">
      <div class="container section-pad">
        <div class="section-head">
          <div>
            <span class="eyebrow">Handpicked</span>
            <h2>Today's best deals</h2>
          </div>
          <a href="<?= SROOT ?>/rooms.php" class="link-arrow">
            View all rooms <i class="fa fa-arrow-right"></i>
          </a>
        </div>

        <div class="rooms-grid">
          <?php
          $rooms = [
            ['./media/images/rooms/1.jpg', 'Diamond Suite',    'Ksh. 12,000', 'Premium'],
            ['./media/images/rooms/2.jpg', 'Executive Room',   'Ksh. 9,500',  'Executive'],
            ['./media/images/rooms/3.jpg', 'Deluxe Twin',      'Ksh. 7,800',  'Deluxe'],
            ['./media/images/rooms/4.jpg', 'Standard Queen',   'Ksh. 5,200',  'Standard'],
          ];
          foreach ($rooms as $r): ?>
            <article class="room-card">
              <div class="room-card-media">
                <span class="badge-float <?= strtolower($r[3]) ?>"><?= $r[3] ?></span>
                <img src="<?= $r[0] ?>" alt="<?= $r[1] ?>" loading="lazy" />
              </div>
              <div class="room-card-body">
                <div class="room-card-head">
                  <h3 class="room-name"><?= $r[1] ?></h3>
                  <div class="room-price"><?= $r[2] ?><span>/day</span></div>
                </div>
                <ul class="room-meta">
                  <li><span>View</span>Ocean</li>
                  <li><span>Beds</span>1 / Queen</li>
                </ul>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- ============ Accommodation ============ -->
    <section id="decoration-accomodation" class="section-alt">
      <div class="container section-pad">
        <div class="split">
          <div class="split-content">
            <div class="section-title">
              <span class="eyebrow">Comfort first</span>
              <h2>Choose the perfect accommodation</h2>
            </div>
            <p>
              Guest expectations for premium are always the priority here at V&amp;N Hotel.
              Every room is designed around rest, privacy, and the kind of view you'll want
              to wake up to.
            </p>

            <ul class="value-list">
              <li>Ocean and garden views</li>
              <li>Premium linens and amenities</li>
              <li>Daily housekeeping</li>
            </ul>

            <a href="#premium-section" class="link-arrow">
              See premium rooms <i class="fa fa-arrow-right"></i>
            </a>
          </div>
          <div class="image-pair">
            <div class="image-card">
              <img src="./media/images/backgrounds/home-1.jpg" alt="" loading="lazy" />
            </div>
            <div class="image-card">
              <img src="./media/images/backgrounds/home-2.jpg" alt="" loading="lazy" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ============ Premium ============ -->
    <section id="premium-section">
      <div class="container section-pad">
        <div class="split split-reverse">
          <div class="image-pair">
            <article class="room-card">
              <div class="room-card-media">
                <span class="badge-float premium">Premium</span>
                <img src="./media/images/backgrounds/home-1.jpg" alt="" loading="lazy" />
              </div>
              <div class="room-card-body">
                <div class="room-card-head">
                  <h3 class="room-name">Diamond Suite</h3>
                  <div class="room-price">Ksh. 12,000<span>/day</span></div>
                </div>
              </div>
            </article>
            <article class="room-card">
              <div class="room-card-media">
                <span class="badge-float premium">Premium</span>
                <img src="./media/images/backgrounds/home-2.jpg" alt="" loading="lazy" />
              </div>
              <div class="room-card-body">
                <div class="room-card-head">
                  <h3 class="room-name">Diamond Suite</h3>
                  <div class="room-price">Ksh. 12,000<span>/day</span></div>
                </div>
              </div>
            </article>
          </div>
          <div class="split-content">
            <div class="section-title">
              <span class="eyebrow">Just for you</span>
              <h2>Premium deals for your premium needs</h2>
            </div>
            <p>
              No doubts, no questions — everything you need is available. Just be
              ready for the experience.
            </p>

            <ul class="value-list">
              <li>Late checkout on request</li>
              <li>Complimentary breakfast</li>
              <li>Airport pickup</li>
            </ul>

            <a href="reservation.php" class="hero-cta" style="margin-top: 12px;">
              Book a premium stay
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- ============ Newsletter banner ============ -->
    <section id="home-banner">
      <div class="banner">
        <div class="container">
          <div class="jumbotron">
            <span class="eyebrow light">Our newsletter</span>
            <h2>Become a member and enjoy a flat 25% discount on bookings.</h2>
            <p class="banner-sub">
              Join our list for early access to seasonal offers and new suites.
            </p>
            <form class="newsletter-form" onsubmit="event.preventDefault();">
              <input type="email" placeholder="you@example.com" required />
              <button type="submit">Join</button>
            </form>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php include(__DIR__ . "/includes/footer.php"); ?>

  <script>
  $(document).ready(function () {
    function esc(s) {
      return $('<div>').text(s == null ? '' : s).html();
    }

    // Clear both dates
    $("#clear-dates").on("click", function () {
      $("#check-in").val("");
      $("#check-out").val("");
      $("#check-in-button").text("Select date");
      $("#check-out-button").text("Select date");
    });

    $("#find-available-rooms-form").submit(function (event) {
      event.preventDefault();

      var fromDate = $("#check-in").val()  || "";
      var toDate   = $("#check-out").val() || "";

      if (!fromDate || !toDate) {
        alert("Please select both From and To dates.");
        return;
      }
      if (new Date(toDate) <= new Date(fromDate)) {
        alert("'To' date must be after 'From' date.");
        return;
      }

      var $section = $("#search-results");
      var $status  = $("#results-status");
      var $grid    = $("#results-grid");

      $section.show();
      $status.text("Searching available rooms…");
      $grid.empty();

      $.ajax({
        url: "search_reservations.php",
        method: "GET",
        dataType: "json",
        data: { from: fromDate, to: toDate }
      })
      .done(function (res) {
        if (!res || res.error) {
          $status.text((res && res.error) ? res.error : "Search failed.");
          return;
        }

        var rooms = res.rooms || [];
        if (!rooms.length) {
          $status.text("No rooms available for those dates.");
          return;
        }

        $status.text(rooms.length + " room(s) available");

        rooms.forEach(function (r) {
          var card = `
            <article class="room-card">
              <div class="room-card-media">
                <span class="badge-float ${esc((r.room_type||'').toLowerCase())}">${esc(r.room_type)}</span>
                <img src="${esc(r.image)}" alt="${esc(r.room_name)}" loading="lazy" />
              </div>
              <div class="room-card-body">
                <div class="room-card-head">
                  <h3 class="room-name">${esc(r.room_name)}</h3>
                  <div class="room-price">Ksh. ${esc(r.price)}<span>/day</span></div>
                </div>
                <ul class="room-meta">
                  <li><span>View</span>${esc(r.view)}</li>
                  <li><span>Beds</span>${esc(r.beds)} / ${esc(r.bed_type)}</li>
                  <li><span>Capacity</span>${esc(r.capacity)} guests</li>
                  <li><span>Nights</span>${esc(r.nights)}</li>
                  <li><span>Total</span>Ksh. ${esc(r.total)}</li>
                </ul>
                <a class="hero-cta" style="margin-top:12px;"
                   href="reservation.php?room_id=${encodeURIComponent(r.id)}&check_in=${encodeURIComponent(r.check_in_date)}&check_out=${encodeURIComponent(r.check_out_date)}">
                  Reserve this room
                </a>
              </div>
            </article>`;
          $grid.append(card);
        });

        $('html, body').animate({
          scrollTop: $section.offset().top - 80
        }, 400);
      })
      .fail(function () {
        $status.text("Search failed. Please try again.");
      });
    });
  });
</script>
</body>
</html>