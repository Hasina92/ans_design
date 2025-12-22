<?php
// --- AJOUTEZ CE BLOC AU DÉBUT DE VOTRE FICHIER ---
require_once 'ans-design-backoffice/config/db.php'; // Assurez-vous que le chemin est correct
require_once 'init_user.php';
session_start();

// Dans header.php ou au début de cart_overlay.php
if (isset($_SESSION['user_id'])) {
    $stmt_user_header = $pdo->prepare("SELECT nom, prenom FROM users WHERE id = ?");
    $stmt_user_header->execute([$_SESSION['user_id']]);
    $user_header = $stmt_user_header->fetch(PDO::FETCH_ASSOC);
    // On stocke dans $user_info['nom_complet'] pour que l'overlay l'affiche
    $user_info['nom_complet'] = trim(($user_header['prenom'] ?? '') . ' ' . ($user_header['nom'] ?? ''));
}

// 1. Valider l'ID du produit
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Rediriger vers le catalogue si l'ID est manquant ou invalide
    header('Location: catalogue.php');
    exit();
}
$produit_id = $_GET['id'];

try {
    // 2. Récupérer les informations du produit
    $stmt_produit = $pdo->prepare("SELECT * FROM produits WHERE id = ? AND actif = 1");
    $stmt_produit->execute([$produit_id]);
    $produit = $stmt_produit->fetch(PDO::FETCH_ASSOC);

    // Si le produit n'existe pas ou n'est pas actif, rediriger
    if (!$produit) {
        header('Location: catalogue.php');
        exit();
    }

    // 3. Récupérer les caractéristiques et leurs options
    $stmt_chars = $pdo->prepare("SELECT id, nom FROM produit_caracteristiques WHERE produit_id = ? ORDER BY ordre ASC");
    $stmt_chars->execute([$produit_id]);
    $caracteristiques = $stmt_chars->fetchAll(PDO::FETCH_ASSOC);

    // Pour chaque caractéristique, on va chercher ses options
    foreach ($caracteristiques as $key => $carac) {
        $stmt_opts = $pdo->prepare("SELECT valeur FROM caracteristique_options WHERE caracteristique_id = ?");
        $stmt_opts->execute([$carac['id']]);
        // On ajoute le tableau des options à notre tableau de caractéristiques
        $caracteristiques[$key]['options'] = $stmt_opts->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    die("Erreur lors de la récupération des détails du produit : " . $e->getMessage());
}
// --- FIN DU BLOC PHP ---
?>

<?php
// On inclut le header qui contient déjà session_start() et la logique d'auth
include 'header.php';
?>
<!-- MAIN -->
<main>
    <!-- ETAPE -->
    <section id="etape-page" class="scrolltop" data-produit-id="<?php echo $produit['id']; ?>"
        data-produit-nom="<?php echo htmlspecialchars($produit['nom']); ?>"
        data-produit-prix="<?php echo $produit['prix_base']; ?>">
        <div class="wrapper">
            <div class="title">
                <a href="catalogue.php#catalogue" class="nav-prev">
                    <img src="assets/img/nav-prev.svg" alt="">
                </a>
                <div class="section-title">
                    <h2>Configurer: <?php echo htmlspecialchars($produit['nom']); ?></h2>
                </div>
            </div>
            <div class="container-config-preview">
                <div class="config-tab">
                    <!-- MODIFIÉ : Génération dynamique des onglets de caractéristiques -->
                    <?php
                    $icons = [
                        'Dimensions' => 'dimensions.svg',
                        'Forme de découpe' => 'decoupe.svg',
                        'Type de papier' => 'papier.svg',
                        'Face' => 'face.svg',
                        'Quantité' => 'quantite.svg'
                    ];
                    ?>
                    <ul class="tabslink-etape">
                        <?php foreach ($caracteristiques as $index => $carac): ?>
                            <?php
                            // Définir le nom de l'icône
                            $iconName = isset($icons[$carac['nom']]) ? $icons[$carac['nom']] : 'dimensions.svg';
                            ?>

                            <li>
                                <a href="#etape_<?php echo $carac['id']; ?>">
                                    <img src="assets/img/<?php echo $iconName; ?>" alt="">
                                    <?php echo htmlspecialchars($carac['nom']); ?>
                                </a>
                            </li>

                        <?php endforeach; ?>
                        <li>
                            <a href="#etape_quantite">
                                <img src="assets/img/quantite.svg" alt="">
                                Quantité
                            </a>
                        </li>
                    </ul>

                    <!-- MODIFIÉ : Génération dynamique du contenu des onglets -->
                    <?php foreach ($caracteristiques as $carac): ?>
                        <div class="tabscontent-etape" id="etape_<?php echo $carac['id']; ?>"
                            data-caracteristique-nom="<?php echo htmlspecialchars($carac['nom']); ?>">
                            <?php if (empty($carac['options'])): ?>
                                <p>Aucune option disponible pour cette caractéristique.</p>
                            <?php else: ?>
                                <?php foreach ($carac['options'] as $option): ?>
                                    <div class="card-etape">
                                        <div class="card-img">
                                            <img src="assets/img/dimensions.svg" alt="">
                                        </div>
                                        <h3><?php echo htmlspecialchars($option['valeur']); ?></h3>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <!-- L'ID a été corrigé pour correspondre au lien dans l'onglet -->
                    <div class="tabscontent-etape quantite" id="etape_quantite">
                        <h3>Choisissez votre quantité</h3>
                        <label for="quantite">Entrez la quantité souhaitée</label>
                        <input type="number" name="quantite">
                    </div>
                    <div class="demande-spec">
                        <label for="demande">Votre fichier</label>
                        <input type="file" name="images[]" id="images" multiple accept="image/*">
                    </div>
                    <div class="demande-spec">
                        <label for="demande">Votre demande personnalisée</label>
                        <textarea name="demande" id="demande_text"
                            placeholder="Ex: Papier cartonné irisé 280g..."></textarea>
                    </div>
                </div>
                <div class="preview-tab">
                    <h3>Votre sélection</h3>
                    <div class="quote-preview-container">

                    </div>
                    <div class="preview-total">
                        <span>Total HT</span>
                        <span><?php echo number_format($produit['prix_base'], 0, ',', ' '); ?> AR</span>
                        <span>Sur Devis</span>
                    </div>
                    <a href="#ajout_panier" id="open-popup-ajout-panier" class="btn-yellow">
                        <img src="assets/img/cart.svg" alt="">
                        Ajouter au panier
                    </a>
                </div>
            </div>
        </div>
    </section>
    <!-- POP-UP AJOUT AU PANIER -->
    <section class="popup pop-up-ajout-panier">
        <div class="pop-up-container">
            <div class="header-ajout-panier">
                <img src="assets/img/check.svg" alt="">
                <h3>
                    Produit ajouté au panier !
                </h3>
            </div>
            <div class="body-ajout-panier">
                <a href="validation-commande.php" class="btn-red">Valider la commande</a>
                <a href="#mini-cart" class="btn open-button">Voir le panier</a>
            </div>
            <a href="#close" id="close-popup-ajout-panier"><img src="assets/img/close.svg" alt=""></a>
        </div>
    </section>
</main>
<!-- FOOTER -->
<?php
// On inclut le footer
include 'footer.php';
?>