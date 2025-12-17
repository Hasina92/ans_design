<?php
$pageTitle = 'Clients & Commandes';
$currentPage = 'clients';
require_once 'includes/header.php';
require_once 'config/db.php';
require_once 'includes/functions.php';

// --- MODIFICATIONS CI-DESSOUS ---

// Récupérer la liste de tous les utilisateurs avec le rôle 'client'
$stmt_users = $pdo->query("SELECT id, nom, prenom, email, telephone, adresse, societe  FROM users WHERE role = 'client' ORDER BY nom, prenom");
$users_as_clients = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// Gérer la sélection d'un utilisateur
$user_details = null;
$commandes_client = [];
if (isset($_GET['client_id'])) {
    $client_id = $_GET['client_id'];

    // Infos de l'utilisateur (au lieu du client)
    $stmt = $pdo->prepare("SELECT id, nom, prenom, email, telephone, adresse, societe  FROM users WHERE id = ?");
    $stmt->execute([$client_id]);
    $user_details = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si l'utilisateur a été trouvé, on récupère ses stats et commandes
    if ($user_details) {
        // Stats de l'utilisateur (identique, juste on utilise son ID)
        $stmt = $pdo->prepare("SELECT COUNT(*) as total_commandes, SUM(total_ttc) as total_depense FROM commandes WHERE client_id = ?");
        $stmt->execute([$client_id]);
        $client_stats = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_details = array_merge($user_details, $client_stats); // Fusionner les infos

        // Historique des commandes de l'utilisateur (identique, juste on utilise son ID)
        $sql_commandes = "
        SELECT 
            c.id,
            c.date_commande,
            c.statut,
            c.total_ttc,
            c.methode_paiement,
            c.details_paiement,
            GROUP_CONCAT(
                CONCAT(ca.description, ' (x', ca.quantite, ')')
                SEPARATOR ' • '
            ) AS articles_details
        FROM commandes c
        LEFT JOIN commande_articles ca ON c.id = ca.commande_id
        WHERE c.client_id = ?
    ";

        $params = [$client_id];

        if (!empty($_GET['statut'])) {
            $sql_commandes .= " AND c.statut = ?";
            $params[] = $_GET['statut'];
        }

        $sql_commandes .= "
        GROUP BY c.id
        ORDER BY c.date_commande DESC
    ";

        $stmt = $pdo->prepare($sql_commandes);
        $stmt->execute($params);
        $commandes_client = $stmt->fetchAll(PDO::FETCH_ASSOC);

    }
}
?>

<div class="page-header">
    <h1>Clients & Commandes</h1>
</div>

<div class="main-panels">
    <div class="panel-clients">
        <div class="panel-header">
            <h3>Sélectionner un Client</h3>
        </div>
        <div class="nom-client">
            <input type="text" id="clientSearchInput" placeholder="Nom client...">
        </div>
        <div class="panel-body" id="clientListContainer">
            <!-- On affiche la liste des utilisateurs -->
            <?php foreach ($users_as_clients as $user): ?>
                <a href="clients_commandes.php?client_id=<?php echo $user['id']; ?>"
                    style="padding:20px; background: #fff; box-shadow: 0px 0px 7.5px 0px rgba(0, 0, 0, 0.1019607843); border-radius: 10px;">
                    <strong><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></strong>
                    <br>
                    <!-- MODIFICATION : On affiche la société si elle existe -->
                    <small><?php echo htmlspecialchars($user['societe'] ?? ''); ?></small>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="panel-commande">
        <?php if ($user_details): ?>
            <div class="container-commande" style="display: flex; align-items: flex-start;">
                <div>
                    <h3>
                        <?php echo htmlspecialchars($user_details['prenom'] . ' ' . $user_details['nom']); ?>
                    </h3>
                    <p style="font-family: 'gilroy-bold'; color:#FDC420">
                        <?php echo htmlspecialchars($user_details['societe'] ?? 'Particulier'); ?>
                    </p>
                </div>
                <div class="commande">
                    <div class="total" style="padding: 10px 20px; border: 1px #707070 solid !important;">
                        <p style="color:#707070">Total Commandes</p>
                        <span style="font-size: 1.5em; font-weight: bold;"><?php echo $user_details['total_commandes']; ?>
                        </span>
                    </div>
                    <div class="depensé" style="padding: 10px 20px; border: 1px #707070 solid !important;">
                        <p style="color:#707070">Total Dépensé</p>
                        <span
                            style="font-size: 1.5em; font-weight: bold; color: #DF4D34;"><?php echo number_format($user_details['total_depense'] ?? 0, 0, ',', ' '); ?>
                            AR</span>
                    </div>
                </div>
                <!-- On retire la société, qui n'est pas dans la table `users` -->
                <small>
                    <a href="mailto:<?= htmlspecialchars($user_details['email']); ?>"
                        style="color: #DF4D34; font-size: 18px; font-family: 'gilroy-bold';">
                        <?= htmlspecialchars($user_details['email']); ?>
                    </a>
                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $user_details['telephone'] ?? ''); ?>"
                        style="color: #DF4D34; font-size: 18px; font-family: 'gilroy-bold';">
                        <?= htmlspecialchars($user_details['telephone'] ?? 'N/A'); ?>
                    </a>
                    <span style="color: #DF4D34; font-size: 18px; font-family: 'gilroy-bold'">
                        <?= htmlspecialchars($user_details['adresse'] ?? 'N/A'); ?>
                    </span>
                </small>
            </div>
            <div class="container-historique">
                <h4>Historique des commandes (<?php echo count($commandes_client); ?>)</h4>
                <div class="container-btn-historique">
                    <a href="clients_commandes.php?client_id=<?php echo $client_id; ?>"
                        style="font-family: 'gilroy-bold'; padding: 10px 20px; border: 1px #170303 solid !important;">Tous</a>
                    <a href="clients_commandes.php?client_id=<?php echo $client_id; ?>&statut=Livrée"
                        style="font-family: 'gilroy-bold'; padding: 10px 20px; border: 1px #170303 solid !important;">Livrée</a>
                    <a href="clients_commandes.php?client_id=<?php echo $client_id; ?>&statut=En production"
                        style="font-family: 'gilroy-bold'; padding: 10px 20px; border: 1px #170303 solid !important;">En
                        production</a>
                </div>

            </div>
            <table>
                <thead>
                    <tr>
                        <th style="text-align: left;">ID COMMANDE</th>
                        <th style="text-align: left;">DATE</th>
                        <th style="text-align: left;">STATUT</th>
                        <th style="text-align: left;">ARTICLES</th>
                        <th style="text-align: left;">TOTAL CA</th>
                        <th style="text-align: left;">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commandes_client as $cmd): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($cmd['id']); ?></td>
                            <td><?= date('Y-m-d', strtotime($cmd['date_commande'])); ?></td>
                            <td>
                                <span class="status <?= getStatusClass($cmd['statut']); ?>">
                                    <?= htmlspecialchars($cmd['statut']); ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($cmd['articles_details']); ?></td>
                            <td><?= number_format($cmd['total_ttc'], 0, ',', ' '); ?> AR</td>
                            <td>
                                <!-- Bouton Détails -->
                                <button class="details-btn" data-commande-id="<?= $cmd['id']; ?>"
                                    style="background: #FDC420;">Détails</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php else: ?>
            <p style="text-align:center; padding:50px;">Sélectionnez un client à gauche pour voir son historique.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Le Modal et le Footer restent inchangés -->
<div class="modal-overlay" id="detailsModal">
    <div class="modal-content">
        <button class="modal-close" id="closeModal">&times;</button>
        <div id="modalBody">
            Chargement...
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>