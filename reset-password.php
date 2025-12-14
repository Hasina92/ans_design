<?php
// cart_overlay.php
// On inclut le fichier qui récupère les infos
require_once 'init_user.php'; 
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="assets/css/fancybox.min.css">
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>ANS Design</title>
    <link rel="icon" href="assets/img/favicon.svg" type="image/x-icon">
</head>

<body>
<?php 
    // On inclut le header qui contient déjà session_start() et la logique d'auth
    include 'header.php'; 
?>
    <!-- MAIN -->
    <main>
        <!-- BANNER -->
        <section id="banner-reset-password" class="scrolltop">
            <img src="assets/img/fond.png" alt="" class="bg-image">
            <div class="wrapper t-center">
                <div class="container-reset-password">
                    <div class="section-title">
                        <h2>Réinitialiser le mot de passe</h2>
                    </div>
                    <form action="" method="post" id="resetPassword">
                        <input type="email" placeholder="Votre email" name="mail">
                        <button type="submit" class="btn-yellow" id="open-popup-reset-password">Envoyer</button>
                    </form>
                </div>
            </div>
        </section>
        <!-- POP-UP RESET PASSWORD -->
        <section class="popup pop-up-reset-password">
            <div class="pop-up-container">
                <div class="header-reset-password">
                    <img src="assets/img/check.svg" alt="">
                    <h3>
                        Un email a été envoyé !
                    </h3>
                </div>
                <div class="body-reset-password">
                    <a href="connexion.php" class="btn">Se connecter</a>
                </div>
                <a href="#close" id="close-popup-reset-password"><img src="assets/img/close.svg" alt=""></a>
            </div>
        </section>
    </main>
    <!-- FOOTER -->
<?php 
    // On inclut le footer
    include 'footer.php'; 
?>