<?php
// cart_overlay.php
require_once 'init_user.php';

// --- RECUPERATION DES COMMANDES ---
$mes_commandes = [];
if (isset($_SESSION['user_id'])) {
    // On récupère les 5 dernières commandes
    $stmt_cmd = $pdo->prepare("SELECT * FROM commandes WHERE client_id = ? ORDER BY date_commande DESC LIMIT 5");
    $stmt_cmd->execute([$_SESSION['user_id']]);
    $mes_commandes = $stmt_cmd->fetchAll(PDO::FETCH_ASSOC);

    // Pour chaque commande, on récupère le nom du premier article pour l'affichage résumé
    foreach ($mes_commandes as $k => $cmd) {
        $stmt_first = $pdo->prepare("SELECT description FROM commande_articles WHERE commande_id = ? LIMIT 1");
        $stmt_first->execute([$cmd['id']]);
        $art = $stmt_first->fetch(PDO::FETCH_ASSOC);
        $mes_commandes[$k]['titre_produit'] = $art ? $art['description'] : 'Commande diverse';
    }
}
?>

<section id="overlay">
    <!-- MINI CART -->
    <div class="wrapper" id="wrapper_mini_cart">
        <div class="header-mini-cart">
            <h2>Mon Espace</h2>
            <img src="assets/img/close.svg" alt="" id="close-button">
        </div>
        <div class="body-mini-cart">
            <ul class="tabslink-cart">
                <li>
                    <?php
                    $count = isset($_SESSION['panier']) ? count($_SESSION['panier']) : 0;
                    ?>
                    <a href="#panier">Panier <span class="number">(<?php echo $count; ?>)</span></a>
                </li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <li>
                        <a href="#mon_compte">Mon compte</a>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="container-nav-cart">
                <div class="tabscontent-cart" id="panier">
                    <!-- On enveloppe les items pour pouvoir les remplacer facilement via AJAX -->
                    <div class="cart-items-wrapper">
                        <?php if (empty($_SESSION['panier'])): ?>
                            <div class="empty-cart-message" style="text-align:center; padding: 20px;">
                                <p>Votre panier est vide.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($_SESSION['panier'] as $index => $item): ?>
                                <div class="card-cart">
                                    <div class="card-img">
                                        <!-- Idéalement, il faudrait stocker l'image dans la session ou la récupérer via l'ID -->
                                        <img src="assets/img/dimensions.svg" alt="Produit">
                                    </div>
                                    <div class="card-text">
                                        <h3 class="name">
                                            <?php echo htmlspecialchars($item['nom']); ?>
                                            <span
                                                style="font-size: 0.8em; color: #666;">(x<?php echo $item['quantite']; ?>)</span>
                                        </h3>
                                        <!-- Affichage des options choisies (facultatif mais utile) -->
                                        <ul style="font-size: 0.7em; color: #888; margin-bottom: 5px;">
                                            <?php foreach ($item['options'] as $key => $val): ?>
                                                <li><?php echo htmlspecialchars($key . ': ' . $val); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <span
                                            class="price"><?php echo number_format($item['prix_base'] * $item['quantite'], 0, ',', ' '); ?>
                                            Ar</span>
                                    </div>
                                    <div class="remove" data-index="<?php echo $index; ?>" style="cursor:pointer;">
                                        <img src="assets/img/close.svg" alt="Supprimer">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="container-bouton">
                        <a href="validation-commande.php" class="btn-yellow">Commander</a>
                        <a href="#" class="btn-red" id="btn-vider-panier">Vider le panier</a>
                    </div>
                </div>

                <!-- CONTENU MON COMPTE -->
                <div class="tabscontent-cart" id="mon_compte">
                    <div class="personal-information">
                        <div class="container-personal-information">
                            <img src="assets/img/profil_user.svg" alt="" class="profil">
                            <div class="text-information">
                                <h3>Bonjour, <?php echo htmlspecialchars($user_info['nom_complet']); ?></h3>
                                <span>Bienvenue dans votre espace.</span>
                            </div>
                        </div>
                        <a href="ans-design-backoffice/logout.php" class="log-out">
                            <img src="assets/img/log_out.svg" alt="">
                        </a>
                    </div>

                    <ul class="tabslink-personal-information">
                        <li><a href="#mes_commandes">Mes commandes</a></li>
                        <li><a href="#mes_fichiers">Mes fichiers</a></li>
                        <li><a href="#mes_devis">Mes devis</a></li>
                        <li><a href="#mon_profil">Mon Profil</a></li>
                    </ul>

                    <!-- ONGLETS COMMANDES DYNAMIQUE -->
                    <div class="tabscontent-personal-information" id="mes_commandes">
                        <?php if (empty($mes_commandes)): ?>
                            <p style="padding: 20px; text-align: center;">Aucune commande trouvée.</p>
                        <?php else: ?>
                            <?php foreach ($mes_commandes as $cmd): ?>
                                <div class="card-commande">
                                    <img src="assets/img/dimensions.svg" alt="" class="img-product"> <!-- Image placeholder -->
                                    <div class="info-product">
                                        <div class="coordonnees-product">
                                            <div class="date">
                                                <span>#<?php echo htmlspecialchars($cmd['numero_commande'] ?? $cmd['id']); ?></span>
                                                <span><?php echo date('d M. Y', strtotime($cmd['date_commande'])); ?></span>
                                            </div>
                                            <?php
                                            $statuts = [
                                                'En attente' => 'en-attente',
                                                'En production' => 'en-production',
                                                'Validation' => 'validation',
                                                'Livrée' => 'livree',
                                                'Annulé' => 'annule',
                                            ];

                                            // Sécurité si statut inconnu
                                            $etat_class = $statuts[$cmd['statut']] ?? 'en-attente';
                                            ?>
                                            <div class="etat <?= $etat_class ?>">
                                                <span><?= htmlspecialchars($cmd['statut']); ?></span>
                                            </div>
                                        </div>
                                        <div class="nom-product">
                                            <span><?php echo htmlspecialchars($cmd['titre_produit']); ?></span>
                                            <span><?php echo number_format($cmd['total_ttc'], 0, ',', ' '); ?> Ar</span>
                                        </div>
                                        <div class="bouton-product">
                                            <a href="#suivi" class="open-popup-suivi-trigger"
                                                data-id="<?php echo $cmd['id']; ?>"
                                                data-ref="<?php echo htmlspecialchars($cmd['numero_commande'] ?? $cmd['id']); ?>"
                                                data-statut="<?php echo htmlspecialchars($cmd['statut']); ?>"
                                                data-date="<?php echo date('d/m/y', strtotime($cmd['date_commande'])); ?>">
                                                Suivre
                                            </a>
                                            <a href="#detail" class="open-popup-detail-trigger"
                                                data-id="<?php echo $cmd['id']; ?>">
                                                Voir les détails
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="tabscontent-personal-information" id="mes_fichiers"></div>
                    <div class="tabscontent-personal-information" id="mes_devis"></div>
                    <div class="tabscontent-personal-information" id="mon_profil"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- POPUP ÉTAT COMMANDE SIMPLE -->
    <div class="popup popup-etat-commande" id="popup-etat-commande">
        <div class="popup-content">
            <span class="close-popup" id="close-popup-etat">&times;</span>

            <h3>Commande <span id="popup-ref"></span></h3>

            <div class="etat etat-popup" id="popup-etat">
                <span id="popup-statut"></span>
            </div>

            <p class="popup-date">
                Commandée le <span id="popup-date"></span>
            </p>
            <ul class="timeline-etapes">
                <li class="etape" data-step="recu">
                    <span class="label">Commande reçue</span>
                </li>
                <li class="etape" data-step="verification">
                    <span class="label">Vérification des fichiers</span>
                </li>
                <li class="etape" data-step="production">
                    <span class="label">En production</span>
                </li>
                <li class="etape" data-step="expedition">
                    <span class="label">Expédition</span>
                </li>
            </ul>
        </div>
    </div>


    <!-- DETAILS COMMANDE (POPUP GÉNÉRIQUE) -->
    <div class="popup pop-up-detail-commande">
        <div class="header-detail-commande">
            <h3>Détail de la commande #<span id="detail-ref"></span></h3>
            <img src="assets/img/close.svg" alt="" id="close-popup-detail">
        </div>
        <div class="body-detail-commande">
            <div id="detail-loading" style="text-align:center;">Chargement...</div>
            <div id="detail-content" style="display:none;">
                <div class="card-recapitulation">
                    <div class="img-recapitulation"><img src="assets/img/dimensions.svg" alt=""></div>
                    <div class="text-recapitulation">
                        <h4 id="detail-nom-produit">...</h4>
                        <span id="detail-prix">...</span>
                    </div>
                </div>
                <div class="livraison">
                    <h4>Livraison</h4>
                    <div class="card-livraison">
                        <span id="detail-livraison-nom"></span><br>
                        <span id="detail-livraison-adresse"></span><br>
                    </div>
                </div>
                <div class="paiement">
                    <h4>Récapitulatif</h4>
                    <div class="card-paiement">
                        <div class="sous-total"><span>Sous-total</span><span id="detail-sous-total"></span></div>
                        <div class="total-livraison"><span>Livraison</span><span>0 Ar</span></div>
                        <!-- A dynamiser si besoin -->
                        <div class="tva"><span>TVA (20%)</span><span id="detail-tva"></span></div>
                        <div class="total"><span>Total</span><span id="detail-total-ttc"></span></div>
                    </div>
                </div>
                <!-- Boutons actions (inchangés) -->
                <div class="bouton-detail">
                    <a href="#" id="btn-facture"><img src="assets/img/upload.svg" alt=""> Facture</a>
                    <a href=""><img src="assets/img/support.svg" alt=""> Support</a>
                </div>
            </div>
        </div>
    </div>
</section>