<?php
$pageTitle = 'Catégories';
$currentPage = 'categories_produits';
require_once 'includes/header.php';
require_once 'config/db.php';

// Récupérer les catégories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY nom ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h1>Gestion des Catégories</h1>

    <div>
        <a href="categorie_form.php"
            style="padding:10px 15px; background:#27ae60; color:white; border-radius:5px; text-decoration:none;">
            + Ajouter une catégorie
        </a>
    </div>
</div>
<div class="panel">
    <table border="1" cellspacing="0" cellpadding="10" width="100%">
        <tr>
            <th>Nom</th>
            <th>Slug</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?= htmlspecialchars($cat['nom']) ?></td>
                <td><?= htmlspecialchars($cat['slug']) ?></td>
                <td style="display: flex; gap: 20px; border: none; width: 100px;">
                    <a href="categorie_form.php?id=<?= $cat['id'] ?>"
                        style="padding: 10px 20px; background-color: #2ECC71; color: white; text-decoration: none; border-radius: 5px; border: none; font-size: 16px;">Modifier</a>
                    <a href="categorie_delete.php?id=<?= $cat['id'] ?>"
                        onclick="return confirm('Supprimer cette catégorie ?')"
                        style="padding: 10px 20px; background-color: #DF4D34; color: white; text-decoration: none; border-radius: 5px; border: none; font-size: 16px;">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php require_once 'includes/footer.php'; ?>