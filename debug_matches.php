<?php
require_once 'database.php';

echo "<h2>🔍 Debug - Récupération des matches</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<p style='color: green;'>✅ Connexion à la base de données réussie</p>";
    
    // Vérifier la structure de la table
    echo "<h3>📋 Structure de la table 'matches':</h3>";
    $describeQuery = "DESCRIBE matches";
    $describeStmt = $conn->prepare($describeQuery);
    $describeStmt->execute();
    $columns = $describeStmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
    echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Compter les matches
    $countQuery = $conn->query("SELECT COUNT(*) as count FROM matches");
    $count = $countQuery->fetch()['count'];
    echo "<p style='color: blue;'>📊 Nombre total de matches: <strong>" . $count . "</strong></p>";
    
    // Afficher tous les matches
    echo "<h3>🎯 Tous les matches dans la base:</h3>";
    $matchesQuery = $conn->query("SELECT * FROM matches ORDER BY match_date ASC");
    $matches = $matchesQuery->fetchAll();
    
    if (!empty($matches)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr>";
        foreach (array_keys($matches[0]) as $header) {
            echo "<th>" . $header . "</th>";
        }
        echo "</tr>";
        
        foreach ($matches as $match) {
            echo "<tr>";
            foreach ($match as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Aucun match trouvé dans la table</p>";
    }
    
    // Tester l'API matches.php
    echo "<h3>🔗 Test de l'API matches.php:</h3>";
    echo "<p><a href='matches.php' target='_blank'>Cliquez ici pour tester matches.php</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>⚙️ Configuration actuelle dans database.php:</h3>";
echo "<ul>";
echo "<li><strong>Host:</strong> localhost</li>";
echo "<li><strong>Port:</strong> 3306</li>";
echo "<li><strong>Base de données:</strong> ticket_platform</li>";
echo "<li><strong>Utilisateur:</strong> root</li>";
echo "<li><strong>Mot de passe:</strong> (vide)</li>";
echo "</ul>";
echo "<p><em>Modifiez ces paramètres dans database.php si nécessaire</em></p>";
?>
