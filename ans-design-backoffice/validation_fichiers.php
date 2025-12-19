<?php
$pageTitle = 'Validation Fichiers';
$currentPage = 'validation';
require_once 'includes/header.php';
require_once 'config/db.php';

// LOGIQUE : Traitement du formulaire (Inchangé)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commande_id'])) {
    $commande_id = $_POST['commande_id'];
    $new_status = isset($_POST['valider']) ? 'En production' : 'Annulé';

    $date_realisation = $_POST['date_realisation'] ?? null;
    $heure_realisation = $_POST['heure_realisation'] ?? null;
    $remarques_generales = $_POST['remarques_generales'] ?? '';
    $message_client = $_POST['message_client'] ?? '';

    $datetime_estimee = null;
    if (!empty($date_realisation) && !empty($heure_realisation)) {
        $datetime_estimee = $date_realisation . ' ' . $heure_realisation . ':00';
    }

    $sql = "UPDATE commandes 
            SET 
                statut = ?, 
                date_realisation_estimee = ?, 
                notes_production = ?, 
                notes_client = ?
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$new_status, $datetime_estimee, $remarques_generales, $message_client, $commande_id]);

    header("Location: validation_fichiers.php?status=updated&commande_id=" . $commande_id);
    exit();
}

// LOGIQUE : Récupère les détails d'une commande (ADAPTÉ)
$commande_details = null;
// Tableau pour stocker tous les articles de la commande (car une commande peut avoir plusieurs lignes)
$articles_commande = [];

if (isset($_GET['commande_id'])) {
    // 1. Récupérer les infos générales de la commande + client
    $stmt = $pdo->prepare("
        SELECT 
            c.*, 
            u.nom, u.prenom
        FROM commandes c
        JOIN users u ON c.client_id = u.id
        WHERE c.id = ?
    ");
    $stmt->execute([$_GET['commande_id']]);
    $commande_details = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Récupérer TOUS les articles de cette commande
    if ($commande_details) {
        $stmt_articles = $pdo->prepare("
            SELECT description, quantite, donnees_personnalisees
            FROM commande_articles
            WHERE commande_id = ?
        ");
        $stmt_articles->execute([$_GET['commande_id']]);
        $articles_commande = $stmt_articles->fetchAll(PDO::FETCH_ASSOC);
    }
}

// LOGIQUE : Récupère la liste des commandes en attente (ADAPTÉ)
// On sélectionne juste la commande, pas besoin de jointure complexe ici pour l'affichage liste
$stmt = $pdo->prepare("
    SELECT c.id, u.nom, u.prenom
    FROM commandes c
    JOIN users u ON c.client_id = u.id
    WHERE c.statut = 'En validation' OR c.statut = 'En attente devis'
    ORDER BY c.date_commande DESC
");
$stmt->execute();
$commandes_a_valider = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <h1>Validation commande du client</h1>
</div>

<div class="main-panels">
    <!-- PANNEAU GAUCHE : LISTE -->
    <div class="panel-validation">
        <div class="panel-header">
            <h3>Commandes à Valider (<?php echo count($commandes_a_valider); ?>)</h3>
        </div>
        <div class="panel-body">
            <?php if (empty($commandes_a_valider)): ?>
                <p style="padding: 15px; color: #555;">Aucune commande en attente.</p>
            <?php else: ?>
                <?php foreach ($commandes_a_valider as $cmd): ?>
                    <a href="validation_fichiers.php?commande_id=<?php echo $cmd['id']; ?>"
                        class="commande-item <?php echo (isset($_GET['commande_id']) && $_GET['commande_id'] == $cmd['id']) ? 'active' : ''; ?>"
                        style="display:block; padding:10px; border-bottom:1px solid #eee; text-decoration:none; color:#333; <?php echo (isset($_GET['commande_id']) && $_GET['commande_id'] == $cmd['id']) ? 'background-color:#fcf3cf;' : ''; ?>">
                        <strong>#<?php echo htmlspecialchars($cmd['id']); ?></strong><br>
                        <?php echo htmlspecialchars($cmd['prenom'] . ' ' . $cmd['nom']); ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- PANNEAU DROIT : DÉTAILS -->
    <div class="panel-commande">
        <?php if ($commande_details): ?>
            <div class="panel-header" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h3>Commande #<?php echo htmlspecialchars($commande_details['numero_commande']); ?></h3>
                    <p class="yellow">
                        <?php echo htmlspecialchars($commande_details['prenom'] . ' ' . $commande_details['nom']); ?>
                    </p>
                    <p class="gray">Statut actuel: <?php echo htmlspecialchars($commande_details['statut']); ?></p>
                </div>
                <div class="number">
                    <?php echo number_format($commande_details['total_ttc'], 0, ',', ' '); ?> AR
                </div>
            </div>

            <div class="panel-body"
                style="display: flex; gap: 20px; margin: 0; padding: 20px 0 0 0; border-top: 3px #898989 solid !important; margin-top: 20px;">
                <!-- COLONNE GAUCHE : VISUALISATION DES FICHIERS -->
                <div style="flex: 1; min-width: 300px;">
                    <h4 style="margin-top:0;">Articles & Fichiers</h4>

                    <?php if (empty($articles_commande)): ?>
                        <p>Aucun article trouvé pour cette commande.</p>
                    <?php else: ?>
                        <?php foreach ($articles_commande as $article): ?>
                            <?php
                            // Décodage JSON
                            $data = json_decode($article['donnees_personnalisees'] ?? '{}', true);
                            $msg_client = $data['message_client'] ?? '';
                            $fichiers = $data['fichiers'] ?? [];
                            $path_prefix = '../'; // Adaptez selon l'emplacement de votre fichier PHP (ex: '../' si dans un sous-dossier)
                            ?>

                            <div class="article-block"
                                style="border:1px solid #ddd; border-radius:8px; margin-bottom:15px; background:#fff;">
                                <h5 style="margin:0 0 10px 0; color:#333; border-bottom:1px solid #eee; padding-bottom:5px;">
                                    <?php echo htmlspecialchars($article['description']); ?> (x<?php echo $article['quantite']; ?>)
                                </h5>

                                <!-- Note Client Spécifique -->
                                <?php if (!empty($msg_client)): ?>
                                    <div
                                        style="background:#f9f9f9; padding:10px; border-left:3px solid #8E44AD; margin-bottom:10px; font-size:0.9em;">
                                        <strong>Note client :</strong> <?php echo nl2br(htmlspecialchars($msg_client)); ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Fichiers -->
                                <!-- Fichiers -->
                                <?php if (!empty($fichiers)): ?>
                                    <div style="display:flex; flex-wrap:wrap; gap:10px;">
                                        <?php foreach ($fichiers as $chemin): ?>
                                            <?php
                                            $nom_fichier = basename($chemin);
                                            $chemin_reel = $path_prefix . $chemin;
                                            $ext = strtolower(pathinfo($nom_fichier, PATHINFO_EXTENSION));
                                            $is_img = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                            ?>
                                            <div style="text-align:center; width:100%;">
                                                <a href="<?php echo htmlspecialchars($chemin_reel); ?>" target="_blank">
                                                    <?php if ($is_img): ?>
                                                        <img src="<?php echo htmlspecialchars($chemin_reel); ?>"
                                                            style="width:100%; height:300px; object-fit:contain; border:1px solid #ccc; border-radius:4px; padding: 20px; border: 1px #898989 dashed !important;">
                                                    <?php else: ?>
                                                        <div
                                                            style="width:100px; height:100px; background:#eee; display:flex; align-items:center; justify-content:center; border:1px solid #ccc;">
                                                            📎</div>
                                                    <?php endif; ?>
                                                </a>
                                                <a href="<?php echo htmlspecialchars($chemin_reel); ?>"
                                                    download="<?php echo htmlspecialchars($nom_fichier); ?>"
                                                    style="display:block; font-size:11px; margin-top:5px; text-decoration:none; color:white;     background: linear-gradient(90deg, #e50051 26.25%, #e94f35 84.96%);
                                                    width: 100%; padding: 20px; border-radius: 30px; margin-top: 20px; font-size: 18px;">
                                                    Télécharger le fichier
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p>Aucun fichier joint.</p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- COLONNE DROITE : FORMULAIRE -->
                <div style="flex: 1;">
                    <form method="POST">
                        <input type="hidden" name="commande_id" value="<?php echo $commande_details['id']; ?>">

                        <h4 style="margin-top:0;">Traitement Commande</h4>

                        <div class="message-retour-client">
                            <div class="flex" style="display:flex; gap:10px; margin-bottom:15px;">
                                <div style="flex: 1;">
                                    <label for="date_realisation">Date de Réalisation Estimée</label>
                                    <?php $date_value = $commande_details['date_realisation_estimee'] ? date('Y-m-d', strtotime($commande_details['date_realisation_estimee'])) : ''; ?>
                                    <input type="date" id="date_realisation" name="date_realisation"
                                        value="<?php echo $date_value; ?>" style="width: 100%; padding: 8px;">
                                </div>
                                <div style="flex: 1;">
                                    <label for="heure_realisation">Heure</label>
                                    <?php $heure_value = $commande_details['date_realisation_estimee'] ? date('H:i', strtotime($commande_details['date_realisation_estimee'])) : ''; ?>
                                    <input type="time" id="heure_realisation" name="heure_realisation"
                                        value="<?php echo $heure_value; ?>" style="width: 100%; padding: 8px;">
                                </div>
                            </div>

                            <div class="remarque-clients" style="margin-bottom:15px;">
                                <label for="remarques_generales">Remarques Internes (Production)</label>
                                <textarea id="remarques_generales" name="remarques_generales" rows="4" style="width:100%;"
                                    placeholder="Notes pour l'équipe..."><?php echo htmlspecialchars($commande_details['notes_production'] ?? ''); ?></textarea>
                            </div>

                            <div class="validation-clients" style="margin-bottom:20px;">
                                <label for="message_client">Message pour le Client</label>
                                <textarea id="message_client" name="message_client" rows="4" style="width:100%;"
                                    placeholder="Visible par le client sur son espace..."><?php echo htmlspecialchars($commande_details['notes_client'] ?? ''); ?></textarea>
                            </div>

                            <?php if ($commande_details['statut'] == 'En validation' || $commande_details['statut'] == 'En attente devis'): ?>
                                <div class="containair-btn" style="display:flex; justify-content:space-between;">
                                    <button type="submit" name="rejeter" class="btn-rejeter"
                                        style="background:#e74c3c; color:white; border:none; padding:10px 20px; cursor:pointer; border-radius:4px;">Rejeter
                                        / Annuler</button>
                                    <button type="submit" name="valider" class="btn-valider"
                                        style="background:#2ecc71; color:white; border:none; padding:10px 20px; cursor:pointer; border-radius:4px;">Valider
                                        & Produire</button>
                                </div>
                            <?php else: ?>
                                <div
                                    style="padding:10px; background:#e8f8f5; color:#27ae60; text-align:center; border-radius:4px;">
                                    <strong>Commande déjà traitée</strong><br>
                                    Statut actuel : <?php echo htmlspecialchars($commande_details['statut']); ?>
                                </div>
                                <div style="margin-top:10px; text-align:right;">
                                    <button type="submit" name="update"
                                        style="background:#3498db; color:white; border:none; padding:8px 15px; cursor:pointer; border-radius:4px;">Mettre
                                        à jour les notes</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

        <?php else: ?>
            <div style="text-align:center; padding:50px; color:#777;">
                <p>Sélectionnez une commande dans la liste de gauche pour afficher les détails et les fichiers.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>