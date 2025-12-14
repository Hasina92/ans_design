<?php
session_start();
header('Content-Type: application/json');

// Connexion à la base
require_once 'ans-design-backoffice/config/db.php';

try {
    if (!isset($_POST['nom'], $_POST['email'], $_POST['message'])) {
        throw new Exception("Veuillez remplir les champs obligatoires.");
    }

    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone'] ?? '');
    $message = trim($_POST['message']);

    // L'ID du client connecté (ou null si visiteur)
    $user_id = $_SESSION['user_id'] ?? null;

    // Requête
    $stmt = $pdo->prepare("
        INSERT INTO demandes_devis (user_id, nom, email, telephone, message)
        VALUES (:user_id, :nom, :email, :telephone, :message)
    ");

    $stmt->execute([
        ':user_id'    => $user_id,
        ':nom'        => $nom,
        ':email'      => $email,
        ':telephone'  => $telephone,
        ':message'    => $message
    ]);

    echo json_encode([
        'success' => true,
        'message' => "Votre demande de devis a été envoyée."
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
