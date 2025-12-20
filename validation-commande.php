<?php
session_start(); // OBLIGATOIRE pour accéder à $_SESSION
require_once 'ans-design-backoffice/config/db.php'; // Pour récupérer les infos user
require_once 'init_user.php';

// Si le panier n'est pas vide et que l'utilisateur est connecté, on récupère ses infos
$user_details = [];
if (!empty($_SESSION['panier']) && isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT nom, prenom, email FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $user_details['nom_complet'] = trim($user['prenom'] . ' ' . $user['nom']);
            $user_details['email'] = $user['email'];
        }
    } catch (Exception $e) { /* Gérer l'erreur silencieusement */
    }
} else {
    // Si le panier est vide ou user non connecté, on redirige
    header('Location: catalogue.php');
    exit();
}

// Initialisation des variables pour le calcul des totaux
$sous_total_commande = 0;
$frais_livraison = 10000;
$total_commande = 0;
$la_commande_est_sur_devis = false;
?>

<?php
// On inclut le header qui contient déjà session_start() et la logique d'auth
include 'header.php';
?>
<!-- MAIN -->
<main>
    <section id="checkout" class="scrolltop">
        <div class="wrapper">
            <div class="section-title">
                <h2>Validation commande</h2>
            </div>
            <div class="container-checkout">
                <div class="client-info">
                    <section class="checkout-section">
                        <form class="checkout-form" action="traitement-commande.php" method="POST">
                            <!-- Informations personnelles -->
                            <div class="form-group">
                                <label for="fullname">Nom complet <span>*</span></label>
                                <!-- MODIFICATION 2 : Champ pré-rempli -->
                                <input type="text" id="fullname" name="fullname" placeholder="Votre nom complet"
                                    required
                                    value="<?php echo htmlspecialchars($user_details['nom_complet'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="email">Adresse e-mail <span>*</span></label>
                                <!-- MODIFICATION 2 : Champ pré-rempli -->
                                <input type="email" id="email" name="email" placeholder="exemple@mail.com" required
                                    value="<?php echo htmlspecialchars($user_details['email'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="phone">Numéro de téléphone <span>*</span></label>
                                <input type="tel" id="phone" name="phone" placeholder="Ex: +261 34 12 345 67" required>
                            </div>

                            <!-- Adresse de livraison -->
                            <div class="form-group">
                                <label for="address">Adresse de livraison <span>*</span></label>
                                <textarea id="address" name="adresse_livraison" rows="3"
                                    placeholder="Votre adresse complète" required></textarea>
                            </div>

                            <div class="form-group">
                                <label for="city">Ville <span>*</span></label>
                                <select id="city" name="city" required>
                                    <option value="">-- Sélectionnez une ville --</option>
                                    <option value="Antananarivo">Antananarivo</option>
                                    <option value="Toamasina">Toamasina</option>
                                    <option value="Antsirabe">Antsirabe</option>
                                    <option value="Fianarantsoa">Fianarantsoa</option>
                                    <option value="Mahajanga">Mahajanga</option>
                                    <option value="Toliara">Toliara</option>
                                    <option value="Antsiranana">Antsiranana</option>
                                    <option value="Ambanja">Ambanja</option>
                                    <option value="Ambatondrazaka">Ambatondrazaka</option>
                                    <option value="Moramanga">Moramanga</option>
                                    <option value="Maroantsetra">Maroantsetra</option>
                                    <option value="Nosy Be">Nosy Be</option>
                                    <option value="Manakara">Manakara</option>
                                    <option value="Mananjary">Mananjary</option>
                                    <option value="Sambava">Sambava</option>
                                    <option value="Vatomandry">Vatomandry</option>
                                    <option value="Betafo">Betafo</option>
                                    <option value="Ambositra">Ambositra</option>
                                    <option value="Ihosy">Ihosy</option>
                                    <option value="Farafangana">Farafangana</option>
                                    <option value="Amparafaravola">Amparafaravola</option>
                                    <option value="Fénérive-Est">Fénérive-Est</option>
                                    <option value="Andapa">Andapa</option>
                                    <option value="Ihosy">Ihosy</option>
                                    <!-- Ajoute d'autres villes si nécessaire -->
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="postal">Code postal</label>
                                <input type="text" id="postal" name="code_postal" placeholder="" required>
                            </div>

                            <!-- Méthode de paiement -->
                            <div class="form-group">
                                <label for="payment">Méthode de paiement <span>*</span></label>
                                <select id="payment" name="payment">
                                    <option value="">-- Choisir une méthode --</option>
                                    <option value="mobile-money">Mobile Money (Mvola, Orange Money...)</option>
                                    <option value="livraison">Paiement à la livraison</option>
                                    <option value="recuperation">Point de vente</option>
                                </select>
                            </div>

                            <!-- Section cachée pour Mobile Money -->
                            <div id="mobile-operators" class="hidden form-group">
                                <label for="operateur">Choisissez votre opérateur</label>
                                <select id="operateur" name="operateur">
                                    <option value="">-- Sélectionner un opérateur --</option>
                                    <option value="telma">Yas</option>
                                    <option value="airtel">Airtel</option>
                                    <option value="orange">Orange</option>
                                </select>
                            </div>

                            <!-- Champs distincts pour chaque opérateur -->
                            <div id="input-telma" class="hidden operator-input form-group">
                                <label for="numero-telma">Numéro MVola (038 12 345 67)</label>
                                <input type="text" id="numero-telma" name="numero-telma"
                                    placeholder="Veuillez indiquer la référence du paiement.">
                            </div>

                            <div id="input-airtel" class="hidden operator-input form-group">
                                <label for="numero-airtel">Numéro Airtel Money (033 12 345 67)</label>
                                <input type="text" id="numero-airtel" name="numero-airtel"
                                    placeholder="Veuillez indiquer la référence du paiement.">
                            </div>

                            <div id="input-orange" class="hidden operator-input form-group">
                                <label for="numero-orange">Numéro Orange Money (032 12 345 67)</label>
                                <input type="text" id="numero-orange" name="numero-orange"
                                    placeholder="Veuillez indiquer la référence du paiement.">
                            </div>

                            <!-- Notes -->
                            <div class="form-group">
                                <label for="notes">Notes (optionnel)</label>
                                <textarea id="notes" name="notes" rows="3"
                                    placeholder="Ajoutez une note à votre commande"></textarea>
                            </div>

                            <?php foreach ($_SESSION['panier'] as $article): ?>
                                <input type="hidden" name="demande_article[]"
                                    value="<?php echo htmlspecialchars($article['demande'] ?? ''); ?>">
                            <?php endforeach; ?>


                            <!-- Bouton -->
                            <button type="submit" class="btn-red">Valider la
                                commande</button>
                        </form>
                    </section>

                </div>

                <div class="resume-produit">
                    <h3>Produits</h3>
                    <div class="container-resume-produit">

                        <?php // On parcourt chaque article stocké dans le panier de la session ?>
                        <?php foreach ($_SESSION['panier'] as $index => $article): ?>
                            <?php
                            // Calcul du sous-total pour cet article spécifique
                            $sous_total_article = $article['prix_base'] * $article['quantite'];
                            // Ajout au sous-total général de la commande
                            $sous_total_commande += $sous_total_article;

                            // Si le prix de base d'un article est 0, toute la commande passe "Sur Devis"
                            if ($article['prix_base'] == 0) {
                                $la_commande_est_sur_devis = true;
                            }
                            ?>
                            <div class="card-resume-produit">
                                <div class="info-resume-produit">
                                    <div class="img-resume-produit">
                                        <img src="assets/img/carte_de_visite.png" alt="">
                                    </div>
                                    <div class="text-resume-produit">
                                        <span class="nom"><?php echo htmlspecialchars($article['nom']); ?></span>

                                        <!-- Ajout pour afficher les options choisies -->
                                        <div class="options-details"
                                            style="font-size: 0.8em; color: #555; margin-top: 4px;">
                                            <?php foreach ($article['options'] as $nom_option => $valeur_option): ?>
                                                <span><?php echo htmlspecialchars($nom_option); ?>:
                                                    <strong><?php echo htmlspecialchars($valeur_option); ?></strong></span><br>
                                            <?php endforeach; ?>
                                        </div>

                                        <span class="quantite">Quantité : <?php echo $article['quantite']; ?></span>
                                    </div>
                                </div>
                                <div class="prix-resume-produit">
                                    <span class="prix">
                                        <?php
                                        // Affiche "Sur Devis" si le prix est 0, sinon affiche le prix formaté
                                        echo ($article['prix_base'] == 0)
                                            ? 'Sur Devis'
                                            : number_format($sous_total_article, 0, ',', '.') . ' AR';
                                        ?>
                                    </span>
                                </div>
                                <!-- Vous pourriez ajouter un lien pour supprimer l'article ici plus tard -->
                                <!-- <a href="panier_action.php?action=remove&key=<?php echo $index; ?>" class="remove-item">X</a> -->
                            </div>
                        <?php endforeach; // Fin de la boucle sur les articles du panier ?>

                        <div class="livraison-resume-produit">
                            <span class="livraison">Livraison</span>
                            <span class="livraison-total"><?php echo number_format($frais_livraison, 0, ',', '.'); ?>
                                AR</span>
                        </div>
                        <div class="total-resume-produit">
                            <span class="total">Total</span>
                            <span class="prix-total">
                                <?php
                                // Si un produit est sur devis, le total est aussi sur devis.
                                if ($la_commande_est_sur_devis) {
                                    echo 'Sur Devis';
                                } else {
                                    // Sinon, on calcule le total final
                                    $total_commande = $sous_total_commande + $frais_livraison;
                                    echo number_format($total_commande, 0, ',', '.') . ' AR';
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const paymentSelect = document.getElementById("payment");
        const livraisonBlock = document.querySelector(".livraison-resume-produit");
        const totalBlock = document.querySelector(".total-resume-produit .prix-total");

        function updateLivraison() {
            if (paymentSelect.value === "recuperation") {
                // Masquer le bloc livraison
                if (livraisonBlock) livraisonBlock.style.display = "none";

                // Recalculer le total sans les frais de livraison
                let sousTotal = <?php echo $sous_total_commande; ?>;
                document.querySelector(".total-resume-produit .prix-total").textContent =
                    sousTotal > 0 ? sousTotal.toLocaleString('fr-FR') + " AR" : "Sur Devis";
            } else {
                // Afficher le bloc livraison
                if (livraisonBlock) livraisonBlock.style.display = "flex";

                // Recalculer le total avec les frais de livraison
                let sousTotal = <?php echo $sous_total_commande; ?>;
                let livraison = <?php echo $frais_livraison; ?>;
                let total = sousTotal + livraison;
                if (<?php echo $la_commande_est_sur_devis ? 'true' : 'false'; ?>) {
                    totalBlock.textContent = "Sur Devis";
                } else {
                    totalBlock.textContent = total.toLocaleString('fr-FR') + " AR";
                }
            }
        }

        if (paymentSelect) {
            paymentSelect.addEventListener("change", updateLivraison);
            // Appel initial pour masquer si déjà sélectionné
            updateLivraison();
        }
    });
</script>

<?php
include 'footer.php';
?>