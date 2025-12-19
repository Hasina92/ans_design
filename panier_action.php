<?php
// panier_action.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// --- FONCTION HTML PANIER ---
function genererHtmlPanier($panier) {
    if (empty($panier)) return '<div class="empty-cart-message" style="text-align:center; padding: 20px;"><p>Votre panier est vide.</p></div>';
    
    $html = '';
    foreach ($panier as $index => $item) {
        $prixTotal = number_format($item['prix_base'] * $item['quantite'], 0, ',', ' ');
        $nom = htmlspecialchars($item['nom']);
        
        $optionsHtml = '<ul style="font-size: 0.7em; color: #888; margin-bottom: 5px;">';
        if (isset($item['options']) && is_array($item['options'])) {
            foreach ($item['options'] as $k => $v) {
                $optionsHtml .= '<li>' . htmlspecialchars($k . ': ' . $v) . '</li>';
            }
        }
        $optionsHtml .= '</ul>';

        // Affichage fichiers joints
        $fileInfo = '';
        if (!empty($item['images'])) {
            $count = count($item['images']);
            $fileInfo = '<div style="font-size:0.7em; color:#007bff; margin-top:2px;">📎 ' . $count . ' fichier(s) joint(s)</div>';
        }

        $demandeInfo = '';
        if (!empty($item['demande'])) {
            $demandeInfo = '<div style="font-size:0.7em; color:#d9534f; margin-top:2px;">📝 Note incluse</div>';
        }

        $html .= '
        <div class="card-cart">
            <div class="card-img"><img src="assets/img/dimensions.svg" alt=""></div>
            <div class="card-text">
                <h3 class="name">' . $nom . ' <span style="font-size: 0.8em; color: #666;">(x' . $item['quantite'] . ')</span></h3>
                ' . $optionsHtml . '
                ' . $fileInfo . '
                ' . $demandeInfo . '
                <span class="price">' . $prixTotal . ' Ar</span>
            </div>
            <div class="remove" data-index="' . $index . '" style="cursor:pointer;"><img src="assets/img/close.svg" alt=""></div>
        </div>';
    }
    return $html;
}

$action = $_POST['action'] ?? '';

// --- ACTION : AJOUTER ---
if ($action === 'add') {
    if (!isset($_POST['produit_id']) || !isset($_POST['quantite'])) {
        echo json_encode(['success' => false, 'message' => 'Données manquantes.']); exit;
    }

    // 1. TRAITEMENT FICHIERS TEMPORAIRES
    $cheminsTemporaires = [];
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $tempDir = 'uploads/temp/'; 
        if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

        $totalFiles = count($_FILES['images']['name']);
        for ($i = 0; $i < $totalFiles; $i++) {
            $tmpName = $_FILES['images']['tmp_name'][$i];
            $ext = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
            
            // Nom unique temporaire
            $newFileName = 'temp_' . time() . '_' . uniqid() . '.' . $ext;
            $targetPath = $tempDir . $newFileName;

            if (move_uploaded_file($tmpName, $targetPath)) {
                $cheminsTemporaires[] = $targetPath;
            }
        }
    }

    $nouvelArticle = [
        'id' => $_POST['produit_id'],
        'nom' => $_POST['produit_nom'],
        'prix_base' => floatval($_POST['produit_prix']),
        'quantite' => intval($_POST['quantite']),
        'options' => $_POST['options'] ?? [],
        'demande' => $_POST['demande'] ?? '',
        'images' => $cheminsTemporaires // On stocke le chemin TEMP
    ];

    // 2. REGROUPEMENT (On ne regroupe PAS si fichiers ou demande texte différente)
    $produitTrouve = false;
    foreach ($_SESSION['panier'] as $index => $art) {
        $hasFiles = !empty($art['images']) || !empty($nouvelArticle['images']);
        $memeDemande = ($art['demande'] ?? '') === ($nouvelArticle['demande'] ?? '');

        if ($art['id'] == $nouvelArticle['id'] && $art['options'] == $nouvelArticle['options'] && $memeDemande && !$hasFiles) {
            $_SESSION['panier'][$index]['quantite'] += $nouvelArticle['quantite'];
            $produitTrouve = true;
            break;
        }
    }

    if (!$produitTrouve) $_SESSION['panier'][] = $nouvelArticle;

    echo json_encode([
        'success' => true,
        'message' => 'Ajouté au panier',
        'cart_count' => count($_SESSION['panier']),
        'cart_html' => genererHtmlPanier($_SESSION['panier'])
    ]);
    exit;
}

// --- ACTION : SUPPRIMER (Nettoyage Temp) ---
if ($action === 'delete') {
    $index = $_POST['index'] ?? -1;
    if (isset($_SESSION['panier'][$index])) {
        // Suppression physique des fichiers temporaires
        if (!empty($_SESSION['panier'][$index]['images'])) {
            foreach ($_SESSION['panier'][$index]['images'] as $file) {
                if (file_exists($file)) unlink($file);
            }
        }
        array_splice($_SESSION['panier'], $index, 1);
        echo json_encode(['success' => true, 'cart_html' => genererHtmlPanier($_SESSION['panier']), 'cart_count' => count($_SESSION['panier'])]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur index']);
    }
    exit;
}

// --- ACTION : VIDER (Nettoyage Temp) ---
if ($action === 'clear') {
    foreach ($_SESSION['panier'] as $art) {
        if (!empty($art['images'])) {
            foreach ($art['images'] as $file) {
                if (file_exists($file)) unlink($file);
            }
        }
    }
    $_SESSION['panier'] = [];
    echo json_encode(['success' => true, 'cart_html' => genererHtmlPanier([]), 'cart_count' => 0]);
    exit;
}
?>