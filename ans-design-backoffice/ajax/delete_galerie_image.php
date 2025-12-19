<?php
require_once '../config/db.php';

$id = (int) $_POST['id'];

$stmt = $pdo->prepare("SELECT image_path FROM produit_images WHERE id = ?");
$stmt->execute([$id]);
$image = $stmt->fetchColumn();

if ($image) {
    @unlink(__DIR__ . '/../' . $image);

    $stmt = $pdo->prepare("DELETE FROM produit_images WHERE id = ?");
    $stmt->execute([$id]);
}

echo 'ok';
