<?php
$currentPage = 'categories';
require_once 'includes/header.php';
require_once 'config/db.php';

$categorie_id = $_GET['categorie_id'] ?? 0;

// Supprimer une réalisation si delete_id est fourni
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Supprimer l'image du serveur
    $stmt = $pdo->prepare("SELECT image FROM realisations WHERE id = ?");
    $stmt->execute([$delete_id]);
    $real = $stmt->fetch();
    if ($real && !empty($real['image']) && file_exists("upload/" . $real['image'])) {
        unlink("upload/" . $real['image']);
    }

    // Supprimer la réalisation
    $stmt = $pdo->prepare("DELETE FROM realisations WHERE id = ?");
    $stmt->execute([$delete_id]);

    header("Location: realisations.php?categorie_id=" . $categorie_id);
    exit;
}

// Récupérer infos de la catégorie
$stmt = $pdo->prepare("SELECT * FROM categories_realisation WHERE id = ?");
$stmt->execute([$categorie_id]);
$categorie = $stmt->fetch();

if (!$categorie) {
    die("Catégorie introuvable.");
}

// Traitement ajout / modification réalisation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $titre = $_POST['titre'];
    $client = $_POST['client'];
    $nombre_ex = $_POST['nombre_ex'];
    $delai = $_POST['delai'];
    $date_realisation = $_POST['date_realisation'] ?? date('Y');
    $logoName = $_POST['old_logo'] ?? null;
    if (!empty($_FILES['logo']['name'])) {
        $logoName = time() . "_logo_" . basename($_FILES['logo']['name']);
        move_uploaded_file($_FILES['logo']['tmp_name'], "upload/" . $logoName);
    }



    $imageName = $_POST['old_image'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "upload/" . $imageName);
    }

    if ($id) {
        // Modification
        $stmt = $pdo->prepare("
        UPDATE realisations 
        SET titre=?, client=?, nombre_ex=?, delai_ex=?, image=?, logo=?, date_realisation=?
        WHERE id=?
    ");

        $stmt->execute([
            $titre,
            $client,
            $nombre_ex,
            $delai,
            $imageName,
            $logoName,
            $date_realisation,
            $id
        ]);

    } else {
        // Ajout
        $stmt = $pdo->prepare("
        UPDATE realisations 
        SET titre=?, client=?, nombre_ex=?, delai_ex=?, image=?, logo=?, date_realisation=?
        WHERE id=?
    ");

        $stmt->execute([
            $titre,
            $client,
            $nombre_ex,
            $delai,
            $imageName,
            $logoName,
            $date_realisation,
            $id
        ]);

    }

    header("Location: realisations.php?categorie_id=" . $categorie_id);
    exit;
}

// Récupérer réalisations
$stmt = $pdo->prepare("SELECT * FROM realisations WHERE categorie_id = ?");
$stmt->execute([$categorie_id]);
$realisations = $stmt->fetchAll();
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h1><?= htmlspecialchars($categorie['titre']) ?></h1>

    <button id="showForm"
        style="padding: 10px 20px; background-color: #2ECC71; color: white; border-radius: 5px; border: none;">
        + Ajouter une réalisation
    </button>
</div>

<div class="panel">
    <!-- FORMULAIRE AJOUT / MODIF -->
    <div id="formContainer" style="display:none; margin-top:20px;" class="formulaire_ajout">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" id="real_id">
            <input type="hidden" name="old_image" id="old_image">

            <label>Titre :</label><br>
            <input type="text" name="titre" id="titre" required><br><br>

            <label>Logo :</label><br>
            <input type="file" name="logo" id="logo"><br><br>

            <label>Client :</label><br>
            <input type="text" name="client" id="client"><br><br>

            <label>Nombre d'ex :</label><br>
            <input type="text" name="nombre_ex" id="nombre_ex"><br><br>

            <label>Délai :</label><br>
            <input type="text" name="delai" id="delai"><br><br>

            <label>Année de réalisation :</label><br>
            <input type="number" name="date_realisation" id="date_realisation" min="2000" max="2100"><br><br>


            <label>Image :</label><br>
            <input type="file" name="image" id="image"><br><br>

            <button type="submit" id="submitBtn">Ajouter</button>
        </form>
    </div>

    <hr>

    <?php if (empty($realisations)): ?>
        <p>Aucune réalisation pour cette catégorie.</p>
    <?php else: ?>
        <table border="1" cellpadding="10">
            <tr>
                <th>Titre</th>
                <th>Logo</th>
                <th>Année</th>
                <th>Client</th>
                <th>Nombre d'ex</th>
                <th>Délai</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($realisations as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['titre']) ?></td>
                    <td>
                        <?php if (!empty($r['logo'])): ?>
                            <img src="upload/<?= htmlspecialchars($r['logo']) ?>" width="60">
                        <?php endif; ?>
                    </td>

                    <td><?= htmlspecialchars($r['date_realisation']) ?></td>
                    <td><?= htmlspecialchars($r['client']) ?></td>
                    <td><?= htmlspecialchars($r['nombre_ex']) ?></td>
                    <td><?= htmlspecialchars($r['delai_ex']) ?></td>
                    <td>
                        <?php if (!empty($r['image'])): ?>
                            <img src="upload/<?= htmlspecialchars($r['image']) ?>" width="100">
                        <?php endif; ?>
                    </td>
                    <td style="display:flex; gap:10px;">
                        <a href="realisations.php?categorie_id=<?= $categorie_id ?>&delete_id=<?= $r['id'] ?>"
                            onclick="return confirm('Voulez-vous vraiment supprimer cette réalisation ?');"
                            style="padding: 10px 20px; background-color: #DF4D34; color: white; border-radius: 5px; text-decoration: none;">
                            Supprimer
                        </a>
                        <button type="button" onclick="editRealisation(
  <?= $r['id'] ?>,
  '<?= addslashes($r['titre']) ?>',
  '<?= addslashes($r['client']) ?>',
  '<?= addslashes($r['nombre_ex']) ?>',
  '<?= addslashes($r['delai_ex']) ?>',
  '<?= addslashes($r['image']) ?>',
  '<?= addslashes($r['logo']) ?>',
  '<?= $r['date_realisation'] ?>'
)
" style="padding: 10px 20px; background-color: #2ECC71; color: white; border-radius: 5px;">
                            Modifier
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<script>
    // Afficher / cacher formulaire
    document.getElementById('showForm').addEventListener('click', () => {
        const form = document.getElementById('formContainer');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
        document.getElementById('submitBtn').textContent = 'Ajouter';
        document.getElementById('real_id').value = '';
        document.getElementById('titre').value = '';
        document.getElementById('date_realisation').value = date;
        document.getElementById('old_logo').value = logo;
        document.getElementById('client').value = '';
        document.getElementById('nombre_ex').value = '';
        document.getElementById('delai').value = '';
        document.getElementById('old_image').value = '';
    });

    // Mode édition
    function editRealisation(id, titre, client, nombre_ex, delai, image) {
        const form = document.getElementById('formContainer');
        form.style.display = 'block';
        document.getElementById('real_id').value = id;
        document.getElementById('titre').value = titre;
        document.getElementById('client').value = client;
        document.getElementById('nombre_ex').value = nombre_ex;
        document.getElementById('delai').value = delai;
        document.getElementById('old_image').value = image;
        document.getElementById('submitBtn').textContent = 'Modifier';
    }
</script>