<?php
session_start();
require_once 'ans-design-backoffice/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

$client_id = $_SESSION['user_id'];
$commandes = [];
$user_info = [];

try {
    // 1. Récupérer les informations de l'utilisateur connecté (pour la section "Facturé à")
    // Je suppose que vous avez une colonne 'adresse' dans votre table users. Sinon, adaptez.
    $stmt_user = $pdo->prepare("SELECT nom, prenom, email FROM users WHERE id = ?");
    $stmt_user->execute([$client_id]);
    $user_info = $stmt_user->fetch(PDO::FETCH_ASSOC);
    $user_info['nom_complet'] = trim($user_info['prenom'] . ' ' . $user_info['nom']);
    // Vous devrez peut-être ajouter une requête pour l'adresse si elle est dans une autre table

    // 2. Récupérer toutes les commandes de l'utilisateur
    $stmt_commandes = $pdo->prepare("SELECT * FROM commandes WHERE client_id = ? ORDER BY date_commande DESC");
    $stmt_commandes->execute([$client_id]);
    $commandes_data = $stmt_commandes->fetchAll(PDO::FETCH_ASSOC);

    // 3. Pour chaque commande, récupérer ses articles et leurs options (logique inchangée)
    foreach ($commandes_data as $commande) {
        $commande_id = $commande['id'];
        
        $stmt_articles = $pdo->prepare("SELECT id, description, quantite, prix_unitaire FROM commande_articles WHERE commande_id = ?");
        $stmt_articles->execute([$commande_id]);
        $articles_data = $stmt_articles->fetchAll(PDO::FETCH_ASSOC);
        
        $articles_complets = [];
        foreach ($articles_data as $article) {
            $article_id = $article['id'];
            $stmt_options = $pdo->prepare("SELECT caracteristique_nom, valeur_choisie FROM commande_article_options WHERE article_id = ?");
            $stmt_options->execute([$article_id]);
            $article['options'] = $stmt_options->fetchAll(PDO::FETCH_ASSOC);
            $articles_complets[] = $article;
        }
        
        $commande['articles'] = $articles_complets;
        $commandes[] = $commande;
    }

} catch (Exception $e) {
    die("Erreur lors de la récupération de l'historique : " . $e->getMessage());
}
?>

<?php include 'header.php'; ?>
    <main>
        <section id="mon-compte" class="scrolltop">
            <div class="wrapper">
                <div class="title">
                    <div class="section-title"><h2>Mon Compte</h2></div>
                    <div class="inner-title"><p>Connecté en tant que : <?php echo htmlspecialchars($user_info['nom_complet'] ?? 'Client'); ?></p></div>
                </div>

                <?php 
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="success-message" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">' . $_SESSION['success_message'] . '</div>';
                    unset($_SESSION['success_message']);
                }
                ?>

                <div class="historique-des-commandes">
                    <h3>Historique des commandes</h3>
                    <div class="accordeon">
                        <?php if (empty($commandes)): ?>
                            <p>Vous n'avez pas encore passé de commande.</p>
                        <?php else: ?>
                            <?php foreach ($commandes as $commande): ?>
                                <div class="accordeon-card-compte">
                                    <div class="accordeon-title-compte">
                                        <div class="order-detail">
                                            <h4>#<?php echo htmlspecialchars($commande['numero_commande']); ?></h4>
                                            <span><?php echo date('d/m/Y', strtotime($commande['date_commande'])); ?></span>
                                        </div>
                                        <div class="order-etat en-attente">
                                            <span><?php echo htmlspecialchars($commande['statut']); ?></span>
                                        </div>
                                        <div class="order-devis">
                                            <span>
                                                <?php 
                                                    echo ($commande['total_ttc'] == 0) ? 'Sur Devis' : number_format($commande['total_ttc'], 0, ',', '.') . ' AR';
                                                ?>
                                            </span>
                                        </div>
                                        <div class="order-arrow"><img src="assets/img/fleche.svg" alt=""></div>
                                    </div>
                                    <div class="accordeon-content-compte">
                                        <div class="order-summary">
                                            <div class="header-order-summary">
                                                <div class="society"><h5>ANS Design Print</h5><span>Votre partenaire impression</span></div>
                                                <div class="order-detail">
                                                    <h5>Commande #<?php echo htmlspecialchars($commande['numero_commande']); ?></h5>
                                                    <span><?php echo date('d/m/Y', strtotime($commande['date_commande'])); ?></span>
                                                </div>
                                            </div>
                                            <div class="body-order-summary">
                                                <div class="facture">
                                                    <span>Facturé à :</span><br>
                                                    <span class="name"><?php echo htmlspecialchars($user_info['nom_complet']); ?></span><br>
                                                    <!-- Adaptez ici si vous avez l'adresse dans la table `users` -->
                                                    <span class="location"><?php /* echo htmlspecialchars($user_info['adresse']); */ ?></span>
                                                </div>
                                                <div class="devis-table">
                                                    <!-- ... (le reste de l'affichage de la table des articles est identique et correct) ... -->
                                                    <?php foreach ($commande['articles'] as $article): ?>
                                                    <div class="devis-row">
                                                        <div class="devis-col description">
                                                            <strong><?php echo htmlspecialchars($article['description']); ?></strong><br>
                                                            <span class="details">
                                                                <?php
                                                                $details_array = [];
                                                                foreach ($article['options'] as $option) {
                                                                    $details_array[] = htmlspecialchars($option['caracteristique_nom']) . ': ' . htmlspecialchars($option['valeur_choisie']);
                                                                }
                                                                echo implode(', ', $details_array);
                                                                ?>
                                                            </span>
                                                        </div>
                                                        <div class="devis-col quantite"><?php echo $article['quantite']; ?></div>
                                                        <div class="devis-col total">
                                                            <?php
                                                                $total_article = $article['prix_unitaire'] * $article['quantite'];
                                                                echo ($article['prix_unitaire'] == 0) ? 'Sur Devis' : number_format($total_article, 0, ',', '.') . ' AR';
                                                            ?>
                                                        </div>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>
<?php include 'footer.php'; ?>