<?php
$currentPage = 'partenaires';
require_once 'includes/header.php';
require_once 'config/db.php';

$message = '';

// Dossier des images
$uploadDir = __DIR__ . '/assets/img/';

// Vérifier si le dossier existe, sinon le créer
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Supprimer un logo
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Récupérer le logo pour le supprimer physiquement
    $stmt = $pdo->prepare("SELECT logo FROM clients_logos WHERE id = ?");
    $stmt->execute([$delete_id]);
    $logoToDelete = $stmt->fetchColumn();
    if ($logoToDelete && file_exists($uploadDir . $logoToDelete)) {
        unlink($uploadDir . $logoToDelete);
    }

    // Supprimer de la base
    $stmt = $pdo->prepare("DELETE FROM clients_logos WHERE id = ?");
    $stmt->execute([$delete_id]);
    header("Location: partenaires.php");
    exit;
}

// Ajouter ou modifier un logo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entreprise = $_POST['entreprise'] ?? '';
    $edit_id = $_POST['edit_id'] ?? '';

    $logo = $_POST['old_logo'] ?? '';

    // Upload du fichier si fourni
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $message = "Format non autorisé. Seuls jpg, png, gif, svg sont acceptés.";
        } else {
            $logo = uniqid() . '.' . $ext;
            if (!move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $logo)) {
                $message = "Erreur lors de l'upload du fichier.";
                $logo = $_POST['old_logo'] ?? '';
            }
        }
    }

    // Si tout est correct, on insère ou met à jour
    if (empty($message)) {
        if ($edit_id) {
            $stmt = $pdo->prepare("UPDATE clients_logos SET entreprise = ?, logo = ? WHERE id = ?");
            $stmt->execute([$entreprise, $logo, $edit_id]);
            $message = "Logo modifié avec succès !";
        } else {
            if (!empty($logo)) {
                $stmt = $pdo->prepare("INSERT INTO clients_logos (entreprise, logo) VALUES (?, ?)");
                $stmt->execute([$entreprise, $logo]);
                $message = "Logo ajouté avec succès !";
            } else {
                $message = "Veuillez sélectionner un fichier logo valide.";
            }
        }
    }
}

// Récupérer tous les logos
$logos = $pdo->query("SELECT * FROM clients_logos ORDER BY ordre ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
    <h1>Gestion des logos clients</h1>
    <?php if ($message): ?>
        <p style="color:green;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <button id="showForm"
        style="padding:10px 20px; background-color:#2ECC71; color:white; border:none; border-radius:5px;">+ Ajouter un
        logo</button>
</div>

<div id="formContainer" class="formulaire_ajout" style="display:none; margin-top:20px;">
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="edit_id" id="edit_id" value="">
        <input type="hidden" name="old_logo" id="old_logo" value="">

        <label>Nom de l'entreprise :</label><br>
        <input type="text" name="entreprise" id="entreprise" required><br><br>

        <label>Logo :</label><br>
        <input type="file" name="logo" accept="image/*"><br><br>

        <button type="submit" id="submitBtn">Ajouter</button>
    </form>
</div>

<div class="panel" style="margin-top:20px;">
    <table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; width:100%;">
        <tr>
            <th>ID</th>
            <th>Entreprise</th>
            <th>Logo</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($logos as $l): ?>
            <tr>
                <td><?= $l['id'] ?></td>
                <td><?= htmlspecialchars($l['entreprise']) ?></td>
                <td>
                    <?php if ($l['logo']): ?>
                        <img src="assets/img/<?= htmlspecialchars($l['logo']) ?>" width="100" alt="">
                    <?php endif; ?>
                </td>
                <td style="display:flex; gap:10px;">
                    <a href="partenaires.php?delete_id=<?= $l['id'] ?>" class="delete"
                        onclick="return confirm('Voulez-vous vraiment supprimer ce logo ?');"
                        style="background-color:#DF4D34;color:white;padding:8px 12px;border-radius:4px;text-decoration:none;">Supprimer</a>
                    <button type="button"
                        onclick="editLogo(<?= $l['id'] ?>, '<?= addslashes($l['entreprise']) ?>', '<?= $l['logo'] ?>')"
                        style="background-color:#2ECC71;color:white;padding:8px 12px;border-radius:4px;">Modifier</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<script>
    document.getElementById('showForm').addEventListener('click', function () {
        const form = document.getElementById('formContainer');
        form.style.display = (form.style.display === 'none') ? 'block' : 'none';
        document.getElementById('submitBtn').textContent = 'Ajouter';
        document.getElementById('edit_id').value = '';
        document.getElementById('entreprise').value = '';
        document.getElementById('old_logo').value = '';
    });

    function editLogo(id, entreprise, logo) {
        const form = document.getElementById('formContainer');
        form.style.display = 'block';
        document.getElementById('edit_id').value = id;
        document.getElementById('entreprise').value = entreprise;
        document.getElementById('old_logo').value = logo;
        document.getElementById('submitBtn').textContent = 'Modifier';
    }
</script>