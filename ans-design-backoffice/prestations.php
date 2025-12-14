<?php
$currentPage = 'prestations';
require_once 'config/db.php';
require_once 'includes/header.php';

$stmt = $pdo->query("SELECT * FROM demandes_devis ORDER BY date_creation DESC");
$demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h1>Demandes de devis</h1>

    <?php if (isset($_GET['update']) && $_GET['update'] == 'success'): ?>
        <p style="color: green; font-weight: bold;">
            ✔ La demande a été mise à jour avec succès.
        </p>
    <?php endif; ?>
</div>
<div class="panel">
    <table border="1" cellspacing="0" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Message</th>
            <th>Date</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($demandes as $d): ?>
            <tr>
                <td><?= $d['id'] ?></td>
                <td><?= htmlspecialchars($d['nom']) ?></td>
                <td><?= htmlspecialchars($d['email']) ?></td>
                <td><?= htmlspecialchars($d['telephone']) ?></td>
                <td><?= nl2br(htmlspecialchars($d['message'])) ?></td>
                <td><?= $d['date_creation'] ?></td>
                <td><?= $d['statut'] ?></td>
                <td>
                    <a href="traiter_devis.php?id=<?= $d['id'] ?>" class="btn"
                        style="padding: 10px 20px; background-color: #DF4D34; color: white; text-decoration: none; border-radius: 5px; border: none; font-size: 16px;">Traiter</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>