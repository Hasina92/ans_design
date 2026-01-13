<?php
$currentPage = 'stats2_edit';
require_once 'includes/header.php';
require_once 'config/db.php';

$stmt = $pdo->query("SELECT * FROM stats_2 ORDER BY id ASC");
$stats2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
    <h1>Modifier les statistiques
    </h1>

    <?php if (!empty($message)): ?>
        <p style="color:green;">
            <?= $message ?>
        </p>
    <?php endif; ?>
</div>
<div class="panel">
    <?php if (isset($_GET['success'])): ?>
        <p class="success">✔ Modifications enregistrées</p>
    <?php endif; ?>

    <form class="formulaire_ajout" action="stats2_update.php" method="POST">
        <?php foreach ($stats2 as $stat): ?>
            <label>Statistique
                <?php echo $stat['id']; ?>
            </label>
            <input type="text" name="stats[<?php echo $stat['id']; ?>]"
                value="<?php echo htmlspecialchars($stat['valeur']); ?>">
        <?php endforeach; ?>

        <button type="submit">Enregistrer</button>
    </form>
</div>