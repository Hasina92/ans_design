<?php
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titre = $_POST['titre'] ?? '';
    $titre_2 = $_POST['titre_2'] ?? '';
    $sous_titre = $_POST['sous_titre'] ?? '';

    $stmt = $pdo->query("SELECT image_fond, image_qr FROM banner WHERE id = 1");
    $current = $stmt->fetch(PDO::FETCH_ASSOC);

    $image_fond = $current['image_fond'];
    $image_qr = $current['image_qr'];

    if (!empty($_FILES['image_fond']['name'])) {
        $image_fond = time() . '_' . $_FILES['image_fond']['name'];
        move_uploaded_file($_FILES['image_fond']['tmp_name'], "../assets/img/" . $image_fond);
    }

    if (!empty($_FILES['image_qr']['name'])) {
        $image_qr = time() . '_' . $_FILES['image_qr']['name'];
        move_uploaded_file($_FILES['image_qr']['tmp_name'], "../assets/img/" . $image_qr);
    }

    $stmt = $pdo->prepare("
        UPDATE banner SET
            titre = ?,
            titre_2 = ?,
            sous_titre = ?,
            image_fond = ?,
            image_qr = ?
        WHERE id = 1
    ");

    $stmt->execute([
        $titre,
        $titre_2,
        $sous_titre,
        $image_fond,
        $image_qr
    ]);

    header("Location: banner_edit.php?success=1");
    exit;
}
