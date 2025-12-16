<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../connexion.php');
    exit();
}

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
require_once 'includes/header.php';
require_once 'config/db.php';
require_once 'includes/functions.php';

// --- Requêtes qui ne changent pas ---
// Chiffre d'Affaires (Jour)
$stmt = $pdo->prepare("SELECT SUM(total_ttc) as ca_jour FROM commandes WHERE statut NOT IN ('Annulé', 'En validation') AND date_commande >= CURDATE() AND date_commande < CURDATE() + INTERVAL 1 DAY");
$stmt->execute();
$ca_jour = $stmt->fetch(PDO::FETCH_ASSOC)['ca_jour'] ?? 0;

// Chiffre d'Affaires (Mois)
$stmt = $pdo->prepare("SELECT SUM(total_ttc) as ca_mois FROM commandes WHERE statut NOT IN ('Annulé', 'En validation') AND date_commande >= DATE_FORMAT(NOW(), '%Y-%m-01') AND date_commande < DATE_FORMAT(NOW(), '%Y-%m-01') + INTERVAL 1 MONTH");
$stmt->execute();
$ca_mois = $stmt->fetch(PDO::FETCH_ASSOC)['ca_mois'] ?? 0;

// Commandes en Production
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM commandes WHERE statut = 'En production'");
$stmt->execute();
$commandes_production = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Avis Clients à Valider
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM commandes WHERE statut = 'Avis à valider'");
$stmt->execute();
$avis_valider = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// --- Requêtes modifiées ---

// Fichiers à Valider (MODIFIÉ)
$stmt = $pdo->prepare("
    SELECT c.id, u.nom, u.prenom, ca.description
    FROM commandes c
    JOIN users u ON c.client_id = u.id -- MODIFICATION : On rejoint la table `users`
    LEFT JOIN commande_articles ca ON c.id = ca.commande_id
    WHERE c.statut = 'En validation'
    GROUP BY c.id -- Ajouté pour éviter les doublons si une commande a plusieurs articles
");
$stmt->execute();
$fichiers_a_valider = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Dernières commandes (MODIFIÉ)
$stmt = $pdo->prepare("
    SELECT c.id, u.nom, u.prenom, ca.description, c.statut, c.total_ttc
    FROM commandes c
    JOIN users u ON c.client_id = u.id -- MODIFICATION : On rejoint la table `users`
    LEFT JOIN commande_articles ca ON c.id = ca.commande_id
    ORDER BY c.date_commande DESC
    LIMIT 6
");
$stmt->execute();
$dernieres_commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- La partie HTML reste exactement la même, elle fonctionnera avec les nouvelles données -->

<div class="page-header">
    <h1>Dashboard</h1>
</div>

<div class="cards-container">
    <div class="card blue">
        <h2><?php echo number_format($ca_jour, 0, ',', ' '); ?> AR</h2>
        <p>Chiffre d'Affaires (Jour)</p>
    </div>
    <div class="card green">
        <h2><?php echo number_format($ca_mois, 0, ',', ' '); ?> AR</h2>
        <p>Chiffre d'Affaires (Mois)</p>
    </div>
    <div class="card yellow">
        <h2><?php echo htmlspecialchars($commandes_production); ?></h2>
        <p>Commandes en Production</p>
    </div>
    <div class="card red">
        <h2><?php echo htmlspecialchars($avis_valider); ?></h2>
        <p>Avis Clients à Valider</p>
    </div>
</div>

<div class="main-panels">
    <div class="panel-fichier">
        <div>
            <div class="panel-header">
                <h3>Fichiers à Valider (<?php echo count($fichiers_a_valider); ?>)</h3>
            </div>
            <div class="panel-body">
                <?php if (empty($fichiers_a_valider)): ?>
                    <p>Aucun fichier à valider.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($fichiers_a_valider as $fichier): ?>
                            <li style="padding:10px; border-bottom:1px solid #eee;">
                                <strong>#<?php echo htmlspecialchars($fichier['id']); ?>
                                    <?php echo htmlspecialchars($fichier['prenom'] . ' ' . $fichier['nom']); ?></strong><br>
                                <small><?php echo htmlspecialchars($fichier['description']); ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <div class="panel-footer" style="margin-top: 20px; text-decoration: underline; color: #DF4D34;">
            <a href="validation_fichiers.php">Voir l'interface de validation &rarr;</a>
        </div>
    </div>

    <div class="panel-commande">
        <div class="panel-header">
            <h3>Dernières Commandes</h3>
        </div>
        <div class="panel-body">
            <table>
                <thead>
                    <tr>
                        <th>ID DEVIS</th>
                        <th>CLIENT</th>
                        <th>ARTICLES</th>
                        <th>STATUT</th>
                        <th>TOTAL CA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dernieres_commandes as $cmd): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($cmd['id']); ?></td>
                            <td><?php echo htmlspecialchars($cmd['prenom'] . ' ' . $cmd['nom']); ?></td>
                            <td><?php echo htmlspecialchars($cmd['description']); ?></td>
                            <td><span
                                    class="status <?php echo getStatusClass($cmd['statut']); ?>"><?php echo htmlspecialchars($cmd['statut']); ?></span>
                            </td>
                            <td><?php echo number_format($cmd['total_ttc'], 2, ',', ' '); ?> €</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>