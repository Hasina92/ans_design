<?php
$host = 'localhost';  
$dbname = 'nemo5160_ans_design';
$user = 'nemo5160_ans_design-admin';
$pass = '.;k)lp+K5+sL'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    // Définir le mode d'erreur PDO sur Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Empêcher les requêtes préparées émulées pour une meilleure sécurité
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
