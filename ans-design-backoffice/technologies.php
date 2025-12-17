<?php
$pageTitle = 'Technologies';
$currentPage = 'technologies';
require_once 'includes/header.php';
require_once 'config/db.php';

// Suppression d'une technologie si demandé
if (isset($_GET['delete'])) {
    $idDelete = intval($_GET['delete']);

    // On récupère le nom des images pour supprimer les fichiers si existants
    $stmtImg = $pdo->prepare("SELECT image, image_technologie FROM technologies WHERE id = ?");
    $stmtImg->execute([$idDelete]);
    $techImages = $stmtImg->fetch(PDO::FETCH_ASSOC);

    if ($techImages['image'] && file_exists(__DIR__ . '/../uploads/technologies/' . $techImages['image'])) {
        unlink(__DIR__ . '/../uploads/technologies/' . $techImages['image']);
    }

    if ($techImages['image_technologie'] && file_exists(__DIR__ . '/../uploads/technologies/' . $techImages['image_technologie'])) {
        unlink(__DIR__ . '/../uploads/technologies/' . $techImages['image_technologie']);
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
        style="padding: 10px 20px; background-color: #2ECC71; color: white; text-decoration: none; border-radius: 5px; font-size: 16px;">
        + Ajouter une Technologie
    </a>
</div>

<?php if (isset($_GET['deleted'])): ?>
    <p class="success">Technologie supprimée avec succès !</p>
<?php endif; ?>

<div class="panel">
    <table style="width:100%; border-collapse: collapse; margin-top:20px;">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Icone</th>
                <th>Image de la technologie</th>
                <th>Actif</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($technologies)): ?>
                <tr>
                    <td colspan="5" style="text-align:center;">Aucune technologie trouvée.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($technologies as $tech): ?>
                    <tr>
                        <td><?= htmlspecialchars($tech['nom']) ?></td>
                        <td>
                            <?php if (!empty($tech['image'])): ?>
                                <img src="../uploads/technologies/<?= htmlspecialchars($tech['image']) ?>" width="80" alt="">
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($tech['image_technologie'])): ?>
                                <img src="../uploads/technologies/<?= htmlspecialchars($tech['image_technologie']) ?>" width="80"
                                    alt="">
                            <?php endif; ?>
                        </td>
                        <td><?= $tech['actif'] ? 'Oui' : 'Non' ?></td>
                        <td style="display: flex; gap: 10px;">
                            <a href="technologies_form.php?id=<?= $tech['id'] ?>"
                                style="padding: 5px 10px; background-color: #2ECC71; color: white; text-decoration: none; border-radius: 5px; font-size: 14px;">
                                Modifier
                            </a>
                            <a href="technologies.php?delete=<?= $tech['id'] ?>"
                                onclick="return confirm('Voulez-vous vraiment supprimer cette technologie ?');"
                                style="padding: 5px 10px; background-color: #DF4D34; color: white; text-decoration: none; border-radius: 5px; font-size: 14px;">
                                Supprimer
                            </a>
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