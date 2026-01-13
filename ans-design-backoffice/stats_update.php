<?php
$currentPage = 'stats_edit';
require_once 'includes/header.php';
require_once 'config/db.php';

if (!isset($_POST['stats'])) {
    header('Location: stats_edit.php');
    exit;
}

foreach ($_POST['stats'] as $id => $valeur) {
    $stmt = $pdo->prepare("UPDATE stats SET valeur = ? WHERE id = ?");
    $stmt->execute([$valeur, $id]);
}

header('Location: stats_edit.php?success=1');
exit;
