<?php
require_once __DIR__ . '/init_user.php';
require_once __DIR__ . '/ans-design-backoffice/dompdf/autoload.inc.php';


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
        @font-face {
            font-family: 'Gilroy';
            src: url('fonts/Gilroy-Regular.ttf') format('truetype');
            font-weight: normal;
        }

        @font-face {
            font-family: 'Gilroy';
            src: url('fonts/Gilroy-Bold.ttf') format('truetype');
            font-weight: bold;
        }

        body {
            font-family: 'Gilroy', sans-serif;
            font-size: 12px;
            color: #333;
        }

        .header {
            width: 100%;
            margin-bottom: 30px;
        }

        .header-left {
            float: left;
        }

        .header-right {
            float: right;
            text-align: right;
        }

        .clear {
            clear: both;
        }

        h1 {
            margin: 0;
            font-size: 28px;
        }

        .box {
            border: 1px solid #ddd;
            padding: 10px;
            margin-top: 15px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        .table th {
            background: #f5f5f5;
            font-weight: bold;
            padding: 10px;
            border: 1px solid #ddd;
        }

        .table td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        .table td:last-child,
        .table th:last-child {
            text-align: right;
        }

        .totaux {
            width: 40%;
            float: right;
            margin-top: 20px;
        }

        .totaux table {
            width: 100%;
            border-collapse: collapse;
        }

        .totaux td {
            padding: 8px;
        }

        .totaux tr:last-child td {
            font-weight: bold;
            font-size: 14px;
            border-top: 2px solid #000;
        }

        .footer {
            position: fixed;
            bottom: 20px;
            text-align: center;
            font-size: 10px;
            color: #777;
            width: 100%;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="header-left">
            <h1>FACTURE</h1>
            <p>
                <strong>Commande :</strong> #<?= $commande['numero_commande'] ?? $commande['id'] ?><br>
                <strong>Date :</strong> <?= date('d/m/Y', strtotime($commande['date_commande'])) ?>
            </p>
        </div>

        <div class="header-right">
            <p>
                <strong><?= htmlspecialchars($user_info['nom_complet'] ?? 'Client') ?></strong><br>
                <?= htmlspecialchars($adresse) ?><br>
                <?= htmlspecialchars($code_postal) ?>
            </p>
        </div>
    </div>

    <div class="clear"></div>

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

    <div class="totaux">
        <table>
            <tr>
                <td>Sous-total</td>
                <td><?= number_format(($commande['total_ttc'] * 0.8) ?? 0, 0, ',', ' ') ?> Ar</td>
            </tr>
            <tr>
                <td>TVA (20%)</td>
                <td><?= number_format(($commande['total_ttc'] * 0.2) ?? 0, 0, ',', ' ') ?> Ar</td>
            </tr>
            <tr>
                <td>Total</td>
                <td><?= number_format($commande['total_ttc'] ?? 0, 0, ',', ' ') ?> Ar</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Merci pour votre confiance – Facture générée automatiquement
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
