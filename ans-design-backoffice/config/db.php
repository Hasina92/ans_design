<?php

// Sécurité : empêche l'accès direct
if (!defined('APP_INIT')) {
    die('Accès interdit');
}

define('DB_HOST', 'localhost');
define('DB_NAME', 'nemo5160_ans_design');      // OK
define('DB_USER', 'nemo5160_ans_design-admin');      // ⚠️ sans tiret
define('DB_PASS', ')96.4XY_1Sit');       // vrai mot de passe

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    error_log('[DB ERROR] ' . $e->getMessage());
    die('Erreur de connexion à la base de données.');
}
