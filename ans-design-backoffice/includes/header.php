<?php
// On s'assure que l'utilisateur est bien connecté
require_once 'auth_check.php';

// --- DÉBUT DES AJOUTS DYNAMIQUES ---

// 1. Inclure la connexion à la BDD, nécessaire pour les requêtes
require_once __DIR__ . '/../config/db.php';

// 2. Calculer le nombre de fichiers à valider
$stmt_validation = $pdo->prepare("SELECT COUNT(*) as count FROM commandes WHERE statut = 'En validation'");
$stmt_validation->execute();
$fichiers_a_valider_count = $stmt_validation->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// 3. Calculer le nombre d'avis clients à valider
$stmt_avis = $pdo->prepare("SELECT COUNT(*) as count FROM commandes WHERE statut = 'Avis à valider'");
$stmt_avis->execute();
$avis_a_valider_count = $stmt_avis->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// --- FIN DES AJOUTS DYNAMIQUES ---
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - A.N.S Design</title>
    <link rel="stylesheet" href="../../style.css">
</head>

<body>
    <div class="main-wrapper">
        <aside class="sidebar">
            <div>
                <div class="sidebar-header">
                    <img src="../../assets/img/logo.svg" alt="">
                </div>
                <nav class="sidebar-nav">
                    <ul>
                        <li><a href="dashboard.php"
                                class="<?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">Accueil</a></li>
                        <li>
                            <a href="validation_fichiers.php"
                                class="<?php echo ($currentPage == 'validation') ? 'active' : ''; ?>">
                                Validation de commande
                                <?php // On affiche le badge seulement si le compteur est supérieur à zéro ?>
                                <?php if ($fichiers_a_valider_count > 0): ?>
                                    <span class="badge"><?php echo $fichiers_a_valider_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li>
                            <a href="clients_commandes.php"
                                class="<?php echo ($currentPage == 'clients') ? 'active' : ''; ?>">
                                Clients & commandes
                                <?php // On affiche le badge seulement si le compteur est supérieur à zéro ?>
                                <?php if ($avis_a_valider_count > 0): ?>
                                    <span class="badge"><?php echo $avis_a_valider_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li><a href="produits.php"
                                class="<?php echo ($currentPage == 'produits') ? 'active' : ''; ?>">Produits</a></li>
                        <li>
                            <a href="categories.php" class="<?= ($currentPage == 'categories') ? 'active' : ''; ?>">
                                Réalisations
                            </a>
                        </li>
                        <li>
                            <a href="equipe.php" class="<?= ($currentPage == 'equipe') ? 'active' : ''; ?>">
                                Equipe
                            </a>
                        </li>
                        <li>
                            <a href="blog.php" class="<?= ($currentPage == 'blog') ? 'active' : ''; ?>">
                                Blog
                            </a>
                        </li>
                        <li>
                            <a href="partenaires.php" class="<?= ($currentPage == 'partenaires') ? 'active' : ''; ?>">
                                Partenaires
                            </a>
                        </li>
                        <li>
                            <a href="prestations.php" class="<?= ($currentPage == 'prestations') ? 'active' : ''; ?>">
                                Prestations
                            </a>
                        </li>
                        <li>
                            <a href="categories_produits.php"
                                class="<?= ($currentPage == 'categories_produits') ? 'active' : ''; ?>">
                                Catégories Produits
                            </a>
                        </li>
                        <li>
                            <a href="technologies.php" class="<?= ($currentPage == 'technologies') ? 'active' : ''; ?>">
                                Technologies
                            </a>
                        </li>
                        <li>
                            <a href="admin_temoignages.php"
                                class="<?= ($currentPage == 'admin_temoignages') ? 'active' : ''; ?>">
                                Témoignages
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <div class="sidebar-footer">
                <a href="logout.php">Se déconnecter</a>
            </div>
        </aside>
        <main class="main-content">