<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Requête simple qui fonctionne avec votre structure existante
    $query = "SELECT 
                id,
                home_team,
                away_team,
                match_date,
                stadium as venue,
                created_at
              FROM matches 
              ORDER BY match_date ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Date actuelle pour calculer la proximité
    $currentDate = new DateTime();
    
    // Traiter chaque match
    foreach ($matches as &$match) {
        $match['id'] = intval($match['id']);
        
        // Heures variables selon l'ID
        switch ($match['id'] % 4) {
            case 1:
                $match['match_time'] = '20:00';
                break;
            case 2:
                $match['match_time'] = '18:00';
                break;
            case 3:
                $match['match_time'] = '19:00';
                break;
            default:
                $match['match_time'] = '17:00';
                break;
        }
        
        // Prix de base selon l'importance du match
        $basePrice = 20.00;
        if ($match['id'] % 3 == 0) {
            $basePrice = 30.00; // Match plus important
        } elseif ($match['id'] % 5 == 0) {
            $basePrice = 35.00; // Match très important
        }
        
        // Calculer la proximité du match
        $matchDate = new DateTime($match['match_date']);
        $daysUntilMatch = $currentDate->diff($matchDate)->days;
        
        // Si le match est dans le passé, calculer les jours depuis
        if ($matchDate < $currentDate) {
            $daysUntilMatch = -$daysUntilMatch;
        }
        
        // Augmenter le prix en fonction de la proximité (seulement pour les matches futurs)
        if ($daysUntilMatch > 0) {
            if ($daysUntilMatch <= 7) {
                $priceMultiplier = 1.5; // Dernière semaine: +50%
            } elseif ($daysUntilMatch <= 14) {
                $priceMultiplier = 1.3; // Deux semaines avant: +30%
            } elseif ($daysUntilMatch <= 30) {
                $priceMultiplier = 1.15; // Un mois avant: +15%
            } else {
                $priceMultiplier = 1.0; // Plus d'un mois: prix normal
            }
        } else {
            $priceMultiplier = 1.0; // Match passé: prix normal
        }
        
        // Prix final
        $match['price'] = round($basePrice * $priceMultiplier, 2);
        
        // Disponibilité (seulement les matches futurs)
        $match['available'] = $daysUntilMatch > 0;
        
        // Extraire seulement la date (sans l'heure)
        $match['match_date'] = date('Y-m-d', strtotime($match['match_date']));
        
        // Ajouter des informations pour l'affichage
        $match['days_until_match'] = abs($daysUntilMatch);
        $match['price_multiplier'] = $priceMultiplier;
        
        // S'assurer que venue existe
        if (!isset($match['venue']) || empty($match['venue'])) {
            $match['venue'] = 'Stade';
        }
        
        // Nettoyer les caractères spéciaux
        $match['home_team'] = html_entity_decode($match['home_team'], ENT_QUOTES, 'UTF-8');
        $match['away_team'] = html_entity_decode($match['away_team'], ENT_QUOTES, 'UTF-8');
        $match['venue'] = html_entity_decode($match['venue'], ENT_QUOTES, 'UTF-8');
    }
    
    // Filtrer pour ne garder que les matches disponibles (futurs)
    $availableMatches = array_filter($matches, function($match) {
        return $match['available'];
    });
    
    // Réindexer le tableau
    $availableMatches = array_values($availableMatches);
    
    echo json_encode([
        'success' => true,
        'count' => count($availableMatches),
        'total_matches' => count($matches),
        'matches' => $availableMatches
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur de base de données: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur générale: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
