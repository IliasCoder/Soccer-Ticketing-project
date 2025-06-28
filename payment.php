<?php
// Activer temporairement l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable error display in output
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/payment_error.log');

require_once 'config.php';
require_once 'database.php';
require_once 'mail_service.php';
require_once 'TicketGenerator.php';

// Log function
function logDebug($message, $data = null) {
    $logMessage = date('Y-m-d H:i:s') . " - " . $message;
    if ($data !== null) {
        $logMessage .= "\nData: " . print_r($data, true);
    }
    error_log($logMessage . "\n");
}

// En-têtes pour éviter les problèmes CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Si c'est une requête OPTIONS, on s'arrête ici
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Vérifier la méthode HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée. Seules les requêtes POST sont acceptées.');
    }

    // Vérifier le Content-Type
    if (!isset($_SERVER['CONTENT_TYPE']) || strpos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {
        throw new Exception('Content-Type invalide. Le Content-Type doit être application/json');
    }

    // Récupérer et décoder les données JSON
    $jsonData = file_get_contents('php://input');
    logDebug("Received JSON data:", $jsonData);

    $data = json_decode($jsonData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Données JSON invalides: " . json_last_error_msg());
    }

    // Vérifier les données requises
    if (!isset($data['customerInfo']) || !isset($data['cartItems']) || !isset($data['totalAmount'])) {
        throw new Exception("Données manquantes dans la requête");
    }

    logDebug("Parsed data:", $data);

    // Test database connection first
    $db = new Database();
    $testConnection = $db->testConnection();
    if (!$testConnection['success']) {
        throw new Exception("Database connection error: " . $testConnection['message']);
    }

    $conn = $db->getConnection();
    logDebug("Database connection successful");
    
    // Commencer une transaction
    $conn->beginTransaction();
    
    try {
        // 1. Créer ou récupérer l'utilisateur
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data['customerInfo']['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $userId = $user['id'];
            logDebug("Found existing user:", $userId);
            // Mettre à jour l'utilisateur
            $stmt = $conn->prepare("UPDATE users SET username = ?, phone = ? WHERE id = ?");
            $stmt->execute([$data['customerInfo']['username'], $data['customerInfo']['phone'] ?? '', $userId]);
        } else {
            logDebug("Creating new user");
            // Créer un nouvel utilisateur
            $stmt = $conn->prepare("INSERT INTO users (username, email, phone, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$data['customerInfo']['username'], $data['customerInfo']['email'], $data['customerInfo']['phone'] ?? '']);
            $userId = $conn->lastInsertId();
            logDebug("Created new user:", $userId);
        }
        
        // 2. Créer la commande
        logDebug("Creating order for user:", $userId);
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_status, created_at) VALUES (?, ?, 'completed', NOW())");
        $stmt->execute([$userId, $data['totalAmount']]);
        $orderId = $conn->lastInsertId();
        logDebug("Created order:", $orderId);
        
        // 3. Créer les tickets
        $tickets = [];
        foreach ($data['cartItems'] as $item) {
            logDebug("Processing cart item:", $item);
            
            // Récupérer les informations du match
            $stmt = $conn->prepare("SELECT * FROM matches WHERE id = ?");
            $stmt->execute([$item['matchId']]);
            $matchInfo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$matchInfo) {
                throw new Exception("Match non trouvé: " . $item['matchId']);
            }
            
            // Créer le ticket dans la base de données
            $stmt = $conn->prepare("INSERT INTO tickets (order_id, match_id, category_id, section_id, seat_number, price, status) 
                                  VALUES (?, ?, ?, ?, ?, ?, 'confirmed')");
            $stmt->execute([
                $orderId,
                $item['matchId'],
                $item['categoryId'],
                $item['sectionId'],
                $item['seatNumber'],
                $item['price']
            ]);
            
            $ticketId = $conn->lastInsertId();
            logDebug("Created ticket:", $ticketId);
            
            $tickets[] = [
                'id' => $ticketId,
                'match_name' => "{$matchInfo['home_team']} vs {$matchInfo['away_team']}",
                'match_date' => $matchInfo['match_date'],
                'stadium' => $matchInfo['stadium'],
                'category_id' => $item['categoryId'],
                'section_id' => $item['sectionId'],
                'seat_number' => $item['seatNumber'],
                'price' => $item['price']
            ];
        }
        
        // Si tout s'est bien passé, valider la transaction
        $conn->commit();
        logDebug("Transaction committed successfully");
        
        // Renvoyer la réponse de succès avec l'URL de redirection
        echo json_encode([
            'success' => true,
            'message' => 'Paiement traité avec succès',
            'orderId' => $orderId,
            'tickets' => $tickets,
            'redirectUrl' => 'confirmation.php?orderId=' . $orderId
        ]);
        exit; // Add exit here to prevent any additional output
        
    } catch (Exception $e) {
        // En cas d'erreur, annuler la transaction
        $conn->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    logDebug("Error occurred:", $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Une erreur est survenue lors du traitement du paiement',
        'message' => $e->getMessage()
    ]);
    exit; // Add exit here to prevent any additional output
}
?>
