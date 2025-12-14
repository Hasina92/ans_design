<?php
require_once 'config/db.php';

$technologie = null;

// --- TRAITEMENT POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    // Récupérer l'image précédente si existante
    $image_name = $_POST['old_image'] ?? null;

    // Upload d'une nouvelle image si fournie
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === 0) {
        $upload_dir = __DIR__ . '/../uploads/technologies/';
        if (!is_dir($upload_dir))
            mkdir($upload_dir, 0777, true);

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid('tech_') . '.' . $ext;

        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $file_name);
        $image_name = $file_name;
    }

    // INSERT ou UPDATE
    if ($id) {
        $stmt = $pdo->prepare("UPDATE technologies SET nom=?, description_courte=?, description_longue=?, image=?, actif=?, ordre=? WHERE id=?");
        $stmt->execute([
            $_POST['nom'],
            $_POST['desc_courte'],
            $_POST['desc_longue'],
            $image_name,
            $_POST['actif'] ?? 0,
            $_POST['ordre'] ?? 0,
            $id
        ]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO technologies (nom, description_courte, description_longue, image, actif, ordre) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['nom'],
            $_POST['desc_courte'],
            $_POST['desc_longue'],
            $image_name,
            $_POST['actif'] ?? 1,
            $_POST['ordre'] ?? 0
        ]);
    }

    // Redirection après succès
    header('Location: technologies.php?success=1');
    exit();
}

// --- MODE ÉDITION ---
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM technologies WHERE id=?");
    $stmt->execute([$_GET['id']]);
    $technologie = $stmt->fetch(PDO::FETCH_ASSOC);
}

// --- Inclure le header après traitement POST ---
require_once 'includes/header.php';
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h1><?= $technologie ? 'Modifier la Technologie' : 'Ajouter une Nouvelle Technologie' ?></h1>
</div>

<form method="POST" enctype="multipart/form-data" class="formulaire_ajout">
    <input type="hidden" name="id" value="<?= $technologie['id'] ?? '' ?>">
    <input type="hidden" name="old_image" value="<?= htmlspecialchars($technologie['image'] ?? '') ?>">

    <div>
        <label for="nom">Nom de la Technologie</label>
        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($technologie['nom'] ?? '') ?>" required
            style="width:100%; padding:10px; margin-top:5px; border-radius:5px;">
    </div>

    <div style="margin-top:15px;">
        <label for="desc_courte">Description Courte</label>
        <textarea id="desc_courte" name="desc_courte" rows="2"
            style="width:100%; padding:10px; border-radius:5px;"><?= htmlspecialchars($technologie['description_courte'] ?? '') ?></textarea>
    </div>

    <div style="margin-top:15px;">
        <label for="desc_longue">Description Longue</label>
        <textarea id="desc_longue" name="desc_longue" rows="4"
            style="width:100%; padding:10px; border-radius:5px;"><?= htmlspecialchars($technologie['description_longue'] ?? '') ?></textarea>
    </div>

    <div style="margin-top:15px;">
        <label for="image">Image</label>
        <input type="file" id="image" name="image" accept="image/*" style="width:100%; padding:10px;">
        <?php if (!empty($technologie['image'])): ?>
            <p>Image actuelle :</p>
            <img src="../uploads/technologies/<?= htmlspecialchars($technologie['image']) ?>"
                alt="<?= htmlspecialchars($technologie['nom']) ?>" style="max-width:150px; border-radius:5px;">
        <?php endif; ?>
    </div>

    <div style="margin-top:15px;">
        <input type="checkbox" id="actif" name="actif" value="1" <?= (isset($technologie) && $technologie['actif']) || !isset($technologie) ? 'checked' : '' ?>>
        <label for="actif">Technologie active (visible sur le site)</label>
    </div>

    <div style="margin-top:15px;">
        <label for="ordre">Ordre d'affichage</label>
        <input type="number" id="ordre" name="ordre" value="<?= htmlspecialchars($technologie['ordre'] ?? 0) ?>"
            style="width:100px; padding:5px;">
    </div>

    <div style="margin-top:25px; text-align:right;">
        <button type="submit"
            style="padding:12px 25px; background-color:#2ECC71; color:white; border:none; border-radius:5px; cursor:pointer; font-size:1em;">
            <?= $technologie ? 'Mettre à jour' : 'Ajouter' ?>
        </button>
    </div>
</form>

<?php require_once 'includes/footer.php'; ?>