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
            <label for="check-in-button">Check in</label>
            <input type="hidden" id="check-in" name="check-in" />
            <button id="check-in-button" type="button" class="input-button">
              Select date
            </button>
          </div>

          <div class="form-group">
            <label for="check-out-button">Check out</label>
            <input type="hidden" id="check-out" name="check-out" value="" />
            <button id="check-out-button" type="button" class="input-button">
              Select date
            </button>
          </div>

          <div class="form-group">
            <label for="form-dropdown">Guests</label>
            <button
              class="dropdown-toggle"
              id="form-dropdown"
              type="button"
              data-toggle="dropdown"
              aria-haspopup="true"
              aria-expanded="false"
            >
              Add guests
            </button>
            <ul class="dropdown-menu" aria-labelledby="form-dropdown">
              <li class="dropdown-menu-item">
                <div class="form-group form-group-nested">
                  <label for="count-adults">Adults</label>
                  <input type="number" min="0" name="count-adults" id="count-adults" placeholder="Ages 13+" />
                </div>
                <div class="form-group form-group-nested">
                  <label for="count-children">Children</label>
                  <input type="number" min="0" name="count-children" id="count-children" placeholder="Ages 1–12" />
                </div>
              </li>
            </ul>
          </div>

          <div class="form-group form-group-submit">
            <button type="submit" class="search-btn" name="find-rooms">
              <i class="fa fa-search"></i>&nbsp; Search
            </button>
          </div>
        </form>
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
      $("#find-available-rooms-form").submit(function (event) {
        event.preventDefault();
        var indate   = $("#check-in").val();
        var outdate  = $("#check-out").val();
        var adults   = $("#count-adults").val() || 0;
        var children = $("#count-children").val() || 0;

        if (indate !== "" && indate !== null) {
          window.location.href = `reservation.php?check_in_date=${indate}&check_out_date=${outdate}&adults=${adults}&children=${children}`;
        } else {
          alert("Please select a check-in date.");
        }
      });
    });
  </script>
</body>
</html>