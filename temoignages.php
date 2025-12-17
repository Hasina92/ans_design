<?php
require_once 'ans-design-backoffice/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $prenom = $_POST['prenom'];
    $poste = $_POST['poste'] ?? '';
    $entreprise = $_POST['entreprise'] ?? '';
    $avis = $_POST['avis'];
    $note = isset($_POST['note']) ? (int) $_POST['note'] : 5;

    $photo = null;
    $uploadDir = __DIR__ . '/uploads/temoignages/';

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {

        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {
            $photo = uniqid('photo_') . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $photo);
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO temoignages (prenom, poste, entreprise, avis, note, photo, valide)
        VALUES (?, ?, ?, ?, ?, ?, 0)
    ");

    $stmt->execute([
        $prenom,
        $poste,
        $entreprise,
        $avis,
        $note,
        $photo
    ]);

    header("Location: index.php#temoignages");
    exit;
}
