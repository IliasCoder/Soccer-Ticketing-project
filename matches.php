
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gestion des requêtes OPTIONS pour CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Vérifier d'abord la structure de votre table
    $describeQuery = "DESCRIBE matches";
    $describeStmt = $conn->prepare($describeQuery);
    $describeStmt->execute();
    $columns = $describeStmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    // Ajouter les colonnes manquantes si nécessaire
    $columnsToAdd = [];
    
    if (!in_array('match_time', $columns)) {
        $columnsToAdd[] = "ADD COLUMN match_time TIME DEFAULT '20:00:00'";
    }
    
    if (!in_array('price', $columns)) {
        $columnsToAdd[] = "ADD COLUMN price DECIMAL(10,2) DEFAULT 25.00";
    }
    
    // Ajouter les colonnes manquantes
    if (!empty($columnsToAdd)) {
        $alterQuery = "ALTER TABLE matches " . implode(', ', $columnsToAdd);
        $conn->exec($alterQuery);
    }
    
    // Construire la requête SELECT en fonction des colonnes disponibles
    $selectFields = [
        'id',
        'home_team',
        'away_team',
        'match_date',
        'created_at'
    ];
    
    // Ajouter les champs conditionnellement
    if (in_array('match_time', $columns)) {
        $selectFields[] = 'match_time';
    } else {
        $selectFields[] = "'20:00:00' as match_time";
    }
    
    if (in_array('price', $columns)) {
        $selectFields[] = 'price';
    } else {
        $selectFields[] = '25.00 as price';
    }
    
    if (in_array('venue', $columns)) {
        $selectFields[] = 'venue';
    } elseif (in_array('stadium', $columns)) {
        $selectFields[] = 'stadium as venue';
    } else {
        $selectFields[] = "'Stade' as venue";  // CORRECTION: apostrophe ajoutée
    }
    
    // Récupérer tous les matches
    $query = "SELECT " . implode(', ', $selectFields) . " FROM matches ORDER BY match_date ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Mettre à jour les prix par défaut si la colonne vient d'être ajoutée
    if (in_array("ADD COLUMN price DECIMAL(10,2) DEFAULT 25.00", $columnsToAdd)) {
        $updatePriceQuery = "UPDATE matches SET price = CASE 
            WHEN id % 4 = 1 THEN 45.00
            WHEN id % 4 = 2 THEN 35.00  
            WHEN id % 4 = 3 THEN 30.00
            ELSE 25.00
        END WHERE price = 25.00";
        $conn->exec($updatePriceQuery);
        
        // Récupérer à nouveau les matches avec les nouveaux prix
        $stmt->execute();
        $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Mettre à jour les heures par défaut si la colonne vient d'être ajoutée
    if (in_array("ADD COLUMN match_time TIME DEFAULT '20:00:00'", $columnsToAdd)) {
        $updateTimeQuery = "UPDATE matches SET match_time = CASE 
            WHEN id % 4 = 1 THEN '20:00:00'
            WHEN id % 4 = 2 THEN '18:00:00'  
            WHEN id % 4 = 3 THEN '19:00:00'
            ELSE '17:00:00'
        END WHERE match_time = '20:00:00'";
        $conn->exec($updateTimeQuery);
        
        // Récupérer à nouveau les matches avec les nouvelles heures
        $stmt->execute();
        $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Formater les données pour l'affichage
    foreach ($matches as &$match) {
        $match['price'] = floatval($match['price']);
        $match['id'] = intval($match['id']);
        
        // Formater la date et l'heure pour l'affichage
        $match['formatted_date'] = date('d/m/Y', strtotime($match['match_date']));
        $match['formatted_time'] = date('H:i', strtotime($match['match_time']));
        
        // S'assurer que venue existe
        if (!isset($match['venue']) || empty($match['venue'])) {
            $match['venue'] = 'Stade';
        }
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($matches),
        'matches' => $matches,
        'debug' => [
            'columns_found' => $columns,
            'columns_added' => $columnsToAdd
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur de base de données: ' . $e->getMessage(),
        'code' => $e->getCode()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur générale: ' . $e->getMessage()
    ]);
}
?>
