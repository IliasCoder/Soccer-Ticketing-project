<?php
// Script de correction automatique des problèmes de base de données
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔧 Correction Automatique des Problèmes</h1>";

// Étape 1: Tenter différentes configurations de connexion
echo "<h2>1. Test de différentes configurations de connexion:</h2>";

$configurations = [
    ['host' => 'localhost', 'port' => '3306', 'user' => 'root', 'pass' => ''],
    ['host' => '127.0.0.1', 'port' => '3306', 'user' => 'root', 'pass' => ''],
    ['host' => 'localhost', 'port' => '3308', 'user' => 'root', 'pass' => ''],
    ['host' => '127.0.0.1', 'port' => '3308', 'user' => 'root', 'pass' => ''],
    ['host' => 'localhost', 'port' => '8889', 'user' => 'root', 'pass' => 'root'], // MAMP
];

$workingConfig = null;

foreach ($configurations as $config) {
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']}";
        $conn = new PDO($dsn, $config['user'], $config['pass']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<p style='color: green;'>✅ Configuration trouvée: {$config['host']}:{$config['port']} avec {$config['user']}</p>";
        $workingConfig = $config;
        break;
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ {$config['host']}:{$config['port']} - " . $e->getMessage() . "</p>";
    }
}

if ($workingConfig) {
    echo "<h2>2. Création/Vérification de la base de données:</h2>";
    
    try {
        // Connexion au serveur
        $dsn = "mysql:host={$workingConfig['host']};port={$workingConfig['port']}";
        $conn = new PDO($dsn, $workingConfig['user'], $workingConfig['pass']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Créer la base de données
        $dbname = 'ticket_platform';
        $conn->exec("CREATE DATABASE IF NOT EXISTS $dbname");
        echo "<p style='color: green;'>✅ Base de données '$dbname' créée/vérifiée</p>";
        
        // Se connecter à la base
        $conn->exec("USE $dbname");
        
        // Créer la table matches
        $sql = "CREATE TABLE IF NOT EXISTS matches (
            id INT AUTO_INCREMENT PRIMARY KEY,
            home_team VARCHAR(100) NOT NULL,
            away_team VARCHAR(100) NOT NULL,
            match_date DATE NOT NULL,
            stadium VARCHAR(200) DEFAULT 'Stade',
            match_time TIME DEFAULT '20:00:00',
            price DECIMAL(10,2) DEFAULT 25.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $conn->exec($sql);
        echo "<p style='color: green;'>✅ Table 'matches' créée/vérifiée</p>";
        
        // Vérifier si la table est vide et ajouter des données
        $stmt = $conn->query("SELECT COUNT(*) FROM matches");
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            echo "<p style='color: orange;'>⚠️ Table 'matches' vide, ajout de données de test...</p>";
            
            $matches = [
                ['PSG', 'Marseille', '2024-12-15', 'Parc des Princes', '20:00:00', 45.00],
                ['Lyon', 'Lille', '2024-12-20', 'Groupama Stadium', '18:00:00', 35.00],
                ['Monaco', 'Bordeaux', '2024-12-22', 'Stade Louis II', '19:00:00', 30.00],
                ['Nantes', 'Rennes', '2024-12-28', 'Stade de la Beaujoire', '17:00:00', 25.00],
                ['Nice', 'Strasbourg', '2025-01-05', 'Allianz Riviera', '20:00:00', 28.00],
                ['Toulouse', 'Montpellier', '2025-01-12', 'Stadium de Toulouse', '19:00:00', 22.00]
            ];
            
            $stmt = $conn->prepare("INSERT INTO matches (home_team, away_team, match_date, stadium, match_time, price) VALUES (?, ?, ?, ?, ?, ?)");
            
            foreach ($matches as $match) {
                $stmt->execute($match);
            }
            
            echo "<p style='color: green;'>✅ " . count($matches) . " matches ajoutés avec succès!</p>";
        } else {
            echo "<p style='color: green;'>✅ Table 'matches' contient déjà $count enregistrements</p>";
        }
        
        // Créer les autres tables nécessaires
        $tables = [
            'users' => "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                phone VARCHAR(20),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            'orders' => "CREATE TABLE IF NOT EXISTS orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                total_amount DECIMAL(10,2) NOT NULL,
                payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )",
            'tickets' => "CREATE TABLE IF NOT EXISTS tickets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                match_id INT NOT NULL,
                category_id VARCHAR(50) NOT NULL,
                section_id VARCHAR(50) NOT NULL,
                seat_number VARCHAR(20) NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                status ENUM('reserved', 'confirmed', 'cancelled') DEFAULT 'reserved',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (order_id) REFERENCES orders(id),
                FOREIGN KEY (match_id) REFERENCES matches(id)
            )"
        ];
        
        foreach ($tables as $tableName => $sql) {
            $conn->exec($sql);
            echo "<p style='color: green;'>✅ Table '$tableName' créée/vérifiée</p>";
        }
        
        echo "<h2>3. Mise à jour du fichier database.php:</h2>";
        
        // Créer/Mettre à jour database.php avec la configuration qui fonctionne
        $databasePhpContent = "<?php
class Database {
    private \$host = \"{$workingConfig['host']}\";
    private \$port = \"{$workingConfig['port']}\";
    private \$db_name = \"ticket_platform\";
    private \$username = \"{$workingConfig['user']}\";
    private \$password = \"{$workingConfig['pass']}\";
    public \$conn;

    public function getConnection() {
        \$this->conn = null;
        try {
            \$dsn = \"mysql:host={\$this->host};port={\$this->port};dbname={\$this->db_name};charset=utf8mb4\";
            \$this->conn = new PDO(\$dsn, \$this->username, \$this->password);
            \$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            \$this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return \$this->conn;
        } catch(PDOException \$exception) {
            throw new PDOException(\"Erreur de connexion à la base de données: \" . \$exception->getMessage());
        }
    }

    public function testConnection() {
        try {
            \$conn = \$this->getConnection();
            return ['success' => true, 'message' => 'Connexion réussie à la base de données'];
        } catch(PDOException \$e) {
            return ['success' => false, 'message' => \$e->getMessage()];
        }
    }
}
?>";
        
        file_put_contents('database.php', $databasePhpContent);
        echo "<p style='color: green;'>✅ Fichier database.php mis à jour avec la configuration qui fonctionne</p>";
        
        echo "<h2>4. Test final:</h2>";
        
        // Test final de l'API
        ob_start();
        include 'matches_simple.php';
        $apiOutput = ob_get_clean();
        
        $apiData = json_decode($apiOutput, true);
        if ($apiData && isset($apiData['success']) && $apiData['success']) {
            echo "<p style='color: green;'>✅ API matches_simple.php fonctionne parfaitement!</p>";
            echo "<p><strong>Matches disponibles:</strong> " . $apiData['count'] . "</p>";
        } else {
            echo "<p style='color: red;'>❌ Problème avec l'API matches_simple.php</p>";
            echo "<pre>" . htmlspecialchars($apiOutput) . "</pre>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erreur lors de la configuration: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<h2>❌ Aucune configuration de base de données fonctionnelle trouvée!</h2>";
    echo "<p>Vérifiez que votre serveur MySQL/MariaDB est démarré.</p>";
    echo "<p>Configurations testées:</p>";
    echo "<ul>";
    foreach ($configurations as $config) {
        echo "<li>{$config['host']}:{$config['port']} avec utilisateur '{$config['user']}'</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<h2>🎯 Prochaines étapes:</h2>";
echo "<ol>";
echo "<li><a href='test_connexion_complete.php'>Exécuter le diagnostic complet</a></li>";
echo "<li><a href='matches_simple.php' target='_blank'>Tester l'API Matches</a></li>";
echo "<li><a href='index.html' target='_blank'>Accéder à la plateforme de billetterie</a></li>";
echo "</ol>";
?>
