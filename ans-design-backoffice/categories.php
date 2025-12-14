<?php
$currentPage = 'categories';
require_once 'includes/header.php';
require_once 'config/db.php';

// Supprimer une catégorie si delete_id est fourni
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Supprimer toutes les réalisations liées à cette catégorie
    $stmt = $pdo->prepare("DELETE FROM realisations WHERE categorie_id = ?");
    $stmt->execute([$delete_id]);

    // Supprimer la catégorie
    $stmt = $pdo->prepare("DELETE FROM categories_realisation WHERE id = ?");
    $stmt->execute([$delete_id]);

    header("Location: categories.php");
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titre'])) {
    $titre = $_POST['titre'];
    $stmt = $pdo->prepare("INSERT INTO categories_realisation (titre) VALUES (?)");
    $stmt->execute([$titre]);
    $message = "Catégorie ajoutée avec succès !";
}

// Récupérer toutes les catégories avec le nombre de réalisations
$categories = $pdo->query("
    SELECT c.id, c.titre, COUNT(r.id) AS nb_realisations
    FROM categories_realisation c
    LEFT JOIN realisations r ON c.id = r.categorie_id
    GROUP BY c.id
")->fetchAll();
?>
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h1>Catégories</h1>

    <?php if (!empty($message)): ?>
        <p style="color:green;"><?= $message ?></p>
    <?php endif; ?>

    <!-- Bouton pour afficher le formulaire -->
    <button id="showForm"
        style="padding: 10px 20px; background-color: #2ECC71; color: white; text-decoration: none; border-radius: 5px; border: none; font-size: 16px;">+
        Ajouter une catégorie</button>
</div>

<!-- Formulaire caché au départ -->
<div id="formContainer" style="display:none; margin-top:20px;" class="formulaire_ajout">
    <form method="post">
        <label>Titre :</label><br>
        <input type="text" name="titre" required>
        <button type="submit">Ajouter</button>
    </form>
</div>

<hr>
<div class="panel">
    <table border="1" cellpadding="10">
        <tr>
            <th>Titre</th>
            <th>Nombre de réalisations</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?= htmlspecialchars($cat['titre']) ?></td>
                <td><?= $cat['nb_realisations'] ?></td>
                <td style="display: flex; gap: 20px; border: none; border-bottom: 1px #000000 solid;">
                    <a href="realisations.php?categorie_id=<?= $cat['id'] ?>"
                        style="padding: 10px 20px; background-color: #2ECC71; color: white; text-decoration: none; border-radius: 5px; border: none; font-size: 16px;">Voir
                        réalisations</a>
                    <a href="categories.php?delete_id=<?= $cat['id'] ?>"
                        onclick="return confirm('Voulez-vous vraiment supprimer cette catégorie et toutes ses réalisations ?');"
                        style="padding: 10px 20px; background-color: #DF4D34; color: white; text-decoration: none; border-radius: 5px; border: none; font-size: 16px;">
                        Supprimer
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<script>
    document.getElementById('showForm').addEventListener('click', function () {
        const form = document.getElementById('formContainer');
        form.style.display = (form.style.display === 'none') ? 'block' : 'none';
    });
</script>