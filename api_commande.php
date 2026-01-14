<?php
// api_commande.php

ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json');

// 1. INIT
if (file_exists('init_user.php')) {
    require_once 'init_user.php';
} else {
    echo json_encode(['success' => false, 'message' => 'Fichier init_user.php introuvable']);
    exit;
}

// 2. SÉCURITÉ
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de commande manquant']);
    exit;
}

$commande_id = (int) $_GET['id'];
$client_id = (int) $_SESSION['user_id'];

try {
    // 3. COMMANDE
    $stmt = $pdo->prepare("
        SELECT *
        FROM commandes
        WHERE id = ? AND client_id = ?
    ");
    $stmt->execute([$commande_id, $client_id]);
    $commande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$commande) {
        echo json_encode(['success' => false, 'message' => 'Commande introuvable']);
        exit;
    }

    // 4. ARTICLES
    $stmt_art = $pdo->prepare("
        SELECT *
        FROM commande_articles
        WHERE commande_id = ?
    ");
    $stmt_art->execute([$commande_id]);
    $articles = $stmt_art->fetchAll(PDO::FETCH_ASSOC);

    // 5. FORMATAGE
    $ref_commande = $commande['numero_commande'] ?? $commande['id'];

    echo json_encode([
        'success' => true,
        'commande' => [
            'id' => $commande['id'], // <-- ajouter cette ligne
            'numero' => $ref_commande,
            'date' => date('d/m/Y', strtotime($commande['date_commande'])),
            'statut' => $commande['statut'],

            'total' => number_format($commande['total_ttc'], 0, ',', ' '),
            'tva' => number_format($commande['total_ttc'] * 0.2, 0, ',', ' '),
            'sous_total' => number_format($commande['total_ttc'] * 0.8, 0, ',', ' '),

            // LIVRAISON
            'livraison_nom' => $user_info['nom_complet'] ?? 'Client',
            'adresse_livraison' => $commande['adresse_livraison'] ?? '',
            'code_postal' => $commande['code_postal'] ?? ''
        ],
        'articles' => $articles
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur SQL : ' . $e->getMessage()
    ]);
}
