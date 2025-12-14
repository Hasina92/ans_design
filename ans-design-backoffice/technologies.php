<?php
$pageTitle = 'Technologies';
$currentPage = 'technologies';
require_once 'includes/header.php';
require_once 'config/db.php';

// Suppression d'une technologie si demandé
if (isset($_GET['delete'])) {
    $idDelete = intval($_GET['delete']);

    // On récupère le nom de l'image pour supprimer le fichier si existant
    $stmtImg = $pdo->prepare("SELECT image FROM technologies WHERE id = ?");
    $stmtImg->execute([$idDelete]);
    $techImg = $stmtImg->fetchColumn();

    if ($techImg && file_exists(__DIR__ . '/../uploads/technologies/' . $techImg)) {
        unlink(__DIR__ . '/../uploads/technologies/' . $techImg);
    }

    $stmtDel = $pdo->prepare("DELETE FROM technologies WHERE id = ?");
    $stmtDel->execute([$idDelete]);

    header('Location: technologies.php?deleted=1');
    exit();
}

// Récupérer toutes les technologies
$stmt = $pdo->query("SELECT * FROM technologies ORDER BY ordre ASC, id DESC");
$technologies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h1>Technologies</h1>
    <a href="technologies_form.php" class="btn-green"
        style="padding: 10px 20px; background-color: #2ECC71; color: white; text-decoration: none; border-radius: 5px; border: none; font-size: 16px;">+
        Ajouter une
        Technologie</a>
</div>

<?php if (isset($_GET['deleted'])): ?>
    <p class="success">Technologie supprimée avec succès !</p>
<?php endif; ?>
<div class="panel">
    <table style="width:100%; border-collapse: collapse; margin-top:20px;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Actif</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($technologies)): ?>
                <tr>
                    <td colspan="6" style="text-align:center;">Aucune technologie trouvée.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($technologies as $tech): ?>
                    <tr>
                        <td><?= $tech['id'] ?></td>
                        <td><?= htmlspecialchars($tech['nom']) ?></td>
                        <td><?= $tech['actif'] ? 'Oui' : 'Non' ?></td>
                        <td style="display: flex; gap: 20px; border: none; width: 100px;">
                            <a href="technologies_form.php?id=<?= $tech['id'] ?>"
                                style="padding: 10px 20px; background-color: #2ECC71; color: white; text-decoration: none; border-radius: 5px; border: none; font-size: 16px;">Modifier</a>
                            <a href="technologies.php?delete=<?= $tech['id'] ?>"
                                onclick="return confirm('Voulez-vous vraiment supprimer cette technologie ?');"
                                style="padding: 10px 20px; background-color: #DF4D34; color: white; text-decoration: none; border-radius: 5px; border: none; font-size: 16px;">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
require_once 'includes/footer.php';
?>