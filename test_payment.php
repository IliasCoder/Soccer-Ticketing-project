<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Test du Système de Paiement</h1>";

// 1. Tester la connexion à la base de données
echo "<h2>1. Test de la connexion à la base de données</h2>";
try {
    require_once 'database.php';
    $db = new Database();
    $conn = $db->getConnection();
    echo "<p style='color: green;'>✅ Connexion à la base de données réussie</p>";
    
    // Vérifier les tables nécessaires
    $tables = ['users', 'orders', 'tickets', 'matches'];
    foreach ($tables as $table) {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✅ Table '$table' existe</p>";
            
            // Afficher la structure de la table
            $stmt = $conn->query("DESCRIBE $table");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<details>";
            echo "<summary>Structure de la table $table</summary>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Clé</th></tr>";
            foreach ($columns as $column) {
                echo "<tr>";
                echo "<td>{$column['Field']}</td>";
                echo "<td>{$column['Type']}</td>";
                echo "<td>{$column['Null']}</td>";
                echo "<td>{$column['Key']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</details>";
        } else {
            echo "<p style='color: red;'>❌ Table '$table' n'existe pas!</p>";
        }
    }
    
    // 2. Préparer les données de test
    echo "<h2>2. Préparation des données de test</h2>";
    
    // Vérifier s'il y a des matches disponibles
    $stmt = $conn->query("SELECT * FROM matches LIMIT 1");
    $match = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$match) {
        echo "<p style='color: orange;'>⚠️ Aucun match trouvé, création d'un match de test...</p>";
        $stmt = $conn->prepare("INSERT INTO matches (home_team, away_team, match_date, match_time, stadium, price) 
                    VALUES ('PSG', 'Marseille', '2024-12-15', '20:00:00', 'Parc des Princes', 45.00)");
        $stmt->execute();
        $matchId = $conn->lastInsertId();
        
        $stmt = $conn->prepare("SELECT * FROM matches WHERE id = ?");
        $stmt->execute([$matchId]);
        $match = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    if (!isset($match['price']) || empty($match['price'])) {
        // Si le prix n'est pas défini, on met à jour le match avec un prix par défaut
        $stmt = $conn->prepare("UPDATE matches SET price = 45.00 WHERE id = ?");
        $stmt->execute([$match['id']]);
        $match['price'] = 45.00;
    }
    
    echo "<p style='color: green;'>✅ Match de test disponible: {$match['home_team']} vs {$match['away_team']}</p>";
    
    // 3. Tester le paiement
    echo "<h2>3. Test du paiement</h2>";
    
    $testData = [
        'customerInfo' => [
            'username' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '0612345678'
        ],
        'cartItems' => [
            [
                'matchId' => $match['id'],
                'matchName' => "{$match['home_team']} vs {$match['away_team']}",
                'categoryId' => 'standard',
                'sectionId' => 'A1',
                'seatNumber' => '123',
                'price' => floatval($match['price'])
            ]
        ],
        'totalAmount' => floatval($match['price'])
    ];
    
    echo "<p>Données de test préparées :</p>";
    echo "<pre>" . htmlspecialchars(json_encode($testData, JSON_PRETTY_PRINT)) . "</pre>";
    
    // Tester d'abord avec la page de confirmation
    echo "<h3>3.1 Test avec confirm_order.php</h3>";
    
    $ch = curl_init('http://localhost/Finalya/Final/confirm_order.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        echo "<p style='color: red;'>❌ Erreur CURL: " . curl_error($ch) . "</p>";
    }
    
    curl_close($ch);
    
    echo "<p>Code HTTP: $httpCode</p>";
    
    if ($httpCode === 200) {
        echo "<p style='color: green;'>✅ Redirection vers la page de confirmation</p>";
        echo "<p><a href='confirm_order.php' class='btn btn-primary' target='_blank'>Voir la page de confirmation</a></p>";
    } else {
        echo "<p style='color: red;'>❌ Erreur lors de la redirection</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}
?> 