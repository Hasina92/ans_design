<?php
require_once 'config/db.php';

$technologie = null;

// --- TRAITEMENT POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    // Récupérer les images précédentes si existantes
    $image_name = $_POST['old_image'] ?? null;
    $image_tech_name = $_POST['old_image_tech'] ?? null;

    // Upload d'une nouvelle image principale
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === 0) {
        $upload_dir = __DIR__ . '/../uploads/technologies/';
        if (!is_dir($upload_dir))
            mkdir($upload_dir, 0777, true);

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = uniqid('tech_') . '.' . $ext;

        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name);
    }

    // Upload d'une nouvelle image_technologie
    if (!empty($_FILES['image_technologie']['name']) && $_FILES['image_technologie']['error'] === 0) {
        $upload_dir = __DIR__ . '/../uploads/technologies/';
        if (!is_dir($upload_dir))
            mkdir($upload_dir, 0777, true);

        $ext = pathinfo($_FILES['image_technologie']['name'], PATHINFO_EXTENSION);
        $image_tech_name = uniqid('techimg_') . '.' . $ext;

        move_uploaded_file($_FILES['image_technologie']['tmp_name'], $upload_dir . $image_tech_name);
    }

    // INSERT ou UPDATE
    if ($id) {
        $stmt = $pdo->prepare("UPDATE technologies 
            SET nom=?, description_courte=?, description_longue=?, image=?, image_technologie=?, actif=?, ordre=? 
            WHERE id=?");
        $stmt->execute([
            $_POST['nom'],
            $_POST['desc_courte'],
            $_POST['desc_longue'],
            $image_name,
            $image_tech_name,
            $_POST['actif'] ?? 0,
            $_POST['ordre'] ?? 0,
            $id
        ]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO technologies 
            (nom, description_courte, description_longue, image, image_technologie, actif, ordre) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['nom'],
            $_POST['desc_courte'],
            $_POST['desc_longue'],
            $image_name,
            $image_tech_name,
            $_POST['actif'] ?? 1,
            $_POST['ordre'] ?? 0
        ]);
    }

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
    <input type="hidden" name="old_image_tech" value="<?= htmlspecialchars($technologie['image_technologie'] ?? '') ?>">

    <div>
        <label for="nom">Nom de la Technologie</label>
        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($technologie['nom'] ?? '') ?>" required>
    </div>

    <div style="margin-top:15px;">
        <label for="desc_courte">Description Courte</label>
        <textarea id="desc_courte" name="desc_courte"
            rows="2"><?= htmlspecialchars($technologie['description_courte'] ?? '') ?></textarea>
    </div>

    <div style="margin-top:15px;">
        <label for="desc_longue">Description Longue</label>
        <textarea id="desc_longue" name="desc_longue"
            rows="4"><?= htmlspecialchars($technologie['description_longue'] ?? '') ?></textarea>
    </div>

    <div style="margin-top:15px;">
        <label for="image">Icone</label>
        <input type="file" id="image" name="image" accept="image/*">
        <?php if (!empty($technologie['image'])): ?>
            <p>Image actuelle :</p>
            <img src="../uploads/technologies/<?= htmlspecialchars($technologie['image']) ?>" width="150">
        <?php endif; ?>
    </div>

    <div style="margin-top:15px;">
        <label for="image_technologie">Image de la technologie</label>
        <input type="file" id="image_technologie" name="image_technologie" accept="image/*">
        <?php if (!empty($technologie['image_technologie'])): ?>
            <p>Image actuelle :</p>
            <img src="../uploads/technologies/<?= htmlspecialchars($technologie['image_technologie']) ?>" width="150">
        <?php endif; ?>
    </div>

    <div style="margin-top:15px;">
        <input type="checkbox" id="actif" name="actif" value="1" <?= (isset($technologie) && $technologie['actif']) || !isset($technologie) ? 'checked' : '' ?>>
        <label for="actif">Technologie active (visible sur le site)</label>
    </div>

    <div style="margin-top:15px;">
        <label for="ordre">Ordre d'affichage</label>
        <input type="number" id="ordre" name="ordre" value="<?= htmlspecialchars($technologie['ordre'] ?? 0) ?>">
    </div>

    <div style="margin-top:25px; text-align:right;">
        <button type="submit"><?= $technologie ? 'Mettre à jour' : 'Ajouter' ?></button>
    </div>
</form>

<?php require_once 'includes/footer.php'; ?>