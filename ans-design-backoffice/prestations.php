<?php
$currentPage = 'prestations';
require_once 'config/db.php';
require_once 'includes/header.php';

/* ============================
   SUPPRESSION D’UNE DEMANDE
   ============================ */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $stmt = $pdo->prepare("DELETE FROM demandes_devis WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: demandes_devis.php?delete=success");
    exit;
}

/* ============================
   LISTE DES DEMANDES
   ============================ */
$stmt = $pdo->query("SELECT * FROM demandes_devis ORDER BY date_creation DESC");
$demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h1>Demandes de devis</h1>

    <?php if (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
        <p style="color: green; font-weight: bold;">
            ✔ La demande a été mise à jour avec succès.
        </p>
    <?php endif; ?>

    <?php if (isset($_GET['delete']) && $_GET['delete'] === 'success'): ?>
        <p style="color: white; background:#E74C3C; padding:8px 15px; border-radius:5px;">
            ✔ Demande supprimée avec succès.
        </p>
    <?php endif; ?>
</div>

<div class="panel">
    <table border="1" cellspacing="0" cellpadding="8" width="100%">
        <tr>
            <th>Nom</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Message</th>
            <th>Date</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>

        <?php if (empty($demandes)): ?>
            <tr>
                <td colspan="7" style="text-align:center;">Aucune demande trouvée.</td>
            </tr>
        <?php endif; ?>

        <?php foreach ($demandes as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['nom']) ?></td>
                <td><?= htmlspecialchars($d['email']) ?></td>
                <td><?= htmlspecialchars($d['telephone']) ?></td>
                <td><?= nl2br(htmlspecialchars($d['message'])) ?></td>
                <td><?= htmlspecialchars($d['date_creation']) ?></td>
                <td><?= htmlspecialchars($d['statut']) ?></td>
                <td style="display:flex; gap:10px;">
                    <!-- BTN TRAITER -->
                    <a href="traiter_devis.php?id=<?= $d['id'] ?>"
                        style="padding: 8px 15px; background-color: #3498DB; color: white; border-radius: 5px; text-decoration: none;">
                        Traiter
                    </a>

                    <!-- BTN SUPPRIMER -->
                    <a href="demandes_devis.php?delete=<?= $d['id'] ?>"
                        onclick="return confirm('Voulez-vous vraiment supprimer cette demande ?');"
                        style="padding: 8px 15px; background-color: #E74C3C; color: white; border-radius: 5px; text-decoration: none;">
                        Supprimer
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>