<?php
// panier_action.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// --- FONCTION POUR GÉNÉRER LE HTML DU PANIER (Inchangée) ---
function genererHtmlPanier($panier)
{
    if (empty($panier)) {
        return '<div class="empty-cart-message" style="text-align:center; padding: 20px;"><p>Votre panier est vide.</p></div>';
    }

    $html = '';
    foreach ($panier as $index => $item) {
        $prixTotal = number_format($item['prix_base'] * $item['quantite'], 0, ',', ' ');
        $nom = htmlspecialchars($item['nom']);

        $optionsHtml = '';
        if (isset($item['options']) && is_array($item['options'])) {
            $optionsHtml .= '<ul style="font-size: 0.7em; color: #888; margin-bottom: 5px;">';
            foreach ($item['options'] as $k => $v) {
                // On affiche proprement Clé : Valeur
                $optionsHtml .= '<li>' . htmlspecialchars($k . ': ' . $v) . '</li>';
            }
            $optionsHtml .= '</ul>';
        }

        $html .= '
        <div class="card-cart">
            <div class="card-img">
                <img src="assets/img/dimensions.svg" alt="">
            </div>
            <div class="card-text">
                <h3 class="name">' . $nom . ' <span style="font-size: 0.8em; color: #666;">(x' . $item['quantite'] . ')</span></h3>
                ' . $optionsHtml . '
                <span class="price">' . $prixTotal . ' Ar</span>
            </div>
            <div class="remove" data-index="' . $index . '" style="cursor:pointer;">
                <img src="assets/img/close.svg" alt="">
            </div>
        </div>';
    }
    return $html;
}
// ----------------------------------------------

$action = $_POST['action'] ?? '';

// ACTION : AJOUTER AU PANIER (LOGIQUE MODIFIÉE ICI)
if ($action === 'add') {
    if (
        !isset($_POST['produit_id']) ||
        !isset($_POST['produit_nom']) ||
        !isset($_POST['produit_prix']) ||
        !isset($_POST['quantite'])
    ) {
        echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
        exit;
    }

    $nouvelArticle = [
        'id' => $_POST['produit_id'],
        'nom' => $_POST['produit_nom'],
        'prix_base' => floatval($_POST['produit_prix']),
        'quantite' => intval($_POST['quantite']),
        'options' => $_POST['options'] ?? [],
        'demande' => $_POST['demande'] ?? ''
    ];

    // --- DÉBUT DE LA LOGIQUE DE REGROUPEMENT ---
    $produitTrouve = false;

    // On parcourt le panier existant
    foreach ($_SESSION['panier'] as $index => $articleExistant) {
        // 1. Est-ce le même ID produit ?
        if ($articleExistant['id'] == $nouvelArticle['id']) {

            // 2. Est-ce que les options sont EXACTEMENT les mêmes ?
            // (Ex: Si je prends des Flyers "Mats", c'est différent de Flyers "Brillants", donc on ne regroupe pas)
            if (
                $articleExistant['options'] == $nouvelArticle['options'] &&
                ($articleExistant['demande'] ?? '') == ($nouvelArticle['demande'] ?? '')
            ) {
                // C'est exactement le même produit : on additionne la quantité
                $_SESSION['panier'][$index]['quantite'] += $nouvelArticle['quantite'];
                $produitTrouve = true;
                break; // On arrête la boucle car on a trouvé
            }
        }
    }

    // Si on n'a pas trouvé de produit identique, on l'ajoute comme nouvelle ligne
    if (!$produitTrouve) {
        $_SESSION['panier'][] = $nouvelArticle;
    }
    // --- FIN DE LA LOGIQUE DE REGROUPEMENT ---

    echo json_encode([
        'success' => true,
        'message' => 'Panier mis à jour !',
        'cart_count' => count($_SESSION['panier']), // Compte le nombre de lignes (pas la quantité totale)
        'cart_html' => genererHtmlPanier($_SESSION['panier']),
        'has_items' => true
    ]);
    exit;
}

// ACTION : SUPPRIMER UN ARTICLE
if ($action === 'delete') {
    $index = $_POST['index'] ?? -1;

    if ($index >= 0 && isset($_SESSION['panier'][$index])) {
        array_splice($_SESSION['panier'], $index, 1);

        echo json_encode([
            'success' => true,
            'message' => 'Produit retiré.',
            'cart_count' => count($_SESSION['panier']),
            'cart_html' => genererHtmlPanier($_SESSION['panier']),
            'has_items' => count($_SESSION['panier']) > 0
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Article introuvable.']);
    }
    exit;
}

// ACTION : VIDER LE PANIER
if ($action === 'clear') {
    $_SESSION['panier'] = [];
    echo json_encode([
        'success' => true,
        'cart_count' => 0,
        'cart_html' => genererHtmlPanier([]),
        'has_items' => false
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Action invalide.']);
?>