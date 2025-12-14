<?php
// api_commande.php

// Désactiver l'affichage des erreurs HTML pour ne pas casser le JSON
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

// 1. INCLURE VOTRE FICHIER D'INIT (Chemin à vérifier !)
// Si api_commande.php est à la racine, ceci doit fonctionner :
if (file_exists('init_user.php')) {
    require_once 'init_user.php';
} else {
    echo json_encode(['success' => false, 'message' => 'Fichier init_user.php introuvable']);
    exit;
}

// 2. VÉRIFICATIONS DE SÉCURITÉ
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de commande manquant']);
    exit;
}

$commande_id = $_GET['id'];
$client_id = $_SESSION['user_id'];

try {
    // 3. RÉCUPÉRER LA COMMANDE
    // Vérifiez bien que les colonnes 'id', 'client_id' existent dans votre table 'commandes'
    $stmt = $pdo->prepare("SELECT * FROM commandes WHERE id = ? AND client_id = ?");
    $stmt->execute([$commande_id, $client_id]);
    $commande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$commande) {
        echo json_encode(['success' => false, 'message' => 'Commande introuvable']);
        exit;
    }

    // 4. RÉCUPÉRER LES ARTICLES
    // Vérifiez que la table est bien 'commande_articles'
    $stmt_art = $pdo->prepare("SELECT * FROM commande_articles WHERE commande_id = ?");
    $stmt_art->execute([$commande_id]);
    $articles = $stmt_art->fetchAll(PDO::FETCH_ASSOC);

    // 5. PRÉPARER LA RÉPONSE
    // Adaptez 'numero_commande' si votre colonne s'appelle juste 'id'
    $ref_commande = isset($commande['numero_commande']) ? $commande['numero_commande'] : $commande['id'];

    echo json_encode([
        'success' => true,
        'commande' => [
            'numero' => $ref_commande,
            'date' => date('d/m/Y', strtotime($commande['date_commande'])),
            'statut' => $commande['statut'],
            'total' => number_format($commande['total_ttc'], 0, ',', ' '),
            'tva' => number_format($commande['total_ttc'] * 0.2, 0, ',', ' '), // Exemple TVA 20%
            'sous_total' => number_format($commande['total_ttc'] * 0.8, 0, ',', ' '),
            'livraison_nom' => $user_info['nom_complet'] ?? 'Client',
            // 'livraison_adresse' => ... (si vous avez l'adresse)
        ],
        'articles' => $articles
    ]);

} catch (Exception $e) {
    // En cas d'erreur SQL, on renvoie le message
    echo json_encode(['success' => false, 'message' => 'Erreur SQL: ' . $e->getMessage()]);
}
?>