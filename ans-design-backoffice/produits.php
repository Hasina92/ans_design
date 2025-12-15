<?php
$pageTitle = 'Gestion des Produits';
$currentPage = 'produits';
require_once 'includes/header.php';
require_once 'config/db.php';

/* ============================
   SUPPRESSION D'UN PRODUIT
   ============================ */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM produits WHERE id = ?");
    $stmt->execute([$id]);

    // Rafraîchir la page sans le paramètre delete
    header("Location: produits.php?deleted=1");
    exit;
}

/* ============================
   FILTRE ET RECHERCHE
   ============================ */
$search = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$order = $_GET['order'] ?? 'ASC';
$order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

/* ============================
   Récupérer les catégories
   ============================ */
$categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY nom ASC");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

/* ============================
   Construire la requête dynamique
   ============================ */
$sql = "SELECT p.*, c.nom AS categorie_nom 
        FROM produits p 
        LEFT JOIN categories c ON p.categorie_id = c.id 
        WHERE 1";

$params = [];

if ($search !== '') {
    $sql .= " AND p.nom LIKE ?";
    $params[] = "%$search%";
}

if ($categoryFilter !== '' && is_numeric($categoryFilter)) {
    $sql .= " AND p.categorie_id = ?";
    $params[] = $categoryFilter;
}

$sql .= " ORDER BY p.nom $order";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
    <h1>Gestion des Produits</h1>

    <div style="display: flex; gap: 10px; margin-top: 10px;">
        <a href="produit_form.php"
            style="padding: 10px 20px; background-color: #2ECC71; color: white; text-decoration: none; border-radius: 5px;">
            + Créer un nouveau produit
        </a>

        <!-- FORMULAIRE DE RECHERCHE ET FILTRE -->
        <form method="get" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <input type="text" name="search" placeholder="Rechercher un produit..."
                value="<?php echo htmlspecialchars($search); ?>"
                style="padding: 10px; border-radius: 5px; border: 1px solid #ccc;">

            <select name="category" style="padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                <option value="">Toutes les catégories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="order" style="padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                <option value="ASC" <?= $order == 'ASC' ? 'selected' : '' ?>>Nom A → Z</option>
                <option value="DESC" <?= $order == 'DESC' ? 'selected' : '' ?>>Nom Z → A</option>
            </select>

            <button type="submit"
                style="padding: 10px 20px; background-color: #3498DB; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Rechercher
            </button>
        </form>
    </div>
</div>

<!-- MESSAGE DE SUPPRESSION -->
<?php if (isset($_GET['deleted'])): ?>
    <p style="color: white; background: #E74C3C; padding: 10px; border-radius: 5px;">
        Produit supprimé avec succès !
    </p>
<?php endif; ?>

<?php if ($search !== '' || $categoryFilter !== ''): ?>
    <p style="margin: 10px 0;">
        <?php if ($search !== ''): ?>
            Résultats de la recherche pour : <strong><?php echo htmlspecialchars($search); ?></strong>
        <?php endif; ?>
        <?php if ($categoryFilter !== '' && is_numeric($categoryFilter)): ?>
            Catégorie filtrée :
            <strong><?php echo htmlspecialchars($categories[array_search($categoryFilter, array_column($categories, 'id'))]['nom']); ?></strong>
        <?php endif; ?>
    </p>
<?php endif; ?>

<div class="panel">
    <table>
        <thead>
            <tr>
                <th>Nom du Produit</th>
                <th>Catégorie</th>
                <th>Prix de base</th>
                <th>Actif</th>
                <th>Phare</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php if (count($produits) === 0): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">Aucun produit trouvé.</td>
                </tr>
            <?php endif; ?>

            <?php foreach ($produits as $produit): ?>
                <tr>
                    <td><?php echo htmlspecialchars($produit['nom']); ?></td>
                    <td><?php echo htmlspecialchars($produit['categorie_nom']); ?></td>
                    <td><?php echo number_format($produit['prix_base'], 2, ',', ' '); ?> ariary</td>
                    <td><?php echo $produit['actif'] ? 'Oui' : 'Non'; ?></td>
                    <td><?php echo $produit['produit_phare'] ? '⭐ Oui' : '—'; ?></td>
                    <td style="display: flex; gap: 10px;">
                        <!-- BTN MODIFIER -->
                        <a href="produit_form.php?id=<?php echo $produit['id']; ?>"
                            style="padding: 10px 20px; background-color: #2ECC71; color: white; text-decoration: none; border-radius: 5px;">
                            Modifier
                        </a>

                        <!-- BTN SUPPRIMER -->
                        <a href="produits.php?delete=<?php echo $produit['id']; ?>"
                            onclick="return confirm('Voulez-vous vraiment supprimer ce produit ?');"
                            style="padding: 10px 20px; background-color: #E74C3C; color: white; text-decoration: none; border-radius: 5px;">
                            Supprimer
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>

    </table>
</div>

<?php require_once 'includes/footer.php'; ?>