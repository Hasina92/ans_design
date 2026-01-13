<?php
$currentPage = 'banner_edit';
require_once 'includes/header.php';
require_once 'config/db.php';

$titre = $_POST['titre'];
$sous_titre = $_POST['sous_titre'];

// Images actuelles
$stmt = $pdo->query("SELECT image_fond, image_qr FROM banner LIMIT 1");
$current = $stmt->fetch(PDO::FETCH_ASSOC);

$image_fond = $current['image_fond'];
$image_qr = $current['image_qr'];

// Upload image fond
if (!empty($_FILES['image_fond']['name'])) {
    $image_fond = time() . '_' . $_FILES['image_fond']['name'];
    move_uploaded_file($_FILES['image_fond']['tmp_name'], "../assets/img/" . $image_fond);
}

// Upload QR
if (!empty($_FILES['image_qr']['name'])) {
    $image_qr = time() . '_' . $_FILES['image_qr']['name'];
    move_uploaded_file($_FILES['image_qr']['tmp_name'], "../assets/img/" . $image_qr);
}

// Update BDD
$stmt = $pdo->prepare("
    UPDATE banner SET
        titre = ?,
        sous_titre = ?,
        image_fond = ?,
        image_qr = ?
    WHERE id = 1
");

$stmt->execute([
    $titre,
    $sous_titre,
    $image_fond,
    $image_qr
]);

header("Location: banner_edit.php?success=1");
exit;
