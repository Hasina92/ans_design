<?php
// Démarrer la session pour accéder à la variable $_SESSION
session_start();

// Vérifier si l'identifiant de l'utilisateur existe dans la session
if (isset($_SESSION['user_id'])) {
    // L'utilisateur est connecté, on le redirige vers le dashboard
    header('Location: dashboard.php');
    exit(); // Toujours appeler exit() après une redirection pour stopper l'exécution du script
} else {
    // L'utilisateur n'est pas connecté, on le redirige vers la page de connexion
    header('Location: login.php');
    exit();
}
?>