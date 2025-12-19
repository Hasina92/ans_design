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

//PLANNING
$stmt = $pdo->prepare("
    SELECT c.id AS commande_id, ca.description, c.date_realisation_estimee
    FROM commandes c
    LEFT JOIN commande_articles ca ON c.id = ca.commande_id
    ORDER BY c.date_realisation_estimee ASC
    LIMIT 3
");
$stmt->execute();
$commandes_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// MEILLEURS VENTES
$stmt = $pdo->prepare("
    SELECT ca.description AS produit, SUM(ca.quantite) AS total_vendu
    FROM commande_articles ca
    GROUP BY ca.description
    ORDER BY total_vendu DESC
    LIMIT 3
");
$stmt->execute();
$topProduits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Trouver la quantité maximale pour normaliser les barres
$maxQuantite = max(array_column($topProduits, 'total_vendu'));

//RETARD LIVRAISON
// Total des commandes
$stmt = $pdo->prepare("SELECT COUNT(*) as total_commandes FROM commandes");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$totalCommandes = $row['total_commandes'] ?? 0;

// Commandes en retard
$stmt = $pdo->prepare("SELECT COUNT(*) as retard_commandes FROM commandes WHERE date_realisation_estimee < CURDATE()");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$commandesRetard = $row['retard_commandes'] ?? 0;


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

// Compter le nombre total de clients
$stmt = $pdo->prepare("SELECT COUNT(*) as total_clients FROM users");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$totalClients = $row['total_clients'] ?? 0;

//SATISFACTION CLIENTS
$stmt = $pdo->prepare("
    SELECT AVG(note) as note_moyenne
    FROM temoignages
    WHERE valide = 1
");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$noteMoyenne = $row['note_moyenne'] ?? 0;
$noteMoyenne = round($noteMoyenne, 1);

//NOMBRE AVIS VALIDE
$stmt = $pdo->prepare("SELECT COUNT(*) as total_avis FROM temoignages WHERE valide = 1");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$totalAvis = $row['total_avis'] ?? 0;

// Arrondir à 1 ou 2 décimales
$noteMoyenne = round($noteMoyenne, 1);

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
                            <td><?php echo number_format($cmd['total_ttc'], 0, ',', ' '); ?> AR</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="statistique" style="margin-top: 40px; display: grid; grid-template-columns: 1fr 1fr 2fr; gap: 20px;">
    <div class="client"
        style="padding: 20px; border-radius: 30px; box-shadow: 0px 0px 7.5px 0px rgba(0, 0, 0, 0.1); background: #fff;">
        <span style="font-family: 'gilroy-extrabold'; font-size: 20px; color: #F47C2C;">Nouveaux Clients</span>
        <br>
        <span style="font-size: 30px; font-family: 'gilroy-extrabold';"><?php echo $totalClients; ?></span>
        <br>
        <span style="font-size: 18px; font-family: 'gilroy-regular';">par rapport au mois dernier</span>
    </div>
    <div class="satisfaction"
        style="padding: 20px; border-radius: 30px; box-shadow: 0px 0px 7.5px 0px rgba(0, 0, 0, 0.1); background: #fff;">
        <span style="font-family: 'gilroy-extrabold'; font-size: 20px; color: #E62F6D;">Satisfaction Client</span>
        <br>
        <?php
        $noteMoyenne = 4.2; // exemple, ta variable déjà calculée
        $nombreEtoiles = 5;
        ?>

        <div style="display:flex; align-items:center; gap:10px; font-family:'gilroy',sans-serif;">
            <span style="font-size:30px; font-family: 'gilroy-extrabold';"><?php echo $noteMoyenne; ?> / 5</span>
            <span style="display:flex; gap:3px;">
                <?php
                for ($i = 1; $i <= $nombreEtoiles; $i++) {
                    if ($i <= floor($noteMoyenne)) {
                        // étoiles pleines
                        echo '<span style="color:#FFD700; font-size:24px;">★</span>';
                    } elseif ($i - $noteMoyenne < 1) {
                        // étoile demi (optionnel)
                        echo '<span style="color:#FFD700; font-size:24px;">⯨</span>'; // demi-étoile approximative
                    } else {
                        // étoiles vides
                        echo '<span style="color:#ccc; font-size:24px;">★</span>';
                    }
                }
                ?>
            </span>
        </div>
        <br>
        <span style="font-size: 18px; font-family: 'gilroy-regular';">Basé sur <?php echo $totalAvis; ?> avis
            récents</span>
    </div>
    <div class="planning"
        style="padding: 20px; border-radius: 30px; box-shadow: 0px 0px 7.5px 0px rgba(0, 0, 0, 0.1); background: #fff;">
        <span style="font-family: 'gilroy-extrabold'; font-size: 20px; color: #F22F2F;">Alerte Planning</span>
        <table
            style="width:100%; border-collapse: collapse; font-family: 'gilroy', sans-serif; margin-top:20px; box-shadow:0 4px 10px rgba(0,0,0,0.1); border-radius:10px; overflow:hidden;">
            <thead style="background-color:#F22F2F; color:#fff;">
                <tr>
                    <th style="padding:12px 15px; text-align:left; font-weight:bold; font-size:16px;">N° Commande</th>
                    <th style="padding:12px 15px; text-align:left; font-weight:bold; font-size:16px;">Taches
                        Article</th>
                    <th style="padding:12px 15px; text-align:left; font-weight:bold; font-size:16px;">Echéances</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commandes_articles as $index => $item): ?>
                    <tr style="background-color:<?php echo $index % 2 == 0 ? '#fff' : '#f7f7f7'; ?>; transition: background-color 0.3s;"
                        onmouseover="this.style.backgroundColor='#ffe6e6';"
                        onmouseout="this.style.backgroundColor='<?php echo $index % 2 == 0 ? '#fff' : '#f7f7f7'; ?>';">
                        <td style="padding:12px 15px; font-size:14px; color:#333;"><?php echo $item['commande_id']; ?></td>
                        <td style="padding:12px 15px; font-size:14px; color:#333;"><?php echo $item['description']; ?></td>
                        <td style="padding:12px 15px; font-size:14px; color:#333; font-family: 'gilroy-extrabold'">
                            <?php echo date('Y-m-d', strtotime($item['date_estimee'] ?? $item['date_realisation_estimee'])); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="statistique" style="margin-top: 40px; display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 20px;">
    <div
        style="background:#fff; border-radius:15px; padding:20px; box-shadow:0 4px 10px rgba(0,0,0,0.1); font-family:'gilroy',sans-serif;">
        <span style="font-family: 'gilroy-extrabold'; font-size: 20px; color: #E62F6D; margin-bottom: 10px;">Meilleures
            ventes</span>
        <br>
        <?php foreach ($topProduits as $produit):
            $pourcentage = ($produit['total_vendu'] / $maxQuantite) * 100;
            ?>
            <div style="margin-bottom:15px;">
                <span style="font-weight:bold; color:#333;"><?php echo $produit['produit']; ?></span>
                <span style="float:right; color:#555;"><?php echo $produit['total_vendu']; ?> vendus</span>
                <div style="background:#f0f0f0; height:8px; border-radius:5px; margin-top:5px; overflow:hidden;">
                    <div style="width:<?php echo $pourcentage; ?>%; background:#E12948; height:100%; border-radius:5px;">
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div
        style="background:#fff; border-radius:15px; padding:20px; box-shadow:0 4px 10px rgba(0,0,0,0.1); font-family:'gilroy',sans-serif;">
        <span style="font-family: 'gilroy-extrabold'; font-size: 20px;color: #F47C2C;margin-bottom: 10px;">Livraisons
            en retard</span>
        <br>
        <span style="font-size:24px; font-weight:bold;"><?php echo $commandesRetard; ?> /
            <?php echo $totalCommandes; ?></span> <br>
        <span style="font-size: 18px; font-family: 'gilroy-regular';">Basé sur dates promises</span>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>