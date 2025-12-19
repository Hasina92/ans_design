<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

// LOGIQUE: On récupère les données spécifiques au popup.
$commande_id = $data['commande_id'] ?? null;
$statut = $data['statut'] ?? null;
$avis_client = $data['avis_client'] ?? ''; // -> avis_client
$notes_sav = $data['notes_sav'] ?? ''; // -> notes_sav
$publier_avis = isset($data['publier_avis']) && $data['publier_avis'] ? 1 : 0;

if (!$commande_id || !$statut) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données invalides ou manquantes.']);
    exit();
}

try {
    // REQUÊTE: Met à jour les champs gérés par le popup.
    // Ne touche PAS à notes_production ou notes_client.
    $sql = "UPDATE commandes 
            SET 
                statut = ?, 
                avis_client = ?,
                notes_sav = ?,
                publier_avis = ?
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $statut,
        $avis_client,
        $notes_sav,
        $publier_avis,
        $commande_id
    ]);

    echo json_encode(['success' => true, 'message' => 'Commande mise à jour avec succès.']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données.']);
}
?>