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
  <section id="realisations">
    <div class="wrapper">
      <div class="section-title">
        <h2>Projets Signés</h2>
      </div>
      <div class="inner-title">
        <p>Un aperçu des projets que nous avons concrétisés pour nos clients</p>
      </div>

      <!-- Onglets dynamiques -->
      <div class="container-btn-slick">
        <button class="slick-prev-custom"><img src="assets/img/arrow.svg" alt=""></button>
        <ul class="tabslink tabslink-realisations">
          <?php foreach ($categories as $index => $cat): ?>
            <li>
              <a href="#realisation_<?= $cat['id'] ?>" class="<?= $index === 0 ? 'active' : '' ?>">
                <?= htmlspecialchars($cat['titre']) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
        <button class="slick-next-custom"><img src="assets/img/arrow.svg" alt=""></button>
      </div>

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
                    <div class="logo_realisation">
                      <?php if (!empty($r['logo'])): ?>
                        <img src="ans-design-backoffice/upload/<?= htmlspecialchars($r['logo']) ?>" alt="">
                      <?php endif; ?>
                    </div>
                    <div>
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
                    <div class="date_realisation">
                      <?php if (!empty($r['date_realisation'])): ?>
                        <span><?= htmlspecialchars($r['date_realisation']) ?></span>
                      <?php endif; ?>
                    </div>
                  </div>

                  <div class="realisation-img">
                    <?php if (!empty($r['image']) && file_exists("ans-design-backoffice/upload/" . $r['image'])): ?>
                      <img src="ans-design-backoffice/upload/<?= $r['image'] ?>" alt="">
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach;
            } ?>
            <?php if (count($realisations) > 4): ?>
              <button class="voir-plus-btn">Voir plus</button>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<!-- FOOTER -->
<?php
// On inclut le footer
include 'footer.php';
?>