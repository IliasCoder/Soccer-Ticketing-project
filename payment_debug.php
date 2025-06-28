<?php
// Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Log PHP errors to file
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Headers CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Log request details
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Request URI: " . $_SERVER['REQUEST_URI']);

// Fonction de logging
function debugLog($type, $data) {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $type,
        'data' => $data
    ];
    file_put_contents('payment_debug.log', json_encode($logEntry, JSON_PRETTY_PRINT) . "\n---\n", FILE_APPEND);
    // Also log to PHP error log
    error_log("Debug [$type]: " . json_encode($data));
}

// Log raw input
debugLog('raw_input', ['raw' => file_get_contents('php://input')]);
debugLog('headers', getallheaders());

try {
    // Get JSON input
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);
    
    debugLog('decoded_data', $data);
    
    // Validate input
    $errors = [];
    if (json_last_error() !== JSON_ERROR_NONE) {
        $errors[] = "Données JSON invalides: " . json_last_error_msg();
    }
    if (empty($data['customerInfo'])) {
        $errors[] = "Information client manquante";
    }
    if (empty($data['cartItems'])) {
        $errors[] = "Panier vide ou invalide";
    }
    if (!isset($data['totalAmount'])) {
        $errors[] = "Montant total invalide";
    }
    
    if (!empty($errors)) {
        debugLog('validation_errors', $errors);
        http_response_code(400);
        echo json_encode(['error' => 'Validation failed', 'details' => $errors]);
        exit;
    }
    
    // If validation passes, return success for testing
    echo json_encode([
        'success' => true,
        'message' => 'Test de paiement réussi',
        'received_data' => $data
    ]);
    
} catch (Exception $e) {
    debugLog('error', $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Une erreur est survenue',
        'details' => $e->getMessage()
    ]);
}
?> 