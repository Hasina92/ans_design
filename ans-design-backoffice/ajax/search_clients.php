<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$query = $_GET['query'] ?? '';

// On cherche dans la table `users` pour les utilisateurs avec le rôle 'client'
$searchTerm = '%' . $query . '%';
$stmt = $pdo->prepare("
    SELECT id, nom, prenom, societe 
    FROM users 
    WHERE role = 'client' 
    AND (nom LIKE ? OR prenom LIKE ? OR societe LIKE ?)
    ORDER BY nom, prenom
");
$stmt->execute([$searchTerm, $searchTerm, $searchTerm]);

$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($clients);
?>