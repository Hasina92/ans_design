<?php
// enregistrer_commande.php

session_start();
require_once '../ans-design-backoffice/config/db.php'; // Adaptez le chemin
require_once 'init_user.php'; 

// 1. Vérifier si le client est connecté et si le panier n'est pas vide
if (!isset($_SESSION['client_id']) || empty($_SESSION['panier'])) {
    header('Location: connexion.php');
    exit();
}

// 2. Vérifier que le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: validation-commande.php');
    exit();
}

$pdo->beginTransaction();

try {
    // 3. Calculer le total et vérifier si c'est "Sur Devis"
    $total_ttc = 0;
    $est_sur_devis = false;
    foreach ($_SESSION['panier'] as $article) {
        if ($article['prix_base'] == 0) {
            $est_sur_devis = true;
            break; // Pas besoin de calculer plus loin, le total sera 0
        }
        $total_ttc += $article['prix_base'] * $article['quantite'];
    }

    // Ajouter les frais de livraison si ce n'est pas sur devis
    if (!$est_sur_devis) {
        $total_ttc += 10000; // Frais de livraison
    }

    // Si c'est sur devis, le total stocké est 0
    if ($est_sur_devis) {
        $total_ttc = 0;
    }

    // 4. Insérer dans VOTRE table `commandes`
    $client_id = $_SESSION['client_id'];
    $statut_initial = 'En attente'; // Ou 'En attente de validation', comme vous préférez

    $stmt_commande = $pdo->prepare(
        "INSERT INTO commandes (client_id, statut, total_ttc) VALUES (?, ?, ?)"
    );
    $stmt_commande->execute([$client_id, $statut_initial, $total_ttc]);
    
    $commande_id = $pdo->lastInsertId();

    // 5. Insérer chaque article dans VOTRE table `commande_articles`
    $stmt_article = $pdo->prepare(
        "INSERT INTO commande_articles (commande_id, description, quantite) VALUES (?, ?, ?)"
    );

    foreach ($_SESSION['panier'] as $article) {
        // On construit la chaîne de description à partir du nom du produit et de ses options
        $description = $article['nom'];
        if (!empty($article['options']) && is_array($article['options'])) {
            $details_options = [];
            foreach ($article['options'] as $nom_opt => $val_opt) {
                $details_options[] = "$nom_opt: $val_opt";
            }
            $description .= ' (' . implode(', ', $details_options) . ')';
        }

        $stmt_article->execute([$commande_id, $description, $article['quantite']]);
    }

    // 6. Si tout va bien, on valide la transaction
    $pdo->commit();

    // 7. Vider le panier et rediriger
    unset($_SESSION['panier']);
    // On utilise l'ID de la commande comme référence
    header('Location: commande-succes.php?id=' . $commande_id);
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    // Affichez un message d'erreur plus convivial pour l'utilisateur
    // et logguez l'erreur technique pour vous.
    error_log($e->getMessage()); // Écrit l'erreur dans les logs du serveur
    die("Une erreur est survenue lors de la finalisation de votre commande. Veuillez réessayer.");
}