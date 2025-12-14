<?php
session_start();

// 1. Supprimer toutes les variables de session
$_SESSION = array();

// 2. Détruire le cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Finalement, détruire la session
session_destroy();

// 4. Rediriger vers la NOUVELLE page de connexion
header('Location: /../connexion.php');
exit();
?>