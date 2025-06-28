<?php
// Test simple de l'API matches
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Test de l'API Matches</h1>";

// Test de matches_simple.php
echo "<h2>Test de matches_simple.php:</h2>";
try {
    $url = 'http://localhost/Final/matches_simple.php'; // Ajustez le chemin selon votre configuration
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "<p style='color:red'>❌ Impossible d'accéder à matches_simple.php</p>";
        echo "<p>Vérifiez que le fichier existe et que votre serveur web fonctionne.</p>";
    } else {
        $data = json_decode($response, true);
        
        if ($data && isset($data['success']) && $data['success']) {
            echo "<p style='color:green'>✅ API fonctionne correctement!</p>";
            echo "<p>Nombre de matches trouvés: " . $data['count'] . "</p>";
            
            if (!empty($data['matches'])) {
                echo "<h3>Premiers matches:</h3>";
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>ID</th><th>Équipes</th><th>Date</th><th>Heure</th><th>Stade</th><th>Prix</th></tr>";
                
                foreach (array_slice($data['matches'], 0, 5) as $match) {
                    echo "<tr>";
                    echo "<td>" . $match['id'] . "</td>";
                    echo "<td>" . $match['home_team'] . " vs " . $match['away_team'] . "</td>";
                    echo "<td>" . $match['match_date'] . "</td>";
                    echo "<td>" . $match['match_time'] . "</td>";
                    echo "<td>" . $match['venue'] . "</td>";
                    echo "<td>" . $match['price'] . "€</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            }
        } else {
            echo "<p style='color:red'>❌ Erreur dans la réponse de l'API:</p>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erreur: " . $e->getMessage() . "</p>";
}

// Test direct de la base de données
echo "<h2>Test direct de la base de données:</h2>";
try {
    require_once 'database.php';
    
    $db = new Database();
    $conn = $db->getConnection();
    
    $query = "SELECT id, home_team, away_team, match_date, stadium as venue, '20:00' as match_time, 25.00 as price FROM matches LIMIT 5";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p style='color:green'>✅ Connexion directe à la base réussie!</p>";
    echo "<p>Matches récupérés: " . count($matches) . "</p>";
    
    if (!empty($matches)) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Équipes</th><th>Date</th><th>Stade</th></tr>";
        
        foreach ($matches as $match) {
            echo "<tr>";
            echo "<td>" . $match['id'] . "</td>";
            echo "<td>" . $match['home_team'] . " vs " . $match['away_team'] . "</td>";
            echo "<td>" . $match['match_date'] . "</td>";
            echo "<td>" . $match['venue'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erreur de connexion directe: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Prochaines étapes:</h2>";
echo "<ol>";
echo "<li><a href='matches_simple.php' target='_blank'>Tester matches_simple.php directement</a></li>";
echo "<li><a href='index.html' target='_blank'>Accéder à la plateforme de billetterie</a></li>";
echo "</ol>";
?>
