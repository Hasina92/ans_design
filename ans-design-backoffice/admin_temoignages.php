<?php
$pageTitle = 'Témoignages';
$currentPage = 'admin_temoignages';
require_once 'includes/header.php';
require_once 'config/db.php';

/* ==========================================================
   1) ACTIONS : VALIDER / DEVALIDER / DELETE
   ========================================================== */
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $action = $_GET['action'];

    if ($action === 'valider') {
        $pdo->prepare("UPDATE temoignages SET valide = 1 WHERE id = ?")->execute([$id]);
    } elseif ($action === 'devalider') {
        $pdo->prepare("UPDATE temoignages SET valide = 0 WHERE id = ?")->execute([$id]);
    } elseif ($action === 'delete') {
        // On supprime la photo si elle existe
        $stmt = $pdo->prepare("SELECT photo FROM temoignages WHERE id = ?");
        $stmt->execute([$id]);
        $photo = $stmt->fetchColumn();

        if ($photo && file_exists(__DIR__ . '/../uploads/temoignages/' . $photo)) {
            unlink(__DIR__ . '/../uploads/temoignages/' . $photo);
        }

        $pdo->prepare("DELETE FROM temoignages WHERE id = ?")->execute([$id]);
    }

    header('Location: admin_temoignages.php');
    exit;
}

/* ==========================================================
   2) AJOUT DE TEMOIGNAGE DEPUIS BACKOFFICE
   ========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prenom'])) {

    $prenom = trim($_POST['prenom']);
    $poste = trim($_POST['poste'] ?? '');
    $entreprise = trim($_POST['entreprise'] ?? '');
    $avis = trim($_POST['avis'] ?? '');
    $note = (int) ($_POST['note'] ?? 5);
    $photo = null;

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {

        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {

            $photo = uniqid('temo_') . '.' . $ext;
            $uploadDir = __DIR__ . '/../uploads/temoignages/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $photo);
        }
    }

    $valide = isset($_POST['valide']) ? 1 : 0;

    $stmt = $pdo->prepare("
        INSERT INTO temoignages (prenom, poste, entreprise, avis, note, photo, valide)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $prenom,
        $poste,
        $entreprise,
        $avis,
        $note,
        $photo,
        $valide
    ]);

    header('Location: admin_temoignages.php?added=1');
    exit;
}


/* ==========================================================
   3) RECUPERATION DES TEMOIGNAGES
   ========================================================== */
$temoignages = $pdo->query("
    SELECT * FROM temoignages ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- ==========================================================
     4) HEADER + BOUTON AJOUTER
     ========================================================== -->
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h1>Témoignages</h1>
    <button id="btn-show-form"
        style="padding: 10px 20px; background-color: #2ECC71; color: white; text-decoration: none; border-radius: 5px; border: none; font-size: 16px;">+
        Ajouter un témoignage</button>
</div>


<?php if (isset($_GET['added'])): ?>
    <p class="success">Témoignage ajouté avec succès.</p>
<?php endif; ?>


<!-- ==========================================================
     6) FORMULAIRE AJOUT (CACHÉ PAR DÉFAUT)
     ========================================================== -->
<div id="form-add-temoignage" style="display:none; margin-top:40px;" class="formulaire_ajout">
    <button id="btn-hide-form" class="btn-red" style="margin-bottom:15px;">Fermer le formulaire</button>

    <form method="POST" enctype="multipart/form-data">
        <label>Prénom</label><br>
        <input type="text" name="prenom" required><br><br>

        <label>Poste</label><br>
        <input type="text" name="poste"><br><br>

        <label>Entreprise</label><br>
        <input type="text" name="entreprise"><br><br>

        <label>Avis</label><br>
        <textarea name="avis" rows="4" required></textarea><br><br>

        <label>Note</label><br>
        <select name="note">
            <option value="5">5</option>
            <option value="4">4</option>
            <option value="3">3</option>
            <option value="2">2</option>
            <option value="1">1</option>
        </select><br><br>

        <label>Photo</label><br>
        <input type="file" name="photo" accept="image/*"><br><br>

        <label><input type="checkbox" name="valide" value="1"> Valider directement</label><br><br>

        <button type="submit" class="btn-green">Ajouter</button>
    </form>
</div>

<!-- ==========================================================
     5) TABLEAU DES TEMOIGNAGES EXISTANTS
     ========================================================== -->

<div class="panel">
    <table border="1" cellpadding="8" width="100%">
        <thead>
            <tr>
                <th>Prénom</th>
                <th>Poste</th>
                <th>Entreprise</th>
                <th>Avis</th>
                <th>Note</th>
                <th>Photo</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($temoignages as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['prenom']) ?></td>
                    <td><?= htmlspecialchars($t['poste']) ?></td>
                    <td><?= htmlspecialchars($t['entreprise']) ?></td>
                    <td style="max-width:400px;"><?= nl2br(htmlspecialchars($t['avis'])) ?></td>
                    <td><?= intval($t['note']) ?></td>

                    <td>
                        <?php if (!empty($t['photo']) && file_exists(__DIR__ . '/../uploads/temoignages/' . $t['photo'])): ?>
                            <img src="../uploads/temoignages/<?= htmlspecialchars($t['photo']) ?>" width="80">
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php if (!$t['valide']): ?>
                            <a href="admin_temoignages.php?action=valider&id=<?= $t['id'] ?>"
                                style="padding: 10px 20px; background-color: #2ECC71; color: white; text-decoration: none; border-radius: 5px; border: 1px #DF4D34 solid !important; font-size: 16px; margin-bottom: 10px;">Valider</a>
                        <?php else: ?>
                            <a href="admin_temoignages.php?action=devalider&id=<?= $t['id'] ?>"
                                style="padding: 10px 20px; background-color: #DF4D34; color: white; text-decoration: none; border: 1px #DF4D34 solid !important; border-radius: 5px; font-size: 16px; margin-bottom: 10px;">Dévalider</a>
                        <?php endif; ?>

                        <a href="admin_temoignages.php?action=delete&id=<?= $t['id'] ?>"
                            onclick="return confirm('Supprimer ce témoignage ?')"
                            style="padding: 10px 20px; background: transparent; color: #DF4D34; text-decoration: none; border-radius: 5px; border: 1px #DF4D34 solid !important; font-size: 16px;">
                            Supprimer
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<!-- ==========================================================
     7) JAVASCRIPT POUR AFFICHER / CACHER LE FORMULAIRE
     ========================================================== -->
<script>
    document.addEventListener("DOMContentLoaded", function () {

        const btnShow = document.getElementById("btn-show-form");
        const btnHide = document.getElementById("btn-hide-form");
        const form = document.getElementById("form-add-temoignage");

        // Afficher le formulaire
        btnShow.addEventListener("click", function () {
            form.style.display = "block";
            form.scrollIntoView({ behavior: "smooth" });
        });

        // Cacher le formulaire
        btnHide.addEventListener("click", function () {
            form.style.display = "none";
            window.scrollTo({ top: 0, behavior: "smooth" });
        });

    });
</script>

<?php require_once 'includes/footer.php'; ?>