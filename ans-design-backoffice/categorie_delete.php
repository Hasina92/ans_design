<?php
require_once 'config/db.php';
require_once 'includes/header.php';
if (!isset($_GET['id'])) {
    die("ID manquant.");
}

$id = intval($_GET['id']);

// Vérifier si la catégorie contient des produits
$stmt = $pdo->prepare("SELECT COUNT(*) FROM produits WHERE categorie_id = ?");
$stmt->execute([$id]);
$nbProduits = $stmt->fetchColumn();

if ($nbProduits > 0) {
    die("Impossible de supprimer : cette catégorie contient des produits.");
}

// Supprimer
$stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
$stmt->execute([$id]);

header("Location: categories.php?deleted=1");
exit;
