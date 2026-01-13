<?php
$currentPage = 'stats2_edit';
require_once 'includes/header.php';
require_once 'config/db.php';

if (!isset($_POST['stats'])) {
    header('Location: stats2_edit.php');
    exit;
}

foreach ($_POST['stats'] as $id => $valeur) {
    $stmt = $pdo->prepare("UPDATE stats_2 SET valeur = ? WHERE id = ?");
    $stmt->execute([$valeur, $id]);
}

header('Location: stats2_edit.php?success=1');
exit;
