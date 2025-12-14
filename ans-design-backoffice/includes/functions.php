<?php
/**
 * Retourne la classe CSS correspondante pour un statut de commande.
 * @param string $status Le statut de la commande.
 * @return string La classe CSS.
 */
function getStatusClass(string $status): string
{
    switch ($status) {
        case 'En production':
            return 'production';
        case 'En validation':
            return 'validation';
        case 'Livrée':
            return 'livree';
        case 'Annulé':
            return 'annule';
        case 'Avis à valider':
            return 'validation'; // ou une autre couleur
        default:
            return '';
    }
}

/**
 * Formate un nombre en devise EUR avec un espace comme séparateur de milliers.
 * @param float $number Le nombre à formater.
 * @return string Le nombre formaté en devise.
 */
function format_currency(float $number): string
{
    return number_format($number, 2, ',', ' ') . ' €';
}
?>