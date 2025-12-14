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
   RECHERCHE DE PRODUITS
   ============================ */
$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE nom LIKE ? ORDER BY nom ASC");
    $stmt->execute(["%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM produits ORDER BY nom ASC");
}

$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
    <h1>Gestion des Produits</h1>

    <div style="display: flex; gap: 10px; margin-top: 10px;">
        <a href="produit_form.php"
            style="padding: 10px 20px; background-color: #2ECC71; color: white; text-decoration: none; border-radius: 5px;">
            + Créer un nouveau produit
        </a>

        <!-- FORMULAIRE DE RECHERCHE -->
        <form method="get" style="display: flex;">
            <input type="text" name="search" placeholder="Rechercher un produit..."
                value="<?php echo htmlspecialchars($search); ?>"
                style="padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
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

<?php if ($search !== ''): ?>
    <p style="margin: 10px 0;">Résultats de la recherche pour : <strong><?php echo htmlspecialchars($search); ?></strong>
    </p>
<?php endif; ?>

<div class="panel">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom du Produit</th>
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
                    <td><?php echo htmlspecialchars($produit['id']); ?></td>
                    <td><?php echo htmlspecialchars($produit['nom']); ?></td>
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