<?php
session_start();
require_once 'ans-design-backoffice/config/db.php';
require_once 'init_user.php';

/* ------------------ SÉCURITÉ ------------------ */
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

/* ------------------ RÉCUPÉRATION VILLE ------------------ */
$ville = htmlspecialchars(trim($_POST['city'] ?? ''));

if (empty($ville)) {
    $_SESSION['error_message'] = "Veuillez sélectionner une ville.";
    header('Location: validation-commande.php');
    exit();
}

try {
    $pdo->beginTransaction();

    /* ------------------ CALCULS ------------------ */
    $total_commande = 0;
    $la_commande_est_sur_devis = false;
    $frais_livraison = 10000;

    foreach ($_SESSION['panier'] as $article) {
        if ($article['prix_base'] == 0) {
            $la_commande_est_sur_devis = true;
        }
        $total_commande += $article['prix_base'] * $article['quantite'];
    }

    if (!$la_commande_est_sur_devis) {
        $total_commande += $frais_livraison;
    } else {
        $total_commande = 0;
    }

    $numero_commande = strtoupper(uniqid('CMD-'));

    /* ------------------ PAIEMENT ------------------ */
    $details_paiement = '';
    if ($_POST['payment'] === 'mobile-money') {
        $operateur = $_POST['operateur'] ?? '';
        $numero = $_POST['numero-' . $operateur] ?? '';
        $details_paiement = "Opérateur: $operateur, Référence: $numero";
    }

    /* ------------------ NOTES ------------------ */
    $notes_production = trim($_POST['notes'] ?? '');

    /* ------------------ INSERT COMMANDE ------------------ */
    $stmt_commande = $pdo->prepare(
        "INSERT INTO commandes 
        (client_id, numero_commande, ville, total_ttc, notes_production, statut, methode_paiement, details_paiement)
        VALUES 
        (:client_id, :numero, :ville, :total, :notes, :statut, :paiement, :details)"
    );

    $stmt_commande->execute([
        ':client_id' => $_SESSION['user_id'],
        ':numero' => $numero_commande,
        ':ville' => $ville,
        ':total' => $total_commande,
        ':notes' => $notes_production,
        ':statut' => $la_commande_est_sur_devis ? 'En attente devis' : 'En validation',
        ':paiement' => $_POST['payment'],
        ':details' => $details_paiement
    ]);

    $commande_id = $pdo->lastInsertId();

    /* ------------------ DOSSIER FICHIERS ------------------ */
    $finalDir = 'uploads/commandes/';
    if (!is_dir($finalDir)) {
        mkdir($finalDir, 0755, true);
    }

    /* ------------------ ARTICLES ------------------ */
    foreach ($_SESSION['panier'] as $article) {

        /* FICHIERS */
        $cheminsDefinitifs = [];

        if (!empty($article['images'])) {
            foreach ($article['images'] as $cheminTemp) {
                if (file_exists($cheminTemp)) {
                    $nomFichier = basename($cheminTemp);
                    $nomFinal = 'cmd_' . $commande_id . '_' . str_replace('temp_', '', $nomFichier);
                    $targetPath = $finalDir . $nomFinal;

                    if (rename($cheminTemp, $targetPath)) {
                        $cheminsDefinitifs[] = $targetPath;
                    }
                }
            }
        }

        /* JSON PERSONNALISATION */
        $donnees_perso = [
            'message_client' => $article['demande'] ?? '',
            'fichiers' => $cheminsDefinitifs
        ];

        $json_perso = json_encode($donnees_perso, JSON_UNESCAPED_UNICODE);

        /* INSERT ARTICLE */
        $stmt_article = $pdo->prepare(
            "INSERT INTO commande_articles 
            (commande_id, description, quantite, prix_unitaire, donnees_personnalisees)
            VALUES 
            (:commande_id, :description, :qte, :prix, :donnees)"
        );

        $stmt_article->execute([
            ':commande_id' => $commande_id,
            ':description' => $article['nom'],
            ':qte' => $article['quantite'],
            ':prix' => $article['prix_base'],
            ':donnees' => $json_perso
        ]);

        $article_id = $pdo->lastInsertId();

        /* OPTIONS */
        if (!empty($article['options'])) {
            foreach ($article['options'] as $nom_option => $valeur_option) {
                $stmt_option = $pdo->prepare(
                    "INSERT INTO commande_article_options 
                    (article_id, caracteristique_nom, valeur_choisie)
                    VALUES 
                    (:article_id, :nom, :valeur)"
                );

                $stmt_option->execute([
                    ':article_id' => $article_id,
                    ':nom' => $nom_option,
                    ':valeur' => $valeur_option
                ]);
            }
        }
    }

    /* ------------------ FINAL ------------------ */
    $pdo->commit();

    unset($_SESSION['panier']);
    $_SESSION['success_message'] = "Votre commande #$numero_commande a bien été enregistrée.";

    header('Location: mon-compte.php');
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Erreur lors de l'enregistrement : " . $e->getMessage());
}
