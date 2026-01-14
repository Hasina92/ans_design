<?php
require_once 'init_user.php';
require_once 'ans-design-backoffice/dompdf/autoload.inc.php';

use Dompdf\Dompdf;

// Sécurité
if (!isset($_SESSION['user_id'], $_GET['id'])) {
    die('Accès refusé');
}

$commande_id = (int) $_GET['id'];
$client_id = $_SESSION['user_id'];

// Récupération commande
$stmt = $pdo->prepare("SELECT * FROM commandes WHERE id = ? AND client_id = ?");
$stmt->execute([$commande_id, $client_id]);
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$commande) {
    die('Commande introuvable');
}

// Récupération articles
$stmt_art = $pdo->prepare("SELECT * FROM commande_articles WHERE commande_id = ?");
$stmt_art->execute([$commande_id]);
$articles = $stmt_art->fetchAll(PDO::FETCH_ASSOC);

// Récupération adresse et code postal depuis la commande
$adresse = $commande['adresse_livraison'] ?? 'Non renseignée';
$code_postal = $commande['code_postal'] ?? 'N/A';

// HTML facture
ob_start();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h1 {
            text-align: center;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 6px;
        }

        .total {
            text-align: right;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <h1>Facture</h1>

    <p>
        <strong>Commande :</strong> #<?= $commande['numero_commande'] ?? $commande['id'] ?><br>
        <strong>Date :</strong> <?= date('d/m/Y', strtotime($commande['date_commande'])) ?><br>
        <strong>Client :</strong> <?= htmlspecialchars($user_info['nom_complet'] ?? 'Client') ?><br>
        <strong>Adresse :</strong> <?= htmlspecialchars($adresse) ?><br>
        <strong>Code postal :</strong> <?= htmlspecialchars($code_postal) ?>
    </p>

    <table class="table">
        <thead>
            <tr>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Prix (Ar)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $art): ?>
                <tr>
                    <td><?= htmlspecialchars($art['description'] ?? 'Produit') ?></td>
                    <td><?= $art['quantite'] ?? 1 ?></td>
                    <td><?= number_format($art['prix'] ?? 0, 0, ',', ' ') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total">
        <p>Sous-total : <?= number_format(($commande['total_ttc'] * 0.8) ?? 0, 0, ',', ' ') ?> Ar</p>
        <p>TVA (20%) : <?= number_format(($commande['total_ttc'] * 0.2) ?? 0, 0, ',', ' ') ?> Ar</p>
        <h3>Total : <?= number_format($commande['total_ttc'] ?? 0, 0, ',', ' ') ?> Ar</h3>
    </div>

</body>

</html>
<?php
$html = ob_get_clean();

// Génération PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Téléchargement PDF
$filename = 'facture_commande_' . $commande_id . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
exit;
