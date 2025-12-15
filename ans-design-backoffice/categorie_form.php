<?php
$pageTitle = 'Catégorie';
$currentPage = 'categories_produits';
require_once 'includes/header.php';
require_once 'config/db.php';
require_once 'includes/header.php';

$categorie = null;

// --- SI MODE ÉDITION ---
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $categorie = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$categorie)
        die("Catégorie introuvable.");
}

// --- SI FORMULAIRE ENVOYÉ ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $nom = $_POST['nom'];
    $slug = $_POST['slug'];

    if ($id) {
        // Update
        $stmt = $pdo->prepare("UPDATE categories SET nom = ?, slug = ? WHERE id = ?");
        $stmt->execute([$nom, $slug, $id]);
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO categories (nom, slug) VALUES (?, ?)");
        $stmt->execute([$nom, $slug]);
    }

    header("Location: categories_produits.php?success=1");
    exit;
}
?>
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h1><?= $categorie ? "Modifier la catégorie" : "Créer une nouvelle catégorie" ?></h1>
</div>
<form method="POST" class="formulaire_ajout">
    <?php if ($categorie): ?>
        <input type="hidden" name="id" value="<?= $categorie['id'] ?>">
    <?php endif; ?>

    <label>Nom de la catégorie</label>
    <input type="text" name="nom" required value="<?= htmlspecialchars($categorie['nom'] ?? '') ?>"
        style="width:100%; padding:10px; margin-bottom:15px;">

    <label>Slug (URL)</label>
    <input type="text" name="slug" required value="<?= htmlspecialchars($categorie['slug'] ?? '') ?>"
        style="width:100%; padding:10px; margin-bottom:15px;">

    <button type="submit" style="padding:12px 20px; background:#2ecc71; color:white; border:none; border-radius:5px;">
        Sauvegarder
    </button>
</form>

<?php require_once 'includes/footer.php'; ?>