<?php
require_once 'config/db.php';
require_once 'includes/header.php';

if (!isset($_GET['id'])) {
    die("ID manquant.");
}

$id = intval($_GET['id']);

// Récupérer la demande
$stmt = $pdo->prepare("SELECT * FROM demandes_devis WHERE id = ?");
$stmt->execute([$id]);
$devis = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$devis) {
    die("Demande introuvable.");
}
?>
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h1>Traitement de la demande #<?= $devis['id'] ?></h1>
</div>

<div class="panel">
    <p><strong>Nom :</strong> <?= htmlspecialchars($devis['nom']); ?></p>
    <p><strong>Email :</strong> <?= htmlspecialchars($devis['email']); ?></p>
    <p><strong>Téléphone :</strong> <?= htmlspecialchars($devis['telephone']); ?></p>
    <p><strong>Message :</strong><br><?= nl2br(htmlspecialchars($devis['message'])); ?></p>
    <p><strong>Date :</strong> <?= $devis['date_creation']; ?></p>

    <hr>

    <h2>Mettre à jour la demande</h2>

    <form method="post" action="update_devis.php" class="formulaire_ajout">
        <input type="hidden" name="id" value="<?= $devis['id'] ?>">

        <label>Statut :</label><br>
        <select name="statut">
            <option value="nouveau" <?= ($devis['statut'] == 'nouveau') ? 'selected' : '' ?>>Nouveau</option>
            <option value="vu" <?= ($devis['statut'] == 'vu') ? 'selected' : '' ?>>Vu</option>
            <option value="traite" <?= ($devis['statut'] == 'traite') ? 'selected' : '' ?>>Traité</option>
        </select>

        <br><br>

        <label>Note admin (interne) :</label>
        <textarea name="note_admin" rows="6"><?= htmlspecialchars($devis['note_admin']) ?></textarea>

        <br><br>

        <button type="submit" class="btn-red">Mettre à jour</button>
    </form>
</div>