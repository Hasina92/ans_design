<?php
require_once 'config/db.php';

$username = 'admin';
$password = 'azerty';

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'Admin')");
if ($stmt->execute([$username, $password_hash])) {
    echo "Utilisateur 'admin' créé avec succès. Mot de passe : password123";
} else {
    echo "Erreur lors de la création de l'utilisateur.";
}
?>