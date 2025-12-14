<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Permet à n'importe quel site d'appeler cette API. Pour la production, vous devriez restreindre à votre nom de domaine.

require_once '../config/db.php';

// On sélectionne uniquement les produits actifs avec leurs infos de base
$stmt = $pdo->query("
    SELECT id, nom, description, prix_base 
    FROM produits 
    WHERE actif = 1 
    ORDER BY nom ASC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($products);
?>