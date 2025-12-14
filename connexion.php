<?php
session_start();
require_once 'ans-design-backoffice/config/db.php';

$error = '';

// --- SI DÉJÀ CONNECTÉ ---
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: ans-design-backoffice/dashboard.php');
    } else {
        header('Location: mon-compte.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* ------------------------------------------------------
       1️⃣ TRAITEMENT DU FORMULAIRE DE CONNEXION
    -------------------------------------------------------- */
    if (isset($_POST['login_submit'])) {

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = "Veuillez remplir tous les champs.";
        } else {
            $stmt = $pdo->prepare("SELECT id, nom, prenom, email, password_hash, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['prenom'] = $user['prenom'];
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
       2️⃣ TRAITEMENT DU FORMULAIRE D'INSCRIPTION
    -------------------------------------------------------- */
    if (isset($_POST['register_submit'])) {

        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email_register'] ?? '');
        $password = trim($_POST['password_register'] ?? '');
        $password_confirm = trim($_POST['password_confirm'] ?? '');

        // Vérification des champs
        if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($password_confirm)) {
            $error = "Veuillez remplir tous les champs.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Adresse email invalide.";
        } elseif ($password !== $password_confirm) {
            $error = "Les mots de passe ne correspondent pas.";
        } else {

            // Vérifier si l'email existe déjà
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);

            if ($check->rowCount() > 0) {
                $error = "Cet email est déjà utilisé.";
            } else {

                // Création compte avec nom et prénom séparés
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                $insert = $pdo->prepare("
                    INSERT INTO users (nom, prenom, email, password_hash, role)
                    VALUES (?, ?, ?, ?, 'client')
                ");
                $insert->execute([$nom, $prenom, $email, $password_hash]);

                // Auto-login après inscription
                $user_id = $pdo->lastInsertId();
                $_SESSION['user_id'] = $user_id;
                $_SESSION['nom'] = $nom;
                $_SESSION['prenom'] = $prenom;
                $_SESSION['role'] = 'client';

                // Redirection espace client
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
                        <h2>Accédez à <span> votre espace</span></h2>
                    </div>
                    <div class="container-connexion-form">
                        <ul class="tabslink-connexion">
                            <li><a href="#connexion">Connexion</a></li>
                            <li><a href="#inscription">Inscription</a></li>
                        </ul>

                        <!-- AFFICHER LE MESSAGE D'ERREUR ICI -->
                        <?php if (!empty($error)): ?>
                            <p style="color: #D32F2F; background-color: #FFEBEE; padding: 10px; border-radius: 4px; margin-top: 1rem; border: 1px solid #D32F2F;">
                                <?php echo htmlspecialchars($error); ?>
                            </p>
                        <?php endif; ?>

                        <div class="tabscontent-connexion" id="connexion">
                            <!-- Le formulaire poste sur la page elle-même (connexion.php) -->
                            <form action="connexion.php" method="post">
                                <!-- J'ai changé name="mail" en name="email" pour être cohérent avec le PHP -->
                                <input type="email" placeholder="Votre email.." name="email" required>
                                <input type="password" placeholder="Votre mot de passe" name="password" required>
                                <!-- On ajoute un 'name' au bouton pour identifier le formulaire soumis -->
                                <button type="submit" name="login_submit" class="btn-yellow">Se connecter</button>
                            </form>
                            <div class="lost-password">
                                <a href="reset-password.php">Mot de passe oublié</a>
                                <label class="remember">
                                    <input id="remember" type="checkbox" />
                                    <span>Se souvenir de moi</span>
                                </label>
                            </div>
                        </div>
                        <div class="tabscontent-connexion" id="inscription">
                            <!-- Pensez à ajouter un name="register_submit" à ce bouton quand vous coderez l'inscription -->
                            <form action="connexion.php" method="post">
                                <input type="text" placeholder="Votre nom" name="nom">
                                <input type="text" placeholder="Votre prénom" name="prenom">
                                <input type="email" placeholder="Votre email.." name="email_register">
                                <input type="password" placeholder="Votre mot de passe" name="password_register">
                                <input type="password" placeholder="Confirmez votre mot de passe" name="password_confirm">
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
    <!-- FOOTER -->
<?php 
    // On inclut le footer
    include 'footer.php'; 
?>