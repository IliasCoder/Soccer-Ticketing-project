<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'ticket_platform');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuration SendGrid
define('SENDGRID_API_KEY', getenv('SENDGRID_API_KEY') ?: 'VOTRE_NOUVELLE_CLE_API');
define('SENDGRID_FROM_EMAIL', 'habibifatimazahrae14@gmail.com');
define('SENDGRID_FROM_NAME', 'Billetterie Sportive');

// Configuration de l'application
define('APP_URL', 'http://localhost/Finalya');
define('DEBUG_MODE', true);

// Fonction pour logger les erreurs
function logError($message) {
    if (DEBUG_MODE) {
        error_log($message);
    }
}

// Fuseau horaire
date_default_timezone_set('Europe/Paris');
?>
