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

// Inclure le header
include 'header.php';
?>

<!-- MAIN -->
<main>
    <!-- BANNER -->
    <section id="banner" class="scrolltop">
        <div class="wrapper">
            <img src="assets/img/fond.png" alt="" class="bg-image">
            <div class="banner-text">
                <h1>#<span class="white">Vouloir la </span> différence</h1>
                <p class="medium white">Votre image mérite l’excellence. Avec A.N.S Design Print, chaque projet devient
                    une réussite sûre et professionnelle.</p>
                <div class="banner-btn">
                    <a href="catalogue.php" class="btn-gradient">Nos Services</a>
                    <a href="#devis" class="btn-white" id="open-popup-devis">Nos Prestations</a>
                </div>
            </div>
            <div class="banner-QR">
                <img src="assets/img/qr-code.png" alt="">
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
                <img src="assets/img/icone_stat.svg" alt="">
                <span class="nombre">10 000+ </span>
                <p class="small">Cartes de visite produites quotidiennement</p>
            </div>
            <div class="card-stat t-center">
                <img src="assets/img/icone_stat_2.svg" alt="">
                <span class="nombre">3 000+ </span>
                <p class="small">Projets réalisés avec succès et livrés</p>
            </div>
            <div class="card-stat t-center">
                <img src="assets/img/icone_stat_3.svg" alt="">
                <span class="nombre">8</span>
                <p class="small">Ans d’expertise au service de vos impressions</p>
            </div>
            <div class="card-stat t-center">
                <img src="assets/img/icone_stat_4.svg" alt="">
                <span class="nombre">2 500+</span>
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
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <img src="assets/img/star-<?= $i <= $t['note'] ? 'actif' : 'inactif' ?>.svg" alt="">
                            <?php endfor; ?>
                        </div>
                        <p class="avis"><?= nl2br(htmlspecialchars($t['avis'])) ?></p>
                        <span class="nom"><?= htmlspecialchars($t['prenom']) ?></span>
                        <span class="poste"><?= htmlspecialchars($t['poste']) ?></span>
                        <span class="poste"><?= htmlspecialchars($t['entreprise']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="slick-prev-custom"><img src="assets/img/arrow.svg" alt=""></button>
            <button class="slick-next-custom"><img src="assets/img/arrow.svg" alt=""></button>
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
                        <h3>PACK SANTATRA <br><b>Starter</b></h3>
                        <span class="prix">150 000 Ar</span>
                        <ul class="description-pack">
                            <li>Logo simple (1–2 propositions, 1 retouche)</li>
                            <li>Carte de visite</li>
                            <li>Signature email</li>
                            <li>Fichiers PNG/JPG</li>
                        </ul>
                    </div>
                    <div class="bouton-pack">
                        <a href="" class="btn-card">Devis</a>
                        <span class="reduction">-20%</span>
                    </div>
                </div>
                <div class="card-pack">
                    <div class="text-pack">
                        <h3>PACK SAHY<br><b>Booster / Startup</b></h3>
                        <span class="prix">350 000 Ar</span>
                        <ul class="description-pack">
                            <li>Logo premium (2–3 propositions, 2–3 retouches)</li>
                            <li>Mini charte graphique (couleurs, typographies, règles d’usage)</li>
                            <li>Kit réseaux sociaux : photo de profil + bannières + 2 templates modifiables</li>
                            <li>Flyer ou dépliant</li>
                            <li>Fichiers sources (.AI/.PSD)</li>
                        </ul>
                    </div>
                    <div class="bouton-pack">
                        <a href="" class="btn-card">Devis</a>
                        <span class="reduction">-20%</span>
                    </div>
                </div>
                <div class="card-pack">
                    <div class="text-pack">
                        <h3>PACK SIDINA<br><b>Business / PME</b></h3>
                        <span class="prix">1 200 000 Ar</span>
                        <ul class="description-pack">
                            <li>Logo complet + déclinaisons</li>
                            <li>Charte graphique détaillée</li>
                            <li>Papeterie complète : cartes, tête de lettre, enveloppes, chemises</li>
                            <li>Brochure commerciale (4 à 8 pages)</li>
                            <li>Roll-up ou X-Banner ou Panneaux</li>
                            <li>Kit web : favicon, bannières, boutons</li>
                        </ul>
                    </div>
                    <div class="bouton-pack">
                        <a href="" class="btn-card">Devis</a>
                        <span class="reduction">-20%</span>
                    </div>
                </div>
                <div class="card-pack">
                    <a href="" class="link-box"></a>
                    <div class="text-pack">
                        <h3>PACK SANGANY<br><b>Elite / Corporate</b></h3>
                        <span class="prix">3 500 000 Ar</span>
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
                    </div>
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
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <!-- STATISTIQUE 2 -->
    <section id="stats" class="stats-2">
        <div class="wrapper">
            <div class="card-stat t-center">
                <span class="nombre">98 %</span>
                <h3>Projets Livrés à Temps</h3>
                <p class="small">Priorité à votre planning : nous garantissons chaque échéance.
                </p>
            </div>
            <div class="card-stat t-center">
                <span class="nombre">50</span>
                <h3>Partenaires de Confiance</h3>
                <p class="small">Nos partenaires privilégient notre expertise pour une communication efficace.
                </p>
            </div>
            <div class="card-stat t-center">
                <span class="nombre">100 %</span>
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
    <?php
    $colors = ['#F5BF2A', '#DF4D34']; // rouge, jaune (tu peux ajouter plus de couleurs)
    foreach ($technologies as $index => $tech):
        $h3Color = $colors[$index % count($colors)]; // alterner les couleurs
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
            <svg id="timeline" class="separateur-historique" version="1.1" id="Calque_1"
                xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                viewBox="0 0 159.8 948.8" style="enable-background:new 0 0 159.8 948.8;" xml:space="preserve">
                <style type="text/css">
                    .st0 {
                        fill: none;
                        stroke: #E12948;
                        stroke-width: 2;
                    }

                    .st1 {
                        fill: #F5BF2A;
                    }

                    .st2 {
                        fill: #E12948;
                    }
                </style>
                <g id="Calque_2_00000149362559753874967320000007765458246492120224_">
                    <g id="Calque_1-2">
                        <path class="st0" d="M79.9,1C36.3,1,1,36.4,1,80c0,32.1,19.5,61,49.3,73.1c9.6,3.9,19.9,5.8,30.3,5.8v0
            c43.6,0.4,78.6,36,78.2,79.6c-0.3,38-27.7,70.3-65,76.9l-1.9,0.3l-1.9,0.2l-1.9,0.2l-1.9,0.1l-1.9,0.1c-0.7,0-1.3,0-1.9,0.1
            l-3.9,0.1l2.3-0.1c-10.4-0.1-20.7,1.9-30.3,5.8C9.9,338.7-9.5,384.7,6.9,425.1c12.1,29.7,40.9,49.2,73,49.2v0.1
            c43.6,0,78.9,35.4,78.9,79c0,32.1-19.5,61-49.3,73.1c-9.6,3.9-19.9,5.8-30.3,5.8l0,0C35.6,632.6,0.6,668.3,1,711.8
            c0.4,37.9,27.7,70.3,65,76.9l1.9,0.3l1.9,0.2l1.9,0.2l1.9,0.2l1.9,0.2c0.7,0,1.3,0,1.9,0.1l3.9,0.1l-2.3,0
            c10.4-0.1,20.7,1.9,30.3,5.8c40.4,16.4,59.8,62.4,43.5,102.8c-12.1,29.7-40.9,49.2-73,49.2" />
                    </g>
                </g>
                <circle class="st1" cx="79.9" cy="79.3" r="61.4" />
                <circle class="st2" cx="79.9" cy="236.1" r="61.4" />
                <circle class="st1" cx="79.9" cy="393" r="61.4" />
                <circle class="st2" cx="79.9" cy="549.8" r="61.4" />
                <circle class="st1" cx="79.9" cy="706.7" r="61.4" />
                <circle class="st2" cx="79.9" cy="863.5" r="61.4" />
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
                    <p>Annexe goodies, lancement de MisiTiako et modernisation de nos équipements.</p>
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
            <ul class="tabslink-processus">
                <li>
                    <a href="#ecoute"> <img src="assets/img/idea.svg" alt=""> <span>Écoute</span> </a>
                </li>
                <li>
                    <a href="#prix"> <img src="assets/img/price.svg" alt=""> <span>Prix</span> </a>
                </li>
                <li>
                    <a href="#validation"> <img src="assets/img/creation.svg" alt=""> <span>Validation</span> </a>
                </li>
                <li>
                    <a href="#verification"> <img src="assets/img/verification.svg" alt=""> <span>Vérification</span>
                    </a>
                </li>
                <li>
                    <a href="#fabrication"> <img src="assets/img/production.svg" alt=""> <span>Fabrication</span> </a>
                </li>
                <li>
                    <a href="#finition"> <img src="assets/img/livraison.svg" alt=""> <span>Finition</span></a>
                </li>
                <li>
                    <a href="#transport"> <img src="assets/img/livraison_3.svg" alt=""> <span>Transport</span></a>
                </li>
            </ul>
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
                <div class="section-title">
                    <h2>Prêt à lancer
                        Votre projet ?</h2>
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