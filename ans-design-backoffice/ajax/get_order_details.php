<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de commande manquant']);
    exit();
}

$commande_id = (int) $_GET['id'];

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

$details['publier_avis'] = (bool) $details['publier_avis'];

echo json_encode($details);
