<?php
session_start();
require_once 'ans-design-backoffice/config/db.php';
require_once 'init_user.php';

// --- SÉCURITÉ ET VALIDATION ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: catalogue.php');
    exit();
}
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Vous devez être connecté pour passer une commande.";
    header('Location: connexion.php');
    exit();
}
if (empty($_SESSION['panier'])) {
    header('Location: catalogue.php');
    exit();
}

try {
    $pdo->beginTransaction();

    // --- 1. CALCULS (inchangé) ---
    $total_commande = 0;
    $la_commande_est_sur_devis = false;
    $frais_livraison = 10000;

    foreach ($_SESSION['panier'] as $article) {
        if ($article['prix_base'] == 0)
            $la_commande_est_sur_devis = true;
        $total_commande += $article['prix_base'] * $article['quantite'];
    }

    if (!$la_commande_est_sur_devis) {
        $total_commande += $frais_livraison;
    } else {
        $total_commande = 0;
    }

    $numero_commande = strtoupper(uniqid('CMD-'));
    $details_paiement = '';
    if ($_POST['payment'] === 'mobile-money') {
        $operateur = $_POST['operateur'] ?? '';
        $numero = $_POST['numero-' . $operateur] ?? '';
        $details_paiement = "Opérateur: $operateur, Référence: $numero";
    }

    $notes_production = '';

    if (!empty($_POST['demande_article'])) {
        foreach ($_POST['demande_article'] as $i => $note) {
            $note = trim($note);
            if ($note !== '') {
                $notes_production .= "Article " . ($i + 1) . " : " . $note . "\n";
            }
        }
    }

    // --- 2. INSÉRER LA COMMANDE (VERSION SIMPLIFIÉE) ---
    // On insère uniquement les infos de la commande elle-même.
    // Le nom du client, son email etc. seront récupérés via la liaison client_id.
    $stmt_commande = $pdo->prepare(
        "INSERT INTO commandes (client_id, numero_commande, total_ttc, notes_production, statut, methode_paiement, details_paiement)
         VALUES (:client_id, :numero, :total, :notes, :statut, :paiement, :details)"
    );

    $stmt_commande->execute([
        ':client_id' => $_SESSION['user_id'],
        ':numero' => $numero_commande,
        ':total' => $total_commande,
        ':statut' => $la_commande_est_sur_devis ? 'En attente devis' : 'En validation',
        ':paiement' => $_POST['payment'],
        ':details' => $details_paiement,
        ':notes' => $notes_production,
    ]);

    $commande_id = $pdo->lastInsertId();

    // --- 3. INSÉRER ARTICLES ET OPTIONS (inchangé, c'était déjà correct) ---
    foreach ($_SESSION['panier'] as $article) {
        $stmt_article = $pdo->prepare(
            "INSERT INTO commande_articles (commande_id, description, quantite, prix_unitaire)
             VALUES (:commande_id, :description, :qte, :prix)"
        );
        $stmt_article->execute([
            ':commande_id' => $commande_id,
            ':description' => $article['nom'],
            ':qte' => $article['quantite'],
            ':prix' => $article['prix_base']
        ]);

        $article_id = $pdo->lastInsertId();

        if (!empty($article['options'])) {
            foreach ($article['options'] as $nom_option => $valeur_option) {
                $stmt_option = $pdo->prepare(
                    "INSERT INTO commande_article_options (article_id, caracteristique_nom, valeur_choisie)
                     VALUES (:article_id, :nom_carac, :valeur)"
                );
                $stmt_option->execute([':article_id' => $article_id, ':nom_carac' => $nom_option, ':valeur' => $valeur_option]);
            }
        }
    }

    $pdo->commit();

    // --- 4. NETTOYAGE ET REDIRECTION (inchangé) ---
    unset($_SESSION['panier']);
    $_SESSION['success_message'] = "Votre commande #$numero_commande a bien été enregistrée !";
    header('Location: mon-compte.php');
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Erreur lors de l'enregistrement de la commande : " . $e->getMessage());
}
?>