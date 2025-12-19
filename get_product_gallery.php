<?php
require_once 'ans-design-backoffice/config/db.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode([]);
    exit;
}

$produit_id = (int) $_GET['id'];

$stmt = $pdo->prepare("
    SELECT image_path
    FROM produit_images
    WHERE produit_id = ?
    ORDER BY id ASC
");
$stmt->execute([$produit_id]);

$images = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($images);
