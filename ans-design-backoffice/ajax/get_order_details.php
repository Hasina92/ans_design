<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de commande manquant']);
    exit();
}

$commande_id = (int) $_GET['id'];

// 1. REQUÊTE PRINCIPALE (Votre logique existante conservée)
// On garde le GROUP_CONCAT pour la compatibilité avec votre code existant (affichage résumé)
$stmt = $pdo->prepare("
    SELECT 
        c.id,
        c.statut,
        c.total_ttc,
        c.notes_client,
        c.avis_client,
        c.notes_production,
        c.notes_sav,
        c.publier_avis,
        c.methode_paiement,
        c.details_paiement,
        c.adresse_livraison,
        c.code_postal,
        c.ville,
        c.numero_commande, -- J'ai ajouté ceci au cas où
        u.nom,
        u.prenom,
        u.societe,
        GROUP_CONCAT(
            CONCAT(ca.description, ' (x', ca.quantite, ')')
            SEPARATOR ' • '
        ) AS articles_details
    FROM commandes c
    JOIN users u ON c.client_id = u.id
    LEFT JOIN commande_articles ca ON c.id = ca.commande_id
    WHERE c.id = :id
    GROUP BY c.id
");

$stmt->execute([':id' => $commande_id]);
$details = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$details) {
    http_response_code(404);
    echo json_encode(['error' => 'Commande non trouvée']);
    exit();
}

// 2. AJOUT : RÉCUPÉRATION DÉTAILLÉE DES ARTICLES ET OPTIONS
// C'est ce bloc qui permet d'afficher les caractéristiques dans le popup
try {
    // A. On récupère chaque article individuellement
    $stmt_articles = $pdo->prepare("
        SELECT id, description, quantite, prix_unitaire 
        FROM commande_articles 
        WHERE commande_id = :id
    ");
    $stmt_articles->execute([':id' => $commande_id]);
    $articles_list = $stmt_articles->fetchAll(PDO::FETCH_ASSOC);

    // B. Pour chaque article, on récupère ses options
    foreach ($articles_list as &$article) {
        $stmt_options = $pdo->prepare("
            SELECT caracteristique_nom, valeur_choisie 
            FROM commande_article_options 
            WHERE article_id = :aid
        ");
        $stmt_options->execute([':aid' => $article['id']]);
        $article['options'] = $stmt_options->fetchAll(PDO::FETCH_ASSOC);
    }

    // C. On ajoute cette liste détaillée à la réponse JSON principale
    $details['articles_detailed_list'] = $articles_list;

} catch (Exception $e) {
    // Si une erreur survient ici, on ne bloque pas tout, on renvoie une liste vide
    $details['articles_detailed_list'] = [];
}

// Formatage booléen (votre code existant)
$details['publier_avis'] = (bool) $details['publier_avis'];

// Envoi de la réponse JSON complète
echo json_encode($details);
?>