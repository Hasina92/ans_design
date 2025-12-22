<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

// Sécurité
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de commande manquant']);
    exit;
}

$commande_id = (int) $_GET['id'];

try {
    // 🔹 REQUÊTE PRINCIPALE (SANS GROUP BY)
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
            c.numero_commande,
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
    ");

    $stmt->execute([':id' => $commande_id]);
    $details = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$details) {
        http_response_code(404);
        echo json_encode(['error' => 'Commande non trouvée']);
        exit;
    }

    // 🔹 ARTICLES
    $stmt_articles = $pdo->prepare("
        SELECT id, description, quantite, prix_unitaire
        FROM commande_articles
        WHERE commande_id = :id
    ");
    $stmt_articles->execute([':id' => $commande_id]);
    $articles_list = $stmt_articles->fetchAll(PDO::FETCH_ASSOC);

    // 🔹 OPTIONS DES ARTICLES
    foreach ($articles_list as &$article) {
        $stmt_options = $pdo->prepare("
            SELECT caracteristique_nom, valeur_choisie
            FROM commande_article_options
            WHERE article_id = :aid
        ");
        $stmt_options->execute([':aid' => $article['id']]);
        $article['options'] = $stmt_options->fetchAll(PDO::FETCH_ASSOC);
    }

    $details['articles_detailed_list'] = $articles_list;
    $details['publier_avis'] = (bool) $details['publier_avis'];

    echo json_encode($details);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur base de données',
        'details' => $e->getMessage() // enlève ceci en production
    ]);
}
