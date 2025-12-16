<?php
$pageTitle = 'Validation Fichiers';
$currentPage = 'validation';
require_once 'includes/header.php';
require_once 'config/db.php';

// LOGIQUE : Traitement du formulaire (Cette partie n'a pas besoin de modification)
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

    header("Location: validation_fichiers.php?status=updated");
    exit();
}

// --- MODIFICATIONS CI-DESSOUS ---

// LOGIQUE : Récupère les détails d'une commande pour affichage (MODIFIÉ)
$commande_details = null;
if (isset($_GET['commande_id'])) {
    $stmt = $pdo->prepare("
        SELECT 
            c.*, 
            u.nom, u.prenom,  -- On récupère depuis la table users (u)
            ca.description as article_desc, 
            cf.chemin_fichier, 
            cf.nom_fichier AS nom_fichier_original
        FROM commandes c
        JOIN users u ON c.client_id = u.id -- MODIFICATION : Jointure sur users
        LEFT JOIN commande_articles ca ON c.id = ca.commande_id
        LEFT JOIN commande_fichiers cf ON c.id = cf.commande_id
        WHERE c.id = ?
        GROUP BY c.id -- S'assure d'avoir une seule ligne même si plusieurs articles/fichiers
    ");
    $stmt->execute([$_GET['commande_id']]);
    $commande_details = $stmt->fetch(PDO::FETCH_ASSOC);
}

// LOGIQUE : Récupère la liste des commandes en attente (MODIFIÉ)
$stmt = $pdo->prepare("
    SELECT c.id, u.nom, u.prenom, ca.description
    FROM commandes c
    JOIN users u ON c.client_id = u.id -- MODIFICATION : Jointure sur users
    LEFT JOIN commande_articles ca ON c.id = ca.commande_id
    WHERE c.statut = 'En validation'
    GROUP BY c.id -- S'assure que chaque commande n'apparaît qu'une fois dans la liste
");
$stmt->execute();
$commandes_a_valider = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- La partie HTML n'a presque pas besoin de changements, sauf une petite correction -->

<div class="page-header">
    <h1>Validation des fichiers Clients</h1>
</div>

<div class="main-panels">
    <div class="panel-validation">
        <div class="panel-header">
            <h3>Commandes à Valider (<?php echo count($commandes_a_valider); ?>)</h3>
        </div>
        <div class="panel-body">
    <?php if (empty($commandes_a_valider)): ?>
        <p style="padding: 15px; color: #555;">Aucune commande en attente de validation.</p>
    <?php else: ?>
        <?php foreach ($commandes_a_valider as $cmd): ?>
            <a href="validation_fichiers.php?commande_id=<?php echo $cmd['id']; ?>" 
               <?php echo (isset($_GET['commande_id']) && $_GET['commande_id'] == $cmd['id']) ? 'style="background-color:#fcf3cf;"' : ''; ?>>
                <strong>#<?php echo htmlspecialchars($cmd['id']); ?>
                    <?php echo htmlspecialchars($cmd['prenom'] . ' ' . $cmd['nom']); ?></strong>
                <br>
                <small><?php echo htmlspecialchars($cmd['description']); ?></small>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
    </div>
    <div class="panel-commande">
        <?php if ($commande_details): ?>
            <div class="panel-header" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h3>Commande #<?php echo htmlspecialchars($commande_details['id']); ?></h3>
                    <!-- PETITE CORRECTION HTML : Le type de client n'existe plus de la même manière, on peut le retirer -->
                    <p class="yellow">
                        <?php echo htmlspecialchars($commande_details['prenom'] . ' ' . $commande_details['nom']); ?>
                    </p>
                    <p class="gray"><?php echo htmlspecialchars($commande_details['article_desc']); ?></p>
                </div>
                <div class="number">
                    <?php echo number_format($commande_details['total_ttc'], 0, ',', ' '); ?> AR
                </div>
            </div>

            <!-- Le reste de votre code HTML est parfait et n'a pas besoin d'être modifié -->

            <div class="panel-body">
                <div style="text-align:center; padding:20px; border:1px solid #eee; border-radius:8px; margin-bottom:20px;">
                    <?php if (!empty($commande_details['chemin_fichier'])): ?>
                        <img src="<?php echo htmlspecialchars($commande_details['chemin_fichier']); ?>" alt="Aperçu fichier"
                            style="max-width:300px; max-height:300px; margin-bottom: 20px; display: block; margin-left: auto; margin-right: auto;">
                        <a href="<?php echo htmlspecialchars($commande_details['chemin_fichier']); ?>"
                            download="<?php echo htmlspecialchars($commande_details['nom_fichier_original'] ?? 'fichier-client'); ?>"
                            style="display:inline-block; padding: 12px 25px; background-color: #8E44AD; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; cursor: pointer; border: none;">
                            ☑ Télécharger le fichier
                        </a>
                    <?php else: ?>
                        <p>Aucun fichier associé.</p>
                    <?php endif; ?>
                </div>

                <form method="POST">
                    <input type="hidden" name="commande_id" value="<?php echo $commande_details['id']; ?>">
                    <h4>Message de Retour Client</h4>
                    <div class="message-retour-client">
                        <div class="flex">
                            <div style="flex: 1;">
                                <label for="date_realisation">Date de Réalisation
                                    Estimée</label>
                                <?php $date_value = $commande_details['date_realisation_estimee'] ? date('Y-m-d', strtotime($commande_details['date_realisation_estimee'])) : ''; ?>
                                <input type="date" id="date_realisation" name="date_realisation"
                                    value="<?php echo $date_value; ?>"
                                    style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                            </div>
                            <div style="flex: 1;">
                                <label for="heure_realisation">Heure</label>
                                <?php $heure_value = $commande_details['date_realisation_estimee'] ? date('H:i', strtotime($commande_details['date_realisation_estimee'])) : ''; ?>
                                <input type="time" id="heure_realisation" name="heure_realisation"
                                    value="<?php echo $heure_value; ?>"
                                    style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                            </div>
                        </div>
                        <div class="remarque-clients">
                            <label for="remarques_generales">Remarques Générales sur la
                                Commande</label>
                            <textarea id="remarques_generales" name="remarques_generales" rows="4"
                                placeholder="Notes générales pour la production (interne)..."><?php echo htmlspecialchars($commande_details['notes_production'] ?? ''); ?></textarea>
                        </div>
                        <div class="validation-clients">
                            <label for="message_client">Message
                                de Validation/Rectifications pour le Client</label>
                            <textarea id="message_client" name="message_client" rows="4"
                                placeholder="Message visible par le client..."><?php echo htmlspecialchars($commande_details['notes_client'] ?? ''); ?></textarea>
                        </div>
                        <?php if ($commande_details['statut'] == 'En validation'): ?>
                            <div class="containair-btn">
                                <button type="submit" name="rejeter" class="btn-rejeter">Rejeter</button>
                                <button type="submit" name="valider" class="btn-valider">Valider</button>
                            </div>
                        <?php else: ?>
                            <p style="text-align:right; font-weight:bold;">Cette commande a déjà été traitée (Statut:
                                <?php echo htmlspecialchars($commande_details['statut']); ?>).
                            </p>
                        <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

<?php else: ?>
    <p style="text-align:center; padding:50px;">Sélectionnez une commande à gauche pour voir les détails.</p>
<?php endif; ?>
</div>
</div>

<?php require_once 'includes/footer.php'; ?>