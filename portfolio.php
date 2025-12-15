<?php $page = 'portfolio';
require_once 'ans-design-backoffice/config/db.php';
require_once 'init_user.php';
?>
<?php
// On inclut le header qui contient déjà session_start() et la logique d'auth
include 'header.php';
?>
<?php
// On inclut le header qui contient déjà session_start() et la logique d'auth
include 'header.php';
?>
<!-- MAIN -->
<main>
  <!-- BANNER BLOG -->
  <section id="banner-catalogue" class="scrolltop banner-portfolio">
    <img src="assets/img/fond.png" alt="" class="bg-image" />
    <div class="wrapper t-center">
      <h1>Nos Réalisations</h1>
      <p class="medium white">
        Un aperçu de projets que nous avons eu le plaisir de concrétiser
        pour nos clients.
      </p>
    </div>
  </section>
  <!-- NOS REALISATIONS -->
  <?php
  // Récupérer toutes les catégories
  $categories = $pdo->query("SELECT * FROM categories_realisation ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

  // Récupérer toutes les réalisations
  $stmt = $pdo->query("SELECT * FROM realisations ORDER BY categorie_id");
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Organiser les réalisations par catégorie
  $all_realisations = [];
  foreach ($rows as $row) {
    $all_realisations[$row['categorie_id']][] = $row; // [] pour stocker plusieurs réalisations
  }
  ?>

  <!-- NOS REALISATIONS -->
  <section id="realisations" class="realisations-page">
    <div class="wrapper">
      <div class="section-title">
        <h2>Nos Réalisations</h2>
      </div>
      <div class="inner-title">
        <p>Un aperçu de projets que nous avons eu le plaisir de concrétiser pour nos clients.</p>
      </div>

      <!-- Onglets dynamiques -->
      <ul class="tabslink tabslink-realisations">
        <?php foreach ($categories as $index => $cat): ?>
          <li>
            <a href="#realisation_<?= $cat['id'] ?>" class="<?= $index === 0 ? 'active' : '' ?>">
              <?= htmlspecialchars($cat['titre']) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>

      <!-- Contenu des réalisations -->
      <div class="container-realisations">
        <?php foreach ($categories as $index => $cat): ?>
          <div class="tabscontent-realisations <?= $index === 0 ? 'active' : '' ?>" id="realisation_<?= $cat['id'] ?>">
            <?php
            $realisations = $all_realisations[$cat['id']] ?? [];
            if (empty($realisations)) {
              echo "<p>Aucune réalisation pour cette catégorie.</p>";
            } else {
              foreach ($realisations as $r): ?>
                <div class="card-realisation">
                  <div class="realisation-text">
                    <h3><?= htmlspecialchars($r['titre']) ?></h3>

                    <ul class="liste">
                      <?php if (!empty($r['client'])): ?>
                        <li><b>Client :</b> <?= htmlspecialchars($r['client']) ?></li>
                      <?php endif; ?>

                      <?php if (!empty($r['nombre_ex'])): ?>
                        <li><b>nb d'ex :</b> <?= htmlspecialchars($r['nombre_ex']) ?></li>
                      <?php endif; ?>

                      <?php if (!empty($r['delai_ex'])): ?>
                        <li><b>delai d'ex :</b> <?= htmlspecialchars($r['delai_ex']) ?></li>
                      <?php endif; ?>
                    </ul>
                  </div>

                  <div class="realisation-img">
                    <?php if (!empty($r['image']) && file_exists("ans-design-backoffice/upload/" . $r['image'])): ?>
                      <img src="ans-design-backoffice/upload/<?= $r['image'] ?>" alt="">
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach;
            } ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<!-- FOOTER -->
<footer>
  <div class="wrapper">
    <div class="container-information">
      <div class="card-information mail">
        <img src="assets/img/mail.svg" alt="" />
        <a href="mailto:ans.designprint.annexe@gmail.com">ans.designprint.annexe@gmail.com</a>
        <a href="mailto:ans.designprint.annexe@gmail.com">ans.designprint.annexe@gmail.com</a>
      </div>
      <div class="card-information phone">
        <img src="assets/img/phone.svg" alt="" />
        <a href="tel:+261346324272">+261 34 63 242 72</a>
        <a href="tel:+261346324272">+261 34 63 242 72</a>
      </div>
      <div class="card-information maps">
        <img src="assets/img/location.svg" alt="" />
        <a href="">Ambanidia, Rond Point Hazo tokana</a>
        <a href="">Ambanidia, Rond Point Hazo tokana</a>
      </div>
      <img src="assets/img/fond.png" alt="" class="bg-image" />
    </div>
    <div class="container-additional-information">
      <div class="text-payement">
        <h3>Payement sécurisé</h3>
        <p class="small">
          Grâce à nos solutions de paiement en ligne sécurisées, vous
          bénéficiez d'une expérience d'achat fluide et flexible, accessible
          24h/24, avec la possibilité de choisir parmi plusieurs options de
          règlement, tout en ayant l'assurance que vos transactions sont
          protégées par les dernières technologies de sécurité.
        </p>
      </div>
      <div class="img-payement">
        <img src="assets/img/mvola.png" alt="" />
        <img src="assets/img/airtel-money.png" alt="" />
        <img src="assets/img/orange-money.svg" alt="" />
        <img src="assets/img/visa.png" alt="" />
      </div>
      <ul class="menu-footer">
        <li>
          <a href="">A propos</a>
        </li>
        <li>
          <a href="">Services</a>
        </li>
        <li>
          <a href="">Portfolio</a>
        </li>
        <li>
          <a href="">Blog</a>
        </li>
        <li>
          <a href="">Devis</a>
        </li>
      </ul>
      <form action="" method="post" class="subscribe">
        <textarea name="" id="" placeholder="Votre message ..."></textarea>
        <button type="submit" class="btn-card">Envoyez</button>
      </form>
      <ul class="social-icon">
        <li>
          <a href="" target="_blank"><img src="assets/img/facebook.svg" alt="" /></a>
        </li>
        <li>
          <a href="" target="_blank"><img src="assets/img/whatsapp.svg" alt="" /></a>
        </li>
        <li>
          <a href="" target="_blank"><img src="assets/img/instagram.svg" alt="" /></a>
        </li>
        <li>
          <a href="" target="_blank"><img src="assets/img/linkedin.svg" alt="" /></a>
        </li>
      </ul>
    </div>
    <a href=".scrolltop" class="fleche-footer">
      <img src="assets/img/fleche-footer.svg" alt="" />
    </a>
  </div>
</footer>
<script src="assets/libs/jquery/jquery.min.js"></script>
<script src="assets/libs/fancybox/fancybox.min.js"></script>
<script src="assets/libs/slick/slick.min.js"></script>
<script src="assets/js/main.js"></script>
</body>

</html>