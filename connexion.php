<?php
session_start();
require_once 'ans-design-backoffice/config/db.php';

$error = '';

/* =========================================================
   SI L'UTILISATEUR EST DÉJÀ CONNECTÉ
========================================================= */
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: ans-design-backoffice/dashboard.php');
    } else {
        header('Location: mon-compte.php');
    }
    exit;
}

/* =========================================================
   TRAITEMENT DES FORMULAIRES
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ------------------------------------------------------
       1️⃣ CONNEXION
    -------------------------------------------------------- */
    if (isset($_POST['login_submit'])) {

        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $error = "Veuillez remplir tous les champs.";
        } else {

            $stmt = $pdo->prepare("
                SELECT id, nom, prenom, email, telephone, societe, password_hash, role
                FROM users
                WHERE email = ?
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['prenom'] = $user['prenom'];
                $_SESSION['telephone'] = $user['telephone'];
                $_SESSION['societe'] = $user['societe'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header("Location: ans-design-backoffice/dashboard.php");
                } else {
                    header("Location: mon-compte.php");
                }
                exit;

            } else {
                $error = "Email ou mot de passe incorrect.";
            }
        }
    }

    /* ------------------------------------------------------
       2️⃣ INSCRIPTION
    -------------------------------------------------------- */
    if (isset($_POST['register_submit'])) {

        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email_register'] ?? '');
        $telephone = trim($_POST['phone_register'] ?? '');
        $societe = trim($_POST['societe_register'] ?? '');
        $adresse = trim($_POST['adresse_register'] ?? '');
        $password = trim($_POST['password_register'] ?? '');
        $password_confirm = trim($_POST['password_confirm'] ?? '');

        if (
            empty($nom) ||
            empty($prenom) ||
            empty($email) ||
            empty($password) ||
            empty($password_confirm)
        ) {
            $error = "Veuillez remplir tous les champs.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Adresse email invalide.";
        } elseif ($password !== $password_confirm) {
            $error = "Les mots de passe ne correspondent pas.";
        } elseif (!empty($telephone) && !preg_match('/^[0-9+\s.-]+$/', $telephone)) {
            $error = "Numéro de téléphone invalide.";
        } else {

            // Vérifier si l'email existe déjà
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);

            if ($check->rowCount() > 0) {
                $error = "Cet email est déjà utilisé.";
            } else {

                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $insert = $pdo->prepare("
                INSERT INTO users (nom, prenom, email, telephone, societe, adresse, password_hash, role)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'client')
            ");
                $insert->execute([
                    $nom,
                    $prenom,
                    $email,
                    $telephone,
                    $societe,
                    $adresse,       // <-- ici correspond à la colonne adresse
                    $password_hash  // <-- mot de passe
                ]);

                // Auto-login après inscription
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['nom'] = $nom;
                $_SESSION['prenom'] = $prenom;
                $_SESSION['telephone'] = $telephone;
                $_SESSION['societe'] = $societe;
                $_SESSION['adresse'] = $adresse;
                $_SESSION['role'] = 'client';

                header("Location: mon-compte.php");
                exit;
            }
        }
    }
}

include 'header.php';
?>

<!-- MAIN -->
<main>
    <section id="banner-connexion" class="scrolltop">
        <img src="assets/img/fond.png" alt="" class="bg-image">

        <div class="wrapper t-center">
            <div class="container-connexion-text">

                <div class="section-title">
                    <h2>Accédez à <span>votre espace</span></h2>
                </div>

                <div class="container-connexion-form">

                    <ul class="tabslink-connexion">
                        <li><a href="#connexion">Connexion</a></li>
                        <li><a href="#inscription">Inscription</a></li>
                    </ul>

                    <?php if (!empty($error)): ?>
                        <p
                            style="color:#D32F2F;background:#FFEBEE;padding:10px;border-radius:4px;margin-top:1rem;border:1px solid #D32F2F;">
                            <?= htmlspecialchars($error) ?>
                        </p>
                    <?php endif; ?>

                    <!-- CONNEXION -->
                    <div class="tabscontent-connexion" id="connexion">
                        <form action="connexion.php" method="post">
                            <input type="email" name="email" placeholder="Votre email" required>
                            <input type="password" name="password" placeholder="Votre mot de passe" required>
                            <button type="submit" name="login_submit" class="btn-yellow">Se connecter</button>
                        </form>
                        <div class="lost-password">
                            <a href="reset-password.php">Mot de passe oublié</a>
                        </div>
                    </div>

                    <!-- INSCRIPTION -->
                    <div class="tabscontent-connexion" id="inscription">
                        <form action="connexion.php" method="post">
                            <input type="text" name="nom" placeholder="Votre nom" required>
                            <input type="text" name="prenom" placeholder="Votre prénom" required>
                            <input type="email" name="email_register" placeholder="Votre email" required>
                            <input type="text" name="phone_register" placeholder="Votre numéro de téléphone">
                            <input type="text" name="adresse_register" placeholder="Votre adresse">
                            <input type="text" name="societe_register" placeholder="Votre société">
                            <input type="password" name="password_register" placeholder="Votre mot de passe" required>
                            <input type="password" name="password_confirm" placeholder="Confirmez votre mot de passe"
                                required>
                            <button type="submit" name="register_submit" class="btn-yellow">S'inscrire</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        <div class="container-connexion-img">
            <img src="assets/img/banner_mon_compte.png" alt="">
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>