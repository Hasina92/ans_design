<?php
require_once 'config/db.php';

if (!isset($_POST['id'], $_POST['statut'])) {
    die("Champs manquants.");
}

$id = intval($_POST['id']);
$statut = $_POST['statut'];
$note_admin = $_POST['note_admin'] ?? '';

$stmt = $pdo->prepare("
    UPDATE demandes_devis 
    SET statut = :statut, note_admin = :note_admin
    WHERE id = :id
");

$stmt->execute([
    ':statut' => $statut,
    ':note_admin' => $note_admin,
    ':id' => $id
]);

header("Location: prestations.php?update=success");
exit;
