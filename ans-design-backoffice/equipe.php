<?php
$currentPage = 'equipe';
require_once 'includes/header.php';
require_once 'config/db.php';

$message = '';

/* ---------------------------
   SUPPRESSION D'UN MEMBRE
---------------------------- */
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM equipe WHERE id = ?");
    $stmt->execute([$delete_id]);

    header("Location: equipe.php");
    exit;
}

/* ---------------------------
   MODIFICATION D'UN MEMBRE
---------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
    $edit_id = $_POST['edit_id'];
    $nom = $_POST['nom'];
    $poste = $_POST['poste'];
    $email = $_POST['email'];
    $description = $_POST['description'] ?? '';

    // Photo
    $photo = $_POST['old_photo'] ?? '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], 'upload/' . $photo);
    }

    $stmt = $pdo->prepare("UPDATE equipe SET nom=?, poste=?, email=?, photo=?, description=? WHERE id=?");
    $stmt->execute([$nom, $poste, $email, $photo, $description, $edit_id]);

    $message = "Membre modifié avec succès !";
}

/* ---------------------------
   AJOUT D'UN MEMBRE
---------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['edit_id'])) {
    $nom = $_POST['nom'];
    $poste = $_POST['poste'];
    $email = $_POST['email'];
    $description = $_POST['description'] ?? '';

    // Photo
    $photo = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], 'upload/' . $photo);
    }

    $stmt = $pdo->prepare("INSERT INTO equipe (nom, poste, email, photo, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nom, $poste, $email, $photo, $description]);

    $message = "Membre ajouté avec succès !";
}

/* ---------------------------
   RÉCUPÉRATION DES MEMBRES
---------------------------- */
$members = $pdo->query("SELECT * FROM equipe ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h1>Équipe</h1>

    <?php if (!empty($message)): ?>
        <p style="color:green;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <button id="showForm"
        style="padding: 10px 20px; background-color: #2ECC71; color: white; border-radius: 5px; border: none;">
        + Ajouter un membre
    </button>
</div>

<div class="panel">

    <!-- FORMULAIRE -->
    <div id="formContainer" style="display:none; margin-top:20px;" class="formulaire_ajout">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="edit_id" id="edit_id">
            <input type="hidden" name="old_photo" id="old_photo">

            <label>Nom :</label><br>
            <input type="text" name="nom" id="nom" required><br><br>

            <label>Poste :</label><br>
            <input type="text" name="poste" id="poste" required><br><br>

            <label>Email :</label><br>
            <input type="email" name="email" id="email"><br><br>

            <label>Photo :</label><br>
            <input type="file" name="photo" accept="image/*"><br><br>

            <label>Description :</label><br>
            <textarea name="description" id="description" rows="4"></textarea><br><br>

            <button type="submit" id="submitBtn">Ajouter</button>
        </form>
    </div>

    <hr>

    <!-- TABLEAU -->
    <table border="1" cellpadding="10" width="100%">
        <tr>
            <th>Nom</th>
            <th>Poste</th>
            <th>Email</th>
            <th>Photo</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($members as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m['nom']) ?></td>
                <td><?= htmlspecialchars($m['poste']) ?></td>
                <td><?= htmlspecialchars($m['email']) ?></td>
                <td>
                    <?php if (!empty($m['photo'])): ?>
                        <img src="upload/<?= htmlspecialchars($m['photo']) ?>" width="80">
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($m['description']) ?></td>
                <td style="display:flex; gap:10px;">
                    <a href="equipe.php?delete_id=<?= $m['id'] ?>" onclick="return confirm('Supprimer ce membre ?');"
                        style="padding:8px 15px; background:#DF4D34; color:white; border-radius:5px;">Supprimer</a>

                    <button type="button" onclick="editMember(
                            <?= $m['id'] ?>,
                            '<?= htmlspecialchars($m['nom'], ENT_QUOTES) ?>',
                            '<?= htmlspecialchars($m['poste'], ENT_QUOTES) ?>',
                            '<?= htmlspecialchars($m['email'], ENT_QUOTES) ?>',
                            '<?= htmlspecialchars($m['photo'], ENT_QUOTES) ?>',
                        )" style="padding:8px 15px; background:#2ECC71; color:white; border-radius:5px;">
                        Modifier
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

</div>

<script>
    // Afficher / cacher formulaire
    document.getElementById('showForm').addEventListener('click', () => {
        const form = document.getElementById('formContainer');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';

        document.getElementById('submitBtn').textContent = 'Ajouter';
        document.getElementById('edit_id').value = '';
        document.getElementById('nom').value = '';
        document.getElementById('poste').value = '';
        document.getElementById('email').value = '';
        document.getElementById('description').value = '';
        document.getElementById('old_photo').value = '';
    });

    // Mode édition
    function editMember(id, nom, poste, email, photo, description) {
        const form = document.getElementById('formContainer');
        form.style.display = 'block';

        document.getElementById('edit_id').value = id;
        document.getElementById('nom').value = nom;
        document.getElementById('poste').value = poste;
        document.getElementById('email').value = email;
        document.getElementById('description').value = description;
        document.getElementById('old_photo').value = photo;

        document.getElementById('submitBtn').textContent = 'Modifier';
    }
</script>