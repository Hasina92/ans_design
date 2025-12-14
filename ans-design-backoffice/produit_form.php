<?php
$pageTitle = 'Créer/Modifier un Produit';
$currentPage = 'produits';
require_once 'includes/header.php';
require_once 'config/db.php';
// Charger les catégories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY nom ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$produit = null;
$caracteristiques = [];

// LOGIQUE : Si un formulaire est soumis (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->beginTransaction();
    try {

        $image_name = $produit['image'] ?? null;

        if (!empty($_FILES['image']['name'])) {
            $upload_dir = __DIR__ . '/../uploads/produits/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid('prod_') . '.' . $ext;

            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $file_name);

            $image_name = $file_name;
        }

        $produit_id = $_POST['produit_id'] ?? null;
        
        // 1. Insérer ou Mettre à jour le produit
        if ($produit_id) {
            $stmt = $pdo->prepare("
                UPDATE produits 
                SET nom = ?, description = ?, prix_base = ?, actif = ?, produit_phare = ?, image = ?, categorie_id = ?, reduction = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $_POST['nom'],
                $_POST['description'],
                $_POST['prix_base'],
                $_POST['actif'] ?? 0,
                $_POST['produit_phare'] ?? 0,
                $image_name,
                $_POST['categorie_id'] ?: null,
                $_POST['reduction'] ?: 0,
                $produit_id
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO produits (nom, description, prix_base, actif, produit_phare, image, categorie_id, reduction) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['nom'],
                $_POST['description'],
                $_POST['prix_base'],
                $_POST['actif'] ?? 0,
                $_POST['produit_phare'] ?? 0,
                $image_name,
                $_POST['categorie_id'] ?: null,
                $_POST['reduction'] ?: 0
            ]);
            $produit_id = $pdo->lastInsertId();
        }

        // 2. Gérer les caractéristiques et options
        // D'abord, supprimer les anciennes pour simplifier la logique
        $stmt_del_opts = $pdo->prepare("DELETE FROM caracteristique_options WHERE caracteristique_id IN (SELECT id FROM produit_caracteristiques WHERE produit_id = ?)");
        $stmt_del_opts->execute([$produit_id]);
        $stmt_del_chars = $pdo->prepare("DELETE FROM produit_caracteristiques WHERE produit_id = ?");
        $stmt_del_chars->execute([$produit_id]);

        if (isset($_POST['caracteristiques'])) {
            foreach ($_POST['caracteristiques'] as $index => $carac_data) {
                // Insérer la caractéristique
                $stmt_char = $pdo->prepare("INSERT INTO produit_caracteristiques (produit_id, nom, ordre) VALUES (?, ?, ?)");
                $stmt_char->execute([$produit_id, $carac_data['nom'], $index]);
                $caracteristique_id = $pdo->lastInsertId();

                // Insérer ses options
                $options = explode(',', $carac_data['options']);
                $stmt_opt = $pdo->prepare("INSERT INTO caracteristique_options (caracteristique_id, valeur) VALUES (?, ?)");
                foreach ($options as $option) {
                    $trimmed_option = trim($option);
                    if (!empty($trimmed_option)) {
                        $stmt_opt->execute([$caracteristique_id, $trimmed_option]);
                    }
                }
            }
        }

        $pdo->commit();
        header('Location: produits.php?success=1');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erreur lors de la sauvegarde : " . $e->getMessage());
    }
}

// LOGIQUE : Si un ID est dans l'URL (mode édition)
if (isset($_GET['id'])) {
    $produit_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
    $stmt->execute([$produit_id]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupérer les caractéristiques et leurs options
    $stmt_chars = $pdo->prepare("SELECT * FROM produit_caracteristiques WHERE produit_id = ? ORDER BY ordre ASC");
    $stmt_chars->execute([$produit_id]);
    $caracteristiques_db = $stmt_chars->fetchAll(PDO::FETCH_ASSOC);

    foreach ($caracteristiques_db as $carac) {
        $stmt_opts = $pdo->prepare("SELECT valeur FROM caracteristique_options WHERE caracteristique_id = ?");
        $stmt_opts->execute([$carac['id']]);
        $options_db = $stmt_opts->fetchAll(PDO::FETCH_COLUMN);
        $carac['options'] = implode(', ', $options_db); // Convertir en chaîne pour le textarea
        $caracteristiques[] = $carac;
    }
}
?>

<div class="page-header">
    <h1><?php echo $produit ? 'Modifier le Produit' : 'Créer un nouveau Produit'; ?></h1>
</div>

<form method="POST" class="panel" enctype="multipart/form-data">
    <input type="hidden" name="produit_id" value="<?php echo $produit['id'] ?? ''; ?>">
    <h3>Informations Générales</h3>
    <div class="container-produit">
        <div>
            <label for="nom">Nom du Produit</label>
            <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($produit['nom'] ?? ''); ?>" required style="width: 100%; padding: 15px; margin-top: 5px; border-radius: 15px;" class="input">
        </div>
        <div>
            <label for="prix_base">Prix de base (ariary)</label>
            <input type="number" step="0.01" id="prix_base" name="prix_base" value="<?php echo htmlspecialchars($produit['prix_base'] ?? '0.00'); ?>" required style="width: 100%; padding: 15px;">
        </div>
        <div>
            <label for="reduction">Réduction (%)</label>
            <input type="number" step="1" min="0" max="100" id="reduction" name="reduction" 
                value="<?php echo htmlspecialchars($produit['reduction'] ?? '0'); ?>" 
                style="width: 100%; padding: 15px;">
        </div>
        <div>
            <label for="categorie_id">Catégorie du produit</label>
            <select id="categorie_id" name="categorie_id" style="width:100%; padding:15px;">
                <option value="">-- Sélectionner une catégorie --</option>

                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"
                        <?= isset($produit['categorie_id']) && $produit['categorie_id'] == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div style="margin-top: 15px;" class="description">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4" style="width: 100%; padding: 8px;"><?php echo htmlspecialchars($produit['description'] ?? ''); ?></textarea>
    </div>
    <div style="margin-top: 15px;">
        <label for="image">Image du produit</label>
        <input type="file" id="image" name="image" accept="image/*" style="width:100%; padding:10px;">

        <?php if (!empty($produit['image'])): ?>
            <p>Image actuelle :</p>
            <img src="../uploads/produits/<?php echo $produit['image']; ?>" 
                style="max-width:150px; border-radius:5px;">
        <?php endif; ?>
    </div>
    <div style="margin-top: 15px;">
        <input type="checkbox" id="actif" name="actif" value="1" <?php echo (isset($produit) && $produit['actif']) || !isset($produit) ? 'checked' : ''; ?>>
        <label for="actif">Produit actif (visible sur le site)</label>
    </div>

    <div style="margin-top: 15px;">
        <input type="checkbox" id="produit_phare" name="produit_phare" value="1"
            <?php echo (!empty($produit['produit_phare'])) ? 'checked' : ''; ?>>
        <label for="produit_phare">Produit phare (affiché sur la page d’accueil)</label>
    </div>

    <hr style="margin: 30px 0;">
    <h3>Caractéristiques (Options du produit)</h3>
    <div id="caracteristiques-container">
        <?php foreach ($caracteristiques as $index => $carac): ?>
            <div class="caracteristique-item">
                <input type="text" name="caracteristiques[<?php echo $index; ?>][nom]" placeholder="Nom (ex: Dimension)" value="<?php echo htmlspecialchars($carac['nom']); ?>" style="flex: 1;">
                <textarea name="caracteristiques[<?php echo $index; ?>][options]" placeholder="Options séparées par une virgule" style="flex: 3;"><?php echo htmlspecialchars($carac['options']); ?></textarea>
                <button type="button" class="remove-caracteristique" style="background: #E74C3C; color: white; border: none; padding: 8px 12px; cursor: pointer; border-radius: 5px;">X</button>
            </div>
        <?php endforeach; ?>
    </div>
    <button type="button" id="add-caracteristique" style="padding: 10px 15px; background-color: #3498DB; color: white; border: none; border-radius: 5px; cursor: pointer;">+ Ajouter une caractéristique</button>
    <p><small>Pour les options, séparez chaque choix par une virgule. Ex: Carré, Coins arrondis, Autre (sur devis)</small></p>

    <hr style="margin: 30px 0;">

    <div style="text-align: right;">
        <button type="submit" style="padding: 12px 25px; background-color: #2ECC71; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1.1em;">Sauvegarder le Produit</button>
    </div>
</form>

<!-- Template caché pour le JavaScript -->
<div id="caracteristique-template" style="display: none;">
    <div class="caracteristique-item" style="display: flex; gap: 15px; align-items: center; margin-bottom: 15px; background: #f9f9f9; padding: 15px; border-radius: 5px;">
        <input type="text" name="caracteristiques[__INDEX__][nom]" placeholder="Nom (ex: Dimension)" style="flex: 1; padding: 8px;">
        <textarea name="caracteristiques[__INDEX__][options]" placeholder="Options séparées par une virgule" style="flex: 3; padding: 8px;"></textarea>
        <button type="button" class="remove-caracteristique" style="background: #E74C3C; color: white; border: none; padding: 8px 12px; cursor: pointer; border-radius: 5px;">X</button>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>