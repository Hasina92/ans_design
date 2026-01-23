<!-- ONLINE -->
<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'nemo5160_ans_design'); 
define('DB_USER', 'nemo5160_ans_design-admin');
define('DB_PASS', ')96.4XY_1Sit');  

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
?>