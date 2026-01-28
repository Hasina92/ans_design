<?php
$currentPage = 'banner_edit';
require_once 'includes/header.php';
require_once 'config/db.php';
$stmt = $pdo->prepare("SELECT * FROM banner LIMIT 1");
$stmt->execute();
$banner = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$banner) {
    die("Aucune bannière trouvée.");
}
?>
<div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
    <h1>Modifier la bannière
    </h1>

    <?php if (!empty($message)): ?>
        <p style="color:green;">
            <?= $message ?>
        </p>
    <?php endif; ?>
</div>
<div class="panel">
    <form class="formulaire_ajout" action="banner_update.php" method="POST" enctype="multipart/form-data">

        <label>Titre Ligne 1</label>
        <input type="text" name="titre" value="<?= htmlspecialchars($banner['titre']) ?>">

        <label>Titre Ligne 2</label>
        <input type="text" name="titre_2" value="<?= htmlspecialchars($banner['titre_2']) ?>">

        <label>Sous-titre</label>
        <textarea name="sous_titre" rows="4"><?= htmlspecialchars($banner['sous_titre']) ?></textarea>

        <label>Image de fond</label>
        <input type="file" name="image_fond">
        <img src="../assets/img/<?php echo $banner['image_fond']; ?>">

        <label>Image bannière</label>
        <input type="file" name="image_qr">
        <img src="../assets/img/<?php echo $banner['image_qr']; ?>">

        <button type="submit">Enregistrer</button>
    </form>
</div>