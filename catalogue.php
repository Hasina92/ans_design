<?php
// --- AJOUTEZ CE BLOC AU DÉBUT DE VOTRE FICHIER (après <head> ou avant <html>) ---
require_once 'ans-design-backoffice/config/db.php'; // Assurez-vous que le chemin est correct
require_once 'init_user.php';

// Récupérer tous les produits actifs pour les afficher dans le catalogue
try {
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE actif = 1 ORDER BY nom ASC");
    $stmt->execute();
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Erreur lors de la récupération des produits : " . $e->getMessage());
}
// --- FIN DU BLOC À AJOUTER ---

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
?>

<?php $page = 'catalogue'; ?>
<?php
// On inclut le header qui contient déjà session_start() et la logique d'auth
include 'header.php';
?>
<!-- MAIN -->
<main>
    <!-- BANNER -->
    <section id="banner-catalogue" class="scrolltop">
        <img src="assets/img/fond.png" alt="" class="bg-image">
        <div class="wrapper t-center">
            <h1><span>Donnez vie à vos</span> idées.</h1>
            <p class="medium white">De la carte de visite au stand d'exposition, nous sommes votre
                partenaire
                unique pour une
                impression de qualité et des services créatifs. </p>
            <div class="banner-btn">
                <a href="" class="btn-yellow">Voir tous les produits</a>
            </div>
        </div>
    </section>
    <!-- ETAPES -->
    <section id="etapes">
        <div class="wrapper">
            <div class="card-etapes t-center">
                <div class="container-img">
                    <img src="assets/img/configurez.svg" alt="">
                </div>
                <h3>Configurez</h3>
                <p>Choisissez votre produit et personnalisez chaque détail.</p>
            </div>
            <div class="card-etapes t-center">
                <div class="container-img">
                    <img src="assets/img/envoyez.svg" alt="">
                </div>
                <h3>Envoyez</h3>
                <p>Transférez vos fichiers en quelques clics.</p>
            </div>
            <div class="card-etapes t-center">
                <div class="container-img">
                    <img src="assets/img/recevez.svg" alt="">
                </div>
                <h3>Recevez</h3>
                <p>Nous imprimons et expédions votre commande.</p>
            </div>
        </div>
    </section>

    <!-- NOS PRODUITS PHARES -->
    <section id="produits-phares">
        <div class="wrapper">

            <div class="section-title">
                <h2>Nos Produits Phares</h2>
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

    <!-- CATALOGUE -->
    <section id="catalogue">
        <div class="wrapper">
            <div class="section-title">
                <h2>Catalogue complet</h2>
            </div>
            <div class="container-catalogue">
                <?php if (empty($produits)): ?>
                    <p>Aucun produit n'est disponible pour le moment.</p>
                <?php else: ?>
                    <?php foreach ($produits as $produit): ?>
                        <div class="card-catalogue">
                            <!-- MODIFIÉ : Le lien envoie maintenant vers etape.php avec l'ID du produit -->
                            <a href="etape.php?id=<?php echo $produit['id']; ?>" class="link-box"></a>

                            <div class="card-icon-catalogue">
                                <img src="uploads/produits/<?php echo $produit['image'] ?? 'default.png'; ?>"
                                    alt="<?php echo htmlspecialchars($produit['nom']); ?>">
                            </div>

                            <div class="card-text-catalogue t-center">
                                <!-- MODIFIÉ : Affiche le nom du produit depuis la BDD -->
                                <h3><?php echo htmlspecialchars($produit['nom']); ?></h3>
                                <a href="etape.php?id=<?php echo $produit['id']; ?>">Configurer et commander </a>
                            </div>
                            <div class="view-detail-popup open-popup-detail-catalogue" data-id="<?php echo $produit['id']; ?>"
                                data-nom="<?php echo htmlspecialchars($produit['nom']); ?>"
                                data-description="<?php echo htmlspecialchars($produit['description']); ?>"
                                data-image="uploads/produits/<?php echo $produit['image'] ?? 'default.png'; ?>">
                                <img src="assets/img/eye.svg" alt="">
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <!-- DETAILS CATALOGUE POP-UP -->
    <section class="popup pop-up-detail-catalogue">
        <div class="pop-up-container">
            <div class="header-detail-catalogue">
                <h3 class="popup-produit-nom">Nom du produit</h3>
                <img src="assets/img/close.svg" class="close-popup-detail-catalogue">
            </div>

            <div class="body-detail-catalogue">
                <div class="img-detail-catalogue">
                    <img src="" class="popup-produit-image">
                </div>

                <div class="text-detail-catalogue">
                    <p class="popup-produit-description"></p>

                    <!-- Le lien sera mis à jour : etape.php?id=X -->
                    <a href="" class="btn-card popup-produit-lien">Configurer ce produit</a>
                    <div class="gallery-product">
                        <img src="" alt="">
                        <img src="" alt="">
                        <img src="" alt="">
                    </div>
                </div>

            </div>
        </div>
    </section>
</main>
<!-- FOOTER -->
<?php
// On inclut le footer
include 'footer.php';
?>