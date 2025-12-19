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

    // --- 1. CALCULS (Inchangé) ---
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

    // Gestion des notes globales (ex: "Appelez-moi avant de livrer")
    // On récupère la note globale du formulaire validation
    $notes_production = isset($_POST['notes']) ? trim($_POST['notes']) : '';

    // (Note: La boucle sur 'demande_article' n'est plus strictement nécessaire ici 
    // car on enregistre désormais la demande spécifique DANS l'article via le JSON, 
    // mais on peut laisser votre logique si vous voulez une concaténation globale).
    if (!empty($_POST['demande_article'])) {
        foreach ($_POST['demande_article'] as $i => $note) {
            $note = trim($note);
            if ($note !== '') {
                $notes_production = isset($_POST['notes']) ? trim($_POST['notes']) : '';
            }
        }
    }

    // --- 2. INSÉRER LA COMMANDE (Inchangé) ---
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

    // --- 3. INSÉRER ARTICLES, FICHIERS ET OPTIONS (ADAPTÉ) ---
    
    // Préparation du dossier définitif pour les fichiers
    $finalDir = 'uploads/commandes/';
    if (!is_dir($finalDir)) {
        mkdir($finalDir, 0755, true);
    }

    foreach ($_SESSION['panier'] as $article) {
        
        // A. GESTION DES FICHIERS (Déplacement Temp -> Final)
        $cheminsDefinitifs = [];
        
        if (!empty($article['images'])) {
            foreach ($article['images'] as $cheminTemp) {
                if (file_exists($cheminTemp)) {
                    $nomFichier = basename($cheminTemp);
                    // On nettoie le nom (enlève 'temp_') et on ajoute l'ID commande
                    // Ex: cmd_55_178383_monlogo.jpg
                    $nomFinal = 'cmd_' . $commande_id . '_' . str_replace('temp_', '', $nomFichier);
                    $targetPath = $finalDir . $nomFinal;

                    // Déplacement physique du fichier
                    if (rename($cheminTemp, $targetPath)) {
                        $cheminsDefinitifs[] = $targetPath;
                    }
                }
            }
        }

        // B. CRÉATION DU JSON (Texte + Chemins fichiers)
        $donnees_perso = [
            'message_client' => $article['demande'] ?? '', // Le texte spécifique à l'article
            'fichiers'       => $cheminsDefinitifs         // Le tableau des nouveaux chemins
        ];
        
        // Encodage (JSON_UNESCAPED_UNICODE garde les accents lisibles)
        $json_perso = json_encode($donnees_perso, JSON_UNESCAPED_UNICODE);

        // C. INSERTION EN BASE (Avec la nouvelle colonne donnees_personnalisees)
        $stmt_article = $pdo->prepare(
            "INSERT INTO commande_articles (commande_id, description, quantite, prix_unitaire, donnees_personnalisees)
             VALUES (:commande_id, :description, :qte, :prix, :donnees_perso)"
        );
        
        $stmt_article->execute([
            ':commande_id' => $commande_id,
            ':description' => $article['nom'],
            ':qte' => $article['quantite'],
            ':prix' => $article['prix_base'],
            ':donnees_perso' => $json_perso // On insère le JSON ici
        ]);

        $article_id = $pdo->lastInsertId();

        // D. INSERTION DES OPTIONS (Inchangé)
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

    // --- 4. NETTOYAGE ET REDIRECTION (Inchangé) ---
    unset($_SESSION['panier']);
    $_SESSION['success_message'] = "Votre commande #$numero_commande a bien été enregistrée !";
    header('Location: mon-compte.php');
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Erreur lors de l'enregistrement de la commande : " . $e->getMessage());
}
?>