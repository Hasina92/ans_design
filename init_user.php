<?php
// init_user.php

// 1. Démarrer la session si elle n'est pas active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Inclure la BDD si la variable $pdo n'existe pas encore
if (!isset($pdo)) {
    // Ajustez le chemin si nécessaire. Si ce fichier est à la racine, c'est bon.
    // Utiliser __DIR__ est plus sûr pour les chemins relatifs.
    require_once __DIR__ . '/ans-design-backoffice/config/db.php';
}

// 3. Initialisation des variables par défaut
$user_info = isset($user_info) ? $user_info : [];
$user_info['nom_complet'] = 'Client';
$nom_client = 'Client'; // Variable utilisée spécifiquement dans panier_action.php

// 4. Récupération des données si connecté
if (isset($_SESSION['user_id'])) {
    try {
        $stmt_init = $pdo->prepare("SELECT nom, prenom FROM users WHERE id = ?");
        $stmt_init->execute([$_SESSION['user_id']]);
        $u_init = $stmt_init->fetch(PDO::FETCH_ASSOC);

        if ($u_init) {
            $nom_complet_calcule = trim(($u_init['prenom'] ?? '') . ' ' . ($u_init['nom'] ?? ''));
            
            if (!empty($nom_complet_calcule)) {
                // On met à jour les deux variables pour la compatibilité avec tous vos fichiers
                $user_info['nom_complet'] = $nom_complet_calcule;
                $nom_client = $nom_complet_calcule;
            }
        }
    } catch (Exception $e) {
        // Erreur silencieuse
    }
}
?>