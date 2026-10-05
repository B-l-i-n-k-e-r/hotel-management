<?php
require_once(__DIR__ . "/config.php");
include(__DIR__ . "/includes/header.php");
?>
<body class="has-nav has-hero">
  <header class="page-header">
    <?php include(__DIR__ . "/includes/navbar.php"); ?>
    <div class="page-header-inner container">
      <span class="eyebrow light">About us</span>
      <h1>Where hospitality meets heritage</h1>
      <p>Seven continents. One standard. A stay designed around you.</p>
    </div>
  </header>

  <main>

    <!-- ============ About ============ -->
    <section class="about-section">
      <div class="container section-pad">
        <div class="about-split">
          <div class="about-media">
            <img
              src="./media/images/backgrounds/home-1.jpg"
              alt="V&N Hotel suite with a view"
              loading="lazy"
            />
          </div>
          <div class="about-content">
            <div class="section-title">
              <span class="eyebrow">Our story</span>
              <h2>About V&amp;N Hotel</h2>
            </div>
            <p>
              Offering top-quality service that every guest deserves. We are an industry leader with a
              brand renowned across all seven continents. Our focus:
            </p>
            <ul class="value-list">
              <li>Core values and heritage</li>
              <li>Diversity and inclusion</li>
              <li>Sustainability and social impact</li>
            </ul>
            <p>
              Our goal is simple — make every stay memorable. Become part of the ever-growing
              V&amp;N Hotel family.
            </p>
            <a href="<?= SROOT ?>/rooms.php" class="link-arrow">
              Explore our rooms <i class="fa fa-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- ============ Team ============ -->
    <section class="team-section section-alt">
      <div class="container section-pad">
        <div class="section-title centered">
          <span class="eyebrow">The people</span>
          <h2>Meet our team</h2>
          <p class="section-sub">Hospitality is a craft. Here are the people who practise it every day.</p>
        </div>

        <div class="team-grid">
          <!-- Vince -->
          <article class="team-card">
            <div class="team-photo">
              <img src="./media/images/aboutus/mariba.jpg" alt="Mariba Vince" loading="lazy" />
            </div>
            <div class="team-body">
              <h3>Mariba Vince</h3>
              <p class="team-role">General Manager</p>
              <p class="team-bio">
                Vince oversees daily operations with a passion for luxury hospitality. He ensures room standards,
                guest accommodations, and front-desk workflows align to deliver a world-class experience.
              </p>
              <div class="team-socials">
                <a href="https://www.facebook.com/alan.dsilva.1253/" aria-label="Facebook" target="_blank" rel="noopener">
                  <i class="fa fa-facebook-square"></i>
                </a>
                <a href="https://github.com/alandsilva26" aria-label="GitHub" target="_blank" rel="noopener">
                  <i class="fa fa-github"></i>
                </a>
                <a href="https://www.linkedin.com/in/alan-dsilva-289b77170/" aria-label="LinkedIn" target="_blank" rel="noopener">
                  <i class="fa fa-linkedin"></i>
                </a>
              </div>
            </div>
          </article>

          <!-- B_linker09 -->
          <article class="team-card">
            <div class="team-photo">
              <img src="./media/images/aboutus/babu.jpg" alt="Aaron D'silva" loading="lazy" />
            </div>
            <div class="team-body">
              <h3>B_linker09</h3>
              <p class="team-role">Head of Guest Experiences</p>
              <p class="team-bio">
                B_linker09 leads our hospitality programs — curating memorable journeys for every visitor, from elite
                amenities to fine dining, translating expectations into unforgettable stays.
              </p>
              <div class="team-socials">
                <a href="https://facebook.com/dsilva.aaron.7" aria-label="Facebook" target="_blank" rel="noopener">
                  <i class="fa fa-facebook-square"></i>
                </a>
                <a href="https://github.com/Aarondsilva0007" aria-label="GitHub" target="_blank" rel="noopener">
                  <i class="fa fa-github"></i>
                </a>
                <a href="https://www.linkedin.com/in/aaron-d-silva-a05756200/" aria-label="LinkedIn" target="_blank" rel="noopener">
                  <i class="fa fa-linkedin"></i>
                </a>
              </div>
            </div>
          </article>

          <!-- Nancy -->
          <article class="team-card">
            <div class="team-photo">
              <img src="./media/images/aboutus/nancy.jpg" alt="Ansel D'souza" loading="lazy" />
            </div>
            <div class="team-body">
              <h3>Nancy Kilonzo</h3>
              <p class="team-role">Technical Director</p>
              <p class="team-bio">
                Nancy manages our digital ecosystem — reservation pipelines, portal security, and marketing analytics —
                keeping every guest transaction safe, stable, and seamless.
              </p>
              <div class="team-socials">
                <a href="https://www.facebook.com/ansel.dsouza.71" aria-label="Facebook" target="_blank" rel="noopener">
                  <i class="fa fa-facebook-square"></i>
                </a>
                <a href="https://github.com/ansel2000" aria-label="GitHub" target="_blank" rel="noopener">
                  <i class="fa fa-github"></i>
                </a>
                <a href="https://www.linkedin.com/in/anselfdsouza/" aria-label="LinkedIn" target="_blank" rel="noopener">
                  <i class="fa fa-linkedin"></i>
                </a>
              </div>
            </div>
          </article>
        </div>
      </div>
    </section>

    <!-- ============ Features ============ -->
    <section class="features-section">
      <div class="container section-pad">
        <div class="section-title centered">
          <span class="eyebrow">On property</span>
          <h2>Hotel features</h2>
          <p class="section-sub">Everything you need, right where you need it.</p>
        </div>

        <div class="features-grid">
          <div class="feature">
            <img src="https://img.icons8.com/pastel-glyph/64/be0854/swimming-pool.png" alt="" loading="lazy" />
            <span>Swimming Pool</span>
          </div>
          <div class="feature">
            <img src="https://img.icons8.com/ios/64/be0854/beach.png" alt="" loading="lazy" />
            <span>Beach View</span>
          </div>
          <div class="feature">
            <img src="https://img.icons8.com/ios-filled/64/be0854/emergency-exit.png" alt="" loading="lazy" />
            <span>Fire Exit</span>
          </div>
          <div class="feature">
            <img src="https://img.icons8.com/pastel-glyph/64/be0854/parking--v1.png" alt="" loading="lazy" />
            <span>Car Parking</span>
          </div>
          <div class="feature">
            <img src="https://img.icons8.com/pastel-glyph/64/be0854/hair-dryer--v1.png" alt="" loading="lazy" />
            <span>Hair Dryer</span>
          </div>
          <div class="feature">
            <img src="https://img.icons8.com/ios-filled/64/be0854/mini-bar.png" alt="" loading="lazy" />
            <span>Mini Bar</span>
          </div>
          <div class="feature">
            <img src="https://img.icons8.com/ios/64/be0854/cocktail.png" alt="" loading="lazy" />
            <span>Drinks</span>
          </div>
          <div class="feature">
            <img src="https://img.icons8.com/ios-filled/64/be0854/car.png" alt="" loading="lazy" />
            <span>Airport Shuttle</span>
          </div>
        </div>
      </div>
    </section>

  </main>

  <?php include(__DIR__ . "/includes/footer.php"); ?>
</body>
</html>