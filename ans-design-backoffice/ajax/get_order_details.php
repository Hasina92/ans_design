<?php
require_once __DIR__ . '/../config/db.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de commande manquant']);
    exit();
}

$commande_id = $_GET['id'];

// On joint la table 'users' et on inclut la colonne 'societe'
$stmt = $pdo->prepare("
    SELECT 
        c.id, c.statut, c.total_ttc, c.notes_client, c.avis_client, 
        c.notes_production, c.notes_sav, 
        c.publier_avis,
        u.nom, u.prenom, u.societe,
        GROUP_CONCAT(CONCAT(ca.description, ' (x', ca.quantite, ')') SEPARATOR ' • ') AS articles_details
    FROM commandes c
    JOIN users u ON c.client_id = u.id
    LEFT JOIN commande_articles ca ON c.id = ca.commande_id
    WHERE c.id = ?
    GROUP BY c.id
");
$stmt->execute([$commande_id]);
$details = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$details) {
    http_response_code(404);
    echo json_encode(['error' => 'Commande non trouvée']);
    exit();
}

header('Content-Type: application/json');
$details['publier_avis'] = (bool) $details['publier_avis'];
echo json_encode($details);
?>