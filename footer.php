<?php
// cart_overlay.php
// On inclut le fichier qui récupère les infos
require_once 'init_user.php';
?>
<!-- FOOTER -->
<footer>
    <div class="wrapper">
        <div class="container-information">
            <div class="card-information mail">
                <img src="assets/img/mail.svg" alt="">
                <a href="mailto:ans.designprint.annexe@gmail.com">ans.designprint.annexe@gmail.com</a>
                <a href="mailto:ans.designprint@gmail.com">ans.designprint@gmail.com</a>
            </div>
            <div class="card-information phone">
                <img src="assets/img/phone.svg" alt="">
                <a href="tel:+261346324272">+261 34 63 242 72</a>
                <a href="tel:+261342385631">+261 34 23 856 31</a>
            </div>
            <div class=" card-information maps">
                <img src="assets/img/location.svg" alt="">
                <a href="">Ambanidia, Rond Point Hazo tokana</a>
                <a href="">Ambanidia, Entre Total et Pharmacie</a>
            </div>
            <img src="assets/img/fond.png" alt="" class="bg-image">
        </div>
        <div class="container-additional-information">
            <div class="text-payement">
                <h3>Paiement sécurisé – Transactions protégées et options flexibles 24h/24</h3>
                <p class="small">Grâce à nos solutions de paiement en ligne sécurisées, vous bénéficiez d'une expérience
                    d'achat fluide et flexible, accessible 24h/24, avec la possibilité de choisir parmi plusieurs
                    options de règlement, tout en ayant l'assurance que vos transactions sont protégées par les
                    dernières technologies de sécurité.</p>
            </div>
            <div class="img-payement">
                <img src="assets/img/mvola.png" alt="">
                <img src="assets/img/airtel-money.png" alt="">
                <img src="assets/img/orange-money.svg" alt="">
                <img src="assets/img/visa.png" alt="">
            </div>
            <ul class="menu-footer">
                <li>
                    <a href="">A propos</a>
                </li>
                <li>
                    <a href="">Services</a>
                </li>
                <li>
                    <a href="">Portfolio</a>
                </li>
                <li>
                    <a href="">Blog</a>
                </li>
                <li>
                    <a href="">Devis</a>
                </li>
            </ul>
            <form action="" method="post" class="subscribe">
                <textarea name="" id="" placeholder="Votre message ..."></textarea>
                <button type="submit" class="btn-card">Envoyez</button>
            </form>
        </div>
        <ul class="social-icon-footer">
            <li>
                <a href="" target="_blank"><img src="assets/img/facebook.svg" alt=""></a>
            </li>
            <li>
                <a href="" target="_blank"><img src="assets/img/whatsapp.svg" alt=""></a>
            </li>
            <li>
                <a href="" target="_blank"><img src="assets/img/instagram.svg" alt=""></a>
            </li>
            <li>
                <a href="" target="_blank"><img src="assets/img/linkedin.svg" alt=""></a>
            </li>
            <li>
                <a href=".scrolltop" class="fleche-footer">
                    <img src="assets/img/arrow-up.svg" alt="">
                </a>
            </li>
        </ul>
    </div>
</footer>
<script src="assets/libs/jquery/jquery.min.js"></script>
<script src="assets/libs/fancybox/fancybox.min.js"></script>
<script src="assets/libs/slick/slick.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.13.0/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.13.0/ScrollTrigger.min.js"></script>
<script src="assets/js/main.js"></script>
<script>
    $(document).ready(function () {

        $('.open-popup-detail-catalogue').on('click', function (e) {
            e.preventDefault();

            // Récupérer les données
            let nom = $(this).data('nom');
            let description = $(this).data('description');
            let image = $(this).data('image');
            let id = $(this).data('id');

            // Injecter dans le popup
            $('.popup-produit-nom').text(nom);
            $('.popup-produit-description').text(description);
            $('.popup-produit-image').attr('src', image);
            $('.popup-produit-lien').attr('href', 'etape.php?id=' + id);

            // Affichage
            $('.pop-up-detail-catalogue').fadeIn();
        });

        // Fermeture
        $('.close-popup-detail-catalogue').on('click', function () {
            $('.pop-up-detail-catalogue').fadeOut();
        });

    });
</script>

<script>
    $(document).ready(function () {

        // --- 1. CONFIGURATION INITIALE ---
        let selectionUtilisateur = {};
        const nomProduit = $('section#etape-page .section-title h2').text().replace('Configurer: ', '');

        // --- 2. FONCTION DE MISE A JOUR DE L'APERCU ---
        function mettreAJourApercu() {
            const container = $('.quote-preview-container');
            container.empty();

            let htmlApercu = `<div class="quote-preview-card"><h4>${nomProduit}</h4><ul>`;
            $('.config-tab .tabscontent-etape[data-caracteristique-nom]').each(function () {
                const nomCarac = $(this).data('caracteristique-nom');
                const valeurChoisie = selectionUtilisateur[nomCarac] || '<span>Non sélectionné</span>';
                htmlApercu += `<li><span>${nomCarac}:</span><span>${valeurChoisie}</span></li>`;
            });
            htmlApercu += `</ul></div>`;
            container.html(htmlApercu);
        }

        // Gestion du clic sur les options (cartes)
        $('.tabscontent-etape .card-etape').on('click', function () {
            const $carteCliquee = $(this);
            $carteCliquee.siblings().removeClass('selected');
            $carteCliquee.addClass('selected');

            const conteneurCarac = $carteCliquee.closest('.tabscontent-etape');
            const nomCarac = conteneurCarac.data('caracteristique-nom');
            const valeurOption = $carteCliquee.find('h3').text().trim();

            selectionUtilisateur[nomCarac] = valeurOption;
            mettreAJourApercu();
        });

        // Appel initial
        mettreAJourApercu();


        // --- 3. FONCTION DE MISE A JOUR DU PANIER (UI) ---
        function updateCartUI(response) {
            // 1. Mise à jour Panier
            $('.tabslink-cart a[href="#panier"] .number').text('(' + response.cart_count + ')');
            $('#panier .cart-items-wrapper').html(response.cart_html);

            if (response.has_items) {
                $('#panier .container-bouton').show();
            } else {
                $('#panier .container-bouton').hide();
            }

            // 2. Mise à jour "Mon Compte"
            // On vérifie que la variable existe
            if (response.nom_client) {
                // Sécurité : Si le serveur renvoie "Client" alors qu'on avait déjà un vrai nom affiché,
                // on ne fait rien (on garde l'ancien nom).
                // Sinon, on met à jour.
                var currentText = $('.text-information h3').text();

                // Si le serveur a trouvé un vrai nom (différent de Client), on l'affiche
                if (response.nom_client !== 'Client') {
                    $('.text-information h3').text('Bonjour, ' + response.nom_client);
                }
                // Si on n'est pas connecté (Client) et que l'écran affiche Client, on laisse Client.
            }
        }

        // --- 4. AJOUT AU PANIER (LA CORRECTION EST ICI) ---

        // On utilise .off() pour nettoyer les anciens clics et on ajoute un drapeau 'processing'
        $('#open-popup-ajout-panier').off('click').on('click', function (e) {
            e.preventDefault();

            // IMPORTANT : Empêche main.js ou d'autres scripts de s'exécuter en même temps sur ce clic
            e.stopImmediatePropagation();

            // Si une requête est déjà en cours, on ne fait rien (Anti-Double Clic)
            if ($(this).data('processing')) return;

            // On verrouille le bouton
            $(this).data('processing', true);
            $(this).css('opacity', '0.5'); // Optionnel : effet visuel

            // Récupération des données
            const sectionProduit = $('#etape-page');
            const produitId = sectionProduit.data('produit-id');
            const produitNom = sectionProduit.data('produit-nom');
            const produitPrix = sectionProduit.data('produit-prix');
            const quantite = $('#etape_quantite input[name="quantite"]').val();

            // Validation Quantité
            if (!quantite || parseInt(quantite) < 1) {
                alert('Veuillez spécifier une quantité valide.');
                $(this).data('processing', false).css('opacity', '1'); // On déverrouille
                return;
            }

            // Validation Options
            const totalCaracteristiques = $('.config-tab .tabscontent-etape[data-caracteristique-nom]').length;
            if (Object.keys(selectionUtilisateur).length < totalCaracteristiques) {
                alert('Veuillez sélectionner une option pour chaque caractéristique.');
                $(this).data('processing', false).css('opacity', '1'); // On déverrouille
                return;
            }

            // Envoi AJAX
            var $btn = $(this); // Référence au bouton pour l'utiliser dans ajax

            $.ajax({
                url: 'panier_action.php',
                method: 'POST',
                data: {
                    action: 'add',
                    produit_id: produitId,
                    produit_nom: produitNom,
                    produit_prix: produitPrix,
                    quantite: quantite,
                    options: selectionUtilisateur,
                    demande: $('#etape-page textarea[name="demande"]').val() || ''
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        // On ouvre la popup seulement maintenant que c'est confirmé
                        $('.pop-up-ajout-panier').addClass('active').fadeIn();

                        // On met à jour l'interface
                        updateCartUI(response);
                    } else {
                        alert(response.message);
                    }
                },
                error: function () {
                    alert('Une erreur est survenue lors de la communication avec le serveur.');
                },
                complete: function () {
                    // QUOI QU'IL ARRIVE (Succès ou Erreur), on déverrouille le bouton
                    $btn.data('processing', false).css('opacity', '1');
                }
            });
        });

        // --- 5. SUPPRESSION ET VIDAGE (Delegation d'événements) ---

        $(document).on('click', '.card-cart .remove', function () {
            const index = $(this).data('index');
            $.ajax({
                url: 'panier_action.php',
                method: 'POST',
                data: { action: 'delete', index: index },
                dataType: 'json',
                success: function (response) {
                    if (response.success) updateCartUI(response);
                }
            });
        });

        $(document).on('click', '#btn-vider-panier', function (e) {
            e.preventDefault();
            if (confirm('Voulez-vous vraiment vider votre panier ?')) {
                $.ajax({
                    url: 'panier_action.php',
                    method: 'POST',
                    data: { action: 'clear' },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) updateCartUI(response);
                    }
                });
            }
        });

        // --- 6. GESTION FERMETURE POPUP AJOUT ---
        // Note : On gère manuellement car main.js peut entrer en conflit
        $('#close-popup-ajout-panier, .pop-up-ajout-panier .btn').on('click', function (e) {
            // Si c'est le bouton "Voir le panier", on laisse faire le lien ou on ouvre l'overlay
            if ($(this).hasClass('open-button')) {
                // Laissez main.js gérer l'ouverture du mini-cart ou faites-le ici
                $('.pop-up-ajout-panier').removeClass('active').fadeOut();
                return;
            }

            // Si c'est "Valider la commande", on laisse le lien fonctionner
            if ($(this).attr('href').indexOf('validation') !== -1) {
                return;
            }

            e.preventDefault();
            $('.pop-up-ajout-panier').removeClass('active').fadeOut();
        });
    });
</script>

<!-- SCRIPT JS POUR GERER LES POPUPS DYNAMIQUES -->
<script>
    $(document).ready(function () {

        // --- 1. POPUP SUIVI ---
        $('.open-popup-suivi-trigger').on('click', function (e) {
            e.preventDefault();

            // Récupérer les données du bouton cliqué
            var ref = $(this).data('ref');
            var statut = $(this).data('statut'); // ex: 'en_attente', 'production'
            var date = $(this).data('date');

            // Remplir la popup
            $('#suivi-ref').text(ref);
            $('#date-recu').text(date);

            // Reset des étapes (enlever les styles "active" ou images)
            $('.card-etape').css('opacity', '0.5'); // Exemple visuel : tout grisé par défaut

            // Logique simple pour activer les étapes selon le statut
            // Adaptez les 'case' selon les vrais statuts de votre BDD
            $('#etape-recu').css('opacity', '1'); // Toujours actif

            if (statut === 'verification' || statut === 'production' || statut === 'expedition' || statut === 'livre') {
                $('#etape-verification').css('opacity', '1');
            }
            if (statut === 'production' || statut === 'expedition' || statut === 'livre') {
                $('#etape-production').css('opacity', '1');
            }
            if (statut === 'expedition' || statut === 'livre') {
                $('#etape-expedition').css('opacity', '1');
            }

            // Afficher la popup
            $('.pop-up-suivi-commande').addClass('active').fadeIn();
        });

        $('#close-popup-suivi').on('click', function () {
            $('.pop-up-suivi-commande').removeClass('active').fadeOut();
        });


        // --- 2. POPUP DETAILS (AJAX) ---
        $(document).on('click', '.open-popup-detail-trigger', function (e) {
            e.preventDefault();
            var id = $(this).data('id');

            // Afficher popup en mode chargement
            $('.pop-up-detail-commande').addClass('active').fadeIn();
            $('#detail-loading').show();
            $('#detail-content').hide();

            console.log("Envoi requête AJAX pour ID: " + id); // LOG 1

            // Appel AJAX vers api_commande.php
            $.ajax({
                url: 'api_commande.php',
                method: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function (response) {
                    console.log("Réponse reçue :", response); // LOG 2

                    if (response.success) {
                        var cmd = response.commande;

                        $('#detail-ref').text(cmd.numero);

                        if (response.articles.length > 0) {
                            $('#detail-nom-produit').text(response.articles[0].description);
                        } else {
                            $('#detail-nom-produit').text("Article divers");
                        }

                        $('#detail-prix').text(cmd.total + ' Ar');
                        $('#detail-livraison-nom').text(cmd.livraison_nom);

                        $('#detail-sous-total').text(cmd.sous_total + ' Ar');
                        $('#detail-tva').text(cmd.tva + ' Ar');
                        $('#detail-total-ttc').text(cmd.total + ' Ar');

                        $('#detail-loading').hide();
                        $('#detail-content').fadeIn();
                    } else {
                        alert('Erreur API : ' + response.message);
                        $('.pop-up-detail-commande').fadeOut();
                    }
                },
                error: function (xhr, status, error) {
                    // AFFICHER L'ERREUR RÉELLE
                    console.error("Erreur AJAX :", status, error);
                    console.log("Réponse serveur :", xhr.responseText);

                    alert('Erreur de connexion (Voir console F12 pour détails).\nStatus: ' + status);
                    $('.pop-up-detail-commande').fadeOut();
                }
            });
        });

        $('#close-popup-detail').on('click', function () {
            $('.pop-up-detail-commande').removeClass('active').fadeOut();
        });

    });
</script>

<script>
    document.querySelector("#form-devis").addEventListener("submit", function (e) {
        e.preventDefault();

        let form = new FormData(this);

        fetch("create_devis.php", {
            method: "POST",
            body: form
        })
            .then(r => r.json())
            .then(data => {
                const msg = document.getElementById("msg-devis");

                if (data.success) {
                    msg.style.color = "green";
                    msg.innerHTML = data.message;
                    this.reset();
                } else {
                    msg.style.color = "red";
                    msg.innerHTML = data.message;
                }
            });
    });
</script>

<script>
    // POP-UP TECHNOLOGIES DYNAMIQUE
    document.addEventListener("DOMContentLoaded", () => {
        const openButtons = document.querySelectorAll(".open-popup-tech");
        const closeButtons = document.querySelectorAll(".pop-up-technologie .close-popup");

        openButtons.forEach(btn => {
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                const popupId = btn.getAttribute("data-popup-id");
                const popup = document.getElementById(popupId);
                if (popup) popup.classList.add("active");
            });
        });

        closeButtons.forEach(btn => {
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                const popup = btn.closest(".pop-up-technologie");
                if (popup) popup.classList.remove("active");
            });
        });

        // Fermer en cliquant à l’extérieur du contenu
        document.querySelectorAll(".pop-up-technologie").forEach(popup => {
            popup.addEventListener("click", (e) => {
                if (e.target === popup) popup.classList.remove("active");
            });
        });
    });
</script>

<script>
    // POP-UP EQUIPE DYNAMIQUE
    document.addEventListener("DOMContentLoaded", () => {
        const openButtons = document.querySelectorAll(".open-popup-equipe");
        const closeButtons = document.querySelectorAll(".pop-up-info-equipe .close-popup");

        // Ouvrir le pop-up correspondant
        openButtons.forEach(btn => {
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                const popupId = btn.getAttribute("data-popup-id");
                const popup = document.getElementById(popupId);
                if (popup) popup.classList.add("active");
            });
        });

        // Fermer via le bouton
        closeButtons.forEach(btn => {
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                const popup = btn.closest(".pop-up-info-equipe");
                if (popup) popup.classList.remove("active");
            });
        });

        // Fermer en cliquant en dehors du contenu
        document.querySelectorAll(".pop-up-info-equipe").forEach(popup => {
            popup.addEventListener("click", (e) => {
                if (e.target === popup) popup.classList.remove("active");
            });
        });
    });

</script>

</body>

</html>