<?php
// Doit être la TOUTE première ligne du fichier, avant tout HTML
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'init_user.php';

// --- 1. Logique PHP pour définir les variables du header ---

// Par défaut, pour un visiteur non connecté
$lien_compte = 'connexion.php';
$texte_compte = 'Connexion / Inscription';

// Si la session de l'utilisateur existe (il est connecté)
if (isset($_SESSION['user_id'])) {

    // Le texte du lien devient "Mon Compte"
    $texte_compte = 'Mon Compte';

    // On détermine le bon lien en fonction de son rôle
    if ($_SESSION['role'] === 'admin') {
        $lien_compte = 'ans-design-backoffice/dashboard.php'; // Lien pour l'administrateur
    } else {
        $lien_compte = 'mon-compte.php'; // Lien pour le client
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="assets/css/fancybox.min.css">
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>ANS Design</title>
    <link rel="icon" href="assets/img/favicon.png" type="image/x-icon">

    <!-- Optionnel mais recommandé -->
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon.png">

</head>

<body>
    <!-- HEADER -->
    <header>
        <div class="wrapper">
            <a href="index.php" class="custom-logo-link logo"><img src="assets/img/logo.svg" alt="logo"></a>
            <nav class="nav-menu">
                <ul id="menu">
                    <li class="<?= ($page == 'accueil') ? 'current-menu-item' : '' ?>">
                        <a href="index.php">Accueil</a>
                    </li>

                    <li class="<?= ($page == 'catalogue') ? 'current-menu-item' : '' ?>">
                        <a href="catalogue.php">Catalogue</a>
                    </li>

                    <li class="<?= ($page == 'portfolio') ? 'current-menu-item' : '' ?>">
                        <a href="portfolio.php">Portfolio</a>
                    </li>

                    <li class="<?= ($page == 'blog') ? 'current-menu-item' : '' ?>">
                        <a href="blog.php">Blog</a>
                    </li>
                </ul>

            </nav>

            <div class="menu-social">
                <!-- Lien principal (visible sur grand écran) -->
                <a href="<?php echo htmlspecialchars($lien_compte); ?>" class="my-account">
                    <img src="assets/img/icone_my_account.svg" alt="">
                    <?php echo htmlspecialchars($texte_compte); ?>
                </a>

                <div class="notification">
                    <!-- Search -->
                    <img src="assets/img/search.svg" alt="search" id="search-icon" style="cursor:pointer; width:24px;">
                    <!-- Icône de compte (visible sur mobile) -->
                    <a href="<?php echo htmlspecialchars($lien_compte); ?>" class="my-account" id="my-account">
                        <img src="assets/img/icone_my_account.svg" alt="">
                    </a>

                    <!-- On affiche les icônes coeur et panier seulement si l'utilisateur est connecté -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="#mini-cart" class="open-button"><img src="assets/img/pannier-header-icon.svg"
                                alt="Panier"></a>
                    <?php endif; ?>

                    <a href="#" id="burgerMenu">
                        <img src="assets/img/hamburger.svg" alt="" class="burgerIcon">
                        <img src="assets/img/close.svg" alt="" class="closeIcon">
                    </a>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] !== 'admin'): ?>
                        <a href="ans-design-backoffice/logout.php" class="logout-btn">
                            <img src="assets/img/log_out.svg" alt="Déconnexion" title="Se déconnecter" style="width:24px;">
                        </a>
                    <?php endif; ?>
                </div>
                <div class="search-wrapper">
                    <div class="search-catalogue" id="search-container">
                        <input type="text" id="search-produit" placeholder="Rechercher un produit...">
                        <div id="resultats-produit"></div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- OVERLAY -->
    <?php include 'cart_overlay.php'; ?>

    <!-- POP-UP DEVIS COMMMANDE -->
    <section class="popup pop-up-devis-commande">
        <div class="pop-up-container">
            <div class="header-devis-commande">
                <h3>
                    Demande de Devis Personnalisé
                </h3>
            </div>
            <div class="body-devis-commande">
                <form id="form-devis">
                    <input type="text" name="nom" placeholder="Votre nom complet" required>
                    <input type="email" name="email" placeholder="Votre e-mail" required>
                    <input type="text" name="telephone" placeholder="Votre téléphone">
                    <textarea name="message" placeholder="Décrivez votre projet…" required></textarea>

                    <button type="submit" class="btn-red">Envoyer ma demande</button>
                </form>

                <div id="msg-devis"></div>
            </div>
            <a href="#close" id="close-popup-devis-commande"><img src="assets/img/close.svg" alt=""></a>
        </div>
    </section>