<?php
require_once 'ans-design-backoffice/config/db.php';

$q = $_GET['q'] ?? '';

if (strlen($q) < 1) {
    echo "";
    exit;
}

$search = "%" . $q . "%";

// Requête préparée avec des paramètres distincts
$stmt = $pdo->prepare("
    SELECT * FROM produits
    WHERE nom LIKE :s1
       OR description LIKE :s2
    LIMIT 20
");

$stmt->execute([
    's1' => $search,
    's2' => $search
]);

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$results) {
    echo "<div class='no-result'>Aucun produit trouvé</div>";
    exit;
}

foreach ($results as $p) {
    echo "
    <div class='result-produit-item' data-id='{$p['id']}'>
        <div>
            <strong>{$p['nom']}</strong>
        </div>
    </div>
    ";
}
