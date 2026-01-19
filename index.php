<?php

$page = 'accueil';

require_once 'ans-design-backoffice/config/db.php';
require_once 'init_user.php';

/* ==========================================
   PRODUITS PHARES (via checkbox backoffice)
   ========================================== */
try {
    $stmt = $pdo->prepare("
        SELECT id, nom, description, prix_base, image, reduction
        FROM produits
        WHERE actif = 1
          AND produit_phare = 1
        ORDER BY id DESC
        LIMIT 8
    ");
    $stmt->execute();
    $produitsPhares = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $produitsPhares = [];
}

/* ==========================================
   TECHNOLOGIES (inchangé)
   ========================================== */
$stmtTech = $pdo->query("
    SELECT *
    FROM technologies
    WHERE actif = 1
    ORDER BY ordre ASC
");
$technologies = $stmtTech->fetchAll(PDO::FETCH_ASSOC);

/* ==================================================
   CATÉGORIES (PAPETERIES)
   ================================================== */
$stmtCat = $pdo->prepare("
    SELECT id, nom
    FROM categories
    ORDER BY nom ASC
");
$stmtCat->execute();
$categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

/* ==================================================
   PRODUITS PHARES PAR CATÉGORIE
   ================================================== */
$produitsParCategorie = [];

$stmtProd = $pdo->prepare("
    SELECT id, nom, prix_base, image, reduction
    FROM produits
    WHERE actif = 1
      AND produit_phare = 1
      AND categorie_id = ?
    ORDER BY id DESC
");

foreach ($categories as $cat) {
    $stmtProd->execute([$cat['id']]);
    $produitsParCategorie[$cat['id']] = $stmtProd->fetchAll(PDO::FETCH_ASSOC);
}

//BANNER
$stmt = $pdo->prepare("SELECT * FROM banner LIMIT 1");
$stmt->execute();
$banner = $stmt->fetch(PDO::FETCH_ASSOC);

//STATS 1
$stmt = $pdo->query("SELECT valeur FROM stats ORDER BY id ASC");
$stats = $stmt->fetchAll(PDO::FETCH_COLUMN);

//STATS 2
$stmt = $pdo->query("SELECT valeur FROM stats_2 ORDER BY id ASC");
$stats2 = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Inclure le header
include 'header.php';
?>

<!-- MAIN -->
<main>
    <!-- BANNER -->
    <section id="banner" class="scrolltop">
        <div class="wrapper">
            <img src="assets/img/<?php echo htmlspecialchars($banner['image_fond']); ?>" alt="" class="bg-image">
            <div class="banner-text">
                <h1>
                    <?php echo $banner['titre']; ?>
                </h1>
                <p class="medium white">
                    <?php echo htmlspecialchars($banner['sous_titre']); ?>
                </p>
                <div class="banner-btn">
                    <a href="catalogue.php" class="btn-gradient">Nos Services</a>
                    <a href="#devis" class="btn-white" id="open-popup-devis">Nos Prestations</a>
                </div>
            </div>
            <div class="banner-QR">
                <img src="assets/img/<?php echo htmlspecialchars($banner['image_qr']); ?>" alt="">
            </div>
        </div>
    </section>
    <!-- CLIENTS -->
    <section id="clients">
        <div class="wrapper">
            <span>La Confiance de Grandes Marques</span>

            <?php
            // Récupérer tous les logos
            $logos = $pdo->query("SELECT * FROM clients_logos ORDER BY ordre ASC")->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <div class="container-logo-client">
                <div class="logos-slide">
                    <?php foreach ($logos as $logo): ?>
                        <?php if (!empty($logo['logo'])): ?>
                            <img src="ans-design-backoffice/assets/img/<?= htmlspecialchars($logo['logo']) ?>"
                                alt="<?= htmlspecialchars($logo['entreprise']) ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <div class="logos-slide">
                    <?php foreach ($logos as $logo): ?>
                        <?php if (!empty($logo['logo'])): ?>
                            <img src="ans-design-backoffice/assets/img/<?= htmlspecialchars($logo['logo']) ?>"
                                alt="<?= htmlspecialchars($logo['entreprise']) ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
    </section>
    <!-- ATOUTS -->
    <section id="atouts">
        <div class="wrapper">
            <div class="section-title">
                <h2>Ce Qui Compte</h2>
            </div>
            <div class="inner-title">
                <p class="medium">Notre différence, votre satisfaction. Survolez une carte pour en savoir davantage.</p>
            </div>
            <div class="container-atouts t-center">
                <div class="card-atout">
                    <img src="assets/img/control.svg" alt="">
                    <span>Service irréprochable </span>
                    <div class="card-hover">
                        <p> Exécution parfaite. Un contrôle rigoureux à chaque étape pour un résultat optimal garanti.
                        </p>
                    </div>
                </div>
                <div class="card-atout">
                    <img src="assets/img/express.svg" alt="">
                    <span>Production express</span>
                    <div class="card-hover">
                        <p> Vos délais, notre priorité grâce à notre parc machine moderne.
                        </p>
                    </div>
                </div>
                <div class="card-atout">
                    <img src="assets/img/conseil.svg" alt="">
                    <span>Conseils d'Experts</span>
                    <div class="card-hover">
                        <p> Un vrai partenaire qui vous accompagne dans le choix des supports et finitions.
                        </p>
                    </div>
                </div>
                <div class="card-atout">
                    <img src="assets/img/livraison_2.svg" alt="">
                    <span>Livraison Partout</span>
                    <div class="card-hover">
                        <p>Où que vous soyez, sur Antananarivo ou dans toutes les provinces de Madagascar.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- STATISTIQUES -->
    <section id="stats">
        <div class="wrapper">
            <div class="card-stat t-center">
                <div class="icone_stat">
                    <img src="assets/img/icone_stat.svg" alt="">
                </div>
                <span class="nombre"><?php echo htmlspecialchars($stats[0]); ?></span>
                <p class="small">Cartes de visite produites quotidiennement</p>
            </div>
            <div class="card-stat t-center">
                <div class="icone_stat">
                    <img src="assets/img/icone_stat_2.svg" alt="">
                </div>
                <span class="nombre"><?php echo htmlspecialchars($stats[1]); ?></span>
                <p class="small">Projets réalisés avec succès et livrés</p>
            </div>
            <div class="card-stat t-center">
                <div class="icone_stat">
                    <img src="assets/img/icone_stat_3.svg" alt="">
                </div>
                <span class="nombre"><?php echo htmlspecialchars($stats[2]); ?></span>
                <p class="small">Ans d’expertise au service de vos impressions</p>
            </div>
            <div class="card-stat t-center">
                <div class="icone_stat">
                    <img src="assets/img/icone_stat_4.svg" alt="">
                </div>
                <span class="nombre"><?php echo htmlspecialchars($stats[3]); ?></span>
                <p class="small">Clients fidèles qui nous font confiance</p>
            </div>
        </div>
    </section>

    <?php
    // Assure que $pdo est disponible (include config/db.php en haut de index)
    $temoignages = $pdo->query("SELECT * FROM temoignages WHERE valide = 1 ORDER BY created_at DESC LIMIT 12")->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <!-- TEMOIGNAGES -->
    <section id="temoignages">
        <div class="wrapper">
            <div class="section-title">
                <h2>Témoignages et réussites clients</h2>
            </div>
            <div class="container-temoignages">
                <?php foreach ($temoignages as $t): ?>
                    <div class="card-temoignage">
                        <div class="profil">
                            <?php if ($t['photo']): ?>
                                <img src="uploads/temoignages/<?= htmlspecialchars($t['photo']) ?>"
                                    alt="<?= htmlspecialchars($t['prenom']) ?>">
                            <?php else: ?>
                                <img src="assets/img/default-user.png" alt="<?= htmlspecialchars($t['prenom']) ?>">
                            <?php endif; ?>
                        </div>
                        <div class="note">
                            <?php
                            // SVG pour chaque note
                            $stars_active = [
                                1 => 'assets/img/star_1.svg',
                                2 => 'assets/img/star_2.svg',
                                3 => 'assets/img/star_3.svg',
                                4 => 'assets/img/star_4.svg',
                                5 => 'assets/img/star_5.svg',
                            ];

                            // SVG pour étoile inactive
                            $star_inactive = 'assets/img/star-inactif.svg';

                            $note = $t['note'] ?? 0; // note actuelle (1 à 5)
                        
                            for ($i = 1; $i <= 5; $i++):
                                $src = $i <= $note ? $stars_active[$note] : $star_inactive;
                                ?>
                                <img src="<?= $src ?>" alt="Étoile <?= $i ?>">
                            <?php endfor; ?>

                        </div>
                        <p class="avis"><?= nl2br(htmlspecialchars($t['avis'])) ?></p>
                        <span class="nom"><?= htmlspecialchars($t['prenom']) ?></span>
                        <span class="poste"><?= htmlspecialchars($t['poste']) ?></span>
                        <span class="poste"><?= htmlspecialchars($t['entreprise']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="slick-prev-custom-temoignages"><img src="assets/img/arrow.svg" alt=""></button>
            <button class="slick-next-custom-temoignages"><img src="assets/img/arrow.svg" alt=""></button>
            <a href="#temoignages" class="btn-gradient" id="open-popup-temoignages">Laissez un avis</a>
        </div>
    </section>
    <!-- POP-UP TEMOIGNAGES -->
    <section class="popup pop-up-temoignages">
        <div class="pop-up-container">
            <div class="header-devis-temoignages">
                <h3>
                    Votre opinion nous est précieuse !
                </h3>
            </div>
            <div class="body-devis-temoignages">
                <form method="POST" action="temoignages.php" enctype="multipart/form-data" id="form-temoignages">
                    <input type="text" name="prenom" placeholder="Votre prénom" required>
                    <input type="text" name="poste" placeholder="Votre poste">
                    <input type="text" name="entreprise" placeholder="Votre entreprise">
                    <textarea name="avis" placeholder="Votre avis" required></textarea>
                    <div class="rating">
                        <input type="radio" name="note" id="star5" value="5" /><label for="star5">★</label>
                        <input type="radio" name="note" id="star4" value="4" /><label for="star4">★</label>
                        <input type="radio" name="note" id="star3" value="3" /><label for="star3">★</label>
                        <input type="radio" name="note" id="star2" value="2" /><label for="star2">★</label>
                        <input type="radio" name="note" id="star1" value="1" /><label for="star1">★</label>
                    </div>
                    <input type="file" name="photo" accept="uploads/temoignages/*">
                    <button type="submit" class="btn-red">Envoyer</button>
                </form>
            </div>
            <a href="#close" id="close-popup-temoignages"><img src="assets/img/close.svg" alt=""></a>
        </div>
    </section>

    <!-- NOS PRODUITS PHARES -->
    <section id="produits-phares">
        <div class="wrapper">

            <div class="section-title">
                <h2>Nos Produits Phares</h2>
            </div>

            <div class="inner-title">
                <p>Nous utilisons des équipements de pointe pour un résultat impeccable à chaque impression.</p>
            </div>

            <!-- ONGLES PAPETERIES (CATÉGORIES) -->
            <ul class="tabslink-produits">
                <?php foreach ($categories as $index => $cat): ?>
                    <li>
                        <a href="#cat_<?= $cat['id'] ?>" class="<?= $index === 0 ? 'active' : '' ?>">
                            <?= htmlspecialchars($cat['nom']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="container-produit">

                <?php foreach ($categories as $index => $cat): ?>
                    <div class="tabscontent-produits <?= $index === 0 ? 'active' : '' ?>" id="cat_<?= $cat['id'] ?>">

                        <?php if (empty($produitsParCategorie[$cat['id']])): ?>
                            <p>Aucun produit phare dans cette catégorie.</p>
                        <?php else: ?>

                            <?php foreach ($produitsParCategorie[$cat['id']] as $p): ?>
                                <div class="card-produit">

                                    <a href="etape.php?id=<?= $p['id']; ?>" class="link-box"></a>

                                    <img src="uploads/produits/<?= htmlspecialchars($p['image']) ?>"
                                        alt="<?= htmlspecialchars($p['nom']) ?>">

                                    <h3><?= htmlspecialchars($p['nom']) ?></h3>

                                    <span>
                                        À partir de <br>
                                        <b><?= number_format($p['prix_base'], 0, ',', ' ') ?> Ar</b>
                                    </span>

                                    <div class="bouton-produit">
                                        <a href="etape.php?id=<?= $p['id']; ?>" class="btn-card">Devis</a>

                                        <?php if (!empty($p['reduction'])): ?>
                                            <span class="reduction">-<?= intval($p['reduction']) ?>%</span>
                                        <?php endif; ?>
                                    </div>

                                </div>
                            <?php endforeach; ?>

                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

            </div>
            <button class="slick-prev-custom-produits"><img src="assets/img/arrow.svg" alt=""></button>
            <button class="slick-next-custom-produits"><img src="assets/img/arrow.svg" alt=""></button>
        </div>
    </section>

    <!-- NOS PACKS -->
    <section id="packs">
        <div class="wrapper">
            <div class="section-title">
                <h2>Packs Design</h2>
            </div>
            <div class="inner-title">
                <p>Du lancement de votre activité à l’identité complète pour grandes sociétés..</p>
            </div>
            <div class="container-pack">
                <div class="card-pack">
                    <div class="text-pack">
                        <h3>PACK</h3>
                        <img src="assets/img/pack_santatra.svg" alt="">
                        <ul class="description-pack">
                            <li>Logo simple (1–2 propositions, 1 retouche)</li>
                            <li>Carte de visite</li>
                            <li>Signature email</li>
                            <li>Fichiers PNG/JPG</li>
                        </ul>
                        <button class="voir-plus">Voir plus</button>
                    </div>
                    <span class="prix">150 000 Ar</span>
                    <div class="bouton-pack">
                        <a href="" class="btn-card">Devis</a>
                        <span class="reduction">-20%</span>
                    </div>
                </div>
                <div class="card-pack">
                    <div class="text-pack">
                        <h3>PACK</h3>
                        <img src="assets/img/pack_sahy.svg" alt="">
                        <ul class="description-pack">
                            <li>Logo premium (2–3 propositions, 2–3 retouches)</li>
                            <li>Mini charte graphique (couleurs, typographies, règles d’usage)</li>
                            <li>Kit réseaux sociaux : photo de profil + bannières + 2 templates modifiables</li>
                            <li>Flyer ou dépliant</li>
                            <li>Fichiers sources (.AI/.PSD)</li>
                        </ul>
                        <button class="voir-plus">Voir plus</button>
                    </div>
                    <span class="prix">350 000 Ar</span>
                    <div class="bouton-pack">
                        <a href="" class="btn-card">Devis</a>
                        <span class="reduction">-20%</span>
                    </div>
                </div>
                <div class="card-pack">
                    <div class="text-pack">
                        <h3>PACK</h3>
                        <img src="assets/img/pack_sidina.svg" alt="">
                        <ul class="description-pack">
                            <li>Logo complet + déclinaisons</li>
                            <li>Charte graphique détaillée</li>
                            <li>Papeterie complète : cartes, tête de lettre, enveloppes, chemises</li>
                            <li>Brochure commerciale (4 à 8 pages)</li>
                            <li>Roll-up ou X-Banner ou Panneaux</li>
                            <li>Kit web : favicon, bannières, boutons</li>
                        </ul>
                        <button class="voir-plus">Voir plus</button>
                    </div>
                    <span class="prix">1 200 000 Ar</span>
                    <div class="bouton-pack">
                        <a href="" class="btn-card">Devis</a>
                        <span class="reduction">-20%</span>
                    </div>
                </div>
                <div class="card-pack">
                    <div class="text-pack">
                        <h3>PACK</h3>
                        <img src="assets/img/pack_sangany.svg" alt="">
                        <ul class="description-pack">
                            <li>Audit & stratégie de marque</li>
                            <li>Refonte totale ou création d’identité</li>
                            <li>Brand Book complet</li>
                            <li>Communication 360° : print + digital</li>
                            <li>Catalogues ou Magazines</li>
                            <li>Motion design (logo animé ou vidéo 15s)</li>
                            <li>Conseils graphiques personnalisés</li>
                            <li>Modifications illimitées</li>
                        </ul>
                        <button class="voir-plus">Voir plus</button>
                    </div>
                    <span class="prix">3 500 000 Ar</span>
                    <div class="bouton-pack">
                        <a href="" class="btn-card">Devis</a>
                        <span class="reduction">-20%</span>
                    </div>
                </div>
            </div>
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
                    <div class="tabscontent-realisations <?= $index === 0 ? 'active' : '' ?>"
                        id="realisation_<?= $cat['id'] ?>">
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
    <!-- STATISTIQUE 2 -->
    <section id="stats" class="stats-2">
        <div class="wrapper">
            <div class="card-stat t-center">
                <span class="nombre">
                    <?php echo htmlspecialchars($stats2[0]); ?>
                </span>
                <h3>Projets Livrés à Temps</h3>
                <p class="small">Priorité à votre planning : nous garantissons chaque échéance.
                </p>
            </div>
            <div class="card-stat t-center">
                <span class="nombre">
                    <?php echo htmlspecialchars($stats2[1]); ?>
                </span>
                <h3>Partenaires de Confiance</h3>
                <p class="small">Nos partenaires privilégient notre expertise pour une communication efficace.
                </p>
            </div>
            <div class="card-stat t-center">
                <span class="nombre"><?php echo htmlspecialchars($stats2[2]); ?></span>
                <h3>Solutions sur Mesure</h3>
                <p class="small">Chaque projet bénéficie d'une solution unique et optimisée.
                </p>
            </div>
        </div>
    </section>
    <!-- TECHNOLOGIES -->
    <section id="technologies">
        <div class="wrapper t-center">
            <div class="section-title">
                <h2>Nos Technologies</h2>
            </div>
            <div class="inner-title">
                <p>Nous utilisons des équipements de pointe pour garantir un résultat impeccable à chaque impression.
                </p>
            </div>
            <div class="container-technologie">
                <?php foreach ($technologies as $tech): ?>
                    <div class="card-technologie">
                        <!-- Chaque carte pointe vers son popup -->
                        <a href="#" class="link-box open-popup-tech"
                            data-popup-id="popup-technologie-<?= $tech['id'] ?>"></a>
                        <img src="uploads/technologies/<?= htmlspecialchars($tech['image']) ?>"
                            alt="<?= htmlspecialchars($tech['nom']) ?>">
                        <h3><?= htmlspecialchars($tech['nom']) ?></h3>
                        <p class="small"><?= htmlspecialchars($tech['description_courte']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- POP-UP TECHNOLOGIES -->
    <?php foreach ($technologies as $index => $tech): ?>

        <?php
        $cycle = $index % 8;
        if (in_array($cycle, [0, 2, 5, 7])) {
            $h3Color = '#F5BF2A'; // jaune
        } else {
            $h3Color = '#DF4D34'; // rouge
        }
        ?>
        <section class="popup pop-up-technologie" id="popup-technologie-<?= $tech['id'] ?>">
            <div class="pop-up-container">
                <div class="header-technologie">
                    <div class="icone-img">
                        <img src="uploads/technologies/<?= htmlspecialchars($tech['image']) ?>"
                            alt="<?= htmlspecialchars($tech['nom']) ?>">
                    </div>
                    <h3 style="color: <?= $h3Color ?>;">
                        <?= htmlspecialchars($tech['nom']) ?>
                    </h3>
                    <p><?= htmlspecialchars($tech['description_courte']) ?></p>
                </div>
                <div class="body-technologie">
                    <?php
                    $description = $tech['description_longue'] ?: "Aucune information détaillée disponible.";
                    $lines = preg_split("/\r\n|\n|\r/", $description);

                    echo "<ul>";
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if ($line !== '') {
                            if (strpos($line, ':') !== false) {
                                list($key, $value) = explode(':', $line, 2);
                                echo "<li><b>" . htmlspecialchars($key, ENT_QUOTES) . ":</b>" . htmlspecialchars($value, ENT_QUOTES) . "</li>";
                            } else {
                                echo "<li>" . htmlspecialchars($line, ENT_QUOTES) . "</li>";
                            }
                        }
                    }
                    echo "</ul>";
                    ?>
                    <div class="img_technologie">
                        <?php if (!empty($tech['image_technologie'])): ?>
                            <img src="uploads/technologies/<?= htmlspecialchars($tech['image_technologie']) ?>"
                                alt="<?= htmlspecialchars($tech['nom']) ?>">
                        <?php endif; ?>
                    </div>
                </div>
                <a href="#close" class="close-popup" id="close-popup-technologie"><img src="assets/img/close.svg"
                        alt="Fermer"></a>
            </div>
        </section>
    <?php endforeach; ?>

    <!-- PASSION-->
    <section id="passion">
        <div class="wrapper">
            <div class="passion-text">
                <div class="section-title">
                    <h2>Plus qu'une impression, une <span>passion.</span></h2>
                </div>
                <div class="inner-title">
                    <p class="small">Depuis notre création, A.N.S Design Print transforme vos idées en réalité tangible.
                        Une équipe passionnée, combinant savoir-faire artisanal et technologies de pointe pour dépasser
                        vos attentes :</p>
                </div>
                <ul>
                    <li><span>Matériaux supérieurs – Rendu impeccable</span></li>
                    <li><span>Accompagnement personnalisé – Chaque projet est unique</span></li>
                </ul>
                <a href="#passion" class="btn-red" id="open-popup-devis">Démarrer un projet</a>
            </div>
            <div class="passion-img slick-image">
                <img src="assets/img/banner_mon_compte.png" alt="">
                <img src="assets/img/banner_mon_compte.png" alt="">
            </div>
        </div>
    </section>
    <!-- HISTORIQUE -->
    <section id="historique">
        <img src="assets/img/fond.png" alt="" class="bg-image">
        <div class="wrapper">
            <div class="section-title">
                <h2>Notre Histoire</h2>
            </div>
            <div class="inner-title">
                <p>Les moments clés qui ont façonné notre expertise.</p>
            </div>
        </div>
    </section>
    <!-- CONTAINER-HISTORIQUE -->
    <section id="container-historique">
        <div class="wrapper">
            <svg id="timeline" class="separateur-historique" version="1.1" xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 167.8 975.3"
                style="enable-background:new 0 0 167.8 975.3;" xml:space="preserve">
                <style type="text/css">
                    .st0 {
                        fill: none;
                        stroke: url(#SVGID_1_);
                        stroke-width: 10;
                        stroke-linecap: round;
                    }

                    .st1 {
                        fill: #E12948;
                    }

                    .st2 {
                        fill: #E44043;
                    }

                    .st3 {
                        fill: #E9623D;
                    }

                    .st4 {
                        fill: #EE8D34;
                    }

                    .st5 {
                        fill: #F3AD2E;
                    }

                    .st6 {
                        fill: #FFFFFF;
                    }
                </style>
                <g id="Calque_1">
                    <g>
                        <linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="83.9" y1="966.025" x2="83.9"
                            y2="9.225">
                            <stop offset="0" style="stop-color:#F5BF2A" />
                            <stop offset="1" style="stop-color:#E12948" />
                        </linearGradient>
                        <path class="st0" d="M83.9,961c43.6,0,78.9-35.3,78.9-78.9c0-43.6-35.3-78.9-78.9-78.9S5,767.9,5,724.3
            c0-43.6,35.3-78.9,78.9-78.9l0,0c43.6,0,78.9-35.3,78.9-78.9c0-43.6-35.3-78.9-78.9-78.9S5,452.3,5,408.7s35.3-78.9,78.9-78.9
            s78.9-35.3,78.9-78.9S127.5,172,83.9,172S5,136.7,5,93.1s35.3-78.9,78.9-78.9" />
                        <circle class="st1" cx="84.9" cy="94.1" r="61.1" />
                        <circle class="st1" cx="84.9" cy="249.6" r="61.1" />
                        <circle class="st2" cx="84.9" cy="409.2" r="61.1" />
                        <circle class="st3" cx="84.9" cy="566.8" r="61.1" />
                        <circle class="st4" cx="84.9" cy="724.4" r="61.1" />
                        <circle class="st5" cx="84.9" cy="882.9" r="61.1" />
                    </g>
                </g>
                <g id="Calque_2" class="icone">
                    <g>
                        <path class="st6" d="M87.8,110.6H74.4c-0.4,0-0.7-0.2-0.9-0.5c-0.3-0.4-6.2-10.8-4.6-22.8c1.6-12.1,11.2-19.3,11.6-19.6
            c0.4-0.3,0.8-0.3,1.2,0c0.4,0.3,10,7.5,11.6,19.6c1.6,12.1-4.4,22.4-4.6,22.8C88.5,110.4,88.2,110.6,87.8,110.6z M87.8,109.5
            L87.8,109.5L87.8,109.5z M75,108.5h12.2c1.1-2.2,5.4-11.1,4.1-21c-1.3-9.6-8.1-16-10.2-17.8c-2.1,1.8-8.9,8.2-10.2,17.8
            C69.6,97.4,73.8,106.3,75,108.5z" />
                    </g>
                    <g>
                        <path class="st6" d="M81.1,98c-3.6,0-6.5-2.9-6.5-6.5c0-3.6,2.9-6.5,6.5-6.5c3.6,0,6.5,2.9,6.5,6.5C87.6,95.1,84.7,98,81.1,98z
             M81.1,87.1c-2.4,0-4.4,2-4.4,4.4c0,2.4,2,4.4,4.4,4.4c2.4,0,4.4-2,4.4-4.4C85.5,89.1,83.5,87.1,81.1,87.1z" />
                    </g>
                    <g>
                        <path class="st6" d="M87.6,114.4h-13c-0.6,0-1-0.5-1-1v-3.5c0-0.6,0.5-1,1-1h13c0.6,0,1,0.5,1,1v3.5
            C88.6,114,88.2,114.4,87.6,114.4z M75.6,112.4h11v-1.4h-11V112.4z" />
                    </g>
                    <g>
                        <path class="st6" d="M64.2,114.8C64.2,114.8,64.2,114.8,64.2,114.8c-0.4,0-0.7-0.2-0.9-0.5c-2.7-4.3-3.4-8.2-2.2-11.7
            c1.8-5.3,7.4-7.5,7.7-7.6c0.5-0.2,1.1,0.1,1.3,0.6c0.2,0.5-0.1,1.1-0.6,1.3c-0.1,0-5,2-6.5,6.4c-0.9,2.5-0.4,5.5,1.3,8.7
            c1.7-2,5.2-5.8,7.9-6.4c0.5-0.1,1.1,0.2,1.2,0.8c0.1,0.5-0.2,1.1-0.8,1.2c-2.4,0.6-6.4,5.1-7.7,6.9
            C64.9,114.6,64.6,114.8,64.2,114.8z" />
                    </g>
                    <g>
                        <path class="st6" d="M97.9,114.8c-0.3,0-0.6-0.1-0.8-0.4c-1.3-1.7-5.3-6.3-7.7-6.9c-0.5-0.1-0.9-0.7-0.8-1.2
            c0.1-0.5,0.7-0.9,1.2-0.8c2.7,0.6,6.2,4.4,7.9,6.4c1.7-3.2,2.2-6.1,1.3-8.7c-1.5-4.4-6.4-6.4-6.5-6.4c-0.5-0.2-0.8-0.8-0.6-1.3
            c0.2-0.5,0.8-0.8,1.3-0.6c0.2,0.1,5.9,2.3,7.7,7.6c1.2,3.5,0.4,7.4-2.2,11.7C98.6,114.6,98.3,114.7,97.9,114.8
            C98,114.8,98,114.8,97.9,114.8z" />
                    </g>
                    <g>
                        <path class="st6"
                            d="M81.4,125.2c-0.4,0-0.7-0.2-0.8-0.6c0,0-1-3.8-3.1-6.3c-0.8,1.1-1.9,2.8-2.1,4.1c0,0.3-0.2,0.5-0.5,0.6
            c-0.3,0.1-0.5,0.1-0.8-0.1c-0.1-0.1-2.1-1.4-2.4-3.7c-0.2-1.5,0.4-2.9,1.7-4.3c0.3-0.3,0.8-0.4,1.1-0.1c0.3,0.3,0.4,0.8,0.1,1.1
            c-0.9,1-1.4,2.1-1.2,3c0.1,0.7,0.5,1.3,0.8,1.8c0.8-2.1,2.5-4.2,2.6-4.3c0.1-0.2,0.4-0.3,0.6-0.3c0.2,0,0.4,0.1,0.6,0.2
            c1.5,1.4,2.5,3.3,3.2,4.9c1-2.9,2.7-4.8,2.8-4.9c0.2-0.2,0.4-0.3,0.7-0.2c1.2,0.2,2.7,2.5,3.7,4.5c1.2-2.8-0.5-4.6-0.6-4.7
            c-0.3-0.3-0.3-0.8,0-1.2c0.3-0.3,0.8-0.3,1.2,0c1.1,1.2,2.6,4.3,0.1,8c-0.2,0.2-0.4,0.4-0.7,0.3c-0.3,0-0.5-0.2-0.7-0.5
            c-1-2-2.2-4-2.9-4.7c-0.7,1-2.3,3.3-2.5,6.3C82.2,124.9,81.9,125.2,81.4,125.2C81.4,125.2,81.4,125.2,81.4,125.2z" />
                    </g>
                    <g>
                        <path class="st6" d="M81.7,80.9c-5,0-9.1-1.3-9.3-1.4c-0.3-0.1-0.5-0.4-0.4-0.8c0.1-0.3,0.4-0.5,0.8-0.4c0.1,0,9.1,2.9,16.7,0
            c0.3-0.1,0.7,0,0.8,0.4c0.1,0.3,0,0.7-0.4,0.8C87.1,80.6,84.3,80.9,81.7,80.9z" />
                    </g>
                    <g>
                        <path class="st6" d="M101.7,88.9c-2.9,0-5.3-2.3-5.5-5.2c-0.1-1.5,0.4-2.9,1.4-4c1-1.1,2.3-1.7,3.8-1.8c1.5-0.1,2.9,0.4,4,1.4
            c1.1,1,1.7,2.3,1.8,3.8c0.1,1.5-0.4,2.9-1.4,4c-1,1.1-2.3,1.7-3.8,1.8C101.9,88.9,101.8,88.9,101.7,88.9z M101.7,79.6
            c-0.1,0-0.1,0-0.2,0c-2.1,0.1-3.7,1.9-3.6,4.1c0.1,2.1,1.9,3.7,4.1,3.6c2.1-0.1,3.7-1.9,3.6-4.1C105.4,81.2,103.7,79.6,101.7,79.6
            z" />
                    </g>
                    <g>
                        <path class="st6" d="M100,96.7c-0.2,0-0.5-0.1-0.7-0.3C99.2,96.3,99,96,99,95.8l-0.1-2c-0.6-0.1-1.4-0.4-2.3-1
            c-0.5-0.3-0.6-0.9-0.3-1.4c0.3-0.5,0.9-0.6,1.4-0.3c1,0.7,2.2,0.8,2.2,0.8c0.4,0,0.7,0.3,0.9,0.6l0.1,0.1c0,0.1,0.1,0.2,0.1,0.3
            l0.1,1.6l2.6-0.2l-0.1-1.8c0-0.5,0.3-0.9,0.8-1.1c1.2-0.3,2.1-1,2.1-1c0.3-0.2,0.7-0.3,1.1-0.2l0.1,0.1c0.1,0,0.2,0.1,0.3,0.2
            l1.2,1.1l1.8-2l-1.3-1.2c-0.4-0.3-0.4-0.9-0.2-1.3c0.7-1,0.8-2.2,0.8-2.2c0-0.4,0.3-0.7,0.6-0.9l0.1-0.1c0.1,0,0.2-0.1,0.3-0.1
            l1.6-0.1l-0.2-2.6l-1.8,0.1c-0.5,0-0.9-0.3-1.1-0.8c-0.3-1.2-1-2.1-1-2.1c-0.2-0.3-0.3-0.7-0.2-1l0.1-0.1c0-0.1,0.1-0.2,0.2-0.3
            l1.1-1.2l-2-1.8l-1.2,1.3c-0.3,0.4-0.9,0.4-1.3,0.2c-1-0.7-2.2-0.8-2.2-0.8c-0.4,0-0.7-0.3-0.9-0.6l-0.1-0.1
            c0-0.1-0.1-0.2-0.1-0.3l-0.1-1.6l-2.6,0.2l0.1,1.8c0,0.5-0.3,0.9-0.8,1.1c-1.2,0.3-2.1,1-2.1,1c-0.3,0.2-0.7,0.3-1,0.2l-0.1-0.1
            c-0.1,0-0.2-0.1-0.3-0.2L94.2,75l-0.8,0.8c-0.4,0.4-1.1,0.4-1.4-0.1s-0.4-1.1,0.1-1.4l1.5-1.4c0.4-0.4,1-0.4,1.4,0l1.5,1.3
            c0.4-0.2,0.9-0.5,1.4-0.7l-0.1-2c0-0.6,0.4-1,1-1.1l4.7-0.3c0.3,0,0.5,0.1,0.7,0.3c0.2,0.2,0.3,0.4,0.3,0.7l0.1,2
            c0.4,0.1,0.9,0.3,1.5,0.5l1.3-1.5c0.4-0.4,1-0.5,1.4-0.1l3.5,3.1c0.2,0.2,0.3,0.4,0.3,0.7c0,0.3-0.1,0.5-0.3,0.7l-1.3,1.5
            c0.2,0.4,0.5,0.9,0.7,1.4l2-0.1c0.6,0,1,0.4,1.1,1l0.3,4.7c0,0.3-0.1,0.5-0.3,0.7c-0.2,0.2-0.4,0.3-0.7,0.3l-2,0.1
            c-0.1,0.4-0.3,0.9-0.5,1.5L113,89c0.4,0.4,0.5,1,0.1,1.4l-3.1,3.5c-0.4,0.4-1,0.5-1.4,0.1l-1.5-1.3c-0.4,0.2-0.9,0.5-1.4,0.7
            l0.1,2c0,0.6-0.4,1-1,1.1L100,96.7C100.1,96.7,100.1,96.7,100,96.7z" />
                    </g>
                </g>
                <g id="Calque_3" class="icone">
                    <g>
                        <path class="st6" d="M95.7,264.5h-2.5v-1.7h2.5c1.5,0,2.8-1.3,2.8-2.8v-8.3c0-1.5-1.3-2.8-2.8-2.8H71.2c-1.5,0-2.8,1.3-2.8,2.8
            v8.3c0,1.5,1.3,2.8,2.8,2.8h2.5v1.7h-2.5c-2.5,0-4.5-2-4.5-4.5v-8.3c0-2.5,2-4.5,4.5-4.5h24.5c2.5,0,4.5,2,4.5,4.5v8.3
            C100.2,262.5,98.2,264.5,95.7,264.5z" />
                    </g>
                    <g>
                        <path class="st6" d="M91,243.1c0.3,0,0.6,0.3,0.6,0.6v3.5H75.4v-3.5c0-0.3,0.3-0.6,0.6-0.6H91 M91,241.4H76c-1.3,0-2.3,1-2.3,2.3
            v5.2h19.5v-5.2C93.2,242.4,92.2,241.4,91,241.4L91,241.4z" />
                    </g>
                    <g>
                        <path class="st6" d="M91.5,258.9v11.2c0,0.3-0.3,0.6-0.6,0.6H76c-0.3,0-0.6-0.3-0.6-0.6v-11.2H91.5 M93.2,257.2H73.7v12.9
            c0,1.3,1,2.3,2.3,2.3h15c1.3,0,2.3-1,2.3-2.3V257.2L93.2,257.2z" />
                    </g>
                    <g>
                        <circle class="st6" cx="93.5" cy="253.6" r="2" />
                    </g>
                    <g>
                        <path class="st6" d="M94.1,259.6H71.2c-0.5,0-0.9-0.4-0.9-0.9c0-0.5,0.4-0.9,0.9-0.9h22.9c0.5,0,0.9,0.4,0.9,0.9
            C95,259.2,94.6,259.6,94.1,259.6z" />
                    </g>
                    <g>
                        <path class="st6" d="M86.9,267.6H80c-0.5,0-0.9-0.4-0.9-0.9c0-0.5,0.4-0.9,0.9-0.9h6.9c0.5,0,0.9,0.4,0.9,0.9
            C87.8,267.2,87.4,267.6,86.9,267.6z" />
                    </g>
                    <g>
                        <path class="st6" d="M89.7,263.4H77.3c-0.5,0-0.9-0.4-0.9-0.9c0-0.5,0.4-0.9,0.9-0.9h12.4c0.5,0,0.9,0.4,0.9,0.9
            C90.5,263,90.1,263.4,89.7,263.4z" />
                    </g>
                    <g>
                        <path class="st6"
                            d="M64.2,255.1c-0.3,0-0.5-0.1-0.7-0.3c-3.7-5.1-3.3-12.3,1.1-16.2c3.1-2.8,7.7-3.6,11.9-2.2
            c0.8-6.8,6.4-12.1,13.1-12.2c0,0,0,0,0,0c5.9,0,11.1,4.2,12.8,10.1c1.8,0,6.7,0.6,9.7,4.9c3,4.4,1.6,9.1,1.3,10
            c-0.1,0.4-0.6,0.7-1.1,0.5c-0.4-0.1-0.7-0.6-0.5-1.1c0.3-0.8,1.4-4.8-1.1-8.5c-3.1-4.6-8.8-4.2-8.9-4.2c-0.4,0-0.8-0.3-0.9-0.7
            c-1.2-5.5-6-9.4-11.3-9.4c0,0,0,0,0,0c-6.1,0-11.3,5.3-11.5,11.7c0,0.3-0.2,0.5-0.4,0.7c-0.2,0.2-0.5,0.2-0.8,0.1
            c-3.9-1.7-8.3-1.2-11.2,1.5c-3.7,3.4-4.1,9.5-0.9,14c0.3,0.4,0.2,0.9-0.2,1.2C64.6,255,64.4,255.1,64.2,255.1z" />
                    </g>
                    <g>
                        <path class="st6" d="M109.2,264.5l-0.3-0.1c-0.2-0.1-4.4-1.6-5.3-8.8c-0.3-2.5,0.1-4.4,1.3-5.8c1.7-1.9,4.2-1.9,4.3-1.9
            c2.2,0.1,3.8,0.8,4.8,2.1c1.6,2,1.1,4.7,1,5.1c-1.1,7.6-5.4,9.2-5.6,9.3L109.2,264.5z M109.2,249.6c0,0-1.8,0-3,1.3
            c-0.9,1-1.2,2.5-0.9,4.4c0.7,5.1,3.1,6.8,3.9,7.3c0.9-0.5,3.4-2.3,4.2-7.8l0-0.1c0,0,0.4-2.2-0.7-3.7
            C112,250.1,110.8,249.7,109.2,249.6z" />
                    </g>
                    <g>
                        <circle class="st6" cx="109.3" cy="254.2" r="2.2" />
                    </g>
                </g>
                <g id="Calque_7" class="icone">
                    <g>
                        <path class="st6" d="M90.1,426.6c-0.5,0-0.9-0.4-0.9-0.9v-3.8c0-0.3,0.2-0.7,0.5-0.8c5.7-3.2,8.6-8.2,8.6-15.1
            c0-5.5-1.5-9.8-4.5-12.7c-4.2-4.1-10.1-4-10.2-4c0,0,0,0-0.1,0c-0.1,0-5.9-0.1-10.2,4c-3,2.9-4.5,7.2-4.5,12.7
            c0,6.9,2.9,11.9,8.6,15.1c0.3,0.2,0.5,0.5,0.5,0.8v3.8c0,0.5-0.4,0.9-0.9,0.9s-0.9-0.4-0.9-0.9v-3.2c-5.9-3.5-9-9.2-9-16.5
            c0-6.1,1.7-10.8,5-14c4.7-4.6,11-4.6,11.5-4.5c0.5,0,6.8,0,11.5,4.5c3.3,3.3,5,8,5,14c0,7.3-3.1,13-9,16.5v3.2
            C91.1,426.1,90.6,426.6,90.1,426.6z" />
                    </g>
                    <g>
                        <path class="st6" d="M92.1,430.6H75c-1.1,0-2-0.9-2-2v-1.4c0-1.1,0.9-2,2-2h17.1c1.1,0,2,0.9,2,2v1.4
            C94.1,429.8,93.2,430.6,92.1,430.6z M75,427.2C75,427.2,74.9,427.3,75,427.2l-0.1,1.5c0,0,0,0.1,0.1,0.1h17.1c0,0,0.1,0,0.1-0.1
            v-1.4c0,0,0-0.1-0.1-0.1H75z" />
                    </g>
                    <g>
                        <path class="st6" d="M89.9,434.1H77.3c-1.1,0-2-0.9-2-2v-1.4c0-1.1,0.9-2,2-2h12.6c1.1,0,2,0.9,2,2v1.4
            C91.8,433.2,91,434.1,89.9,434.1z M77.3,430.6C77.2,430.6,77.2,430.7,77.3,430.6l-0.1,1.5c0,0,0,0.1,0.1,0.1h12.6
            c0,0,0.1,0,0.1-0.1v-1.4c0,0,0-0.1-0.1-0.1H77.3z" />
                    </g>
                    <g>
                        <path class="st6" d="M83.6,427.2c-0.5,0-0.9-0.4-0.9-0.9v-11c0-0.5,0.4-0.9,0.9-0.9c0.5,0,0.9,0.4,0.9,0.9v11
            C84.5,426.8,84.1,427.2,83.6,427.2z" />
                    </g>
                    <g>
                        <path class="st6" d="M83.6,416.3c-4.4,0-8.6-2.1-11.2-5.6c-0.2-0.3-0.2-0.8,0-1.1c2.6-3.5,6.8-5.6,11.2-5.6
            c4.4,0,8.6,2.1,11.2,5.6c0.2,0.3,0.2,0.8,0,1.1C92.1,414.1,88,416.3,83.6,416.3z M74.4,410.1c2.3,2.7,5.6,4.3,9.2,4.3
            c3.6,0,7-1.6,9.2-4.3c-2.3-2.7-5.6-4.3-9.2-4.3C80,405.8,76.6,407.4,74.4,410.1z" />
                    </g>
                    <g>
                        <path class="st6" d="M102.9,423.4c-0.2,0-0.5-0.1-0.6-0.2l-3.7-3.4c-0.4-0.4-0.4-0.9-0.1-1.3c0.4-0.4,0.9-0.4,1.3-0.1l3.7,3.4
            c0.4,0.4,0.4,0.9,0.1,1.3C103.4,423.3,103.1,423.4,102.9,423.4z" />
                    </g>
                    <g>
                        <path class="st6" d="M108.4,405.8h-5c-0.5,0-0.9-0.4-0.9-0.9c0-0.5,0.4-0.9,0.9-0.9h5c0.5,0,0.9,0.4,0.9,0.9
            C109.3,405.3,108.9,405.8,108.4,405.8z" />
                    </g>
                    <g>
                        <path class="st6" d="M99.2,392.3c-0.3,0-0.5-0.1-0.7-0.3c-0.4-0.4-0.3-1,0-1.3l3.7-3.5c0.4-0.4,1-0.3,1.3,0c0.4,0.4,0.3,1,0,1.3
            l-3.7,3.5C99.6,392.2,99.4,392.3,99.2,392.3z" />
                    </g>
                    <g>
                        <path class="st6" d="M83.3,386c-0.5,0-0.9-0.4-0.9-0.9v-4.5c0-0.5,0.4-0.9,0.9-0.9c0.5,0,0.9,0.4,0.9,0.9v4.5
            C84.2,385.6,83.8,386,83.3,386z" />
                    </g>
                    <g>
                        <path class="st6" d="M68.2,392.3c-0.2,0-0.4-0.1-0.6-0.2l-4-3.5c-0.4-0.3-0.4-0.9-0.1-1.3c0.3-0.4,0.9-0.4,1.3-0.1l4,3.5
            c0.4,0.3,0.4,0.9,0.1,1.3C68.7,392.2,68.5,392.3,68.2,392.3z" />
                    </g>
                    <g>
                        <path class="st6" d="M63,405.8h-5.2c-0.5,0-0.9-0.4-0.9-0.9c0-0.5,0.4-0.9,0.9-0.9H63c0.5,0,0.9,0.4,0.9,0.9
            C64,405.3,63.5,405.8,63,405.8z" />
                    </g>
                    <g>
                        <path class="st6" d="M64.3,422.7c-0.2,0-0.5-0.1-0.7-0.3c-0.4-0.4-0.4-1,0-1.3l3.4-3.3c0.4-0.4,1-0.4,1.3,0c0.4,0.4,0.4,1,0,1.3
            l-3.4,3.3C64.7,422.6,64.5,422.7,64.3,422.7z" />
                    </g>
                    <g>
                        <path class="st6" d="M86.7,408.3c-0.2,0.3-0.5,0.6-1,0.6c-0.6,0-1.1-0.5-1.1-1.1c0-0.4,0.2-0.7,0.5-0.9c-0.5-0.2-1-0.3-1.5-0.3
            c-2,0-3.6,1.6-3.6,3.6c0,2,1.6,3.6,3.6,3.6s3.6-1.6,3.6-3.6C87.2,409.5,87,408.9,86.7,408.3z" />
                    </g>
                </g>
                <g id="Calque_4" class="icone">
                    <g>
                        <rect x="70.2" y="550.6" class="st6" width="30.7" height="1.7" />
                    </g>
                    <g>
                        <polygon class="st6"
                            points="108.4,587.7 62.2,587.7 62.2,558.7 63.9,558.7 63.9,586 106.7,586 106.7,558.8 108.4,558.8 		" />
                    </g>
                    <g>
                        <path class="st6" d="M111.1,552.3H59.5c-0.4,0-0.8-0.2-1-0.6c-0.2-0.4-0.2-0.8-0.1-1.2l4-8.3h45.6l4,8.3c0.2,0.4,0.2,0.8-0.1,1.2
            C111.9,552.1,111.5,552.3,111.1,552.3z M60.3,550.6h49.9l-3.2-6.6H63.6L60.3,550.6z" />
                    </g>
                    <g>
                        <path class="st6" d="M111.7,591.2H58.9V586h52.8V591.2z M60.6,589.5H110v-1.7H60.6V589.5z" />
                    </g>
                    <g>
                        <polygon class="st6" points="71,551.8 69.4,551.1 72.6,542.8 74.2,543.4 		" />
                    </g>
                    <g>
                        <polygon class="st6" points="99.6,551.8 96.4,543.4 98,542.8 101.2,551.1 		" />
                    </g>
                    <g>
                        <polygon class="st6" points="81.3,551.5 79.6,551.4 80.1,543 81.8,543.1 		" />
                    </g>
                    <g>
                        <polygon class="st6" points="89.3,551.5 88.8,543.1 90.5,543 91,551.4 		" />
                    </g>
                    <g>
                        <path class="st6" d="M65.1,559.9c-3,0-4.5-1.4-5.3-2.5c-0.5-0.8-0.8-1.7-0.8-2.7v-3.3h1.7v3.3c0,0.6,0.2,1.2,0.5,1.7
            c0.8,1.2,2.1,1.7,3.9,1.7c1.7,0,2.9-0.6,3.7-1.7c0.3-0.5,0.5-1.1,0.5-1.7v-3.3H71v3.3c0,1-0.3,1.9-0.8,2.7
            C69.5,558.5,68,559.9,65.1,559.9z" />
                    </g>
                    <g>
                        <path class="st6" d="M75.3,559.9c-3,0-4.5-1.4-5.3-2.5c-0.5-0.8-0.8-1.7-0.8-2.7v-3.3H71v3.3c0,0.6,0.2,1.2,0.5,1.7
            c0.8,1.2,2.1,1.7,3.9,1.7c1.7,0,2.9-0.6,3.7-1.7c0.3-0.5,0.5-1.1,0.5-1.7v-3.3h1.7v3.3c0,1-0.3,1.9-0.8,2.7
            C79.7,558.5,78.2,559.9,75.3,559.9z" />
                    </g>
                    <g>
                        <path class="st6" d="M85.6,559.9c-3,0-4.5-1.4-5.3-2.5c-0.5-0.8-0.8-1.7-0.8-2.7v-3.3h1.7v3.3c0,0.6,0.2,1.2,0.5,1.7
            c0.8,1.2,2.1,1.7,3.9,1.7c1.7,0,2.9-0.6,3.7-1.7c0.3-0.5,0.5-1.1,0.5-1.7v-3.3h1.7v3.3c0,1-0.3,1.9-0.8,2.7
            C89.9,558.5,88.4,559.9,85.6,559.9z" />
                    </g>
                    <g>
                        <path class="st6" d="M95.8,559.9c-3,0-4.5-1.4-5.3-2.5c-0.5-0.8-0.8-1.7-0.8-2.7v-3.3h1.7v3.3c0,0.6,0.2,1.2,0.5,1.7
            c0.8,1.2,2.1,1.7,3.9,1.7c1.7,0,2.9-0.6,3.7-1.7c0.3-0.5,0.5-1.1,0.5-1.7v-3.3h1.7v3.3c0,1-0.3,1.9-0.8,2.7
            C100.1,558.5,98.6,559.9,95.8,559.9z" />
                    </g>
                    <g>
                        <path class="st6" d="M106,559.9c-3,0-4.5-1.4-5.3-2.5c-0.5-0.8-0.8-1.7-0.8-2.7v-3.3h1.7v3.3c0,0.6,0.2,1.2,0.5,1.7
            c0.8,1.2,2.1,1.7,3.9,1.7c1.7,0,2.9-0.6,3.7-1.7c0.3-0.5,0.5-1.1,0.5-1.7v-3.3h1.7v3.3c0,1-0.3,1.9-0.8,2.7
            C110.3,558.5,108.8,559.9,106,559.9z" />
                    </g>
                    <g>
                        <path class="st6" d="M81.3,576.8h-9c-2.1,0-3.9-1.8-3.9-4v-5.7c0-2.2,1.7-4,3.9-4h9c2.1,0,3.9,1.8,3.9,4v5.7
            C85.2,575,83.5,576.8,81.3,576.8z M72.4,564.9c-1.2,0-2.2,1-2.2,2.2v5.7c0,1.2,1,2.2,2.2,2.2h9c1.2,0,2.2-1,2.2-2.2v-5.7
            c0-1.2-1-2.2-2.2-2.2H72.4z" />
                    </g>
                    <g>
                        <path class="st6" d="M103.2,587.7H87.8v-17.8c0-3.7,3-6.8,6.6-6.8h2.2c3.6,0,6.6,3,6.6,6.8V587.7z M89.5,586h12v-16.1
            c0-2.8-2.2-5-4.9-5h-2.2c-2.7,0-4.9,2.2-4.9,5V586z" />
                    </g>
                </g>
                <g id="Calque_5" class="icone">
                    <g>
                        <rect x="87.5" y="722" class="st6" width="0.4" height="1.9" />
                    </g>
                    <g>
                        <path class="st6"
                            d="M105.8,748.4H69c-0.8,0-1.5-0.6-1.5-1.4V730h1.9v16.5h36V730h1.9v16.9C107.3,747.7,106.6,748.4,105.8,748.4z" />
                    </g>
                    <g>
                        <path class="st6"
                            d="M83,731H63c-0.4,0-0.7-0.2-0.8-0.5c-0.2-0.3-0.1-0.7,0.1-1l5.5-7.1c0.2-0.2,0.5-0.4,0.7-0.4h19
            c0.3,0,0.7,0.2,0.8,0.5c0.2,0.3,0.2,0.7,0,1l-4.5,7.1C83.6,730.8,83.3,731,83,731z M64.9,729.1h17.6l3.3-5.2H68.9L64.9,729.1z" />
                    </g>
                    <g>
                        <path class="st6"
                            d="M112.4,731h-20c-0.3,0-0.6-0.2-0.8-0.4l-4.5-7.1c-0.2-0.3-0.2-0.7,0-1c0.2-0.3,0.5-0.5,0.8-0.5h19
            c0.3,0,0.6,0.1,0.7,0.4l5.5,7.1c0.2,0.3,0.3,0.7,0.1,1C113.1,730.8,112.8,731,112.4,731z M93,729.1h17.6l-4-5.2H89.7L93,729.1z" />
                    </g>
                    <g>
                        <rect x="86.8" y="722.9" class="st6" width="1.9" height="24.5" />
                    </g>
                    <g>
                        <path class="st6" d="M102.5,744.8H92.4c-0.5,0-0.9-0.4-0.9-0.9c0-0.5,0.4-0.9,0.9-0.9h10.1c0.5,0,0.9,0.4,0.9,0.9
            C103.5,744.4,103,744.8,102.5,744.8z" />
                    </g>
                    <g>
                        <path class="st6" d="M83.3,744.8H73.1c-0.5,0-0.9-0.4-0.9-0.9c0-0.5,0.4-0.9,0.9-0.9h10.1c0.5,0,0.9,0.4,0.9,0.9
            C84.2,744.4,83.8,744.8,83.3,744.8z" />
                    </g>
                    <g>
                        <path class="st6" d="M99.4,722.5c-0.4,0-0.7-0.3-0.7-0.7l0-0.8c0-0.5,0.2-1,0.7-1.2l5.6-2.7l-4.3-4.5c-0.4-0.4-0.5-0.9-0.3-1.4
            l2.4-5.6l-6.3-0.8c-0.5-0.1-0.9-0.4-1-0.9l-1.9-5.8l-5.3,3.3c-0.4,0.3-0.9,0.3-1.3,0l-5.4-3.3l-1.8,5.8c-0.1,0.5-0.6,0.8-1.1,0.9
            l-6.3,0.7l2.5,5.6c0.2,0.5,0.1,1-0.2,1.4l-4.2,4.4l5.6,2.8c0.4,0.2,0.7,0.7,0.7,1.2l0,0.8c0,0.4-0.4,0.7-0.8,0.7
            c-0.4,0-0.7-0.4-0.7-0.8l0-0.7l-5.8-2.9c-0.4-0.2-0.6-0.5-0.7-0.9c-0.1-0.4,0.1-0.8,0.3-1.1l4.3-4.5l-2.6-5.7
            c-0.2-0.4-0.1-0.8,0.1-1.1c0.2-0.3,0.6-0.6,0.9-0.6l6.4-0.8l1.9-5.9c0.1-0.4,0.4-0.7,0.8-0.8c0.4-0.1,0.8-0.1,1.1,0.1l5.5,3.4
            l5.5-3.4c0.3-0.2,0.7-0.2,1.1-0.1c0.4,0.1,0.6,0.4,0.8,0.8l2,6l6.5,0.8c0.4,0,0.7,0.3,0.9,0.6c0.2,0.3,0.2,0.8,0.1,1.1l-2.4,5.7
            l4.4,4.6c0.3,0.3,0.4,0.7,0.3,1.1c-0.1,0.4-0.3,0.7-0.7,0.9l-5.7,2.8l0,0.6C100.2,722.1,99.8,722.4,99.4,722.5
            C99.4,722.5,99.4,722.5,99.4,722.5z" />
                    </g>
                    <g>
                        <path class="st6" d="M81.6,710.7c0-0.4,0.1-0.7,0.2-0.8c0.1-0.1,0.3-0.2,0.5-0.2h0.1c0.1,0,0.2,0,0.3,0c0.1,0,0.1,0,0.2,0.1
            c0.1,0,0.1,0.1,0.1,0.1c0,0,0.1,0.1,0.1,0.1s0,0.1,0,0.1c0,0,0,0.1,0,0.2c0,0.1,0,0.1,0,0.2c0,0.1,0,0.7,0,1.9c0,1.3,0,2.8,0,4.5
            c0,0.5-0.1,0.7-0.4,0.8c-0.1,0-0.2,0-0.3,0c-0.3,0-0.5-0.1-0.6-0.3c-0.4-0.5-0.9-1.3-1.5-2.3c-0.7-1-1.1-1.7-1.4-2.1
            c0-0.1-0.1-0.1-0.1-0.1c0,0,0,0.1,0,0.2v4c0,0.2,0,0.4-0.1,0.5c-0.1,0.1-0.3,0.1-0.6,0.1c-0.3,0-0.5-0.1-0.6-0.2
            c-0.1-0.1-0.1-0.3-0.1-0.4V717c0-0.4,0-0.9,0-1.6c0-0.7,0-1.3,0-1.6c0-0.4,0-0.9,0-1.6c0-0.7,0-1.2,0-1.6v-0.1c0-0.1,0-0.2,0-0.2
            c0-0.1,0-0.1,0.1-0.2c0-0.1,0.1-0.1,0.2-0.2c0.1,0,0.2-0.1,0.3-0.1H78c0.2,0,0.4,0,0.5,0.1c0.1,0.1,0.2,0.2,0.3,0.4
            c0.9,1.5,1.9,2.9,2.7,4.2c0,0,0,0,0,0c0,0,0,0,0,0c0,0,0,0,0,0v-2.1C81.6,711.7,81.6,711.1,81.6,710.7z" />
                        <path class="st6"
                            d="M83.5,713.5c0-0.2,0-0.5,0-0.9c0-0.4,0-0.7,0-0.9c0-0.4,0-0.7,0-0.9c0-0.2,0-0.4,0-0.5c0-0.1,0.1-0.2,0.1-0.3
            c0-0.1,0.1-0.1,0.2-0.2c0.1,0,0.2-0.1,0.3-0.1c0.1,0,0.3,0,0.5,0c0.4,0,0.9,0,1.4,0c0.5,0,0.8,0,1,0c0.5,0,0.8,0.1,1,0.2
            c0.1,0.1,0.2,0.4,0.2,0.7c0,0.2-0.1,0.4-0.2,0.4c-0.1,0.1-0.4,0.1-0.7,0.1c-0.1,0-0.4,0-0.8,0c-0.4,0-0.8,0-1.1,0h-0.2
            c-0.2,0-0.3,0.3-0.3,0.8c0,0.2,0,0.5,0,0.9c0,0,0,0.1,0.1,0.1c0.1,0.1,0.1,0.1,0.1,0.1c0.5,0,0.8,0,1.1,0h0.9c0.2,0,0.3,0,0.4,0.1
            s0.1,0.2,0.1,0.4c0,0,0,0.1,0,0.1c0,0.1,0,0.1,0,0.1c0,0.2-0.1,0.4-0.2,0.5c-0.1,0.1-0.4,0.1-0.7,0.1c-0.1,0-0.3,0-0.6,0
            c-0.3,0-0.5,0-0.7,0h-0.2c0,0-0.1,0-0.1,0.1c-0.1,0.1-0.1,0.1-0.1,0.2c0,0.2,0,0.5,0,0.9v0.4c0,0.1,0,0.1,0.1,0.2
            c0.1,0.1,0.1,0.1,0.2,0.1h0.1c0.3,0,0.6,0,0.9,0c0.4,0,0.6,0,0.7,0c0.5,0,0.8,0,0.9,0.1c0.1,0.1,0.2,0.3,0.2,0.6
            c0,0.3,0,0.5-0.1,0.6c0,0.1-0.1,0.1-0.2,0.1h-0.1l-2.1,0c-0.2,0-0.5,0-0.8,0c-0.4,0-0.6,0-0.8,0c-0.3,0-0.5-0.2-0.5-0.5V713.5z" />
                        <path class="st6" d="M88.6,710.3c0-0.2,0.1-0.3,0.3-0.4c0.2-0.1,0.5-0.1,0.7-0.1c0.1,0,0.2,0,0.3,0.1c0,0.1,0.1,0.2,0.2,0.5
            c0.2,0.7,0.4,1.4,0.5,2c0.2,0.7,0.3,1.1,0.3,1.5c0.1,0.3,0.1,0.5,0.2,0.7c0,0.1,0.1,0.1,0.1,0.1c0.1,0,0.1,0,0.1-0.1
            c0-0.2,0.1-0.5,0.2-0.8c0.1-0.4,0.2-0.7,0.2-0.9c0.1-0.2,0.1-0.5,0.2-0.8c0.1-0.3,0.2-0.6,0.3-0.8c0,0,0-0.1,0.1-0.2
            c0-0.1,0.1-0.2,0.1-0.3c0-0.1,0.1-0.1,0.1-0.2c0-0.1,0.1-0.1,0.2-0.2c0.1,0,0.2,0,0.3,0h0.1c0,0,0,0,0,0c0,0,0,0,0,0
            c0.1,0,0.3,0,0.4,0.1c0.1,0,0.2,0.1,0.2,0.2c0.1,0.1,0.1,0.2,0.1,0.3c0,0.1,0.1,0.2,0.1,0.3c0.1,0.3,0.2,0.8,0.4,1.6
            c0.2,0.8,0.3,1.4,0.5,1.8c0,0.1,0.1,0.1,0.1,0.1c0.1,0,0.1,0,0.1-0.1c0.4-1.8,0.8-3.1,1.1-3.9c0,0,0-0.1,0.1-0.2
            c0-0.1,0-0.1,0.1-0.2c0,0,0-0.1,0.1-0.1c0-0.1,0-0.1,0.1-0.1c0,0,0,0,0.1-0.1s0.1,0,0.1,0c0,0,0.1,0,0.1,0c0.1,0,0.1,0,0.2,0
            c0,0,0,0,0.1,0c0,0,0.1,0,0.1,0c0,0,0,0,0,0c0,0,0,0,0,0c0.2,0,0.4,0,0.5,0.1c0.1,0.1,0.1,0.2,0.1,0.3c0,0.1,0,0.2,0,0.3
            c0,0.2-0.3,1.3-0.8,3.2c-0.5,1.9-0.9,3.1-1.1,3.7c-0.1,0.1-0.1,0.2-0.2,0.3c-0.1,0.1-0.1,0.1-0.2,0.1c-0.1,0-0.2,0-0.3,0h0
            c-0.1,0-0.2,0-0.3,0c-0.1,0-0.2-0.1-0.2-0.1c-0.1-0.1-0.1-0.1-0.1-0.1c0,0,0-0.1-0.1-0.2c0-0.1-0.1-0.2-0.1-0.2
            c-0.1-0.4-0.3-0.8-0.4-1.3c-0.1-0.4-0.2-0.9-0.4-1.4c-0.1-0.5-0.2-0.9-0.3-1.2c0-0.1-0.1-0.1-0.1-0.1c-0.1,0-0.1,0-0.1,0.1
            c-0.2,0.5-0.4,1.2-0.6,2.1c-0.2,0.9-0.4,1.5-0.5,1.9c0,0.1-0.1,0.2-0.1,0.2c0,0.1-0.1,0.1-0.1,0.2c-0.1,0.1-0.1,0.1-0.2,0.2
            c-0.1,0-0.2,0.1-0.3,0.1h-0.1c-0.1,0-0.2,0-0.2,0c-0.1,0-0.1-0.1-0.2-0.1c-0.1-0.1-0.1-0.1-0.1-0.1c0,0-0.1-0.1-0.1-0.2
            c0-0.1-0.1-0.2-0.1-0.2c0,0,0-0.1-0.1-0.2c0-0.1,0-0.2,0-0.2c0-0.1-0.2-0.6-0.4-1.6c-0.3-1-0.5-2-0.8-2.9
            c-0.2-0.9-0.4-1.5-0.5-1.7C88.6,710.4,88.6,710.3,88.6,710.3z" />
                    </g>
                    <g>
                        <path class="st6" d="M104.9,701.2c-0.2,0-0.3-0.1-0.5-0.2c-0.3-0.3-0.4-0.7-0.1-1.1l2.8-3.4c0.3-0.3,0.7-0.4,1.1-0.1
            c0.3,0.3,0.4,0.7,0.1,1.1l-2.8,3.4C105.3,701.1,105.1,701.2,104.9,701.2z" />
                    </g>
                    <g>
                        <path class="st6" d="M110.5,712.6c-0.1,0-0.2,0-0.4-0.1l-4-2.2c-0.4-0.2-0.5-0.7-0.3-1c0.2-0.4,0.7-0.5,1-0.3l4,2.2
            c0.4,0.2,0.5,0.7,0.3,1C111,712.5,110.8,712.6,110.5,712.6z" />
                    </g>
                    <g>
                        <path class="st6" d="M106.8,705.5c-0.4,0-0.7-0.3-0.7-0.6c-0.1-0.4,0.2-0.8,0.6-0.9l7.8-1.4c0.4-0.1,0.8,0.2,0.9,0.6
            c0.1,0.4-0.2,0.8-0.6,0.9l-7.8,1.4C106.9,705.5,106.9,705.5,106.8,705.5z" />
                    </g>
                    <g>
                        <path class="st6" d="M70.6,701.2c-0.2,0-0.4-0.1-0.6-0.3l-2.8-3.4c-0.3-0.3-0.2-0.8,0.1-1.1c0.3-0.3,0.8-0.2,1.1,0.1l2.8,3.4
            c0.3,0.3,0.2,0.8-0.1,1.1C70.9,701.2,70.8,701.2,70.6,701.2z" />
                    </g>
                    <g>
                        <path class="st6" d="M64.9,712.6c-0.3,0-0.5-0.1-0.7-0.4c-0.2-0.4-0.1-0.8,0.3-1l4-2.2c0.4-0.2,0.8-0.1,1,0.3
            c0.2,0.4,0.1,0.8-0.3,1l-4,2.2C65.2,712.6,65.1,712.6,64.9,712.6z" />
                    </g>
                    <g>
                        <path class="st6" d="M68.6,705.5c0,0-0.1,0-0.1,0l-7.8-1.4c-0.4-0.1-0.7-0.5-0.6-0.9c0.1-0.4,0.5-0.7,0.9-0.6l7.8,1.4
            c0.4,0.1,0.7,0.5,0.6,0.9C69.3,705.2,69,705.5,68.6,705.5z" />
                    </g>
                </g>
                <g id="Calque_6" class="icone">
                    <g>
                        <path class="st6" d="M111.2,903.4H65.5c-1.8,0-3.3-1.5-3.3-3.3v-34.9c0-1.8,1.5-3.3,3.3-3.3h45.6c1.8,0,3.3,1.5,3.3,3.3v34.9
            C114.5,901.9,113,903.4,111.2,903.4z M65.5,863.6c-0.9,0-1.6,0.7-1.6,1.6v34.9c0,0.9,0.7,1.6,1.6,1.6h45.6c0.9,0,1.6-0.7,1.6-1.6
            v-34.9c0-0.9-0.7-1.6-1.6-1.6H65.5z" />
                    </g>
                    <g>
                        <path class="st6" d="M76.5,875.4c-2.1,0-3.8-1.7-3.8-3.8c0-2.1,1.7-3.8,3.8-3.8c2.1,0,3.8,1.7,3.8,3.8
            C80.3,873.7,78.6,875.4,76.5,875.4z M76.5,869.5c-1.2,0-2.1,0.9-2.1,2.1s0.9,2.1,2.1,2.1c1.2,0,2.1-0.9,2.1-2.1
            S77.7,869.5,76.5,869.5z" />
                    </g>
                    <g>
                        <path class="st6" d="M100.1,875.4c-2.1,0-3.8-1.7-3.8-3.8c0-2.1,1.7-3.8,3.8-3.8s3.8,1.7,3.8,3.8
            C104,873.7,102.2,875.4,100.1,875.4z M100.1,869.5c-1.2,0-2.1,0.9-2.1,2.1s0.9,2.1,2.1,2.1s2.1-0.9,2.1-2.1
            S101.3,869.5,100.1,869.5z" />
                    </g>
                    <g>
                        <path class="st6" d="M102.4,890.2c-2.1,0-3.8-1.7-3.8-3.8c0-2.1,1.7-3.8,3.8-3.8c2.1,0,3.8,1.7,3.8,3.8
            C106.3,888.5,104.5,890.2,102.4,890.2z M102.4,884.3c-1.2,0-2.1,0.9-2.1,2.1c0,1.2,0.9,2.1,2.1,2.1c1.2,0,2.1-0.9,2.1-2.1
            C104.5,885.2,103.6,884.3,102.4,884.3z" />
                    </g>
                    <g>
                        <path class="st6" d="M87.9,890.2c-2.1,0-3.8-1.7-3.8-3.8c0-2.1,1.7-3.8,3.8-3.8c2.1,0,3.8,1.7,3.8,3.8
            C91.7,888.5,90,890.2,87.9,890.2z M87.9,884.3c-1.2,0-2.1,0.9-2.1,2.1c0,1.2,0.9,2.1,2.1,2.1c1.2,0,2.1-0.9,2.1-2.1
            C90,885.2,89,884.3,87.9,884.3z" />
                    </g>
                    <g>
                        <path class="st6" d="M72.1,890.2c-2.1,0-3.8-1.7-3.8-3.8c0-2.1,1.7-3.8,3.8-3.8c2.1,0,3.8,1.7,3.8,3.8
            C76,888.5,74.3,890.2,72.1,890.2z M72.1,884.3c-1.2,0-2.1,0.9-2.1,2.1c0,1.2,0.9,2.1,2.1,2.1c1.2,0,2.1-0.9,2.1-2.1
            C74.2,885.2,73.3,884.3,72.1,884.3z" />
                    </g>
                    <g>
                        <path class="st6" d="M99.5,887.2h-8.6c-0.5,0-0.9-0.4-0.9-0.9c0-0.5,0.4-0.9,0.9-0.9h8.6c0.5,0,0.9,0.4,0.9,0.9
            C100.3,886.8,99.9,887.2,99.5,887.2z" />
                    </g>
                    <g>
                        <path class="st6" d="M89.8,885c-0.2,0-0.4-0.1-0.6-0.2c-0.4-0.3-0.4-0.8-0.1-1.2l8.5-10.3c0.3-0.4,0.8-0.4,1.2-0.1
            c0.4,0.3,0.4,0.8,0.1,1.2l-8.5,10.3C90.3,884.9,90,885,89.8,885z" />
                    </g>
                    <g>
                        <path class="st6" d="M102,884.3c-0.4,0-0.8-0.3-0.9-0.7l-1.4-9c-0.1-0.5,0.2-0.9,0.7-1c0.5-0.1,0.9,0.2,1,0.7l1.4,9
            c0.1,0.5-0.2,0.9-0.7,1C102.1,884.3,102,884.3,102,884.3z" />
                    </g>
                    <g>
                        <path class="st6" d="M73,884.4c-0.1,0-0.2,0-0.2,0c-0.5-0.1-0.7-0.6-0.6-1.1l2.7-9.1c0.1-0.5,0.6-0.7,1.1-0.6
            c0.5,0.1,0.7,0.6,0.6,1.1l-2.7,9.1C73.7,884.2,73.4,884.4,73,884.4z" />
                    </g>
                    <g>
                        <path class="st6" d="M86.1,884.9c-0.3,0-0.5-0.1-0.7-0.3l-7.8-10.1c-0.3-0.4-0.2-0.9,0.2-1.2c0.4-0.3,0.9-0.2,1.2,0.2l7.8,10.1
            c0.3,0.4,0.2,0.9-0.2,1.2C86.5,884.8,86.3,884.9,86.1,884.9z" />
                    </g>
                    <g>
                        <path class="st6" d="M84.9,887.2h-9.8c-0.5,0-0.9-0.4-0.9-0.9c0-0.5,0.4-0.9,0.9-0.9h9.8c0.5,0,0.9,0.4,0.9,0.9
            C85.8,886.8,85.4,887.2,84.9,887.2z" />
                    </g>
                    <g>
                        <path class="st6" d="M87.9,901c-1.4,0-2.5-1.1-2.5-2.5c0-1.4,1.1-2.5,2.5-2.5c1.4,0,2.5,1.1,2.5,2.5C90.4,899.9,89.2,901,87.9,901
            z M87.9,897.8c-0.4,0-0.7,0.3-0.7,0.7s0.3,0.7,0.7,0.7c0.4,0,0.7-0.3,0.7-0.7S88.3,897.8,87.9,897.8z" />
                    </g>
                    <g>
                        <path class="st6" d="M113.6,895.3H63.1c-0.5,0-0.9-0.4-0.9-0.9c0-0.5,0.4-0.9,0.9-0.9h50.5c0.5,0,0.9,0.4,0.9,0.9
            C114.5,895,114.1,895.3,113.6,895.3z" />
                    </g>
                    <g>
                        <path class="st6"
                            d="M94.9,910.1H81.8c-0.3,0-0.5-0.1-0.7-0.4c-0.2-0.2-0.2-0.5-0.1-0.8l2.1-6.8c0.1-0.4,0.4-0.6,0.8-0.6h8.9
            c0.4,0,0.7,0.2,0.8,0.6l2.1,6.8c0.1,0.3,0,0.5-0.1,0.8C95.4,910,95.2,910.1,94.9,910.1z M83,908.4h10.8l-1.5-5h-7.7L83,908.4z" />
                    </g>
                    <g>
                        <path class="st6" d="M99.5,910.1H76.3c-0.5,0-0.9-0.4-0.9-0.9c0-0.5,0.4-0.9,0.9-0.9h23.2c0.5,0,0.9,0.4,0.9,0.9
            C100.3,909.8,99.9,910.1,99.5,910.1z" />
                    </g>
                </g>
            </svg>
            <div class="card-historique">
                <div class="card-historique_item">
                    <h3>2018</h3>
                    <span>Lancement d’A.N.S Création</span>
                    <p>Nous lançons A.N.S Création avec l'ambition de créer une agence tout-en-un, du design à
                        l'impression.</p>
                </div>
            </div>
            <div class="card-historique">
                <div class="card-historique_item">
                    <h3>2019-2021</h3>
                    <span>Adaptation à la crise, impression en ligne</span>
                    <p>Nous pivotons en développant un service d'impression en ligne, renforçant notre capacité
                        d'innovation.</p>
                </div>
            </div>

            <div class="card-historique">
                <div class="card-historique_item">
                    <h3>2021</h3>
                    <span>Rebranding et premier atelier à Antanimora</span>
                    <p>Rebranding, fondation de A.N.S.com, et ouverture de notre premier atelier à Antanimora.</p>
                </div>
            </div>
            <div class="card-historique">
                <div class="card-historique_item">
                    <h3>2022</h3>
                    <span>Siège principal à Ambanidia</span>
                    <p>Ouverture de notre siège principal à Ambanidia pour une meilleure accessibilité et relation
                        client.</p>
                </div>
            </div>
            <div class="card-historique">
                <div class="card-historique_item">
                    <h3>2024</h3>
                    <span>Expansion, goodies, modernisation des équipements</span>
                    <p>Annexe goodies, lancement de MisiTiako et <br> modernisation de nos équipements.</p>
                </div>
            </div>
            <div class="card-historique">
                <div class="card-historique_item">
                    <h3>2025</h3>
                    <span>Site web professionnel et CRM intégré</span>
                    <p>Lancement de notre site web professionnel unique à Madagascar et intégration d'un CRM.</p>
                </div>
            </div>
        </div>
    </section>
    <!-- EQUIPE -->
    <?php
    // Récupérer tous les membres de l'équipe (email inclus)
    $members = $pdo->query("SELECT * FROM equipe ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <section id="equipe">
        <div class="wrapper">

            <div class="section-title t-center">
                <h2>Notre Équipe</h2>
            </div>

            <div class="inner-title">
                <p>Les visages derrière la qualité et le service A.N.S. Une synergie de talents à votre écoute.</p>
            </div>
            <div class="container-equipe">
                <?php if (empty($members)): ?>
                    <p>Aucun membre pour le moment.</p>

                <?php else: ?>
                    <?php foreach ($members as $m): ?>
                        <div class="card-equipe">

                            <!-- Photo + icônes -->
                            <div class="equipe-img">

                                <?php if (!empty($m['photo'])): ?>
                                    <img src="ans-design-backoffice/upload/<?= htmlspecialchars($m['photo']) ?>"
                                        alt="<?= htmlspecialchars($m['nom']) ?>">
                                <?php else: ?>
                                    <img src="assets/img/default-user.png" alt="<?= htmlspecialchars($m['nom']) ?>">
                                <?php endif; ?>

                                <div class="info-membre">

                                    <!-- Icône email (si email existe) -->
                                    <?php if (!empty($m['email'])): ?>
                                        <a href="mailto:<?= htmlspecialchars($m['email']) ?>" target="_blank">
                                            <img src="assets/img/mail.svg" alt="Mail">
                                        </a>
                                    <?php endif; ?>

                                    <!-- Icône popup info -->
                                    <a href="#" class="open-popup-equipe" data-popup-id="popup-equipe-<?= $m['id'] ?>">
                                        <img src="assets/img/question-mark.svg" alt="Info">
                                    </a>

                                </div>
                            </div>

                            <!-- Nom + Poste -->
                            <div class="equipe-text t-center">
                                <h3><?= htmlspecialchars($m['nom']) ?></h3>
                                <p class="small"><?= htmlspecialchars($m['poste']) ?></p>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button class="slick-prev-custom-equipe"><img src="assets/img/arrow.svg" alt=""></button>
            <button class="slick-next-custom-equipe"><img src="assets/img/arrow.svg" alt=""></button>
        </div>
    </section>

    <!-- POP-UP EQUIPE -->
    <?php foreach ($members as $m): ?>
        <section class="popup pop-up-info-equipe" id="popup-equipe-<?= $m['id'] ?>">
            <div class="pop-up-container">
                <div class="header-info-equipe">
                    <div class="nom_poste">
                        <img src="<?= !empty($m['photo']) ? 'ans-design-backoffice/upload/' . htmlspecialchars($m['photo']) : 'assets/img/default-user.png' ?>"
                            alt="<?= htmlspecialchars($m['nom']) ?>">
                        <h3><?= htmlspecialchars($m['nom']) ?></h3>
                        <span class="poste"><?= htmlspecialchars($m['poste']) ?></span>
                    </div>
                </div>
                <div class="body-info-equipe">
                    <?php
                    $description = $m['description'] ?: "Aucune description disponible.";

                    // Séparer par les sauts de ligne
                    $lines = preg_split("/\r\n|\n|\r/", $description);

                    // Si au moins une ligne, créer une liste
                    if (!empty($lines)) {
                        echo "<ul>";
                        foreach ($lines as $line) {
                            $line = trim($line);
                            if ($line !== '') {
                                echo "<li>" . htmlspecialchars($line) . "</li>";
                            }
                        }
                        echo "</ul>";
                    }
                    ?>
                </div>
                <a href="#close" class="close-popup" id="close-popup-info-equipe"><img src="assets/img/close.svg"
                        alt="Fermer"></a>
            </div>
        </section>
    <?php endforeach; ?>

    <!-- MARQUE -->
    <section id="marque">
        <div class="wrapper t-center">
            <div class="section-title">
                <h2>Donnez du pouvoir à votre marque.</h2>
            </div>
            <div class="inner-title">
                <p>Chaque projet est traité avec soin et précision</p>
            </div>
            <a href="#devis" class="btn-white" id="open-popup-devis">Demande devis</a>
        </div>
    </section>
    <!-- PROCESSUS -->
    <section id="processus">
        <div class="wrapper">
            <div class="section-title">
                <h2>Notre Processus</h2>
            </div>
            <div class="inner-title">
                <p>Chaque projet est traité avec soin et précision</p>
            </div>
            <div class="container-tabslink-processus">
                <svg class="separateur-processus" version="1.1" id="Calque_1" xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 1380.4 132.7"
                    style="enable-background:new 0 0 1380.4 132.7;" xml:space="preserve">
                    <style type="text/css">
                        .st0 {
                            fill: none;
                            stroke: url(#SVGID_1_);
                            stroke-width: 10;
                            stroke-linecap: round;
                            stroke-miterlimit: 10;
                        }
                    </style>
                    <linearGradient id="SVGID_1_" x1="0" y1="0" x2="100%" y2="0">
                        <stop offset="0" style="stop-color:#E12948" />
                        <stop offset="1" style="stop-color:#EFAC2D" />
                    </linearGradient>

                    <path class="st0" d="M1375.4,66.7c0-28.1-4-61-37.3-61h-269c-33.3,0-36.3,32.9-36.3,61v-0.3V67v-0.3c0,28.1-3,61-36.3,61h-269
c-33.3,0-37.3-32.9-37.3-61l0,0c0-28.1-4-61-37.3-61h-269c-33.3,0-36.3,32.9-36.3,61v-0.3V67v-0.3c0,28.1-3,61-36.3,61h-269
C9,127.7,5,94.8,5,66.7" stroke="url(#SVGID_1_)" />
                </svg>
                <button class="slick-next-custom-processus"><img src="assets/img/arrow.svg" alt=""></button>
                <ul class="tabslink-processus">
                    <li>
                        <a href="#ecoute"> <img src="assets/img/idea.svg" alt=""> <span>Briefing</span> </a>
                    </li>
                    <li>
                        <a href="#prix"> <img src="assets/img/price.svg" alt=""> <span>Prix</span> </a>
                    </li>
                    <li>
                        <a href="#validation"> <img src="assets/img/creation.svg" alt=""> <span>BAT</span> </a>
                    </li>
                    <li>
                        <a href="#verification"> <img src="assets/img/verification.svg" alt="">
                            <span>Preparation</span>
                        </a>
                    </li>
                    <li>
                        <a href="#fabrication"> <img src="assets/img/production.svg" alt=""> <span>Production</span>
                        </a>
                    </li>
                    <li>
                        <a href="#finition"> <img src="assets/img/livraison.svg" alt=""> <span>Controle
                                qualité</span></a>
                    </li>
                    <li>
                        <a href="#transport"> <img src="assets/img/livraison_3.svg" alt=""> <span>Livraison</span></a>
                    </li>
                </ul>
                <button class="slick-prev-custom-processus"><img src="assets/img/arrow.svg" alt=""></button>
            </div>
            <div class="tabscontent-processus" id="ecoute">
                <div class="processus-text">
                    <div class="section-title">
                        <h2>Consultation Initiale </h2>
                    </div>
                    <div class="inner-title">
                        <p>Nous discutons avec vous de vos besoins, du support, des quantités et des délais.
                            Détail supplémentaire : Nous conseillons le type de papier, la finition ou la technologie la
                            plus adaptée.
                            Importance : Une compréhension claire dès le départ évite les erreurs et les coûts imprévus.
                        </p>
                    </div>
                </div>
                <div class="processus-img">
                    <img src="" alt="">
                </div>
            </div>
            <div class="tabscontent-processus" id="prix">
                <div class="processus-text">
                    <div class="section-title">
                        <h2>Devis et Proposition </h2>
                    </div>
                    <div class="inner-title">
                        <p>Nous vous envoyons un devis détaillé avec le coût, les matériaux, la finition et les
                            délais.
                            Détail supplémentaire : Vous pouvez ajuster le projet (quantité, papier, finition) avant
                            validation.
                            Importance : Assure la transparence et fixe les attentes contractuelles.
                        </p>
                    </div>
                </div>
                <div class="processus-img">
                    <img src="" alt="">
                </div>
            </div>
            <div class="tabscontent-processus" id="validation">
                <div class="processus-text">
                    <div class="section-title">
                        <h2>Validation du BAT </h2>
                    </div>
                    <div class="inner-title">
                        <p>Vous recevez une épreuve finale (PDF ou papier) pour approuver textes, images et couleurs.
                            Détail supplémentaire : Pour les projets complexes, un échantillon imprimé réel peut être
                            fourni.
                            Importance : Garantit que la production commence avec votre accord, sans mauvaise surprise.
                        </p>
                    </div>
                </div>
                <div class="processus-img">
                    <img src="" alt="">
                </div>
            </div>
            <div class="tabscontent-processus" id="verification">
                <div class="processus-text">
                    <div class="section-title">
                        <h2>Préparation des Fichiers </h2>
                    </div>
                    <div class="inner-title">
                        <p>Nous vérifions que vos fichiers sont prêts pour l’impression : couleurs, taille, résolution.
                            Détail supplémentaire : Tout problème technique est signalé ou corrigé avant production.
                            Importance : Évite les erreurs lors de l’impression et assure un résultat net et fidèle.
                        </p>
                    </div>
                </div>
                <div class="processus-img">
                    <img src="" alt="">
                </div>
            </div>
            <div class="tabscontent-processus" id="fabrication">
                <div class="processus-text">
                    <div class="section-title">
                        <h2>Impression </h2>
                    </div>
                    <div class="inner-title">
                        <p>Nous imprimons votre projet sur les machines adaptées (Offset, Numérique, Grand Format…).
                            Détail supplémentaire : Les couleurs et la qualité sont contrôlées en continu pour
                            uniformité et précision.
                            Importance : Transforme vos idées en produit tangible, fidèle à vos attentes.
                        </p>
                    </div>
                </div>
                <div class="processus-img">
                    <img src="" alt="">
                </div>
            </div>
            <div class="tabscontent-processus" id="finition">
                <div class="processus-text">
                    <div class="section-title">
                        <h2>Finitions et Contrôle Qualité </h2>
                    </div>
                    <div class="inner-title">
                        <p>Découpe, pliage, reliure, pelliculage, assemblage… et vérification finale du produit.
                            Détail supplémentaire : Chaque pièce est inspectée pour s’assurer qu’elle est parfaite avant
                            emballage.
                            Importance : Assure un produit final soigné, durable et prêt à l’usage.
                        </p>
                    </div>
                </div>
                <div class="processus-img">
                    <img src="" alt="">
                </div>
            </div>
            <div class="tabscontent-processus" id="transport">
                <div class="processus-text">
                    <div class="section-title">
                        <h2>Livraison</h2>
                    </div>
                    <div class="inner-title">
                        <p>Nous emballons soigneusement et livrons votre commande à l’adresse souhaitée.
                            Détail supplémentaire : Livraison possible en express ou en plusieurs étapes selon vos
                            besoins.
                            Importance : Garantit que le produit arrive intact et dans les délais convenus.
                        </p>
                    </div>
                </div>
                <div class="processus-img">
                    <img src="" alt="">
                </div>
            </div>
        </div>
    </section>
    <!--FAQ-->
    <section id="faq">
        <div class="wrapper">
            <div class="section-title">
                <h2>Questions Fréquentes</h2>
            </div>
            <div class="inner-title">
                <p>Trouvez ici les réponses aux questions les plus courantes que nous recevons.</p>
            </div>
            <div class="accordeon">
                <div class="accordeon-content">
                    <div class="accordeon-title">
                        <h3>Quels supports et produits proposez-vous ?</h3>
                        <div class="icon">
                            <img src="assets/img/fleche.svg" alt="" class="fleche">
                        </div>
                    </div>
                    <div class="accordeon-text">
                        <p>Nous proposons une large gamme : flyers, dépliants, cartes de visite, affiches, brochures,
                            catalogues, bâches, roll-ups, stands parapluie, et divers objets publicitaires (stylos,
                            accessoires tech, etc.). Selon vos besoins, nous pouvons également commander des articles
                            spécifiques auprès de nos fournisseurs en Chine si le stock local n’est pas disponible.</p>
                    </div>
                </div>
                <div class="accordeon-content">
                    <div class="accordeon-title">
                        <h3>Offrez-vous un service de conception graphique simple, personnalisée ou juste des
                            impressions ?</h3>
                        <div class="icon">
                            <img src="assets/img/fleche.svg" alt="" class="fleche">
                        </div>
                    </div>
                    <div class="accordeon-text">
                        <p>Oui, nous proposons des services allant de la mise en page simple à la création complète
                            d’identité visuelle, ainsi que des designs personnalisés pour vos supports. Des packs prêts
                            à l’emploi sont disponibles, ou nous pouvons créer des concepts uniques selon vos besoins.
                        </p>
                    </div>
                </div>
                <div class="accordeon-content">
                    <div class="accordeon-title">
                        <h3>Quels types de fichiers dois-je vous envoyer pour un rendu optimal ?</h3>
                        <div class="icon">
                            <img src="assets/img/fleche.svg" alt="" class="fleche">
                        </div>
                    </div>
                    <div class="accordeon-text">
                        <p>Préférez les fichiers prêts à imprimer : PDF haute résolution, TIFF ou JPEG pour les photos,
                            AI/EPS/PDF vectoriel pour logos et graphiques. Évitez les fichiers provenant des réseaux
                            sociaux (Facebook, Messenger, WhatsApp) car ils sont souvent compressés et en basse
                            définition.
                        </p>
                    </div>
                </div>
                <div class="accordeon-content">
                    <div class="accordeon-title">
                        <h3>Les impressions sont-elles durables et les couleurs fidèles aux fichiers ?</h3>
                        <div class="icon">
                            <img src="assets/img/fleche.svg" alt="" class="fleche">
                        </div>
                    </div>
                    <div class="accordeon-text">
                        <p>Oui, mais la durabilité et la fidélité des couleurs dépendent de la technologie choisie, du
                            type de support et de l’encre. Même des matériels identiques peuvent produire des rendus
                            légèrement différents selon ces critères.
                        </p>
                    </div>
                </div>
                <div class="accordeon-content">
                    <div class="accordeon-title">
                        <h3>Pourquoi les couleurs sur écran peuvent-elles différer de celles sur papier ?</h3>
                        <div class="icon">
                            <img src="assets/img/fleche.svg" alt="" class="fleche">
                        </div>
                    </div>
                    <div class="accordeon-text">
                        <p>Les écrans utilisent le mode RVB, tandis que l’impression utilise le CMJN. Le calibrage de
                            l’écran, le type de support et même l’appareil utilisé (ordinateur, tablette, smartphone)
                            influencent la différence.
                        </p>
                    </div>
                </div>
                <div class="accordeon-content">
                    <div class="accordeon-title">
                        <h3>Peut-on voir un échantillon avant la production complète ?</h3>
                        <div class="icon">
                            <img src="assets/img/fleche.svg" alt="" class="fleche">
                        </div>
                    </div>
                    <div class="accordeon-text">
                        <p>Oui, nous proposons des épreuves numériques (BAT) ou physiques, payantes mais remboursables
                            dès que la commande complète est passée.
                        </p>
                    </div>
                </div>
                <div class="accordeon-content">
                    <div class="accordeon-title">
                        <h3>Quels papiers, supports et finitions garantissent un rendu professionnel ?</h3>
                        <div class="icon">
                            <img src="assets/img/fleche.svg" alt="" class="fleche">
                        </div>
                    </div>
                    <div class="accordeon-text">
                        <p>Privilégiez des papiers de grammage élevé et des finitions adaptées au projet (pelliculage,
                            vernis sélectif, textures pour invitations ou certificats, tissu adhésif pour effets
                            muraux).
                        </p>
                    </div>
                </div>
                <div class="accordeon-content">
                    <div class="accordeon-title">
                        <h3>Quels sont les délais moyens et proposez-vous des impressions express ?</h3>
                        <div class="icon">
                            <img src="assets/img/fleche.svg" alt="" class="fleche">
                        </div>
                    </div>
                    <div class="accordeon-text">
                        <p>Les délais varient de 24h à 5 jours selon la complexité et la quantité. Certaines impressions
                            rapides (cartes de visite, badges, autocollants) peuvent être prêtes en 30 minutes à la
                            boutique.
                        </p>
                    </div>
                </div>
                <div class="accordeon-content">
                    <div class="accordeon-title">
                        <h3>Comment obtenir un devis et quels moyens de paiement acceptez-vous ?</h3>
                        <div class="icon">
                            <img src="assets/img/fleche.svg" alt="" class="fleche">
                        </div>
                    </div>
                    <div class="accordeon-text">
                        <p>Devis gratuit via site web, email ou en boutique. Paiement par cartes bancaires, virements,
                            chèques, espèces ou paiements mobiles (MVOLA, Airtel Money, Orange Money). Fournissez toutes
                            les spécifications exactes pour obtenir l’offre la plus précise.
                        </p>
                    </div>
                </div>
                <div class="accordeon-content">
                    <div class="accordeon-title">
                        <h3>Pourquoi vos tarifs sont-ils dégressifs selon la quantité ?</h3>
                        <div class="icon">
                            <img src="assets/img/fleche.svg" alt="" class="fleche">
                        </div>
                    </div>
                    <div class="accordeon-text">
                        <p>Les coûts fixes (préparation, tests de couleurs, calage) sont répartis sur un plus grand
                            nombre d’exemplaires. Même si le temps d’exécution est similaire, le coût unitaire diminue
                            pour les grandes quantités.
                        </p>
                    </div>
                </div>
                <div class="accordeon-content">
                    <div class="accordeon-title">
                        <h3>Pourquoi modifiez-vous parfois notre fichier avant impression et cela affecte-t-il le design
                            ?</h3>
                        <div class="icon">
                            <img src="assets/img/fleche.svg" alt="" class="fleche">
                        </div>
                    </div>
                    <div class="accordeon-text">
                        <p>Des ajustements techniques (fonds perdus, conversion RVB → CMJN, polices, redimensionnement
                            proportionnel) peuvent être nécessaires, sans altérer le design original, pour garantir un
                            rendu parfait.
                        </p>
                    </div>
                </div>
                <div class="accordeon-content">
                    <div class="accordeon-title">
                        <h3>Qu’est-ce qu’un fichier basse définition et quelles en sont les causes ?</h3>
                        <div class="icon">
                            <img src="assets/img/fleche.svg" alt="" class="fleche">
                        </div>
                    </div>
                    <div class="accordeon-text">
                        <p>Un fichier basse définition a une résolution insuffisante (300 dpi), ce qui entraîne un
                            rendu pixellisé. Cela peut venir d’images web, de redimensionnements excessifs ou d’un
                            mauvais paramétrage à l’export. </p>
                    </div>
                </div>
                <div class="accordeon-content">
                    <div class="accordeon-title">
                        <h3>Pourquoi certaines impressions ou finitions ne sont pas possibles pour de petites quantités
                            ?</h3>
                        <div class="icon">
                            <img src="assets/img/fleche.svg" alt="" class="fleche">
                        </div>
                    </div>
                    <div class="accordeon-text">
                        <p>Certains procédés industriels nécessitent un minimum de production pour être rentables,
                            expliquant également les tarifs dégressifs.</p>
                    </div>
                </div>
                <div class="accordeon-content">
                    <div class="accordeon-title">
                        <h3>Combien de concepts initiaux proposez-vous et comment se déroulent les révisions ?</h3>
                        <div class="icon">
                            <img src="assets/img/fleche.svg" alt="" class="fleche">
                        </div>
                    </div>
                    <div class="accordeon-text">
                        <p>Généralement 2 à 3 concepts initiaux, suivis de 1 ou 2 séries de révisions. Si vos
                            recommandations sont claires dès le départ, une seule correction peut suffire.</p>
                    </div>
                </div>
                <div class="accordeon-content">
                    <div class="accordeon-title">
                        <h3>Proposez-vous un contrôle qualité et un support post-lancement ?</h3>
                        <div class="icon">
                            <img src="assets/img/fleche.svg" alt="" class="fleche">
                        </div>
                    </div>
                    <div class="accordeon-text">
                        <p>Oui, chaque commande est vérifiée avant livraison et nous assurons un support pour tout
                            ajustement ou réclamation après livraison.</p>
                    </div>
                </div>
                <div class="accordeon-content">
                    <div class="accordeon-title">
                        <h3>Comment préparer mon fichier pour qu’il soit compatible avec vos supports et machines ?</h3>
                        <div class="icon">
                            <img src="assets/img/fleche.svg" alt="" class="fleche">
                        </div>
                    </div>
                    <div class="accordeon-text">
                        <p>Format PDF ou TIFF, 300 dpi minimum, mode CMJN, fonds perdus 2-3 mm, polices vectorisées ou
                            incluses..</p>
                    </div>
                </div>
                <div class="accordeon-content">
                    <div class="accordeon-title">
                        <h3>Que faire si j’ai besoin de conseils pour améliorer mon design avant impression ?</h3>
                        <div class="icon">
                            <img src="assets/img/fleche.svg" alt="" class="fleche">
                        </div>
                    </div>
                    <div class="accordeon-text">
                        <p>Contactez notre service client ou notre studio graphique. Nos experts peuvent vous guider sur
                            la mise en page, les couleurs, la typographie et le rendu final..</p>
                    </div>
                </div>
                <div class="accordeon-content">
                    <div class="accordeon-title">
                        <h3>Qui détient les droits d’auteur et d’exploitation après paiement ?</h3>
                        <div class="icon">
                            <img src="assets/img/fleche.svg" alt="" class="fleche">
                        </div>
                    </div>
                    <div class="accordeon-text">
                        <p>Après paiement complet, les droits d’exploitation sont transférés au client. Les termes
                            exacts doivent être vérifiés dans le contrat ; le graphiste peut conserver certains droits
                            moraux.</p>
                    </div>
                </div>
                <div class="accordeon-content">
                    <div class="accordeon-title">
                        <h3>Pouvez-vous fournir des conseils ou maquettes pour choisir le meilleur support et finition ?
                        </h3>
                        <div class="icon">
                            <img src="assets/img/fleche.svg" alt="" class="fleche">
                        </div>
                    </div>
                    <div class="accordeon-text">
                        <p>Oui, nous proposons conseils personnalisés et échantillons pour visualiser et choisir la
                            meilleure option (papier, finition, support) pour votre projet.</p>
                    </div>
                </div>
                <div class="accordeon-content">
                    <div class="accordeon-title">
                        <h3>Offrez-vous un suivi ou assistance si des ajustements sont nécessaires après livraison ?
                        </h3>
                        <div class="icon">
                            <img src="assets/img/fleche.svg" alt="" class="fleche">
                        </div>
                    </div>
                    <div class="accordeon-text">
                        <p>Oui, nous accompagnons le client pour tout problème de livraison ou non-conformité. Si
                            l’erreur vient de notre côté, nous réimprimons gratuitement. Si elle vient du client, nous
                            proposons une remise sur la réimpression. En cas d’erreurs partagées, nous partageons le
                            coût à 50/50..</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--CONSEILS-->
    <section id="conseils">
        <div class="wrapper">
            <div class="section-title">
                <h2>Nos Derniers Conseils</h2>
            </div>
            <div class="inner-title">
                <p>Retrouvez nos actualités, astuces et inspirations pour une communication réussie.</p>
            </div>
            <div class="container-conseils">
                <?php $articles = $pdo->query("SELECT * FROM blog ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC); ?>
                <?php foreach ($articles as $a): ?>
                    <div class="card-conseil">

                        <!-- Lien vers single post -->
                        <a href="single-blog.php?id=<?= $a['id'] ?>" class="link-box"></a>

                        <div class="conseil-img">
                            <img src="ans-design-backoffice/upload/<?= $a['image'] ?>" alt="">
                        </div>

                        <div class="conseil-text">
                            <h3><?= htmlspecialchars($a['titre']) ?></h3>

                            <!-- Extrait -->
                            <p><?= htmlspecialchars(substr($a['extrait'], 0, 200)) ?>...</p>

                            <a href="single-blog.php?id=<?= $a['id'] ?>">Lire la suite →</a>
                        </div>

                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </section>
    <!-- FORMULAIRE -->
    <section id="formulaire">
        <div class="wrapper">
            <div class="formulaire-text">
                <div class="container-bouton">
                    <div class="section-title">
                        <h2>Prêt à lancer
                            Votre projet ?</h2>
                    </div>
                    <div class="bouton_connexion">
                        <a href="connexion.php" class="btn">Connexion</a>
                        <a href="connexion.php" class="btn-white">Inscription</a>
                    </div>
                </div>
                <div class="inner-title">
                    <p>Contactez-nous pour discuter de vos idées ou pour toute demande d'information. Notre
                        équipe est là pour vous accompagner.</p>
                </div>
                <form action="" method="post">
                    <input type="text" placeholder="Votre nom.." name="nom">
                    <input type="text" placeholder="Votre prénom ..." name="prenom">
                    <textarea name="" id="" placeholder="Votre message ..."></textarea>
                    <button type="submit" class="btn-card">Envoyez</button>
                </form>
            </div>
            <div class="formulaire-img">
                <img src="assets/img/banner_mon_compte.png" alt="">
            </div>
        </div>
    </section>
</main>
<!-- FOOTER -->
<?php
// On inclut le footer
include 'footer.php';
?>