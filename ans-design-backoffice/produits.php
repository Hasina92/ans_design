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
   RÉCUPÉRATION DES PRODUITS
   ============================ */
$stmt = $pdo->query("SELECT * FROM produits ORDER BY nom ASC");
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h1>Gestion des Produits</h1>

    <a href="produit_form.php"
        style="padding: 10px 20px; background-color: #2ECC71; color: white; text-decoration: none; border-radius: 5px;">
        + Créer un nouveau produit
    </a>
</div>

<!-- MESSAGE DE SUPPRESSION -->
<?php if (isset($_GET['deleted'])): ?>
    <p style="color: white; background: #E74C3C; padding: 10px; border-radius: 5px;">
        Produit supprimé avec succès !
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